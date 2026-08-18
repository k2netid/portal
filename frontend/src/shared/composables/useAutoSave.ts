import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, onUnmounted, watch, type Ref, type ComputedRef } from 'vue';
import api from '@/engine/api/client';

interface AutoSaveOptions {
    interval?: number | Ref<number> | ComputedRef<number> | (() => number);
    enabled?: boolean | Ref<boolean> | ComputedRef<boolean> | (() => boolean);
    onSave?: (data: unknown) => void;
    onError?: (error: unknown) => void;
    shouldSave?: (form: Record<string, unknown>) => boolean;
}

interface AutoSaveReturn {
    isSaving: Ref<boolean>;
    lastSaved: Ref<Date | null>;
    saveStatus: Ref<'idle' | 'saving' | 'saved' | 'error'>;
    hasChanges: Ref<boolean>;
    saveNow: () => Promise<void>;
    formatLastSaved: () => string;
    startAutoSave: () => void;
    stopAutoSave: () => void;
}

const normalizePublishedAtForApi = (value: unknown): unknown => {
    if (typeof value !== 'string' || value.trim() === '') return value;

    // HTML datetime-local value (no timezone) -> convert to explicit UTC ISO.
    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)) {
        const date = new Date(value);
        if (!Number.isNaN(date.getTime())) {
            return date.toISOString();
        }
    }

    return value;
};

/**
 * Auto-save composable for content editor
 */
