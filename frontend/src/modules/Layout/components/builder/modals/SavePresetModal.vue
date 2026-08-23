<template>
  <BaseModal
    :is-open="true"
    :title="$t('builder.panels.presets.saveModal.title')"
    :width="400"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <div class="save-preset-modal">
        <p class="modal-description">
            {{ $t('builder.panels.presets.saveModal.description') }}
        </p>

        <div class="form-field">
            <label class="builder-label">{{ $t('builder.panels.presets.saveModal.nameLabel') }}</label>
            <BaseInput 
                v-model="presetName"
                :placeholder="$t('builder.panels.presets.saveModal.placeholder')"
                autofocus
                @keyup.enter="handleSave"
            />
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" @click="$emit('close')">{{ $t('builder.fields.actions.cancel') }}</button>
            <button 
                class="btn-save" 
                :disabled="!presetName || loading"
                @click="handleSave"
            >
                <div v-if="loading" class="spinner-xs"></div>
                <span v-else>{{ $t('builder.panels.presets.saveModal.saveBtn') }}</span>
            </button>
        </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { BaseModal, BaseInput } from '@/components/builder/ui';

interface Props {
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'save', name: string): void;
}>();

const presetName = ref('');

const handleSave = () => {
    if (!presetName.value || props.loading) return;
    emit('save', presetName.value);
};
</script>

<style scoped>
.save-preset-modal {
    padding: 0;
}

.modal-description {
    font-size: 13px;
    color: var(--builder-text-muted);
    margin-bottom: 20px;
    line-height: 1.5;
}

.form-field {
    margin-bottom: 24px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.btn-cancel {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--builder-border);
    border-radius: var(--border-radius-sm);
    color: var(--builder-text-primary);
    font-size: 13px;
    cursor: pointer;
}

.btn-save {
    padding: 8px 20px;
    background: var(--builder-accent);
    border: none;
    border-radius: var(--border-radius-sm);
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 100px;
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-xs {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: rotate 0.8s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
