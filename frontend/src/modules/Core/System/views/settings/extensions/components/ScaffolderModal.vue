<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-xl bg-card border border-border/80 rounded-xl overflow-y-auto max-h-[85vh]">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Wand class="w-5 h-5 text-emerald-400" />
          {{ t('system.appStore.scaffolder.title') }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ t('system.appStore.scaffolder.title') }}
        </DialogDescription>
      </DialogHeader>

      <div class="mt-4 space-y-5 text-sm text-muted-foreground leading-relaxed">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-xs text-foreground font-semibold">{{ t('system.appStore.scaffolder.fields.pluginName') }}</label>
            <input
              v-model="form.name"
              type="text"
              :placeholder="t('system.appStore.scaffolder.placeholders.pluginName')"
              class="w-full bg-background border border-border/80 rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all"
              @input="autoSlugify"
            />
          </div>
          <div class="space-y-1">
            <label class="text-xs text-foreground font-semibold">{{ t('system.appStore.scaffolder.fields.pluginSlug') }}</label>
            <input
              v-model="form.slug"
              type="text"
              :placeholder="t('system.appStore.scaffolder.placeholders.pluginSlug')"
              class="w-full bg-background border border-border/80 rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-xs text-foreground font-semibold">{{ t('system.appStore.scaffolder.fields.author') }}</label>
            <input
              v-model="form.author"
              type="text"
              :placeholder="t('system.appStore.scaffolder.placeholders.author')"
              class="w-full bg-background border border-border/80 rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all"
            />
          </div>
          <div class="space-y-1">
            <label class="text-xs text-foreground font-semibold">{{ t('system.appStore.scaffolder.fields.version') }}</label>
            <input
              v-model="form.version"
              type="text"
              :placeholder="t('system.appStore.scaffolder.placeholders.version')"
              class="w-full bg-background border border-border/80 rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all"
            />
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs text-foreground font-semibold">{{ t('system.appStore.scaffolder.fields.description') }}</label>
          <textarea
            v-model="form.description"
            rows="2"
            :placeholder="t('system.appStore.scaffolder.placeholders.description')"
            class="w-full bg-background border border-border/80 rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all resize-none"
          ></textarea>
        </div>

        <div class="flex items-center justify-between bg-emerald-500/5 border border-emerald-500/10 p-3 rounded-lg">
          <div class="flex flex-col gap-0.5">
            <span class="text-xs text-foreground font-semibold flex items-center gap-1">
              <Download class="w-3.5 h-3.5 text-emerald-400" />
              {{ t('system.appStore.scaffolder.installLocal.title') }}
            </span>
            <span class="text-[11px] text-muted-foreground">{{ t('system.appStore.scaffolder.installLocal.description') }}</span>
          </div>
          <input
            v-model="form.install_locally"
            type="checkbox"
            class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500 bg-background border-border"
          />
        </div>

        <div class="border-t border-border/40 pt-4 space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-foreground uppercase tracking-wider">{{ t('system.appStore.scaffolder.routes.title') }}</h4>
            <Button
              variant="outline"
              class="text-xs py-1 px-2.5 h-7 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10 flex items-center gap-1"
              @click="addRoute"
            >
              <Plus class="w-3 h-3" /> {{ t('system.appStore.scaffolder.routes.add') }}
            </Button>
          </div>

          <div v-if="form.routes.length === 0" class="text-xs text-center py-4 bg-muted/10 border border-dashed border-border rounded-lg">
            {{ t('system.appStore.scaffolder.routes.empty') }}
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="(r, idx) in form.routes"
              :key="idx"
              class="grid grid-cols-12 gap-2 items-center bg-card/50 p-2 border border-border rounded-lg"
            >
              <select
                v-model="r.method"
                class="col-span-3 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              >
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="DELETE">DELETE</option>
              </select>

              <input
                v-model="r.uri"
                type="text"
                :placeholder="t('system.appStore.scaffolder.placeholders.routeUri')"
                class="col-span-4 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              />

              <input
                v-model="r.action"
                type="text"
                :placeholder="t('system.appStore.scaffolder.placeholders.routeAction')"
                class="col-span-4 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              />

              <button
                class="col-span-1 text-rose-400 hover:text-rose-300 flex justify-center items-center"
                @click="removeRoute(idx)"
              >
                <Trash class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <div class="border-t border-border/40 pt-4 space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-foreground uppercase tracking-wider">{{ t('system.appStore.scaffolder.menus.title') }}</h4>
            <Button
              variant="outline"
              class="text-xs py-1 px-2.5 h-7 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10 flex items-center gap-1"
              @click="addMenu"
            >
              <Plus class="w-3 h-3" /> {{ t('system.appStore.scaffolder.menus.add') }}
            </Button>
          </div>

          <div v-if="form.sidebar_menu.length === 0" class="text-xs text-center py-4 bg-muted/10 border border-dashed border-border rounded-lg">
            {{ t('system.appStore.scaffolder.menus.empty') }}
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="(m, idx) in form.sidebar_menu"
              :key="idx"
              class="grid grid-cols-12 gap-2 items-center bg-card/50 p-2 border border-border rounded-lg"
            >
              <input
                v-model="m.title"
                type="text"
                :placeholder="t('system.appStore.scaffolder.placeholders.menuTitle')"
                class="col-span-3 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              />

              <input
                v-model="m.route"
                type="text"
                :placeholder="t('system.appStore.scaffolder.placeholders.menuRoute')"
                class="col-span-4 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              />

              <select
                v-model="m.group"
                class="col-span-2 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              >
                <option value="academic">{{ t('system.appStore.scaffolder.menus.groups.academic') }}</option>
                <option value="publishing">{{ t('system.appStore.scaffolder.menus.groups.publishing') }}</option>
                <option value="system">{{ t('system.appStore.scaffolder.menus.groups.system') }}</option>
              </select>

              <input
                v-model="m.icon"
                type="text"
                :placeholder="t('system.appStore.scaffolder.placeholders.icon')"
                class="col-span-2 bg-background border border-border rounded-lg text-xs py-1 px-2 text-foreground focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
              />

              <button
                class="col-span-1 text-rose-400 hover:text-rose-300 flex justify-center items-center"
                @click="removeMenu(idx)"
              >
                <Trash class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <div
          v-if="scaffoldError"
          class="bg-rose-500/10 border border-rose-500/20 p-3 rounded-lg flex gap-2 text-xs text-rose-400"
        >
          <AlertTriangle class="w-5 h-5 shrink-0 animate-bounce" />
          <span>{{ scaffoldError }}</span>
        </div>
      </div>

      <DialogFooter class="mt-6 flex justify-end gap-2 border-t border-border/30 pt-4">
        <Button
          variant="secondary"
          :disabled="scaffolding"
          @click="$emit('update:open', false)"
        >
          {{ t('system.appStore.cancel') }}
        </Button>
        <Button
          class="bg-emerald-600 hover:bg-emerald-700 text-white border-0"
          :disabled="scaffolding"
          @click="submitScaffold"
        >
          <span v-if="scaffolding" class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full border-2 border-emerald-200 border-t-emerald-600 animate-spin"></span>
            {{ t('system.appStore.scaffolder.generating') }}
          </span>
          <span v-else class="flex items-center gap-1.5">
            <Wand class="w-4 h-4" />
            {{ t('system.appStore.scaffolder.submit') }}
          </span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, Button } from '@/shared/components/ui';

