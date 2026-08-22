import { defineStore } from 'pinia';
import { ref, markRaw, type Component } from 'vue';
import type { User } from '@/engine/types/auth';

export interface DashboardEntry {
    id: string;
    priority: number;
    condition: (user: User, authStore: unknown) => boolean;
    component: Component;
}

export const useDashboardStore = defineStore('dashboard', () => {
    const registry = ref<DashboardEntry[]>([]);

    const registerDashboard = (entry: DashboardEntry) => {
        // Use markRaw to prevent Vue from making the component reactive
        registry.value.push({
            ...entry,
            component: markRaw(entry.component)
        });
        
        // Sort by priority descending
        registry.value.sort((a, b) => b.priority - a.priority);
    };

    const getActiveDashboard = (user: User, authStore: unknown) => {
        const match = registry.value.find(entry => entry.condition(user, authStore));
        return match ? match.component : null;
    };

    return {
        registry,
        registerDashboard,
        getActiveDashboard
    };
});
