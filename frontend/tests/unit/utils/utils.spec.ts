import { describe, it, expect } from 'vitest'

// Test utility functions that can be tested in isolation

describe('Utility Functions', () => {
    describe('formatDate', () => {
        const formatDate = (dateString: string) => {
            if (!dateString) return ''
            const date = new Date(dateString)
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date)
        }

        it('formats a valid date string', () => {
            const result = formatDate('2026-01-30T11:00:00Z')
            expect(result).toContain('Jan')
            expect(result).toContain('30')
            expect(result).toContain('2026')
        })

        it('returns empty string for empty input', () => {
            expect(formatDate('')).toBe('')
        })

        it('handles ISO date format', () => {
            const result = formatDate('2025-12-25T14:30:00.000Z')
            expect(result).toContain('Dec')
            expect(result).toContain('25')
            expect(result).toContain('2025')
        })
    })

    describe('parseUserAgent', () => {
        const parseUserAgent = (userAgent: string) => {
            if (!userAgent) return 'Unknown'
            if (userAgent.includes('Chrome')) return 'Chrome'
            if (userAgent.includes('Firefox')) return 'Firefox'
            if (userAgent.includes('Safari')) return 'Safari'
            if (userAgent.includes('Edge')) return 'Edge'
            if (userAgent.includes('Opera')) return 'Opera'
            return userAgent.substring(0, 50) + (userAgent.length > 50 ? '...' : '')
        }

        it('detects Chrome browser', () => {
            const ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'
            expect(parseUserAgent(ua)).toBe('Chrome')
        })

        it('detects Firefox browser', () => {
            const ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Firefox/121.0'
            expect(parseUserAgent(ua)).toBe('Firefox')
        })

        it('detects Safari browser', () => {
            const ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) Safari/605.1.15'
            expect(parseUserAgent(ua)).toBe('Safari')
        })

        it('detects Edge browser', () => {
            const ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0.0.0'
            expect(parseUserAgent(ua)).toBe('Edge')
        })

        it('detects Opera browser', () => {
            const ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Opera/100.0.0.0'
            expect(parseUserAgent(ua)).toBe('Opera')
        })

        it('returns Unknown for empty user agent', () => {
            expect(parseUserAgent('')).toBe('Unknown')
        })

        it('truncates unknown long user agents', () => {
            const longUA = 'A'.repeat(100)
            const result = parseUserAgent(longUA)
            expect(result.length).toBeLessThanOrEqual(53)
            expect(result).toContain('...')
        })
    })

    describe('cn utility (class concatenation)', () => {
        // Simple cn implementation for testing
        const cn = (...classes: (string | undefined | null | false)[]): string => {
            return classes.filter(Boolean).join(' ')
        }

        it('concatenates class names', () => {
            expect(cn('class-a', 'class-b')).toBe('class-a class-b')
        })

        it('filters out falsy values', () => {
            expect(cn('class-a', undefined, 'class-b', null, 'class-c', false)).toBe('class-a class-b class-c')
        })

        it('handles empty input', () => {
            expect(cn()).toBe('')
        })

        it('handles single class', () => {
            expect(cn('single-class')).toBe('single-class')
        })
    })
})

describe('Validation Helpers', () => {
    describe('Email validation', () => {
        const isValidEmail = (email: string): boolean => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return emailRegex.test(email)
        }

        it('validates correct email formats', () => {
            expect(isValidEmail('user@example.com')).toBe(true)
            expect(isValidEmail('test.user@domain.org')).toBe(true)
            expect(isValidEmail('user+tag@example.co.uk')).toBe(true)
        })

        it('rejects invalid email formats', () => {
            expect(isValidEmail('invalid')).toBe(false)
            expect(isValidEmail('user@')).toBe(false)
            expect(isValidEmail('@domain.com')).toBe(false)
            expect(isValidEmail('user domain.com')).toBe(false)
        })
    })

    describe('Slug generation', () => {
        const generateSlug = (text: string): string => {
            return text
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w-]+/g, '')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '')
        }

        it('converts text to lowercase slug', () => {
            expect(generateSlug('Hello World')).toBe('hello-world')
        })

        it('handles special characters', () => {
            expect(generateSlug('Hello! World?')).toBe('hello-world')
        })

        it('handles multiple spaces', () => {
            expect(generateSlug('Hello    World')).toBe('hello-world')
        })

        it('removes trailing hyphens', () => {
            expect(generateSlug('Hello World!')).toBe('hello-world')
        })

        it('handles empty string', () => {
            expect(generateSlug('')).toBe('')
        })
    })
})
