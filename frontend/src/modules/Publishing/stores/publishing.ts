import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { PublishingService } from '@/modules/Publishing/services/publishingService';
import type { CMSState, Content } from '@/modules/Publishing/types/content';
import { useSystemStore } from '@/modules/Core/System/stores/system';


export const usePublishingStore = defineStore('Jejakawan', {
    state: (): CMSState => ({
        contents: [],
        categories: [],
        settings: {}, // Store settings by group or flat key-value
        currentContent: null,
        loading: false,
        loadingGroups: {}, // To track loading state for specific settings groups
        settingsPromises: {}, // To store promises for ongoing settings group fetches
        publicSettingsLoaded: false,
    }),

    actions: {
        isAuthenticatedLocally(): boolean {
            const userRaw = localStorage.getItem('user');
            if (!userRaw) return false;

            try {
                const parsed = JSON.parse(userRaw);
                return !!parsed && typeof parsed === 'object';
            } catch {
                return false;
            }
        },

        async fetchSettingsGroup(group: string) {
            // If already loading, return existing promise
            if (this.loadingGroups[group]) {
                return this.settingsPromises[group];
            }

            // Mark this group as loading
            this.loadingGroups = { ...this.loadingGroups, [group]: true };

            // Create and store the promise for this fetch operation
            const promise = (async () => {
                try {
                    const response = await PublishingService.settingsGroup(group);
                    const settingsData = response.data || {};
                    
                    // Only update if there are actual new keys or changed values to prevent reactive thrashing
                    const hasChanges = Object.entries(settingsData).some(([key, value]) => this.settings[key] !== value);
                    if (hasChanges) {
                        this.settings = { ...this.settings, ...settingsData };
                    }
                    return settingsData;
                }
                catch (error: unknown) {
                    logger.error(`Error fetching ${group} settings:`, error);
                    return {};
                } finally {
                    this.loadingGroups = { ...this.loadingGroups, [group]: false };
                    delete this.settingsPromises[group];
                }
            })();

            this.settingsPromises = { ...this.settingsPromises, [group]: promise };
            return promise;
        },


        async fetchContents(params: Record<string, unknown> = {}) {
            this.loading = true;
            try {
                const response = await PublishingService.publicContents(params);
                const { data } = parseResponse(response);
                this.contents = ensureArray(data);
                return { data: this.contents };
            } catch (error: unknown) {
                logger.error('Error fetching contents:', error);
                this.contents = [];
                return { data: [] };
            } finally {
                this.loading = false;
            }
        },

        async fetchContent(slug: string): Promise<Content | null> {
            this.loading = true;
            try {
                const response = await PublishingService.publicContent(slug);
                this.currentContent = response.data;
                return response.data;
            } catch (error: unknown) {
                logger.error('Error fetching content:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const response = await PublishingService.publicCategories();
                const { data } = parseResponse(response);
                this.categories = ensureArray(data);
                return this.categories;
            } catch (error: unknown) {
                logger.error('Error fetching categories:', error);
                this.categories = [];

                return [];
            }
        },
        
        async fetchPublicSettings() {
            if (this.publicSettingsLoaded) return this.settings;
            try {
                if (!this.isAuthenticatedLocally()) {
                    // Skip fetching /manage/ endpoints for guest users to prevent 401 redirect loops.
                    // Instead, fetch canonical public settings.
                    const systemStore = useSystemStore();
                    await systemStore.fetchPublicSettings();
                    this.publicSettingsLoaded = true;
                    return this.settings;
                }
                // Fetch basic Jejakawan and Layout settings as "public" settings (when logged in)
                await Promise.all([
                    this.fetchSettingsGroup('Jejakawan'),
                    this.fetchSettingsGroup('layout')
                ]);
                this.publicSettingsLoaded = true;
                return this.settings;
            } catch (error: unknown) {
                logger.error('Error fetching public settings:', error);
                return this.settings;
            }
        },

    },
});
