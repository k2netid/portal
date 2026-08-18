<template>
  <div class="space-y-4">
    <!-- Section 1: Cache Status & Management (Collapsible) -->
    <div class="bg-card border border-border rounded-lg overflow-hidden">
      <button 
        type="button"
        class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-muted/50"
        @click="sections.cache = !sections.cache"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-primary/10 text-primary">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            ><path d="M21 12a9 9 0 11-6.219-8.56" /></svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-foreground">
              {{ $t('system.settings.cache.status') }}
            </h3>
            <p class="text-xs text-muted-foreground">
              {{ $t('system.settings.descriptions.cache_status') }}
            </p>
          </div>
        </div>
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          width="20"
          height="20" 
          viewBox="0 0 24 24" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
          class="text-muted-foreground"
          :class="{ 'rotate-180': sections.cache }"
        ><path d="m6 9 6 6 6-6" /></svg>
      </button>
            
      <div
        v-show="sections.cache"
        class="border-t border-border"
      >
        <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left: Status -->
          <div>
            <div
              v-if="!cacheStatus"
              class="text-center py-8 text-muted-foreground text-sm"
            >
              {{ $t('system.settings.cache.loading') }}
            </div>
            <div v-else>
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="p-3 rounded-lg bg-muted border border-border/50">
                  <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">
                    {{ $t('system.settings.cache.driver') }}
                  </p>
                  <p class="text-sm font-bold text-foreground mt-1">
                    {{ cacheStatus.driver }}
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-muted border border-border/50">
                  <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">
                    {{ $t('system.settings.cache.status') }}
                  </p>
                  <p class="text-sm font-bold text-foreground mt-1">
                    {{ cacheStatus.enabled ? $t('system.settings.enabled') : $t('system.settings.disabled') }}
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-muted border border-border/50">
                  <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">
                    {{ $t('system.settings.cache.keys') }}
                  </p>
                  <p class="text-sm font-bold text-foreground mt-1">
                    {{ cacheStatus.keys }}
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-muted border border-border/50">
                  <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">
                    {{ $t('system.settings.cache.size') }}
                  </p>
                  <p class="text-sm font-bold text-foreground mt-1">
                    {{ cacheStatus.size }}
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <button
                  type="button"
                  :disabled="clearingCache"
                  class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium border border-destructive/30 bg-destructive/10 text-destructive rounded-lg hover:bg-destructive/20 disabled:opacity-50"
                  @click="$emit('clear-cache')"
                >
                  <svg
                    v-if="clearingCache"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                  ><circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  /><path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  /></svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  ><path d="M3 6h18" /><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" /><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" /></svg>
                  {{ clearingCache ? $t('system.settings.cache.clearing') : $t('system.settings.cache.clear') }}
                </button>
                <button
                  type="button"
                  :disabled="warmingCache"
                  class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium border border-border bg-card text-foreground rounded-lg hover:bg-muted disabled:opacity-50"
                  @click="$emit('warm-cache')"
                >
                  <svg
                    v-if="warmingCache"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                  ><circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  /><path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  /></svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  ><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.1.24-2.14.7-3.09C7.07 13.1 8 13.9 8.5 14.5z" /></svg>
                  {{ warmingCache ? $t('system.settings.cache.warming') : $t('system.settings.cache.warm') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Right: Configuration -->
          <div class="space-y-4">
            <template
              v-for="setting in cacheSettings"
              :key="setting.id"
            >
              <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                  {{ $t('system.settings.labels.' + setting.key) }}
                </label>
                <p
                  v-if="setting.description"
                  class="text-xs text-muted-foreground mb-2"
                >
                  {{ $t('system.settings.descriptions.' + setting.key) }}
                </p>

                <select
                  v-if="setting.key === 'cache_driver'"
                  :value="(formData[setting.key] as any)"
                  class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                  :class="{ 'border-destructive': errors?.[setting.key] }"
                  @change="(e) => updateField(setting.key, (e.target as HTMLSelectElement).value)"
                >
                  <option value="file">
                    File (Local Storage)
                  </option>
                  <option value="database">
                    Database (Default)
                  </option>
                  <option value="redis">
                    Redis (High Performance)
                  </option>
                  <option value="failover">
                    Redis + Failover (Recommended)
                  </option>
                </select>

                <div v-else-if="setting.key === 'enable_cache'">
                  <label class="flex items-center cursor-pointer">
                    <div class="relative">
                      <input 
                        :checked="Boolean(formData[setting.key])" 
                        type="checkbox"
                        class="sr-only peer" 
                        @change="(e) => updateField(setting.key, (e.target as HTMLInputElement).checked)"
                      >
                      <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after: after:shadow-md peer-checked:bg-success" />
                    </div>
                    <span class="ml-3 text-sm text-foreground">
                      {{ formData[setting.key] ? $t('system.settings.enabled') : $t('system.settings.disabled') }}
                    </span>
                  </label>
                </div>

                <input
                  v-else-if="setting.key === 'cache_ttl'"
                  :value="(formData[setting.key] as any)"
                  type="number"
                  class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                  :class="{ 'border-destructive': errors?.[setting.key] }"
                  @input="(e) => updateField(setting.key, parseInt((e.target as HTMLInputElement).value))"
                >
                <p
                  v-if="errors && errors[setting.key]"
                  class="text-sm text-destructive mt-1"
                >
                  {{ Array.isArray(errors[setting.key]) ? (errors[setting.key] as string[])[0] : errors[setting.key] }}
                </p>
              </div>
            </template>
          </div>
        </div>

        <!-- Redis Infrastructure Link (More prominent) -->
        <div
          v-if="formData.cache_driver === 'redis' || formData.cache_driver === 'failover'"
          class="mx-6 mb-6 p-5 rounded-xl border border-primary/20 bg-primary/5 shadow-sm"
        >
          <div class="flex items-center justify-between gap-4">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                ><path d="M12 2v8" /><path d="m16 6-4 4-4-4" /><path d="M12 16v6" /><path d="m8 18 4 4 4-4" /><path d="M19 9h2" /><path d="M15 9h2" /><path d="M3 9h2" /><path d="M7 9h2" /><path d="M19 15h2" /><path d="M15 15h2" /><path d="M3 15h2" /><path d="M7 15h2" /></svg>
              </div>
              <div>
                <h4 class="font-bold text-sm text-foreground">
                  Redis Server Infrastructure
                </h4>
                <p class="text-xs text-muted-foreground leading-relaxed mt-0.5">
                  Server Redis terdeteksi sebagai driver aktif. Gunakan halaman manajemen untuk memantau penggunaan memory, client, dan statistik performa server secara detail.
                </p>
              </div>
            </div>
            <router-link
              :to="consolePath('/redis')"
              class="flex-shrink-0 px-4 py-2 text-xs font-semibold bg-primary text-primary-foreground rounded-lg hover:opacity-90 transition-all flex items-center gap-2"
            >
              Monitor Server
              <ArrowRight class="w-3.5 h-3.5" />
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: CDN Configuration (Collapsible) -->
    <div class="bg-card border border-border rounded-lg overflow-hidden">
      <button 
        type="button"
        class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-muted/50"
        @click="sections.cdn = !sections.cdn"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-info/10 text-info">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            ><circle
              cx="12"
              cy="12"
              r="10"
            /><path d="M2 12h20" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" /></svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-foreground">
              CDN Configuration
            </h3>
            <p class="text-xs text-muted-foreground">
              {{ $t('system.settings.descriptions.cdn_status') }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span
            v-if="formData.enable_cdn"
            class="px-2 py-0.5 text-xs font-medium rounded-full bg-success/10 text-success"
          >Aktif</span>
          <span
            v-else
            class="px-2 py-0.5 text-xs font-medium rounded-full bg-muted text-muted-foreground"
          >Nonaktif</span>
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            width="20"
            height="20" 
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor" 
            stroke-width="2" 
            stroke-linecap="round" 
            stroke-linejoin="round"
            class="text-muted-foreground"
            :class="{ 'rotate-180': sections.cdn }"
          ><path d="m6 9 6 6 6-6" /></svg>
        </div>
      </button>
            
      <div
        v-show="sections.cdn"
        class="border-t border-border p-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Enable CDN -->
          <div v-if="cdnEnabledSetting">
            <label class="block text-sm font-medium text-foreground mb-1">
              {{ $t('system.settings.labels.' + (cdnEnabledSetting?.key || '')) }}
            </label>
            <p
              v-if="cdnEnabledSetting?.description"
              class="text-xs text-muted-foreground mb-3"
            >
              {{ $t('system.settings.descriptions.' + (cdnEnabledSetting?.key || '')) }}
            </p>
            <label class="flex items-center cursor-pointer">
              <div class="relative">
                <input 
                  :checked="Boolean(formData[cdnEnabledSetting?.key || ''])" 
                  type="checkbox"
                  class="sr-only peer" 
                  @change="(e) => updateField(cdnEnabledSetting?.key || '', (e.target as HTMLInputElement).checked)"
                >
                <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after: after:shadow-md peer-checked:bg-success" />
              </div>
              <span class="ml-3 text-sm text-foreground">
                {{ formData[cdnEnabledSetting?.key || ''] ? $t('system.settings.enabled') : $t('system.settings.disabled') }}
              </span>
            </label>
          </div>
                    
          <!-- CDN Provider -->
          <div v-if="cdnPresetSetting">
            <label
              class="block text-sm font-medium text-foreground mb-1"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ cdnPresetSetting ? $t('system.settings.labels.' + cdnPresetSetting.key) : 'CDN Provider' }}
            </label>
            <p
              class="text-xs text-muted-foreground mb-2"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ cdnPresetSetting ? $t('system.settings.descriptions.' + cdnPresetSetting.key) : 'Pilih provider CDN Anda' }}
            </p>
            <select
              :value="formData[cdnPresetSetting?.key || '']"
              :disabled="!formData.enable_cdn"
              class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              @change="(e) => updateField(cdnPresetSetting?.key || '', (e.target as HTMLSelectElement).value)"
            >
              <option value="custom">
                Custom / Other
              </option>
              <option value="bunny">
                BunnyCDN
              </option>
              <option value="cloudflare">
                Cloudflare
              </option>
              <option value="aws">
                AWS CloudFront
              </option>
            </select>
          </div>

          <!-- CDN URL -->
          <div
            v-if="cdnUrlSetting"
            class="md:col-span-2"
          >
            <label
              class="block text-sm font-medium text-foreground mb-1"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ $t('system.settings.labels.' + (cdnUrlSetting?.key || '')) }}
            </label>
            <p
              v-if="cdnUrlSetting?.description"
              class="text-xs text-muted-foreground mb-2"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ $t('system.settings.descriptions.' + (cdnUrlSetting?.key || '')) }}
            </p>
            <input
              :value="(formData[cdnUrlSetting?.key || ''] as any)"
              type="text"
              :disabled="!formData.enable_cdn"
              class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              :placeholder="!formData.enable_cdn ? 'Enable CDN to configure URL' : 'https://cdn.example.com'"
              :class="{ 'border-destructive': errors?.[cdnUrlSetting?.key || ''] }"
              @input="(e) => updateField(cdnUrlSetting?.key || '', (e.target as HTMLInputElement).value)"
            >
            <p
              v-if="errors && cdnUrlSetting && errors[cdnUrlSetting.key]"
              class="text-sm text-destructive mt-1"
            >
              {{ Array.isArray(errors[cdnUrlSetting.key]) ? (errors[cdnUrlSetting.key] as string[])[0] : errors[cdnUrlSetting.key] }}
            </p>
            <p
              v-if="formData.cdn_preset === 'bunny'"
              class="text-xs text-primary mt-1"
            >
              Tip: Use your BunnyCDN Pull Zone URL (e.g. https://my-zone.b-cdn.net)
            </p>
          </div>

          <!-- Included Dirs -->
          <div v-if="cdnIncludedDirsSetting">
            <label
              class="block text-sm font-medium text-foreground mb-1"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ cdnIncludedDirsSetting ? $t('system.settings.labels.' + cdnIncludedDirsSetting.key) : 'Direktori yang Disertakan' }}
            </label>
            <p
              class="text-xs text-muted-foreground mb-2"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ cdnIncludedDirsSetting ? $t('system.settings.descriptions.' + cdnIncludedDirsSetting.key) : 'Daftar direktori untuk dilayani via CDN' }}
            </p>
            <input
              :value="(formData[cdnIncludedDirsSetting?.key || ''] as any)"
              type="text"
              :disabled="!formData.enable_cdn"
              class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              :class="{ 'border-destructive': errors?.[cdnIncludedDirsSetting?.key || ''] }"
              @input="(e) => updateField(cdnIncludedDirsSetting?.key || '', (e.target as HTMLInputElement).value)"
            >
            <p
              v-if="errors && cdnIncludedDirsSetting && errors[cdnIncludedDirsSetting.key]"
              class="text-sm text-destructive mt-1"
            >
              {{ Array.isArray(errors[cdnIncludedDirsSetting.key]) ? (errors[cdnIncludedDirsSetting.key] as string[])[0] : errors[cdnIncludedDirsSetting.key] }}
            </p>
          </div>

          <!-- Excluded Extensions -->
          <div v-if="cdnExcludedExtsSetting">
            <label
              class="block text-sm font-medium text-foreground mb-1"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ cdnExcludedExtsSetting ? $t('system.settings.labels.' + cdnExcludedExtsSetting.key) : 'Ekstensi yang Dikecualikan' }}
            </label>
            <p
              class="text-xs text-muted-foreground mb-2"
              :class="{'opacity-50': !formData.enable_cdn}"
            >
              {{ cdnExcludedExtsSetting ? $t('system.settings.descriptions.' + cdnExcludedExtsSetting.key) : 'Ekstensi file yang dikecualikan dari CDN' }}
            </p>
            <input
              :value="(formData[cdnExcludedExtsSetting?.key || ''] as any)"
              type="text"
              :disabled="!formData.enable_cdn"
              class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              :class="{ 'border-destructive': errors?.[cdnExcludedExtsSetting?.key || ''] }"
              @input="(e) => updateField(cdnExcludedExtsSetting?.key || '', (e.target as HTMLInputElement).value)"
            >
            <p
              v-if="errors && cdnExcludedExtsSetting && errors[cdnExcludedExtsSetting.key]"
              class="text-sm text-destructive mt-1"
            >
              {{ Array.isArray(errors[cdnExcludedExtsSetting.key]) ? (errors[cdnExcludedExtsSetting.key] as string[])[0] : errors[cdnExcludedExtsSetting.key] }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Other Performance Settings (Collapsible) -->
    <div
      v-if="otherSettings.length > 0"
      class="bg-card border border-border rounded-lg overflow-hidden"
    >
      <button 
        type="button"
        class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-muted/50"
        @click="sections.other = !sections.other"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-warning/10 text-warning">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            ><circle
              cx="12"
              cy="12"
              r="3"
            /><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" /></svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-foreground">
              {{ $t('system.settings.tabs.performance') }} Settings
            </h3>
            <p class="text-xs text-muted-foreground">
              {{ $t('system.settings.descriptions.performance_status') }}
            </p>
          </div>
        </div>
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          width="20"
          height="20" 
          viewBox="0 0 24 24" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
          class="text-muted-foreground"
          :class="{ 'rotate-180': sections.other }"
        ><path d="m6 9 6 6 6-6" /></svg>
      </button>
            
      <div
        v-show="sections.other"
        class="border-t border-border p-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <template
            v-for="setting in otherSettings"
            :key="setting.id"
          >
            <div>
              <label class="block text-sm font-medium text-foreground mb-1">
                {{ $t('system.settings.labels.' + setting.key) }}
              </label>
              <p
                v-if="setting.description"
                class="text-xs text-muted-foreground mb-2"
              >
                {{ $t('system.settings.descriptions.' + setting.key) }}
              </p>

              <input
                v-if="setting.type === 'string'"
                :value="(formData[setting.key] as any)"
                type="text"
                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                :class="{ 'border-destructive': errors?.[setting.key] }"
                @input="(e) => updateField(setting.key, (e.target as HTMLInputElement).value)"
              >
              <input
                v-else-if="setting.type === 'integer'"
                :value="(formData[setting.key] as any)"
                type="number"
                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                :class="{ 'border-destructive': errors?.[setting.key] }"
                @input="(e) => updateField(setting.key, parseInt((e.target as HTMLInputElement).value))"
              >
              <div v-else-if="setting.type === 'boolean'">
                <label class="flex items-center cursor-pointer">
                  <div class="relative">
                    <input 
                      :checked="Boolean(formData[setting.key])" 
                      type="checkbox"
                      class="sr-only peer" 
                      @change="(e) => updateField(setting.key, (e.target as HTMLInputElement).checked)"
                    >
                    <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after: after:shadow-md peer-checked:bg-success" />
                  </div>
                  <span class="ml-3 text-sm text-foreground">
                    {{ formData[setting.key] ? $t('system.settings.enabled') : $t('system.settings.disabled') }}
                  </span>
                </label>
              </div>
              <p
                v-if="errors && errors[setting.key]"
                class="text-sm text-destructive mt-1"
              >
                {{ Array.isArray(errors[setting.key]) ? (errors[setting.key] as string[])[0] : errors[setting.key] }}
              </p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import { ref, computed } from 'vue'
import { ArrowRight } from 'lucide-vue-next'
import type { CacheStatus } from '@/engine/types/settings'

interface Setting {
    id: string | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
    description?: string;
}


interface SectionState {
    cache: boolean;
    cdn: boolean;
    other: boolean;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    cacheStatus?: CacheStatus | null;
    clearingCache?: boolean;
    warmingCache?: boolean;
    errors?: Record<string, string[] | string>;
}

const props = withDefaults(defineProps<Props>(), {
    cacheStatus: null,
    clearingCache: false,
    warmingCache: false,
    errors: () => ({})
});

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
    (e: 'clear-cache'): void;
    (e: 'warm-cache'): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

// Collapsible section states
const sections = ref<SectionState>({
    cache: true,
    cdn: false,
    other: false
})

const performanceSettings = computed(() => {
    return props.settings.filter(s => s && s.group === 'performance')
})

const cacheSettings = computed(() => {
    return performanceSettings.value.filter(s => 
        ['cache_driver', 'enable_cache', 'cache_ttl'].includes(s.key)
    )
})

const cdnEnabledSetting = computed(() => performanceSettings.value.find(s => s.key === 'enable_cdn'))
const cdnUrlSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_url'))
const cdnPresetSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_preset'))
const cdnIncludedDirsSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_included_dirs'))
const cdnExcludedExtsSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_excluded_extensions'))


const otherSettings = computed(() => {
    return performanceSettings.value.filter(s => 
        !['cache_driver', 'enable_cache', 'cache_ttl', 'enable_cdn', 'cdn_url', 'cdn_preset', 'cdn_included_dirs', 'cdn_excluded_extensions'].includes(s.key)
    )
})
</script>
