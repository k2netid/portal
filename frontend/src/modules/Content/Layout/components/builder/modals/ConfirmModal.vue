<template>
  <BaseModal
    :is-open="isOpen"
    :title="title"
    :width="400"
    :show-close="false"
    :close-on-backdrop="false"
  >
    <div class="confirm-modal-content">
      <div class="confirm-icon" :class="iconClass">
        <component :is="iconComponent" :size="28" />
      </div>
      <p class="confirm-message">{{ message }}</p>
    </div>

    <template #footer>
      <BaseButton variant="secondary" @click="handleCancel">
        {{ cancelText }}
      </BaseButton>
      <BaseButton :variant="confirmVariant" @click="handleConfirm">
        {{ confirmText }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';

import { BaseModal, BaseButton } from '@/components/builder/ui';

interface Props {
  isOpen?: boolean;
  title?: string;
  message?: string;
  confirmText?: string;
  cancelText?: string;
  type?: 'danger' | 'warning' | 'info' | 'delete' | 'error' | string;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  title: 'Confirm',
  message: 'Are you sure?',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'warning'
});

const emit = defineEmits<{
  (e: 'confirm'): void;
  (e: 'cancel'): void;
}>();

const iconComponent = computed(() => {
  switch (props.type) {
    case 'danger':
      return AlertCircle;
    case 'delete':
      return Trash2;
    case 'info':
      return Info;
    case 'warning':
    default:
      return AlertTriangle;
  }
});

const iconClass = computed(() => `icon-${props.type}`);

const confirmVariant = computed(() => {
  return props.type === 'danger' || props.type === 'delete' ? 'danger' : 'primary';
});

const handleConfirm = () => {
  emit('confirm');
};

const handleCancel = () => {
  emit('cancel');
};
</script>

<style scoped>
.confirm-modal-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
}

.confirm-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
}

.icon-danger,
.icon-delete {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.icon-warning {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.icon-info {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.confirm-message {
  margin: 0;
  font-size: 14px;
  line-height: 1.6;
  color: var(--builder-text-secondary);
  max-width: 300px;
}
</style>
