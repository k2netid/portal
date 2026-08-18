<template>
  <BaseModal
    :is-open="true"
    title="Add Canvas"
    :width="400"
    draggable
    @close="$emit('close')"
  >
    <div class="add-canvas-form">
      <!-- Name -->
      <div class="form-group">
        <label class="form-label">Canvas Name</label>
        <BaseInput 
          v-model="form.title" 
          placeholder="Enter canvas name" 
          autofocus 
          class="canvas-name-input"
        />
      </div>

      <!-- Global Toggle -->
      <div class="form-group toggle-group">
        <label class="form-label">Make Global</label>
        <BaseToggle v-model="form.isGlobal" />
      </div>

      <!-- Append Options (Dropdown Selector) -->
      <div class="form-group">
        <div class="label-with-info">
          <label class="form-label">Append To Main Canvas</label>
          <Info :size="14" class="info-icon" />
        </div>
        
        <BaseDropdown align="left" class="append-dropdown">
          <template #trigger="{ open }">
            <div class="dropdown-selector" :class="{ 'is-open': open }">
              <span>{{ activeAppendLabel }}</span>
              <ChevronDown :size="14" class="chevron-icon" />
            </div>
          </template>
          <template #default="{ close }">
            <div class="dropdown-menu-list">
              <div 
                v-for="option in appendOptions" 
                :key="option.value"
                class="dropdown-menu-item"
                :class="{ 'is-active': form.append === option.value }"
                @click="form.append = option.value; close()"
              >
                <span>{{ option.label }}</span>
                <Check v-if="form.append === option.value" :size="14" class="check-icon" />
              </div>
            </div>
          </template>
        </BaseDropdown>
      </div>
    </div>

    <template #footer>
      <div class="modal-footer-actions">
        <button class="btn btn-cancel" @click="$emit('close')">Cancel</button>
        <button 
          class="btn btn-primary" 
          :disabled="!form.title" 
          @click="handleSubmit"
        >
          Add Canvas
        </button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { BaseModal, BaseInput, BaseToggle, BaseDropdown } from '@/components/builder/ui';

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'add', payload: { title: string; isGlobal: boolean; append: string }): void;
}>();

const form = reactive({
  title: '',
  isGlobal: false,
  append: 'none'
});

const appendOptions = [
  { label: "Don't Append", value: 'none' },
  { label: 'Append Above', value: 'above' },
  { label: 'Append Below', value: 'below' }
];

const activeAppendLabel = computed(() => {
  return appendOptions.find(opt => opt.value === form.append)?.label || "Don't Append";
});

const handleSubmit = () => {
  if (!form.title) return;
  emit('add', { ...form });
};
</script>

<style scoped>
.add-canvas-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.toggle-group {
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}

.form-label {
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
}

.label-with-info {
  display: flex;
  align-items: center;
  gap: 6px;
}

.info-icon {
  color: #64748b;
  cursor: help;
}

.append-dropdown {
  width: 100%;
}

.dropdown-selector {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: #2a3544;
  border: 1px solid #3d4b5c;
  border-radius: 6px;
  color: #f8fafc;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.dropdown-selector:hover, .dropdown-selector.is-open {
  border-color: #3b82f6;
}

.dropdown-menu-list {
  background: #1e293b;
  border: 1px solid #3d4b5c;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
  padding: 4px 0;
  min-width: 200px;
}

.dropdown-menu-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 14px;
  color: #cbd5e1;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.dropdown-menu-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.dropdown-menu-item.is-active {
  background: #2563eb;
  color: white;
}

.chevron-icon {
  color: #64748b;
}

.modal-footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  width: 100%;
  padding: 12px 16px;
}

.btn {
  padding: 6px 16px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-cancel {
  background: #334155;
  color: #cbd5e1;
}

.btn-cancel:hover {
  background: #475569;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Dark theme overrides */
:deep(.base-modal-container) {
  background: #111827;
  border-color: #374151;
}

:deep(.base-modal-header) {
  padding: 12px 16px;
  border-bottom-color: #1f2937;
}

:deep(.base-modal-title) {
  font-size: 14px;
  color: #f3f4f6;
}

:deep(.base-modal-footer) {
  background: #1f2937;
  border-top-color: #1f2937;
}

:deep(.base-input__field) {
  background: #2a3544 !important;
  color: #f8fafc !important;
  border: 1px solid #3d4b5c !important;
  height: 32px !important;
  font-size: 13px !important;
}

:deep(.base-input__field:focus) {
  border-color: #3b82f6 !important;
}
</style>
