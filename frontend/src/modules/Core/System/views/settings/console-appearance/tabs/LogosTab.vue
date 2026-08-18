<template>
  <div class="relative">
    <div
      v-if="!hasWhiteLabel"
      class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-background/85 p-8 text-center backdrop-blur-sm"
    >
      <div class="mb-3 rounded-full border border-amber-500/20 bg-amber-500/10 p-3 text-amber-500 shadow-sm">
        <Lock class="h-6 w-6 animate-bounce" />
      </div>
      <h4 class="text-base font-semibold text-foreground">
        {{ t('system.settings.consoleAppearance.whiteLabelRequired') }}
      </h4>
      <p class="mt-1 max-w-sm text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.whiteLabelDescription') }}
      </p>
    </div>

    <div
      class="space-y-6 p-6"
      :class="{ 'pointer-events-none select-none opacity-50': !hasWhiteLabel }"
    >
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="flex flex-col gap-2 rounded-xl border border-border/50 bg-card p-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-foreground">{{ t('system.settings.consoleAppearance.logoLightLabel') }}</span>
            <span class="rounded-full bg-muted/60 px-2 py-0.5 font-mono text-[9px] text-muted-foreground">LIGHT BACKDROP</span>
          </div>
          <p class="text-xs text-foreground/80">
            {{ t('system.settings.consoleAppearance.logoLightDescription') }}
          </p>
          <div class="mt-2 flex items-center gap-3">
            <div class="flex h-12 w-32 items-center justify-center overflow-hidden rounded-lg border border-border/60 bg-white/70 shadow-sm">
              <img
                v-if="form.app_logo_light"
                :src="String(form.app_logo_light)"
                class="h-full w-full object-contain p-1"
                alt=""
              >
              <Image
                v-else
                class="h-5 w-5 text-muted-foreground/40"
              />
            </div>
            <div class="flex items-center gap-1.5">
              <Button
                type="button"
                variant="secondary"
                size="sm"
                class="h-8 text-xs"
                :disabled="!hasWhiteLabel"
                @click="showLogoLightPicker = true"
              >
                <UploadCloud class="mr-1 h-3.5 w-3.5" />
                {{ form.app_logo_light ? t('common.actions.change') : t('common.actions.select') }}
              </Button>
              <Button
                v-if="form.app_logo_light && hasWhiteLabel"
                type="button"
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:bg-destructive/10"
                @click="form.app_logo_light = ''"
              >
                <Trash2 class="h-3.5 w-3.5" />
              </Button>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-2 rounded-xl border border-border/50 bg-card p-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-foreground">{{ t('system.settings.consoleAppearance.logoDarkLabel') }}</span>
            <span class="rounded-full bg-muted/60 px-2 py-0.5 font-mono text-[9px] text-muted-foreground">DARK BACKDROP</span>
          </div>
          <p class="text-xs text-foreground/80">
            {{ t('system.settings.consoleAppearance.logoDarkDescription') }}
          </p>
          <div class="mt-2 flex items-center gap-3">
            <div class="flex h-12 w-32 items-center justify-center overflow-hidden rounded-lg border border-border/60 bg-black/90 shadow-sm">
              <img
                v-if="form.app_logo_dark"
                :src="String(form.app_logo_dark)"
                class="h-full w-full object-contain p-1"
                alt=""
              >
              <Image
                v-else
                class="h-5 w-5 text-muted-foreground/40"
              />
            </div>
            <div class="flex items-center gap-1.5">
              <Button
                type="button"
                variant="secondary"
                size="sm"
                class="h-8 text-xs"
                :disabled="!hasWhiteLabel"
                @click="showLogoDarkPicker = true"
              >
                <UploadCloud class="mr-1 h-3.5 w-3.5" />
                {{ form.app_logo_dark ? t('common.actions.change') : t('common.actions.select') }}
              </Button>
              <Button
                v-if="form.app_logo_dark && hasWhiteLabel"
                type="button"
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:bg-destructive/10"
                @click="form.app_logo_dark = ''"
              >
                <Trash2 class="h-3.5 w-3.5" />
              </Button>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-2 rounded-xl border border-border/50 bg-card p-4">
          <span class="text-xs font-semibold text-foreground">{{ t('system.settings.consoleAppearance.logoCompactLabel') }}</span>
          <p class="text-xs text-foreground/80">
            {{ t('system.settings.consoleAppearance.logoCompactDescription') }}
          </p>
          <div class="mt-2 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg border border-border/60 bg-muted/40 shadow-sm">
              <img
                v-if="form.app_logo_compact"
                :src="String(form.app_logo_compact)"
                class="h-full w-full object-contain p-1"
                alt=""
              >
              <Image
                v-else
                class="h-5 w-5 text-muted-foreground/40"
              />
            </div>
            <div class="flex items-center gap-1.5">
              <Button
                type="button"
                variant="secondary"
                size="sm"
                class="h-8 text-xs"
                :disabled="!hasWhiteLabel"
                @click="showLogoCompactPicker = true"
              >
                <UploadCloud class="mr-1 h-3.5 w-3.5" />
                {{ form.app_logo_compact ? t('common.actions.change') : t('common.actions.select') }}
              </Button>
              <Button
                v-if="form.app_logo_compact && hasWhiteLabel"
                type="button"
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:bg-destructive/10"
                @click="form.app_logo_compact = ''"
              >
                <Trash2 class="h-3.5 w-3.5" />
              </Button>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-2 rounded-xl border border-border/50 bg-card p-4">
          <span class="text-xs font-semibold text-foreground">{{ t('system.settings.consoleAppearance.faviconLabel') }}</span>
          <p class="text-xs text-foreground/80">
            {{ t('system.settings.consoleAppearance.faviconDescription') }}
          </p>
          <div class="mt-2 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg border border-border/60 bg-muted/40 shadow-sm">
              <img
                v-if="form.app_favicon"
                :src="String(form.app_favicon)"
                class="h-full w-full object-contain p-1"
                alt=""
              >
              <Image
                v-else
                class="h-5 w-5 text-muted-foreground/40"
              />
            </div>
            <div class="flex items-center gap-1.5">
              <Button
                type="button"
                variant="secondary"
                size="sm"
                class="h-8 text-xs"
                :disabled="!hasWhiteLabel"
                @click="showFaviconPicker = true"
              >
                <UploadCloud class="mr-1 h-3.5 w-3.5" />
                {{ form.app_favicon ? t('common.actions.change') : t('common.actions.select') }}
              </Button>
              <Button
                v-if="form.app_favicon && hasWhiteLabel"
                type="button"
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:bg-destructive/10"
                @click="form.app_favicon = ''"
              >
                <Trash2 class="h-3.5 w-3.5" />
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Lock, UploadCloud, Trash2, Image } from 'lucide-vue-next';
import Button from '@/shared/components/ui/Button.vue';
import { useI18n } from 'vue-i18n';
import { useConsoleAppearanceContext } from '../composables/useConsoleAppearancePage';

const { t } = useI18n();
const {
    form,
    hasWhiteLabel,
    showLogoLightPicker,
    showLogoDarkPicker,
    showLogoCompactPicker,
    showFaviconPicker,
} = useConsoleAppearanceContext();
</script>