export function useAutoSave(form: Ref<Record<string, unknown>>, contentId: Ref<number | null | string> | number | string | null = null, options: AutoSaveOptions = {}): AutoSaveReturn {
    const {
        interval = 30000, // 30 seconds
        enabled: enabledOption = true,
        onSave = null,
        onError = null,
    } = options;

    // Support both boolean and getter function for enabled
    const enabled = typeof enabledOption === 'function'
        ? computed(enabledOption)
        : (typeof enabledOption === 'object' && enabledOption !== null && 'value' in enabledOption
            ? enabledOption
            : ref(enabledOption)) as Ref<boolean>;
    const intervalMs = typeof interval === 'function'
        ? computed(interval)
        : (typeof interval === 'object' && interval !== null && 'value' in interval
            ? interval
            : ref(interval)) as Ref<number>;

    const isSaving = ref(false);
    const lastSaved = ref<Date | null>(null);
    const saveStatus = ref<'idle' | 'saving' | 'saved' | 'error'>('idle'); // idle, saving, saved, error
    const autoSaveInterval = ref<ReturnType<typeof setInterval> | null>(null);
    const hasChanges = ref(false);
    const lastSavedData = ref<Record<string, unknown> | null>(null);

    // Track form changes
    const checkChanges = () => {
        if (!lastSavedData.value) {
            hasChanges.value = true;
            return;
        }

        try {
            const currentData = JSON.stringify(form.value || {});
            const savedData = JSON.stringify(lastSavedData.value || {});
            hasChanges.value = currentData !== savedData;
        } catch (e) {
            logger.warning('AutoSave: Failed to compare changes', e);
            hasChanges.value = true; // Assume changed on error
        }
    };

    // Watch form for changes
    watch(
        () => form.value,
        () => {
            hasChanges.value = true;
            checkChanges();
        },
        { deep: true }
    );

    // Helper to get enabled value
    const getEnabled = () => {
        return enabled.value;
    };

    // Auto-save function
    const performAutoSave = async () => {
        // Don't save if session terminated, no changes, already saving, or disabled
        if (window.__isSessionTerminated || !hasChanges.value || isSaving.value || !getEnabled()) {
            if (window.__isSessionTerminated) stopAutoSave();
            return;
        }

        // Check custom validator or default title check
        const shouldSaveFn = options.shouldSave;
        if (shouldSaveFn) {
            if (!shouldSaveFn(form.value)) {
                return;
            }
        } else if (!form.value.title || (form.value.title as string).trim() === '') {
            // Default check: must have title
            return;
        }

        isSaving.value = true;
        saveStatus.value = 'saving';

        try {
            let response;
            const currentContentId = typeof contentId === 'object' && contentId !== null && 'value' in contentId
                ? contentId.value
                : contentId;

            // Prepare payload (tags should already be in form.value if using formWithTags)
            const payload = {
                ...form.value,
                published_at: normalizePublishedAtForApi(form.value.published_at),
                // Keep current status for existing content; only new drafts default to draft.
                status: currentContentId ? (form.value.status || 'draft') : 'draft',
            };

            if (currentContentId) {
                // Update existing content
                response = await api.patch(`/manage/publishing/contents/${currentContentId}/autosave`, payload);
            } else {
                // Create new draft
                response = await api.post('/manage/publishing/contents/autosave', payload);

                // If new content was created, update contentId
                if (response.data?.id) {
                    if (typeof contentId === 'object' && contentId !== null && 'value' in contentId) {
                        contentId.value = response.data.id;
                    } else {
                        // Can't update primitive contentId, but that's okay
                        logger.warning('Auto-save created content but contentId is not reactive');
                    }
                }
            }

            lastSaved.value = new Date();
            saveStatus.value = 'saved';
            hasChanges.value = false;
            try {
                lastSavedData.value = JSON.parse(JSON.stringify(form.value || {}));
            } catch (e) {
                logger.warning('AutoSave: Failed to update last saved state', e);
            }

            // Callback
            if (onSave) {
                onSave(response.data);
            }

            // Reset status after 3 seconds
            setTimeout(() => {
                if (saveStatus.value === 'saved') {
                    saveStatus.value = 'idle';
                }
            }, 3000);
        } catch (error) {
            logger.error('Auto-save failed:', error);
            saveStatus.value = 'error';

            // Callback
            if (onError) {
                onError(error);
            }

            // Reset error status after 5 seconds
            setTimeout(() => {
                if (saveStatus.value === 'error') {
                    saveStatus.value = 'idle';
                }
            }, 5000);
        } finally {
            isSaving.value = false;
        }
    };

    // Start auto-save interval
    const startAutoSave = () => {
        if (!getEnabled()) return;
        stopAutoSave();

        // Initial save after 5 seconds if there are changes
        setTimeout(() => {
            if (hasChanges.value) {
                performAutoSave();
            }
        }, 5000);

        // Then save every interval
        autoSaveInterval.value = setInterval(() => {
            performAutoSave();
        }, intervalMs.value);
    };

    // Stop auto-save
    const stopAutoSave = () => {
        if (autoSaveInterval.value) {
            clearInterval(autoSaveInterval.value);
            autoSaveInterval.value = null;
        }
    };

    // Manual save trigger
    const saveNow = async () => {
        await performAutoSave();
    };

    // Format last saved time
    const formatLastSaved = () => {
        if (!lastSaved.value) return 'Not saved yet';

        const now = new Date();
        const diff = now.getTime() - lastSaved.value.getTime();
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);

        if (seconds < 10) return 'Just now';
        if (seconds < 60) return `${seconds}s ago`;
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;

        return lastSaved.value.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    // Initialize
    onMounted(() => {
        try {
            lastSavedData.value = JSON.parse(JSON.stringify(form.value || {}));
        } catch (e) {
            logger.warning('AutoSave: Failed to clone initial state', e);
            lastSavedData.value = {};
        }

        if (getEnabled()) {
            startAutoSave();
        }
    });

    // Watch enabled flag
    watch(enabled, (newValue) => {
        if (newValue && !autoSaveInterval.value) {
            startAutoSave();
        } else if (!newValue && autoSaveInterval.value) {
            stopAutoSave();
        }
    });

    watch(intervalMs, (newValue, oldValue) => {
        if (newValue === oldValue) return;
        if (!getEnabled()) return;
        if (!autoSaveInterval.value) return;
        startAutoSave();
    });

    // Cleanup
    onUnmounted(() => {
        stopAutoSave();
    });

    return {
        isSaving,
        lastSaved,
        saveStatus,
        hasChanges,
        saveNow,
        formatLastSaved,
        startAutoSave,
        stopAutoSave,
    };
}
