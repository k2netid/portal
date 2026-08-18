import { defineStore } from 'pinia'
import axios from 'axios'
import type { AxiosResponse } from 'axios'
import { logger } from '@/shared/utils/logger'
import i18n from '@/engine/i18n'
import type { Composer } from 'vue-i18n'
import { getCanvasFingerprint } from '@/shared/utils/fingerprint'

const composer = i18n.global as unknown as Composer
const t = composer.t.bind(composer)

export const useSecurityStore = defineStore('security', {
    state: () => ({
        isShieldVisible: false,
        shieldProgress: 0,
        shieldStatus: t('system.security.shield.challenge.status.initializing'),
        lastChallenge: null as { nonce: string; difficulty: number } | null,
    }),

    actions: {
        showShield() {
            this.isShieldVisible = true
            this.shieldProgress = 0
            this.shieldStatus = t('system.security.shield.challenge.status.verifying')
        },

        hideShield() {
            this.isShieldVisible = false
            this.shieldProgress = 0
        },

        updateShield(progress: number, status: string) {
            this.shieldProgress = progress
            this.shieldStatus = status
        },

        async solveChallenge(nonce: string, difficulty: number): Promise<string | null> {
            this.lastChallenge = { nonce, difficulty }
            this.showShield()
            this.updateShield(10, t('system.security.shield.challenge.status.analyzing'))

            try {
                const solution = await this.calculatePoW(nonce, difficulty)

                if (solution !== null) {
                    this.updateShield(80, t('system.security.shield.challenge.status.finalizing'))
                    
                    const fingerprint = getCanvasFingerprint()
                    const success = await this.verifyOnBackend(nonce, solution, fingerprint)

                    if (success) {
                        this.updateShield(100, t('system.security.shield.challenge.status.verified'))
                        setTimeout(() => this.hideShield(), 500)
                        return String(solution)
                    }
                }

                throw new Error('Verification failed')
            } catch (error) {
                logger.error('Security challenge failed:', error)
                this.updateShield(0, t('system.security.shield.challenge.status.failed'))
                setTimeout(() => this.hideShield(), 2000)
                return null
            }
        },

        async calculatePoW(nonce: string, difficulty: number): Promise<number | null> {
            const target = '0'.repeat(difficulty)
            let solution = 0
            const maxAttempts = 1000000

            // Simple interval-based chunking to keep UI responsive if not using worker
            const encoder = new TextEncoder()

            // We'll process in chunks of 5000 to allow UI updates
            while (solution < maxAttempts) {
                for (let i = 0; i < 5000; i++) {
                    const data = encoder.encode(nonce + solution)
                    const hashBuffer = await crypto.subtle.digest('SHA-256', data)
                    const hashArray = Array.from(new Uint8Array(hashBuffer))
                    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('')

                    if (hashHex.startsWith(target)) {
                        return solution
                    }
                    solution++
                }

                // Update progress slightly
                this.shieldProgress = Math.min(70, this.shieldProgress + 2)
                // Yield to main thread
                await new Promise(resolve => setTimeout(resolve, 0))
            }

            return null
        },

        async verifyOnBackend(nonce: string, solution: number, fingerprint?: string): Promise<boolean> {
            try {
                // Use a direct axios call to avoid circular interceptor issues
                const response: AxiosResponse<{ success: boolean; data: { verified: boolean } }> = await axios.post('/api/v1/security/verify-connection', {
                    nonce,
                    solution: String(solution),
                    fingerprint,
                }, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })

                const payload = response.data
                return payload.success && payload.data.verified
            } catch {
                return false
            }
        }
    }
})
