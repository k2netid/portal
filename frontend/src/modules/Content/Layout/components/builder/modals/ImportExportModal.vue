<template>
  <BaseModal
    :is-open="true"
    title="Import & Export"
    :width="450"
    draggable
    @close="$emit('close')"
  >
    <template #header>
      <div class="modal-tabs">
        <button 
          v-for="tab in tabs"
          :key="tab.id"
          class="modal-tab"
          :class="{ 'modal-tab--active': activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>
    </template>

    <div class="import-export-content">
      <!-- Export Tab -->
      <div v-if="activeTab === 'export'" class="tab-pane">
        <div class="form-group">
          <label class="form-label">Export File Name</label>
          <BaseInput 
            v-model="exportForm.fileName" 
            placeholder="Main Canvas" 
            class="export-name-input"
          />
        </div>
        <button class="btn btn-submit btn-full" @click="handleExport">
          Export Builder Layout
        </button>
      </div>

      <!-- Import Tab -->
      <div v-else class="tab-pane">
        <div class="form-group">
          <label class="form-label">Choose File</label>
          <div 
            class="file-dropzone" 
            :class="{ 'is-dragover': isDragOver }"
            @click="triggerFileInput"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
            @drop.prevent="handleDrop"
          >
            <span class="dropzone-text">{{ selectedFile ? selectedFile.name : 'No File Selected.' }}</span>
            <input 
              ref="fileInput" 
              type="file" 
              accept=".json" 
              style="display: none" 
              @change="onFileSelected" 
            />
          </div>
        </div>

        <div class="import-options">
          <label class="form-label">Import Options</label>
          <div v-for="option in importOptions" :key="option.id" class="option-row">
            <BaseCheckbox v-model="option.value" :label="option.label" />
          </div>
        </div>

        <button 
          class="btn btn-submit btn-full" 
          :disabled="!selectedFile" 
          @click="handleImport"
        >
          Import Builder Layout
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { BaseModal, BaseInput, BaseCheckbox } from '@/components/builder/ui';

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'export', payload: { fileName: string }): void;
  (e: 'import', payload: { file: File; options: Record<string, boolean> }): void;
}>();

const activeTab = ref('export');
const tabs = [
  { id: 'export', label: 'Export' },
  { id: 'import', label: 'Import' }
];

const exportForm = reactive({
  fileName: ''
});

const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isDragOver = ref(false);

const importOptions = ref([
  { id: 'replace', label: 'Replace Existing Content.', value: true },
  { id: 'backup', label: 'Download Backup Before Importing', value: false },
  { id: 'presets', label: 'Import Presets', value: false },
  { id: 'new', label: 'Import To New Canvas', value: false }
]);

const triggerFileInput = () => {
  fileInput.value?.click();
};

const onFileSelected = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    selectedFile.value = file;
  }
};

const handleDrop = (e: DragEvent) => {
  isDragOver.value = false;
  const files = e.dataTransfer?.files;
  if (files && files.length > 0) {
    const file = files[0];
    if (file.name.endsWith('.json')) {
      selectedFile.value = file;
    }
  }
};

const handleExport = () => {
  emit('export', { fileName: exportForm.fileName || 'Layout' });
};

const handleImport = () => {
  if (!selectedFile.value) return;
  
  const options = importOptions.value.reduce((acc, opt) => {
    acc[opt.id] = opt.value;
    return acc;
  }, {} as Record<string, boolean>);
  
  emit('import', { file: selectedFile.value, options });
};
</script>

<style scoped>
.modal-tabs {
  display: flex;
  margin-left: 20px;
}

.modal-tab {
  background: none;
  border: none;
  padding: 8px 12px;
  color: #94a3b8;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  font-weight: 500;
  font-size: 13px;
  transition: all 0.2s;
}

.modal-tab:hover {
  color: #f8fafc;
}

.modal-tab--active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.import-export-content {
  padding: 16px;
}

.tab-pane {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
}

.file-dropzone {
  height: 48px;
  border: 1px dashed #3d4b5c;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #1f2937;
  padding: 0 16px;
  text-align: center;
}

.file-dropzone:hover, .file-dropzone.is-dragover {
  border-color: #3b82f6;
  background: #2a3544;
}

.dropzone-text {
  font-size: 13px;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.import-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.option-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-submit {
  background: #2563eb;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-full {
  width: 100%;
}

/* Dark theme overrides */
:deep(.base-modal-container) {
  background: #111827;
  border-color: #374151;
}

:deep(.base-modal-header) {
  padding: 8px 16px;
  border-bottom-color: #1f2937;
}

:deep(.base-modal-title) {
  font-size: 14px;
  color: #f3f4f6;
}

:deep(.base-input__field) {
  background: #2a3544 !important;
  color: #f8fafc !important;
  border: 1px solid #3d4b5c !important;
  height: 32px !important;
  font-size: 13px !important;
}

:deep(.base-checkbox-box) {
  width: 14px !important;
  height: 14px !important;
  background: #2a3544 !important;
  border-color: #3d4b5c !important;
}

:deep(.base-checkbox-label) {
  font-size: 12px !important;
  color: #cbd5e1 !important;
  font-weight: 500;
}
</style>
