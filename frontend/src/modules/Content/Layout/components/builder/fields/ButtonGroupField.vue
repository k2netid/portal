<template>
  <div class="button-group-field">
    <BaseSegmentedControl
      :model-value="value"
      :options="mappedOptions"
      :full-width="true"
      @update:model-value="(val) => $emit('update:value', val)"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import type { SettingDefinition, BlockInstance } from '@/types/builder'
import { useI18n } from 'vue-i18n'
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import AlignJustify from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Grid from 'lucide-vue-next/dist/esm/icons/grid-2x2.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import Rows from 'lucide-vue-next/dist/esm/icons/rows-2.js';
import Square from 'lucide-vue-next/dist/esm/icons/square.js';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import Video from 'lucide-vue-next/dist/esm/icons/video.js';
import Link from 'lucide-vue-next/dist/esm/icons/link.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import Lock from 'lucide-vue-next/dist/esm/icons/lock.js';
import Unlock from 'lucide-vue-next/dist/esm/icons/lock-open.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Smartphone from 'lucide-vue-next/dist/esm/icons/smartphone.js';
import Tablet from 'lucide-vue-next/dist/esm/icons/tablet.js';
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js';
import Minimize from 'lucide-vue-next/dist/esm/icons/minimize.js';
import Maximize from 'lucide-vue-next/dist/esm/icons/maximize.js';
import type { Component } from 'vue'

const iconMap: Record<string, Component> = {
  AlignLeft, AlignCenter, AlignRight, AlignJustify, 
  Layout, Grid, List, Columns, Rows, Square, 
  Type, Image, Video, Link, Mail, Globe, 
  Eye, EyeOff, Lock, Unlock, Settings, Trash2,
  Smartphone, Tablet, Monitor, Minimize, Maximize
}
import { BaseSegmentedControl } from '@/components/builder/ui'

interface FieldOption {
  value: string | number | boolean;
  label: string;
  icon?: string;
}

const props = defineProps<{
  field: SettingDefinition;
  value: string | number | boolean;
}>()

defineEmits<{
  (e: 'update:value', value: string | number | boolean): void;
}>()

const { t, te } = useI18n()
const module = inject<BlockInstance>('module', {} as BlockInstance)

const getOptionLabel = (option: FieldOption) => {
  const type = module?.type || 'common'
  const name = props.field.name
  const val = option.value

  // 1. Module specific: builder.settings.{type}.{name}.options.{val}
  if (te(`builder.settings.${type}.${name}.options.${val}`)) {
    return t(`builder.settings.${type}.${name}.options.${val}`)
  }

  // 2. Common field: builder.settings.fields.${name}.options.{val}
  if (te(`builder.settings.fields.${name}.options.${val}`)) {
    return t(`builder.settings.fields.${name}.options.${val}`)
  }

  return option.label
}

const mappedOptions = computed(() => {
  return ((props.field.options as FieldOption[]) || []).map((opt: FieldOption) => ({
    ...opt,
    label: getOptionLabel(opt),
    icon: opt.icon ? iconMap[opt.icon] || iconMap.Grid : undefined,
    iconOnly: !!opt.icon
  }))
})
</script>

<style scoped>
.button-group-field {
  width: 100%;
}
</style>
