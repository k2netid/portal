<template>
  <BaseModal
    :is-open="isOpen"
    :title="title"
    :width="400"
    @close="handleCancel"
  >
    <div class="input-modal-body">
      <p v-if="message" class="input-message">{{ message }}</p>
      
      <div class="input-wrapper">
        <input 
          ref="inputRef"
          v-model="inputValue"
          type="text"
          class="modal-input"
          :placeholder="placeholder"
          @keydown.enter="handleConfirm"
          @keydown.esc="handleCancel"
        />
      </div>

      <div class="modal-actions">
        <button class="btn-cancel" @click="handleCancel">
          {{ cancelText }}
        </button>
        <button class="btn-confirm" @click="handleConfirm">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { BaseModal } from '@/components/builder/ui';

interface Props {
  isOpen?: boolean;
  title?: string;
  message?: string;
  placeholder?: string;
  initialValue?: string;
  confirmText?: string;
  cancelText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  title: '',
  message: '',
  placeholder: '',
  initialValue: '',
  confirmText: 'OK',
  cancelText: 'Cancel'
});

const emit = defineEmits<{
  (e: 'confirm', value: string): void;
  (e: 'cancel'): void;
}>();

const inputValue = ref('');
const inputRef = ref<HTMLInputElement | null>(null);

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    inputValue.value = props.initialValue;
    nextTick(() => {
      inputRef.value?.focus();
      inputRef.value?.select();
    });
  }
});

const handleConfirm = () => {
  emit('confirm', inputValue.value);
};

const handleCancel = () => {
  emit('cancel');
};
</script>

<style scoped>
.input-modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.input-message {
  color: var(--builder-text-secondary);
  font-size: 14px;
  margin: 0;
  line-height: 1.5;
}

.input-wrapper {
  display: flex;
  flex-direction: column;
}

.modal-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid var(--builder-border);
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
}

.modal-input:focus {
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px rgba(var(--builder-accent-rgb), 0.1);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.btn-cancel, .btn-confirm {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: transparent;
  border: 1px solid var(--builder-border);
  color: var(--builder-text-secondary);
}

.btn-cancel:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.btn-confirm {
  background: var(--builder-accent);
  border: 1px solid var(--builder-accent);
  color: white;
}

.btn-confirm:hover {
  filter: brightness(1.1);
}
</style>