import {
  AlertTriangle,
  Download,
  Plus,
  Trash,
  Wand,
} from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps<{
  open: boolean;
  scaffolding: boolean;
  scaffoldError: string;
}>();

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void;
  (e: 'scaffold', formPayload: Record<string, unknown>): void;
  (e: 'clear-error'): void;
}>();

const createDefaultForm = () => ({
  name: '',
  slug: '',
  author: t('system.appStore.scaffolder.defaultAuthor'),
  version: '1.0.0',
  description: '',
  install_locally: true,
  routes: [] as Array<{ method: string; uri: string; action: string }>,
  sidebar_menu: [] as Array<{ id: string; title: string; route: string; group: string; icon: string }>,
});

const form = ref(createDefaultForm());

const autoSlugify = () => {
  form.value.slug = form.value.name
    .toLowerCase()
    .replace(/[^a-z0-9-]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

watch(() => props.open, (newVal) => {
  if (!newVal) {
    form.value = createDefaultForm();
    emit('clear-error');
  }
});

const addRoute = () => {
  form.value.routes.push({ method: 'GET', uri: '', action: '' });
};

const removeRoute = (idx: number) => {
  form.value.routes.splice(idx, 1);
};

const addMenu = () => {
  form.value.sidebar_menu.push({
    id: `menu-${Math.floor(Math.random() * 1000)}`,
    title: '',
    route: '',
    group: 'academic',
    icon: 'star',
  });
};

const removeMenu = (idx: number) => {
  form.value.sidebar_menu.splice(idx, 1);
};

const submitScaffold = () => {
  emit('scaffold', form.value);
};
</script>
