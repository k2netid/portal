<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.system.info.title')"
      :subtitle="t('system.system.info.subtitle')"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <!-- Active Tab Navigation Pills -->
          <div class="bg-muted p-1 rounded-lg flex items-center gap-1 border border-border">
            <button
              type="button"
              @click="activeTab = 'overview'"
              :class="activeTab === 'overview' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
              class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition flex items-center gap-2"
            >
              <Activity class="h-3.5 w-3.5" />
              <span>{{ t('system.system.info.tabs.overview') }}</span>
            </button>
            <button
              type="button"
              @click="activeTab = 'requirements'"
              :class="activeTab === 'requirements' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
              class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition flex items-center gap-2"
            >
              <ShieldCheck class="h-3.5 w-3.5 text-primary" />
              <span>{{ t('system.system.info.tabs.requirements') }}</span>
              <span
                v-if="requirementsData?.overview"
                :class="requirementsData.overview.is_ready ? 'bg-green-500/15 text-green-600 dark:text-green-400 border-green-500/30' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30'"
                class="px-1.5 py-0.5 text-[10px] font-bold rounded border leading-none"
              >
                {{ requirementsData.overview.score_percent }}%
              </span>
            </button>
          </div>
        </div>
      </template>
    </PageHeader>

    <ConsoleListCard>
      <div class="p-6 space-y-6">
        <!-- Loading Skeleton -->
        <div v-if="loading" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-card border border-border rounded-lg p-6 h-24 animate-pulse" />
            <div class="bg-card border border-border rounded-lg p-6 h-24 animate-pulse" />
            <div class="bg-card border border-border rounded-lg p-6 h-24 animate-pulse" />
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-card border border-border rounded-lg p-6 h-64 animate-pulse" />
            <div class="bg-card border border-border rounded-lg p-6 h-64 animate-pulse" />
          </div>
        </div>

        <!-- Main Content -->
        <template v-else>
          <!-- TAB 1: OVERVIEW & MAINTENANCE -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <!-- System Health Top Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-muted-foreground">
                      {{ t('system.system.info.health.title') }}
                    </p>
                    <p
                      class="text-2xl font-semibold mt-1"
                      :class="systemHealth === 'healthy' ? 'text-green-600 dark:text-green-400' : systemHealth === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400'"
                    >
                      {{ systemHealth === 'healthy' ? t('system.system.info.health.healthy') : systemHealth === 'warning' ? t('system.system.info.health.warning') : t('system.system.info.health.critical') }}
                    </p>
                  </div>
                  <div>
                    <CheckCircle
                      v-if="systemHealth === 'healthy'"
                      class="h-12 w-12 text-green-600 dark:text-green-400"
                    />
                    <AlertTriangle
                      v-else
                      class="h-12 w-12"
                      :class="systemHealth === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400'"
                    />
                  </div>
                </div>
              </div>

              <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <Zap class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-muted-foreground">
                      {{ t('system.system.info.cache.title') }}
                    </p>
                    <p class="text-2xl font-semibold text-foreground">
                      {{ cacheStatusLabel }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <Clock class="h-8 w-8 text-sky-600 dark:text-sky-400" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-muted-foreground">
                      {{ t('system.system.info.uptime') }}
                    </p>
                    <p class="text-2xl font-semibold text-foreground">
                      {{ formatUptime(systemInfo.uptime) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- System Info Specifications Card -->
              <div class="lg:col-span-2 bg-card border border-border rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                  <h2 class="text-lg font-semibold text-foreground">
                    {{ t('system.system.info.title') }}
                  </h2>
                  <router-link
                    :to="consolePath('/settings?tab=performance')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 text-sm font-medium transition"
                  >
                    <RotateCcw class="h-4 w-4" />
                    {{ t('system.system.info.cache.manage') }}
                  </router-link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Application -->
                  <div>
                    <h3 class="text-sm font-bold text-foreground mb-3 border-b border-border pb-1">
                      {{ t('system.system.info.sections.application') }}
                    </h3>
                    <dl class="space-y-2.5">
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.phpVersion') }}
                        </dt>
                        <dd class="text-sm text-foreground font-mono font-semibold">
                          {{ systemInfo.php_version || '-' }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.laravelVersion') }}
                        </dt>
                        <dd class="text-sm text-foreground font-mono font-semibold">
                          {{ systemInfo.laravel_version || '-' }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.environment') }}
                        </dt>
                        <dd class="text-sm text-foreground capitalize font-medium">
                          {{ systemInfo.environment || '-' }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.debugMode') }}
                        </dt>
                        <dd
                          class="text-sm font-medium"
                          :class="systemInfo.debug_mode ? 'text-red-500' : 'text-foreground'"
                        >
                          {{ systemInfo.debug_mode ? t('system.system.info.sections.enabled') : t('system.system.info.sections.disabled') }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          Cache / Session Driver
                        </dt>
                        <dd class="text-xs font-mono bg-accent px-2 py-0.5 rounded text-foreground">
                          {{ systemInfo.cache_driver }} / {{ systemInfo.session_driver }}
                        </dd>
                      </div>
                    </dl>
                  </div>

                  <!-- Server & OS -->
                  <div>
                    <h3 class="text-sm font-bold text-foreground mb-3 border-b border-border pb-1">
                      {{ t('system.system.info.sections.server') }}
                    </h3>
                    <dl class="space-y-2.5">
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          OS / Distro
                        </dt>
                        <dd class="text-sm text-foreground font-semibold truncate max-w-[200px]" :title="systemInfo.os_distro">
                          {{ systemInfo.os_distro || '-' }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.serverSoftware') }}
                        </dt>
                        <dd
                          class="text-sm text-foreground truncate max-w-[200px] font-mono text-xs"
                          :title="systemInfo.server_software"
                        >
                          {{ systemInfo.server_software || '-' }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.memoryUsage') }}
                        </dt>
                        <dd class="text-sm text-foreground font-mono">
                          {{ displayMemory }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.diskUsage') }}
                        </dt>
                        <dd class="text-sm text-foreground font-mono">
                          {{ displayDisk }}
                        </dd>
                      </div>
                      <div class="flex justify-between items-center">
                        <dt class="text-sm text-muted-foreground">
                          {{ t('system.system.info.sections.database') }}
                        </dt>
                        <dd class="text-sm text-foreground font-semibold truncate max-w-[200px]" :title="systemInfo.database_version">
                          {{ systemInfo.database || '-' }}
                        </dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="bg-card border border-border rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">
                  {{ t('system.system.info.quickActions.title') }}
                </h2>
                <div class="grid grid-cols-2 gap-3">
                  <router-link
                    :to="consolePath('/settings')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Settings class="h-7 w-7 text-primary mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.settings') }}</span>
                  </router-link>
                          
                  <router-link
                    :to="consolePath('/backups')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Download class="h-7 w-7 text-green-600 dark:text-green-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.backups') }}</span>
                  </router-link>
                          
                  <router-link
                    :to="consolePath('/redis')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Database class="h-7 w-7 text-red-500 dark:text-red-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.redis') }}</span>
                  </router-link>
                          
                  <router-link
                    :to="consolePath('/scheduled-tasks')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Clock class="h-7 w-7 text-blue-500 dark:text-blue-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.scheduledTasks') }}</span>
                  </router-link>
                          
                  <router-link
                    :to="consolePath('/scheduled-tasks?action=run_command')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Terminal class="h-7 w-7 text-yellow-500 dark:text-yellow-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.commandRunner') }}</span>
                  </router-link>

                  <router-link
                    :to="consolePath('/system/notifications')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Bell class="h-7 w-7 text-purple-500 dark:text-purple-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.notifications') }}</span>
                  </router-link>

                  <router-link
                    :to="consolePath('/settings?tab=email')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <Mail class="h-7 w-7 text-orange-500 dark:text-orange-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.emailSettings') }}</span>
                  </router-link>

                  <router-link
                    :to="consolePath('/email-templates')"
                    class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
                  >
                    <FileText class="h-7 w-7 text-sky-500 dark:text-sky-400 mb-2" />
                    <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.emailTemplates') }}</span>
                  </router-link>
                </div>
              </div>
            </div>

            <!-- System Care & Maintenance Centre -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
              <div class="flex items-center gap-3 mb-4">
                <Settings class="h-6 w-6 text-primary" />
                <div>
                  <h2 class="text-lg font-bold text-foreground">
                    {{ t('system.system.info.maintenance.title') }}
                  </h2>
                  <p class="text-sm text-muted-foreground">
                    {{ t('system.system.info.maintenance.subtitle') }}
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <!-- Clean Junk Files -->
                <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-2">
                      <Trash2 class="h-5 w-5 text-red-500" />
                      <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.junk.title') }}</h3>
                    </div>
                    <p class="text-xs text-muted-foreground mb-4">
                      {{ t('system.system.info.maintenance.cards.junk.description') }}
                    </p>
                  </div>
                  <button
                    type="button"
                    @click="handleCleanJunk"
                    :disabled="actionLoading"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                    {{ t('system.system.info.maintenance.cards.junk.action') }}
                  </button>
                </div>

                <!-- Optimize Database -->
                <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-2">
                      <Database class="h-5 w-5 text-indigo-500" />
                      <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.database.title') }}</h3>
                    </div>
                    <p class="text-xs text-muted-foreground mb-4">
                      {{ t('system.system.info.maintenance.cards.database.description') }}
                    </p>
                  </div>
                  <button
                    type="button"
                    @click="handleOptimizeDb"
                    :disabled="actionLoading"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
                  >
                    <Database class="h-3.5 w-3.5" />
                    {{ t('system.system.info.maintenance.cards.database.action') }}
                  </button>
                </div>

                <!-- Performance Boost -->
                <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-2">
                      <Zap class="h-5 w-5 text-amber-500" />
                      <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.performance.title') }}</h3>
                    </div>
                    <p class="text-xs text-muted-foreground mb-4">
                      {{ t('system.system.info.maintenance.cards.performance.description') }}
                    </p>
                  </div>
                  <button
                    type="button"
                    @click="handleBoostPerf"
                    :disabled="actionLoading"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-md text-xs font-semibold disabled:opacity-50 transition"
                  >
                    <Zap class="h-3.5 w-3.5" />
                    {{ t('system.system.info.maintenance.cards.performance.action') }}
                  </button>
                </div>

                <!-- Factory Reset -->
                <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-2">
                      <RefreshCw class="h-5 w-5 text-rose-600" />
                      <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.reset.title') }}</h3>
                    </div>
                    <p class="text-xs text-muted-foreground mb-4">
                      {{ t('system.system.info.maintenance.cards.reset.description') }}
                    </p>
                  </div>
                  <button
                    type="button"
                    @click="openResetModal"
                    :disabled="actionLoading"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
                  >
                    <RefreshCw class="h-3.5 w-3.5" />
                    {{ t('system.system.info.maintenance.cards.reset.action') }}
                  </button>
                </div>
              </div>

              <!-- Operations Console Output -->
              <div v-if="consoleOutput" class="mt-6 border border-border bg-black rounded-lg p-4 font-mono text-xs text-green-400 max-h-48 overflow-y-auto">
                <div class="flex justify-between items-center mb-2 border-b border-green-900 pb-2">
                  <span>{{ t('system.system.info.maintenance.console.header') }}</span>
                  <button type="button" @click="consoleOutput = ''" class="text-red-400 hover:underline">{{ t('system.system.info.maintenance.console.clear') }}</button>
                </div>
                <pre class="whitespace-pre-wrap">{{ consoleOutput }}</pre>
              </div>
            </div>
          </div>

          <!-- TAB 2: SYSTEM REQUIREMENTS & DIAGNOSTICS MATRIX -->
          <div v-else-if="activeTab === 'requirements'" class="space-y-6">
            <!-- Score & Quick Actions Banner -->
            <div class="bg-gradient-to-br from-card to-accent/30 border border-border rounded-xl p-6 shadow-sm">
              <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <!-- Score Breakdown -->
                <div class="flex items-center gap-5">
                  <div
                    class="w-20 h-20 rounded-2xl flex flex-col items-center justify-center border-2 font-black"
                    :class="requirementsData?.overview?.is_ready ? 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/30' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30'"
                  >
                    <span class="text-2xl leading-none">{{ requirementsData?.overview?.score_percent || 0 }}%</span>
                    <span class="text-[10px] tracking-wider uppercase mt-1 font-bold">Kesiapan</span>
                  </div>

                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <h2 class="text-xl font-bold text-foreground">
                        {{ t('system.system.info.requirements.title') }}
                      </h2>
                      <span
                        class="px-2.5 py-0.5 text-xs font-bold rounded-full"
                        :class="requirementsData?.overview?.is_ready ? 'bg-green-500/15 text-green-600 dark:text-green-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'"
                      >
                        {{ requirementsData?.overview?.is_ready ? t('system.system.info.requirements.ready') : t('system.system.info.requirements.needs_attention') }}
                      </span>
                    </div>
                    <p class="text-xs text-muted-foreground max-w-xl">
                      {{ t('system.system.info.requirements.subtitle') }}
                    </p>

                    <!-- Summary Badges -->
                    <div class="flex flex-wrap items-center gap-2 mt-3 text-xs">
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-500/10 text-green-600 dark:text-green-400 font-semibold border border-green-500/20">
                        <CheckCircle class="h-3.5 w-3.5" />
                        {{ requirementsData?.overview?.passed || 0 }} Lulus
                      </span>
                      <span v-if="requirementsData?.overview?.warnings" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold border border-amber-500/20">
                        <AlertTriangle class="h-3.5 w-3.5" />
                        {{ t('system.system.info.requirements.warnings_count', { count: requirementsData.overview.warnings }) }}
                      </span>
                      <span v-if="requirementsData?.overview?.errors" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-500/10 text-red-600 dark:text-red-400 font-semibold border border-red-500/20">
                        <XCircle class="h-3.5 w-3.5" />
                        {{ t('system.system.info.requirements.errors_count', { count: requirementsData.overview.errors }) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2 self-stretch lg:self-auto justify-end">
                  <button
                    type="button"
                    @click="handleAutoFix"
                    :disabled="autoFixing || reqLoading"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg text-xs font-bold shadow-sm transition disabled:opacity-50"
                  >
                    <Wrench v-if="!autoFixing" class="h-4 w-4" />
                    <Loader2 v-else class="h-4 w-4 animate-spin" />
                    <span>{{ autoFixing ? t('system.system.info.requirements.autofixing') : t('system.system.info.requirements.autofix_btn') }}</span>
                  </button>

                  <button
                    type="button"
                    @click="fetchRequirements"
                    :disabled="reqLoading"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-accent hover:bg-accent/80 text-foreground rounded-lg text-xs font-semibold border border-border transition"
                  >
                    <RotateCcw class="h-3.5 w-3.5" :class="reqLoading ? 'animate-spin' : ''" />
                    <span>{{ t('system.system.info.requirements.recheck') }}</span>
                  </button>
                </div>
              </div>

              <!-- Server Specs Quick Bar -->
              <div v-if="requirementsData?.server_spec" class="mt-6 pt-4 border-t border-border/70 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
                  <span class="text-[10px] text-muted-foreground block font-medium">OS / Distro</span>
                  <span class="text-xs font-bold text-foreground truncate block" :title="requirementsData.server_spec.distro">
                    {{ requirementsData.server_spec.distro }}
                  </span>
                </div>
                <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
                  <span class="text-[10px] text-muted-foreground block font-medium">PHP Runtime</span>
                  <span class="text-xs font-bold text-foreground font-mono block">
                    {{ requirementsData.server_spec.php_version }} ({{ requirementsData.server_spec.php_sapi }})
                  </span>
                </div>
                <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
                  <span class="text-[10px] text-muted-foreground block font-medium">Database</span>
                  <span class="text-xs font-bold text-foreground truncate block" :title="requirementsData.server_spec.database_version">
                    {{ requirementsData.server_spec.database_engine.toUpperCase() }}
                  </span>
                </div>
                <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
                  <span class="text-[10px] text-muted-foreground block font-medium">Redis In-Memory</span>
                  <span class="text-xs font-bold text-foreground block">
                    v{{ requirementsData.server_spec.redis_version }} ({{ requirementsData.server_spec.redis_latency }})
                  </span>
                </div>
                <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
                  <span class="text-[10px] text-muted-foreground block font-medium">Queue Worker</span>
                  <span class="text-xs font-bold text-foreground block">
                    {{ requirementsData.server_spec.queue_workers_count > 0 ? `${requirementsData.server_spec.queue_workers_count} Active` : 'Sync / Idle' }}
                  </span>
                </div>
                <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
                  <span class="text-[10px] text-muted-foreground block font-medium">Scheduler Cron</span>
                  <span
                    class="text-xs font-bold block"
                    :class="requirementsData.server_spec.cron_configured ? 'text-green-600 dark:text-green-400' : 'text-amber-500'"
                  >
                    {{ requirementsData.server_spec.cron_configured ? 'Configured (* * * * *)' : 'Unconfigured' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Controls: Category Filter Tabs & Search -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
              <!-- Filter Tabs -->
              <div class="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full">
                <button
                  v-for="cat in categoryList"
                  :key="cat.id"
                  type="button"
                  @click="selectedCategory = cat.id"
                  :class="selectedCategory === cat.id ? 'bg-primary text-primary-foreground font-bold shadow-sm' : 'bg-accent/40 text-muted-foreground hover:text-foreground border border-border'"
                  class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition"
                >
                  {{ cat.name }}
                </button>
              </div>

              <!-- Search Input -->
              <div class="relative min-w-[240px]">
                <Search class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                <input
                  type="text"
                  v-model="searchQuery"
                  :placeholder="t('system.system.info.requirements.search_placeholder')"
                  class="w-full pl-9 pr-3 py-1.5 bg-accent/20 border border-border rounded-lg text-xs text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40"
                />
              </div>
            </div>

            <!-- Requirements Matrix Items -->
            <div class="space-y-3">
              <div
                v-for="item in filteredRequirements"
                :key="item.id"
                class="bg-card border border-border/90 rounded-xl p-4 transition hover:border-border shadow-sm"
              >
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                  <!-- Left: Status Icon & Details -->
                  <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <div class="mt-0.5 flex-shrink-0">
                      <CheckCircle2
                        v-if="item.status === 'ok'"
                        class="h-5 w-5 text-green-600 dark:text-green-400"
                      />
                      <AlertTriangle
                        v-else-if="item.status === 'warning'"
                        class="h-5 w-5 text-amber-500"
                      />
                      <XCircle
                        v-else
                        class="h-5 w-5 text-red-500"
                      />
                    </div>

                    <div class="min-w-0 flex-1">
                      <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-bold text-sm text-foreground">{{ item.name }}</span>
                        <span
                          v-if="item.required"
                          class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary border border-primary/20"
                        >
                          Wajib
                        </span>
                        <span
                          v-else
                          class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-muted text-muted-foreground"
                        >
                          Rekomendasi
                        </span>
                        <span class="text-[11px] text-muted-foreground font-mono bg-accent/50 px-2 py-0.5 rounded">
                          {{ formatCategory(item.category) }}
                        </span>
                      </div>

                      <p class="text-xs text-muted-foreground leading-relaxed">
                        {{ item.description }}
                      </p>
                    </div>
                  </div>

                  <!-- Right: Values & Remediation Button -->
                  <div class="flex sm:flex-col items-end justify-between sm:justify-center gap-2 self-stretch sm:self-auto border-t sm:border-t-0 pt-2 sm:pt-0 border-border/40">
                    <div class="text-right">
                      <div class="flex items-center gap-1.5 justify-end">
                        <span class="text-[11px] text-muted-foreground">Terdeteksi:</span>
                        <span
                          class="text-xs font-mono font-bold"
                          :class="item.status === 'ok' ? 'text-green-600 dark:text-green-400' : item.status === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'"
                        >
                          {{ item.current_value }}
                        </span>
                      </div>
                      <div class="text-[10px] text-muted-foreground">
                        Min: <span class="font-medium text-foreground">{{ item.required_value }}</span>
                      </div>
                    </div>

                    <button
                      v-if="item.status !== 'ok' || activeGuideId === item.id"
                      type="button"
                      @click="toggleGuide(item.id)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-accent/50 hover:bg-accent text-foreground rounded-md text-[11px] font-semibold transition border border-border"
                    >
                      <HelpCircle class="h-3 w-3 text-primary" />
                      <span>{{ activeGuideId === item.id ? 'Tutup Panduan' : 'Panduan Perbaikan' }}</span>
                      <ChevronDown class="h-3 w-3 transition-transform" :class="activeGuideId === item.id ? 'rotate-180' : ''" />
                    </button>
                  </div>
                </div>

                <!-- Remediation Guide Accordion Drawer -->
                <div
                  v-if="activeGuideId === item.id && item.fix_guide"
                  class="mt-4 pt-3 border-t border-border/60 bg-accent/15 -mx-4 -mb-4 p-4 rounded-b-xl space-y-3"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-foreground flex items-center gap-1.5">
                      <Terminal class="h-3.5 w-3.5 text-primary" />
                      {{ t('system.system.info.requirements.guide.title') }}
                    </span>
                    <span class="text-[10px] text-muted-foreground">Klik perintah untuk menyalin</span>
                  </div>

                  <!-- Guide Snippets -->
                  <div class="space-y-2 text-xs">
                    <!-- Ubuntu / Debian -->
                    <div v-if="item.fix_guide.ubuntu" class="bg-background/80 border border-border rounded-lg p-2.5">
                      <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold text-muted-foreground">
                          {{ t('system.system.info.requirements.guide.ubuntu') }}
                        </span>
                        <button
                          type="button"
                          @click="copySnippet(item.fix_guide.ubuntu)"
                          class="inline-flex items-center gap-1 text-[10px] text-primary hover:underline"
                        >
                          <Copy class="h-3 w-3" /> Salin
                        </button>
                      </div>
                      <code class="block font-mono text-[11px] text-green-600 dark:text-green-400 select-all break-all">
                        {{ item.fix_guide.ubuntu }}
                      </code>
                    </div>

                    <!-- RHEL / AlmaLinux / CentOS -->
                    <div v-if="item.fix_guide.rhel" class="bg-background/80 border border-border rounded-lg p-2.5">
                      <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold text-muted-foreground">
                          {{ t('system.system.info.requirements.guide.rhel') }}
                        </span>
                        <button
                          type="button"
                          @click="copySnippet(item.fix_guide.rhel)"
                          class="inline-flex items-center gap-1 text-[10px] text-primary hover:underline"
                        >
                          <Copy class="h-3 w-3" /> Salin
                        </button>
                      </div>
                      <code class="block font-mono text-[11px] text-green-600 dark:text-green-400 select-all break-all">
                        {{ item.fix_guide.rhel }}
                      </code>
                    </div>

                    <!-- General / cPanel -->
                    <div v-if="item.fix_guide.general" class="bg-background/80 border border-border rounded-lg p-2.5">
                      <span class="text-[11px] font-bold text-muted-foreground block mb-1">
                        {{ t('system.system.info.requirements.guide.general') }}
                      </span>
                      <p class="text-xs text-foreground">
                        {{ item.fix_guide.general }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="filteredRequirements.length === 0" class="text-center py-12 bg-accent/10 border border-dashed border-border rounded-xl">
                <Search class="h-8 w-8 mx-auto text-muted-foreground mb-2 opacity-50" />
                <p class="text-sm font-semibold text-foreground">Tidak ada kebutuhan sistem yang cocok</p>
                <p class="text-xs text-muted-foreground mt-1">Coba gunakan kata kunci pencarian atau filter kategori lain.</p>
              </div>
            </div>
          </div>

          <!-- Factory Reset Password Confirmation Modal -->
          <div v-if="showResetModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="bg-card border border-rose-500/30 max-w-lg w-full rounded-xl p-6 shadow-2xl relative overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-tr from-rose-500/10 to-transparent pointer-events-none" />
              
              <div class="flex items-center gap-3 mb-6 border-b border-border pb-4">
                <div class="p-3 rounded-xl bg-rose-500/20 text-rose-500">
                  <AlertTriangle class="h-8 w-8 animate-pulse" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-foreground">Peringatan: Factory Reset</h3>
                  <p class="text-xs text-muted-foreground">Tindakan ini bersifat destruktif dan permanen.</p>
                </div>
              </div>

              <!-- Pre-Reset Protections State -->
              <div v-if="!isResetting">
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 mb-6">
                  <p class="text-sm text-amber-500 font-semibold mb-2">Sangat Disarankan: Backup Data Anda</p>
                  <p class="text-xs text-amber-500/80 mb-3">
                    Sebelum melanjutkan, buat salinan data untuk mencegah kehilangan permanen.
                  </p>
                  <router-link :to="consolePath('/backups')" class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-500 rounded text-xs font-semibold transition">
                    <Download class="h-3.5 w-3.5" /> Lakukan Backup Sekarang
                  </router-link>
                </div>

                <div class="space-y-3 mb-6">
                  <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="pt-0.5">
                      <input type="checkbox" v-model="resetConfirm1" class="w-4 h-4 rounded border-border text-rose-600 focus:ring-rose-500 bg-accent" />
                    </div>
                    <span class="text-xs text-foreground group-hover:text-rose-400 transition">Saya mengerti bahwa <strong>seluruh isi database</strong> akan dikosongkan.</span>
                  </label>
                  
                  <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="pt-0.5">
                      <input type="checkbox" v-model="resetConfirm2" class="w-4 h-4 rounded border-border text-rose-600 focus:ring-rose-500 bg-accent" />
                    </div>
                    <span class="text-xs text-foreground group-hover:text-rose-400 transition">Saya mengerti bahwa <strong>seluruh file media dan unggahan</strong> akan dihapus permanen.</span>
                  </label>

                  <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="pt-0.5">
                      <input type="checkbox" v-model="resetConfirm3" class="w-4 h-4 rounded border-border text-rose-600 focus:ring-rose-500 bg-accent" />
                    </div>
                    <span class="text-xs text-foreground group-hover:text-rose-400 transition">Saya mengerti bahwa tindakan ini <strong>tidak dapat dibatalkan</strong>.</span>
                  </label>
                </div>

                <div class="space-y-4 mb-2">
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground mb-1.5">
                      Ketik <strong class="text-foreground select-all">RESET SYSTEM</strong> untuk mengonfirmasi
                    </label>
                    <input
                      type="text"
                      v-model="resetChallenge"
                      placeholder="RESET SYSTEM"
                      class="w-full px-3 py-2 bg-accent/20 border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-rose-500 font-mono uppercase tracking-widest"
                    />
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground mb-1.5">
                      {{ t('system.system.info.maintenance.resetModal.passwordLabel') }}
                    </label>
                    <input
                      type="password"
                      v-model="resetPassword"
                      :placeholder="t('common.placeholders.passwordMask')"
                      class="w-full px-3 py-2 bg-accent/20 border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-rose-500"
                    />
                  </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 border-t border-border pt-4">
                  <button
                    type="button"
                    @click="showResetModal = false"
                    class="px-4 py-2 bg-accent hover:bg-accent/80 text-foreground rounded-lg text-xs font-semibold transition"
                  >
                    {{ t('system.system.info.maintenance.resetModal.cancel') }}
                  </button>
                  <button
                    type="button"
                    @click="handleFactoryReset"
                    :disabled="!canReset"
                    class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2"
                  >
                    <RefreshCw class="h-3.5 w-3.5" /> MULAI FACTORY RESET
                  </button>
                </div>
              </div>

              <!-- Resetting Progress State -->
              <div v-else class="py-6 text-center">
                <div class="mb-6 relative w-24 h-24 mx-auto">
                  <RefreshCw class="h-24 w-24 text-rose-500 animate-spin opacity-20" />
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl font-bold text-foreground">{{ resetProgress }}%</span>
                  </div>
                </div>
                
                <h4 class="text-lg font-bold text-foreground mb-2">Memproses Factory Reset</h4>
                <p class="text-sm text-rose-400 font-mono mb-6 animate-pulse">{{ resetStepText }}</p>
                
                <div class="w-full bg-accent/50 rounded-full h-2 mb-4 overflow-hidden border border-border">
                  <div class="bg-rose-500 h-2 rounded-full transition-all duration-500 ease-out" :style="{ width: `${resetProgress}%` }"></div>
                </div>
                
                <p class="text-xs text-muted-foreground">Mohon jangan menutup jendela ini atau me-refresh browser.</p>
              </div>
            </div>
          </div>
        </template>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { useToast } from '@/shared/composables/useToast';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import {
  Activity,
  AlertTriangle,
  Bell,
  CheckCircle,
  CheckCircle2,
  ChevronDown,
  Clock,
  Copy,
  Database,
  Download,
  FileText,
  HelpCircle,
  Loader2,
  Mail,
  RefreshCw,
  RotateCcw,
  Search,
  Settings,
  ShieldCheck,
  Terminal,
  Trash2,
  Wrench,
  XCircle,
  Zap,
} from 'lucide-vue-next';

interface DiskUsage {
    used: string;
    total: string;
    percent?: number;
}

interface SystemInfo {
    uptime: number;
    php_version: string;
    laravel_version: string;
    environment: string;
    debug_mode: boolean;
    server_software: string;
    os_distro?: string;
    os_kernel?: string;
    php_sapi?: string;
    database_version?: string;
    memory_usage: string;
    memory_usage_percent: number;
    disk_usage: DiskUsage | string;
    disk_usage_percent: number;
    database: string;
    cache_driver?: string;
    session_driver?: string;
}

interface CacheData {
    status: string;
}

interface RequirementFixGuide {
    ubuntu: string;
    rhel: string;
    general: string;
}

interface RequirementItem {
    id: string;
    name: string;
    category: string;
    required: boolean;
    current_value: string;
    required_value: string;
    status: 'ok' | 'warning' | 'error';
    description: string;
    fix_guide: RequirementFixGuide;
    can_autofix: boolean;
}

interface ServerSpec {
    distro: string;
    kernel: string;
    php_version: string;
    php_sapi: string;
    web_server: string;
    database_engine: string;
    database_version: string;
    redis_version: string;
    redis_latency: string;
    redis_memory: string;
    node_version: string;
    npm_version: string;
    queue_workers_count: number;
    cron_configured: boolean;
}

interface RequirementsData {
    overview: {
        total: number;
        passed: number;
        warnings: number;
        errors: number;
        score_percent: number;
        is_ready: boolean;
    };
    server_spec: ServerSpec;
    items: RequirementItem[];
}

const { t } = useI18n();
const toast = useToast();

const activeTab = ref<'overview' | 'requirements'>('overview');
const loading = ref(true);
const reqLoading = ref(false);
const autoFixing = ref(false);
const activeGuideId = ref<string | null>(null);
const selectedCategory = ref<string>('all');
const searchQuery = ref<string>('');

const systemInfo = ref<Partial<SystemInfo>>({});
const requirementsData = ref<RequirementsData | null>(null);
const CACHE_STATUS_ACTIVE = 'active';
const cacheStatus = ref(CACHE_STATUS_ACTIVE);

const categoryList = computed(() => [
    { id: 'all', name: t('system.system.info.requirements.categories.all') },
    { id: 'php_core', name: t('system.system.info.requirements.categories.php_core') },
    { id: 'php_extensions', name: t('system.system.info.requirements.categories.php_extensions') },
    { id: 'database', name: t('system.system.info.requirements.categories.database') },
    { id: 'caching', name: t('system.system.info.requirements.categories.caching') },
    { id: 'storage_permissions', name: t('system.system.info.requirements.categories.storage_permissions') },
    { id: 'background_services', name: t('system.system.info.requirements.categories.background_services') },
]);

const filteredRequirements = computed(() => {
    if (!requirementsData.value?.items) return [];
    let list = requirementsData.value.items;

    if (selectedCategory.value !== 'all') {
        list = list.filter(item => item.category === selectedCategory.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(item => 
            item.name.toLowerCase().includes(q) || 
            item.description.toLowerCase().includes(q) ||
            item.current_value.toLowerCase().includes(q)
        );
    }

    return list;
});

const cacheStatusLabel = computed(() => {
    const s = (cacheStatus.value || '').toLowerCase();
    if (s === 'active' || s === 'aktif') return t('system.system.info.cache.active');
    return t('system.system.info.cache.inactive');
});

const systemHealth = computed(() => {
    if (requirementsData.value?.overview) {
        if (requirementsData.value.overview.errors > 0) return 'critical';
        if (requirementsData.value.overview.warnings > 0) return 'warning';
    }
    
    const memoryUsage = systemInfo.value.memory_usage_percent || 0;
    const diskUsage = systemInfo.value.disk_usage_percent || 0;
    
    if (memoryUsage > 90 || diskUsage > 90) return 'critical';
    if (memoryUsage > 75 || diskUsage > 75) return 'warning';
    return 'healthy';
});

const displayMemory = computed(() => {
    if (!systemInfo.value.memory_usage) return '-';
    if (typeof systemInfo.value.memory_usage === 'string') {
        return systemInfo.value.memory_usage;
    }
    return formatBytes(systemInfo.value.memory_usage as number);
});

const displayDisk = computed(() => {
    const usage = systemInfo.value.disk_usage;
    if (!usage) return '-';
    if (typeof usage === 'object') {
        return `${usage.used} / ${usage.total} (${usage.percent || 0}%)`;
    }
    return usage;
});

const formatCategory = (cat: string): string => {
    switch (cat) {
        case 'php_core': return 'PHP Core';
        case 'php_extensions': return 'PHP Extension';
        case 'database': return 'Database';
        case 'caching': return 'Cache & Redis';
        case 'storage_permissions': return 'Folder Storage';
        case 'background_services': return 'Background Service';
        default: return cat;
    }
};

const toggleGuide = (id: string) => {
    if (activeGuideId.value === id) {
        activeGuideId.value = null;
    } else {
        activeGuideId.value = id;
    }
};

const copySnippet = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        toast.success.action(t('common.messages.copied'));
    } catch {
        toast.error.action('Failed to copy to clipboard');
    }
};

const fetchSystemInfo = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/info');
        systemInfo.value = parseSingleResponse<SystemInfo>(response) || {};

        try {
            const cacheResponse = await api.get('/manage/system/cache-status');
            cacheStatus.value = parseSingleResponse<CacheData>(cacheResponse)?.status || CACHE_STATUS_ACTIVE;
        } catch (error: unknown) {
            logger.warning('Failed to fetch cache status:', error);
            cacheStatus.value = CACHE_STATUS_ACTIVE;
        }

        // Fetch requirements in background
        await fetchRequirements();
    } catch (error: unknown) {
        logger.error('Failed to fetch system info:', error);
    } finally {
        loading.value = false;
    }
};

const fetchRequirements = async (): Promise<void> => {
    reqLoading.value = true;
    try {
        const res = await api.get('/manage/system/requirements');
        requirementsData.value = parseSingleResponse<RequirementsData>(res) || null;
    } catch (error: unknown) {
        logger.error('Failed to fetch system requirements:', error);
    } finally {
        reqLoading.value = false;
    }
};

const handleAutoFix = async (): Promise<void> => {
    autoFixing.value = true;
    try {
        const res = await api.post('/manage/system/requirements/autofix');
        const data = parseSingleResponse<{ fixed?: string[]; failed?: string[]; message?: string }>(res);
        toast.success.action(data?.message || t('system.system.info.requirements.autofix_success'));
        await fetchRequirements();
    } catch (error: unknown) {
        logger.error('Auto fix failed:', error);
        toast.error.fromResponse(error);
    } finally {
        autoFixing.value = false;
    }
};

const formatUptime = (seconds?: number): string => {
    if (!seconds) return '-';
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
};

const formatBytes = (bytes: number): string => {
    if (!bytes || typeof bytes !== 'number') return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

// Maintenance Centre State & Handlers
const actionLoading = ref(false);
const consoleOutput = ref('');
const showResetModal = ref(false);
const resetPassword = ref('');

// Advanced Reset Protections
const resetConfirm1 = ref(false);
const resetConfirm2 = ref(false);
const resetConfirm3 = ref(false);
const resetChallenge = ref('');
const resetProgress = ref(0);
const resetStepText = ref('');
const isResetting = ref(false);

const canReset = computed(() => {
    return resetConfirm1.value && 
           resetConfirm2.value && 
           resetConfirm3.value && 
           resetChallenge.value === 'RESET SYSTEM' && 
           resetPassword.value.length > 0 &&
           !isResetting.value;
});

const appendConsole = (msg: string): void => {
    const timestamp = new Date().toLocaleTimeString();
    consoleOutput.value += `[${timestamp}] ${msg}\n`;
};

const handleCleanJunk = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.junk.start'));
    try {
        const res = await api.post('/manage/system/maintenance/clean-junk');
        const data = parseSingleResponse<{ deleted_files?: number; files_removed?: number; freed_bytes?: number; cleaned_bytes?: number }>(res);
        const count = data?.deleted_files ?? data?.files_removed ?? 0;
        const bytes = data?.freed_bytes ?? data?.cleaned_bytes ?? 0;
        appendConsole(t('system.system.info.maintenance.logs.junk.success'));
        appendConsole(t('system.system.info.maintenance.logs.junk.filesDeleted', { count }));
        appendConsole(t('system.system.info.maintenance.logs.junk.spaceFreed', { size: formatBytes(bytes) }));
    } catch (err: unknown) {
        logger.error('Failed to clean junk:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.junk.error', { message }));
    } finally {
        actionLoading.value = false;
    }
};

const handleOptimizeDb = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.database.start'));
    try {
        const res = await api.post('/manage/system/maintenance/optimize-db');
        const data = parseSingleResponse<{ optimized_tables: number; purged_orphans: number }>(res);
        appendConsole(t('system.system.info.maintenance.logs.database.success'));
        appendConsole(t('system.system.info.maintenance.logs.database.tablesOptimized', { count: data?.optimized_tables || 0 }));
        appendConsole(t('system.system.info.maintenance.logs.database.orphansPurged', { count: data?.purged_orphans || 0 }));
    } catch (err: unknown) {
        logger.error('Failed to optimize database:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.database.error', { message }));
    } finally {
        actionLoading.value = false;
    }
};

const handleBoostPerf = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.performance.start'));
    try {
        await api.post('/manage/system/maintenance/boost');
        appendConsole(t('system.system.info.maintenance.logs.performance.success'));
        appendConsole(t('system.system.info.maintenance.logs.performance.mode'));
    } catch (err: unknown) {
        logger.error('Failed to boost performance:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.performance.error', { message }));
    } finally {
        actionLoading.value = false;
    }
};

const openResetModal = (): void => {
    resetPassword.value = '';
    resetConfirm1.value = false;
    resetConfirm2.value = false;
    resetConfirm3.value = false;
    resetChallenge.value = '';
    resetProgress.value = 0;
    resetStepText.value = '';
    isResetting.value = false;
    showResetModal.value = true;
};

const handleFactoryReset = async (): Promise<void> => {
    if (!canReset.value) return;
    
    isResetting.value = true;
    resetProgress.value = 10;
    resetStepText.value = 'Preparing Factory Reset...';
    appendConsole(t('system.system.info.maintenance.logs.reset.start'));

    try {
        window.__factoryResetInProgress = true;
        const payload = { password: resetPassword.value };

        resetStepText.value = 'Step 1/3: Clearing Sandboxes & Cache...';
        await api.post('/manage/system/maintenance/factory-reset/step-1', payload);
        resetProgress.value = 40;
        appendConsole('Sandbox & Caches cleared.');

        resetStepText.value = 'Step 2/3: Wiping Media & Logs...';
        await api.post('/manage/system/maintenance/factory-reset/step-2', payload);
        resetProgress.value = 75;
        appendConsole('Media and logs wiped.');

        resetStepText.value = 'Step 3/3: Wiping Database Schema...';
        const res = await api.post('/manage/system/maintenance/factory-reset/step-3', payload);
        const setupToken = String(
            (res.data as { setup_token?: string })?.setup_token
            ?? (res.data as { data?: { setup_token?: string } })?.data?.setup_token
            ?? '',
        ).trim();

        if (!setupToken) {
            throw new Error('Setup token missing from factory reset response');
        }

        resetProgress.value = 100;
        resetStepText.value = 'Reset Complete. Redirecting to setup...';
        appendConsole('Database migrated. Redirecting to post-reset setup.');

        const { resetLockdown } = await import('@/engine/api/client');
        resetLockdown();
        window.__factoryResetInProgress = true;
        window.location.replace(`/setup?token=${encodeURIComponent(setupToken)}`);
        return;
    } catch (err: unknown) {
        window.__factoryResetInProgress = false;
        logger.error('Failed to factory reset:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.reset.error', { message }));
        isResetting.value = false;
    }
};

onMounted(() => {
    fetchSystemInfo();
});
</script>
