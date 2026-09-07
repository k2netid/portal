import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import Can from '@/shared/components/auth/Can.vue';
import { vCan, vRole } from '@/shared/directives/permission';
import { useAuthStore } from '@/modules/Core/System/stores/auth';

describe('RBAC UI Primitives (Can.vue & v-can / v-role directives)', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('renders default slot when user has permission', () => {
    const authStore = useAuthStore();
    authStore.isAuthenticated = true;
    authStore.user = {
      id: '1',
      name: 'Editor User',
      email: 'editor@example.com',
      roles: [{ id: '1', name: 'editor', guard_name: 'web' }],
      permissions: [{ id: '1', name: 'edit content', guard_name: 'web' }],
    } as any;

    const wrapper = mount(Can, {
      props: { permission: 'edit content' },
      slots: {
        default: '<button class="edit-btn">Edit</button>',
        fallback: '<span class="no-access">No Access</span>',
      },
    });

    expect(wrapper.find('.edit-btn').exists()).toBe(true);
    expect(wrapper.find('.no-access').exists()).toBe(false);
  });

  it('renders fallback slot when user lacks permission', () => {
    const authStore = useAuthStore();
    authStore.isAuthenticated = true;
    authStore.user = {
      id: '2',
      name: 'Author User',
      email: 'author@example.com',
      roles: [{ id: '2', name: 'author', guard_name: 'web' }],
      permissions: [{ id: '2', name: 'create content', guard_name: 'web' }],
    } as any;

    const wrapper = mount(Can, {
      props: { permission: 'publish content' },
      slots: {
        default: '<button class="publish-btn">Publish</button>',
        fallback: '<button class="disabled-btn" disabled>Locked</button>',
      },
    });

    expect(wrapper.find('.publish-btn').exists()).toBe(false);
    expect(wrapper.find('.disabled-btn').exists()).toBe(true);
  });

  it('super admin bypasses permission check and always renders default slot', () => {
    const authStore = useAuthStore();
    authStore.isAuthenticated = true;
    authStore.user = {
      id: '99',
      name: 'Super User',
      email: 'super@example.com',
      roles: [{ id: '99', name: 'super', guard_name: 'web' }],
      permissions: [],
    } as any;

    const wrapper = mount(Can, {
      props: { permission: 'any random non-existent permission' },
      slots: {
        default: '<div class="allowed">Allowed</div>',
      },
    });

    expect(wrapper.find('.allowed').exists()).toBe(true);
  });

  it('v-can directive removes element when user lacks permission', () => {
    const authStore = useAuthStore();
    authStore.isAuthenticated = true;
    authStore.user = {
      id: '3',
      name: 'Basic User',
      email: 'basic@example.com',
      roles: [{ id: '3', name: 'member', guard_name: 'web' }],
      permissions: [{ id: '3', name: 'view profile', guard_name: 'web' }],
    } as any;

    const Component = {
      template: '<div><button id="del-btn" v-can="\'delete users\'">Delete</button></div>',
    };

    const wrapper = mount(Component, {
      global: {
        directives: { can: vCan },
      },
    });

    expect(wrapper.find('#del-btn').exists()).toBe(false);
  });

  it('v-can.disabled directive disables element instead of removing', () => {
    const authStore = useAuthStore();
    authStore.isAuthenticated = true;
    authStore.user = {
      id: '4',
      name: 'Author User',
      email: 'author@example.com',
      roles: [{ id: '4', name: 'author', guard_name: 'web' }],
      permissions: [{ id: '4', name: 'edit content', guard_name: 'web' }],
    } as any;

    const Component = {
      template: '<div><button id="pub-btn" v-can.disabled="\'publish content\'">Publish</button></div>',
    };

    const wrapper = mount(Component, {
      global: {
        directives: { can: vCan },
      },
    });

    const btn = wrapper.find('#pub-btn');
    expect(btn.exists()).toBe(true);
    expect(btn.attributes('disabled')).toBeDefined();
    expect(btn.classes()).toContain('cursor-not-allowed');
  });

  it('v-role directive removes element when user does not have the required role', () => {
    const authStore = useAuthStore();
    authStore.isAuthenticated = true;
    authStore.user = {
      id: '5',
      name: 'Author User',
      email: 'author@example.com',
      roles: [{ id: '4', name: 'author', guard_name: 'web' }],
      permissions: [],
    } as any;

    const Component = {
      template: '<div><button id="admin-panel" v-role="\'admin\'">Admin Panel</button></div>',
    };

    const wrapper = mount(Component, {
      global: {
        directives: { role: vRole },
      },
    });

    expect(wrapper.find('#admin-panel').exists()).toBe(false);
  });
});

