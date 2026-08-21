<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.redis.title')"
      :subtitle="t('system.redis.description')"
    />

    <!-- Shadcn Tabs -->
    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <div class="mb-6">
        <TabsList class="bg-transparent p-0 h-auto gap-0 border-b border-border/70 w-full justify-start">
          <TabsTrigger
            value="statistics"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none font-semibold text-xs transition"
          >
            <BarChart3 class="w-4 h-4 mr-2" />
            {{ t('system.redis.tabs.statistics') }}
          </TabsTrigger>
          <TabsTrigger
            value="explorer"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none font-semibold text-xs transition"
          >
            <Search class="w-4 h-4 mr-2" />
            {{ t('system.redis.tabs.explorer') }}
          </TabsTrigger>
          <TabsTrigger
            value="cache"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none font-semibold text-xs transition"
          >
            <Database class="w-4 h-4 mr-2" />
            {{ t('system.redis.tabs.cache') }}
          </TabsTrigger>
          <TabsTrigger
            value="settings"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none font-semibold text-xs transition"
          >
            <Settings class="w-4 h-4 mr-2" />
            {{ t('system.redis.tabs.settings') }}
          </TabsTrigger>
        </TabsList>
      </div>

      <!-- 1. Statistics Tab -->
      <TabsContent
        value="statistics"
        class="space-y-6"
      >
        <Card
          v-if="stats.total_keys === 0 && (stats.hits > 0 || stats.misses > 0 || stats.total_commands > 0)"
          class="border-blue-500/30 bg-blue-500/5 shadow-sm"
        >
          <CardContent class="pt-4 pb-4">
            <p class="text-xs text-blue-700 dark:text-blue-300 leading-relaxed">
              {{ t('system.redis.statistics.hitMiss.keysEmptyNotice') }}
            </p>
          </CardContent>
        </Card>

        <!-- Top Statistics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.version')"
            :value="stats.version || '-'"
            :icon="Database"
            tone="info"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.memory')"
            :value="stats.used_memory || '-'"
            :icon="HardDrive"
            tone="success"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.peakMemory')"
            :value="stats.used_memory_peak || '-'"
            :icon="Flame"
            tone="warning"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.keys')"
            :value="stats.total_keys || 0"
            :icon="Zap"
            tone="primary"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.uptime')"
            :value="stats.uptime_days || '-'"
            :icon="Clock"
            tone="muted"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.clients')"
            :value="stats.connected_clients || 0"
            :icon="Users"
            tone="primary"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.hitRate')"
            :value="stats.hit_rate || '0%'"
            :icon="Target"
            tone="info"
          />
          <ConsoleStatCard
            :label="t('system.redis.statistics.grid.ops')"
            :value="stats.operations_per_sec || 0"
            :icon="Activity"
            tone="success"
          />
        </div>

        <!-- Hit / Miss Breakdown Cards -->
        <Card class="border border-border/80 shadow-sm">
          <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
            <CardTitle class="text-base font-bold flex items-center gap-2">
              <Activity class="w-4 h-4 text-primary" />
              {{ t('system.redis.statistics.hitMiss.title') }}
            </CardTitle>
            <p class="text-xs text-muted-foreground">
              {{ t('system.redis.statistics.hitMiss.lifetime') }}
            </p>
          </CardHeader>
          <CardContent class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-xl border border-border/50 shadow-sm">
                <span class="font-medium text-sm text-foreground">{{ t('system.redis.statistics.hitMiss.hits') }}</span>
                <div class="text-right">
                  <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ formatNumber(stats.hits) }}</span>
                  <p class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">
                    {{ t('system.redis.statistics.hitMiss.cumulative') }}
                  </p>
                </div>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-xl border border-border/50 shadow-sm">
                <span class="font-medium text-sm text-foreground">{{ t('system.redis.statistics.hitMiss.misses') }}</span>
                <div class="text-right">
                  <span class="text-xl font-black text-destructive">{{ formatNumber(stats.misses) }}</span>
                  <p class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">
                    {{ t('system.redis.statistics.hitMiss.cumulative') }}
                  </p>
                </div>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-xl border border-border/50 shadow-sm">
                <span class="font-medium text-sm text-foreground">{{ t('system.redis.statistics.hitMiss.hitRate') }}</span>
                <div class="text-right">
                  <span class="text-xl font-black text-blue-600 dark:text-blue-400">{{ stats.hit_rate || '0%' }}</span>
                  <p class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">
                    {{ t('system.redis.statistics.grid.fragmentation') }}: {{ stats.mem_fragmentation_ratio || '1.0' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Local Snapshot Section -->
            <div class="mt-6 pt-5 border-t border-border/60">
              <h4 class="text-xs font-bold text-foreground mb-3 flex items-center gap-1.5">
                <Clock class="w-3.5 h-3.5 text-primary" />
                {{ t('system.redis.statistics.hitMiss.sinceLastClear') }}
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex justify-between items-center p-3.5 bg-muted/20 rounded-xl border border-border/50">
                  <span class="font-medium text-xs text-foreground">{{ t('system.redis.statistics.hitMiss.hits') }}</span>
                  <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ formatNumber(statsSinceLastClear.hits) }}</span>
                </div>
                <div class="flex justify-between items-center p-3.5 bg-muted/20 rounded-xl border border-border/50">
                  <span class="font-medium text-xs text-foreground">{{ t('system.redis.statistics.hitMiss.misses') }}</span>
                  <span class="text-base font-bold text-destructive">{{ formatNumber(statsSinceLastClear.misses) }}</span>
                </div>
                <div class="flex justify-between items-center p-3.5 bg-muted/20 rounded-xl border border-border/50">
                  <span class="font-medium text-xs text-foreground">{{ t('system.redis.statistics.hitMiss.hitRate') }}</span>
                  <span class="text-base font-bold text-blue-600 dark:text-blue-400">{{ statsSinceLastClear.hitRate }}</span>
                </div>
              </div>
              <p class="text-[11px] text-muted-foreground mt-2.5">
                {{ t('system.redis.statistics.hitMiss.snapshotNotice') }}
              </p>
            </div>
          </CardContent>
          <div class="px-6 py-3.5 border-t border-border/50 flex items-center justify-between bg-muted/10 rounded-b-xl">
            <Button 
              variant="outline"
              size="sm"
              :disabled="loadingStats" 
              @click="loadStats"
              class="text-xs"
            >
              <RefreshCw
                class="w-3.5 h-3.5 mr-2"
                :class="loadingStats ? 'animate-spin' : ''"
              />
              {{ loadingStats ? t('system.redis.messages.loading') : t('system.redis.statistics.refresh') }}
            </Button>
            <span class="text-[11px] text-muted-foreground font-medium">{{ t('system.redis.statistics.autoRefresh') }}</span>
          </div>
        </Card>
      </TabsContent>

      <!-- 2. Key Explorer Tab (New Feature) -->
      <TabsContent
        value="explorer"
        class="space-y-6"
      >
        <Card class="border border-border/80 shadow-sm">
          <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <CardTitle class="text-base font-bold flex items-center gap-2">
                  <Search class="w-4 h-4 text-primary" />
                  {{ t('system.redis.explorer.title') }}
                </CardTitle>
                <p class="text-xs text-muted-foreground mt-0.5">
                  {{ t('system.redis.explorer.subtitle') }}
                </p>
              </div>

              <!-- Connection Database Selector -->
              <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-muted-foreground">{{ t('system.redis.explorer.connection') }}</span>
                <select
                  v-model="explorerConnection"
                  @change="searchRedisKeys"
                  class="text-xs font-bold bg-accent/40 border border-border rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary/40 cursor-pointer"
                >
                  <option value="cache">{{ t('system.redis.explorer.cacheDb') }}</option>
                  <option value="default">{{ t('system.redis.explorer.defaultDb') }}</option>
                </select>
              </div>
            </div>
          </CardHeader>

          <CardContent class="p-6 space-y-4">
            <!-- Search Filter Bar -->
            <form @submit.prevent="searchRedisKeys" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
              <div class="relative flex-1">
                <Search class="h-3.5 w-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                <input
                  type="text"
                  v-model="searchPattern"
                  :placeholder="t('system.redis.explorer.searchPlaceholder')"
                  class="w-full pl-8 pr-8 py-2 bg-accent/20 border border-border rounded-lg text-xs text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition font-mono"
                />
                <button
                  v-if="searchPattern && searchPattern !== '*'"
                  type="button"
                  @click="searchPattern = '*'; searchRedisKeys()"
                  class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>

              <Button
                type="submit"
                size="sm"
                :disabled="searchingKeys"
                class="text-xs font-bold min-w-[120px]"
              >
                <Loader2 v-if="searchingKeys" class="w-3.5 h-3.5 mr-2 animate-spin" />
                <Search v-else class="w-3.5 h-3.5 mr-2" />
                {{ searchingKeys ? t('system.redis.explorer.searching') : t('system.redis.explorer.searchBtn') }}
              </Button>
            </form>

            <!-- Results Summary & Table -->
            <div class="flex items-center justify-between text-xs text-muted-foreground px-1">
              <span>{{ t('system.redis.explorer.totalFound', { count: explorerKeys.length }) }}</span>
              <span v-if="cacheStats.key_prefix" class="font-mono text-[11px] bg-accent/40 px-2 py-0.5 rounded">
                Prefix: {{ cacheStats.key_prefix }}
              </span>
            </div>

            <div v-if="explorerKeys.length > 0" class="border border-border/70 rounded-xl overflow-hidden shadow-sm">
              <Table>
                <TableHeader>
                  <TableRow class="bg-muted/40 hover:bg-muted/40">
                    <TableHead class="font-bold text-xs">{{ t('system.redis.explorer.key') }}</TableHead>
                    <TableHead class="font-bold text-xs w-24">{{ t('system.redis.explorer.type') }}</TableHead>
                    <TableHead class="font-bold text-xs w-28">{{ t('system.redis.explorer.size') }}</TableHead>
                    <TableHead class="font-bold text-xs w-28">{{ t('system.redis.explorer.ttl') }}</TableHead>
                    <TableHead class="font-bold text-xs text-right w-28">{{ t('system.redis.explorer.actions') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="item in explorerKeys"
                    :key="item.raw_key || item.key"
                    class="hover:bg-muted/30 transition text-xs"
                  >
                    <TableCell class="font-mono font-medium text-foreground py-2.5 break-all max-w-md">
                      {{ item.key }}
                    </TableCell>
                    <TableCell class="py-2.5">
                      <span
                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                        :class="getTypeBadgeClass(item.type)"
                      >
                        {{ item.type }}
                      </span>
                    </TableCell>
                    <TableCell class="py-2.5 font-mono text-muted-foreground font-medium">
                      {{ item.size }}
                    </TableCell>
                    <TableCell class="py-2.5 font-mono text-muted-foreground">
                      {{ item.ttl }}
                    </TableCell>
                    <TableCell class="py-2.5 text-right space-x-1 whitespace-nowrap">
                      <button
                        type="button"
                        @click="openKeyInspector(item)"
                        class="px-2 py-1 bg-primary/10 hover:bg-primary/20 text-primary rounded-md text-[11px] font-semibold transition"
                      >
                        {{ t('system.redis.explorer.inspect') }}
                      </button>
                      <button
                        type="button"
                        @click="handleDeleteKey(item)"
                        class="px-2 py-1 bg-destructive/10 hover:bg-destructive/20 text-destructive rounded-md text-[11px] font-semibold transition"
                      >
                        {{ t('system.redis.explorer.delete') }}
                      </button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Empty State -->
            <div v-else-if="!searchingKeys" class="p-12 text-center text-muted-foreground border border-dashed border-border rounded-xl bg-accent/5">
              <Database class="w-10 h-10 mx-auto opacity-25 mb-2" />
              <p class="text-sm font-semibold text-foreground">{{ t('system.redis.explorer.empty') }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Key Details Inspector Modal Drawer / Dialog -->
        <div
          v-if="selectedInspectKey"
          class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in"
          @click.self="selectedInspectKey = null"
        >
          <div class="bg-card border border-border rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between bg-muted/20">
              <div class="flex items-center gap-2">
                <Terminal class="w-4 h-4 text-primary" />
                <h3 class="font-bold text-sm text-foreground">
                  {{ t('system.redis.explorer.inspectModal.title') }}
                </h3>
              </div>
              <button
                type="button"
                @click="selectedInspectKey = null"
                class="text-muted-foreground hover:text-foreground rounded-lg p-1 transition"
              >
                <X class="w-4 h-4" />
              </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4 flex-1 text-xs">
              <!-- Key Metadata Grid -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-muted/30 p-3.5 rounded-xl border border-border/60">
                <div>
                  <span class="text-[10px] font-bold text-muted-foreground uppercase block">{{ t('system.redis.explorer.inspectModal.type') }}</span>
                  <span class="font-mono font-bold text-primary uppercase text-xs">{{ inspectDetails?.type || '-' }}</span>
                </div>
                <div>
                  <span class="text-[10px] font-bold text-muted-foreground uppercase block">{{ t('system.redis.explorer.inspectModal.memory') }}</span>
                  <span class="font-mono font-bold text-foreground text-xs">{{ inspectDetails?.size || '-' }}</span>
                </div>
                <div>
                  <span class="text-[10px] font-bold text-muted-foreground uppercase block">{{ t('system.redis.explorer.inspectModal.ttl') }}</span>
                  <span class="font-mono font-bold text-foreground text-xs">{{ inspectDetails?.ttl || '-' }}</span>
                </div>
                <div>
                  <span class="text-[10px] font-bold text-muted-foreground uppercase block">{{ t('system.redis.explorer.inspectModal.connection') }}</span>
                  <span class="font-mono font-bold text-foreground text-xs">{{ inspectDetails?.connection || '-' }}</span>
                </div>
              </div>

              <!-- Full Key Name -->
              <div>
                <span class="text-[11px] font-bold text-muted-foreground block mb-1">
                  {{ t('system.redis.explorer.inspectModal.keyName') }}
                </span>
                <code class="block font-mono text-xs p-2.5 bg-background border border-border rounded-lg text-foreground break-all select-all">
                  {{ inspectDetails?.key || selectedInspectKey.key }}
                </code>
              </div>

              <!-- Payload Viewer -->
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <span class="text-[11px] font-bold text-muted-foreground">Payload Data</span>
                  <button
                    type="button"
                    @click="copyInspectPayload"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary hover:underline"
                  >
                    <Copy class="w-3 h-3" />
                    {{ t('system.redis.explorer.inspectModal.copy') }}
                  </button>
                </div>
                <div class="relative bg-background border border-border rounded-xl p-3.5 max-h-72 overflow-y-auto font-mono text-xs">
                  <Loader2 v-if="loadingKeyDetails" class="w-5 h-5 animate-spin mx-auto text-primary my-8" />
                  <pre v-else class="text-green-600 dark:text-green-400 whitespace-pre-wrap break-all leading-relaxed select-all">{{ formattedInspectValue }}</pre>
                </div>
              </div>
            </div>

            <div class="px-6 py-3.5 border-t border-border bg-muted/10 flex justify-end">
              <Button
                variant="outline"
                size="sm"
                @click="selectedInspectKey = null"
                class="text-xs font-semibold"
              >
                {{ t('system.redis.explorer.inspectModal.close') }}
              </Button>
            </div>
          </div>
        </div>
      </TabsContent>

      <!-- 3. Cache Actions Tab -->
      <TabsContent
        value="cache"
        class="space-y-6"
      >
        <!-- Cache Stats Cards (Top) -->
        <div
          v-if="cacheStats"
          class="grid grid-cols-1 md:grid-cols-3 gap-4"
        >
          <ConsoleStatCard
            :label="t('system.redis.cache.stats.keys')"
            :value="formatNumber(cacheStats.total_keys)"
            :icon="Database"
            tone="primary"
          />
          <ConsoleStatCard
            :label="t('system.redis.cache.stats.size')"
            :value="cacheStats.cache_size || '-'"
            :icon="HardDrive"
            tone="info"
          />
          <ConsoleStatCard
            :label="t('system.redis.cache.stats.expired')"
            :value="formatNumber(cacheStats.expired_keys)"
            :icon="Clock"
            tone="warning"
          />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Top Keys Table (Wide - Left) -->
          <ConsoleListCard class="lg:col-span-2 flex flex-col h-full">
            <div class="flex items-center gap-2 border-b border-border/50 bg-muted/20 px-5 py-4">
              <BarChart3 class="w-4 h-4 text-primary" />
              <h3 class="text-base font-bold text-foreground">
                {{ t('system.redis.cache.stats.topKeys') }}
              </h3>
            </div>
            <div
              v-if="cacheStats && cacheStats.top_keys?.length"
              class="overflow-x-auto"
            >
              <Table>
                <TableHeader>
                  <TableRow class="bg-muted/40 hover:bg-muted/40">
                    <TableHead class="w-full text-xs font-bold">
                      {{ t('system.redis.cache.stats.table.key') }}
                    </TableHead>
                    <TableHead class="whitespace-nowrap text-xs font-bold">
                      {{ t('system.redis.cache.stats.table.size') }}
                    </TableHead>
                    <TableHead class="whitespace-nowrap text-right text-xs font-bold">
                      {{ t('system.redis.cache.stats.table.ttl') }}
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="(key, index) in cacheStats.top_keys"
                    :key="index"
                    class="hover:bg-muted/30 text-xs"
                  >
                    <TableCell class="font-mono text-xs break-all py-3 font-medium text-foreground">
                      {{ key.key }}
                    </TableCell>
                    <TableCell class="text-xs font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap py-3">
                      {{ key.size }}
                    </TableCell>
                    <TableCell class="text-xs text-muted-foreground whitespace-nowrap text-right py-3 font-mono">
                      {{ key.ttl }}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <div
              v-else
              class="p-10 text-center text-muted-foreground flex flex-col items-center gap-2"
            >
              <Database class="w-8 h-8 opacity-20" />
              <p class="text-xs font-medium">{{ t('common.placeholders.noData') }}</p>
            </div>
          </ConsoleListCard>

          <!-- Quick Cache Flush Actions (Right) -->
          <Card class="h-full flex flex-col border border-border/80 shadow-sm">
            <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
              <CardTitle class="text-base font-bold flex items-center gap-2">
                <Zap class="w-4 h-4 text-amber-500" />
                {{ t('common.actions.title') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="p-6">
              <div class="grid grid-cols-2 gap-3">
                <!-- Warm Cache -->
                <button
                  :disabled="warming"
                  class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-border hover:border-primary/50 hover:bg-primary/5 group relative overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                  @click="warmCache"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110 transition-transform">
                    <Loader2
                      v-if="warming"
                      class="w-5 h-5 animate-spin"
                    />
                    <Flame
                      v-else
                      class="w-5 h-5"
                    />
                  </div>
                  <span class="mt-2 text-xs font-bold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ t('system.redis.cache.actions.warm.button') }}
                  </span>
                </button>

                <!-- Flush All -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-border hover:border-destructive/50 hover:bg-destructive/5 group disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                  @click="flushCache('all')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-destructive/10 text-destructive group-hover:scale-110 transition-transform">
                    <Trash2 class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-bold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ t('system.redis.cache.actions.all.button') }}
                  </span>
                </button>

                <!-- Flush App Cache -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-border hover:border-orange-500/50 hover:bg-orange-500/5 group disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                  @click="flushCache('cache')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-orange-500/10 text-orange-500 group-hover:scale-110 transition-transform">
                    <HardDrive class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-bold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ t('system.redis.cache.actions.cache.button') }}
                  </span>
                </button>

                <!-- Flush Config -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-border hover:border-indigo-500/50 hover:bg-indigo-500/5 group disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                  @click="flushCache('config')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-indigo-500/10 text-indigo-500 group-hover:scale-110 transition-transform">
                    <Settings class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-bold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ t('system.redis.cache.actions.config.button') }}
                  </span>
                </button>

                <!-- Flush Route -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-border hover:border-emerald-500/50 hover:bg-emerald-500/5 group disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                  @click="flushCache('route')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-500/10 text-emerald-500 group-hover:scale-110 transition-transform">
                    <Route class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-bold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ t('system.redis.cache.actions.route.button') }}
                  </span>
                </button>

                <!-- Flush View -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-border hover:border-blue-500/50 hover:bg-blue-500/5 group disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                  @click="flushCache('view')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-500/10 text-blue-500 group-hover:scale-110 transition-transform">
                    <Eye class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-bold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ t('system.redis.cache.actions.view.button') }}
                  </span>
                </button>
              </div>
            </CardContent>
          </Card>
        </div>
      </TabsContent>

      <!-- 4. Settings Tab -->
      <TabsContent
        value="settings"
        class="space-y-6"
      >
        <!-- Global Driver Warning -->
        <Card
          v-if="!isRedisDriverActive"
          class="border-amber-500/40 bg-amber-500/5"
        >
          <CardContent class="pt-6">
            <div class="flex items-start gap-4">
              <div class="p-2 rounded-full bg-amber-500/20 text-amber-600">
                <AlertTriangle class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <h4 class="font-bold text-amber-800 dark:text-amber-200 text-sm">
                  {{ t('system.redis.disabledWarning.title') }}
                </h4>
                <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed">
                  {{ t('system.redis.disabledWarning.desc') }} 
                  <router-link
                    :to="consolePath('/settings?tab=performance')"
                    class="underline font-bold hover:text-amber-900 inline-flex items-center gap-1 mx-1"
                  >
                    {{ t('system.redis.disabledWarning.performanceLink') }} <ArrowRight class="w-3 h-3" />
                  </router-link>
                  {{ t('system.redis.disabledWarning.actionHint') }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <form
          :inert="!isRedisDriverActive"
          @submit.prevent="saveSettings"
        >
          <Accordion
            type="multiple"
            class="w-full space-y-4"
            :default-value="['Connection', 'Session & Queue']"
          >
            <AccordionItem
              v-for="group in groupedSettings"
              :key="group.name"
              :value="group.name"
              class="border border-border rounded-xl bg-card px-2 shadow-sm"
            >
              <AccordionTrigger class="px-4 hover:no-underline">
                <div class="flex items-center gap-3 text-left">
                  <div class="p-2 rounded-lg bg-primary/10 text-primary">
                    <component
                      :is="getGroupIcon(group.name)"
                      class="w-5 h-5"
                    />
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-foreground">
                      {{ getGroupName(group.name) }}
                    </h3>
                    <p class="text-xs text-muted-foreground font-normal">
                      {{ getGroupDescription(group.name) }}
                    </p>
                  </div>
                </div>
              </AccordionTrigger>
                
              <AccordionContent class="px-4 pb-4 pt-2 border-t border-border/50 mt-2">
                <!-- Connection Group Extras (Live Ping Test) -->
                <div
                  v-if="group.name === 'Connection'"
                  class="mb-6 space-y-3"
                >
                  <div class="flex items-center justify-between bg-muted/30 p-3.5 rounded-xl border border-border/50">
                    <div class="text-xs text-muted-foreground">
                      {{ t('system.redis.settings.testDescription') }}
                    </div>
                    <Button 
                      size="sm"
                      type="button"
                      :disabled="testing" 
                      @click="testConnection"
                      class="text-xs font-bold"
                    >
                      <Loader2
                        v-if="testing"
                        class="w-3.5 h-3.5 mr-2 animate-spin"
                      />
                      <Zap
                        v-else
                        class="w-3.5 h-3.5 mr-2"
                      />
                      {{ testing ? t('system.redis.settings.testing') : t('system.redis.settings.test') }}
                    </Button>
                  </div>

                  <!-- Connection Status -->
                  <div
                    v-if="connectionStatus"
                    :class="cn('rounded-xl p-3.5 text-xs border border-border/50 flex items-center gap-2.5 font-medium', connectionStatus.type === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-destructive/10 border-destructive/20 text-destructive')"
                  >
                    <Zap
                      v-if="connectionStatus.type === 'success'"
                      class="w-4 h-4 flex-shrink-0"
                    />
                    <AlertTriangle
                      v-else
                      class="w-4 h-4 flex-shrink-0"
                    />
                    <span>{{ connectionStatus.message }}</span>
                  </div>
                </div>

                <!-- Settings Inputs Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                  <div
                    v-for="setting in group.items"
                    :key="setting.key"
                    class="space-y-1.5"
                  >
                    <Label
                      :for="setting.key"
                      class="text-xs font-bold text-foreground block"
                    >
                      {{ formatSettingLabel(setting.key) }}
                    </Label>
                    <p
                      v-if="setting.description"
                      class="text-[11px] text-muted-foreground"
                    >
                      {{ setting.description }}
                    </p>

                    <div
                      v-if="setting.type === 'string' || setting.type === 'integer'"
                      class="relative"
                    >
                      <Input
                        :id="setting.key"
                        v-model="(settingsForm[setting.key] as any)"
                        :type="setting.type === 'integer' ? 'number' : (setting.is_encrypted ? (showPassword ? 'text' : 'password') : 'text')"
                        :class="[
                          { 'border-destructive focus-visible:ring-destructive': errors[setting.key] },
                          setting.is_encrypted ? 'pr-10' : '',
                          'text-xs font-mono'
                        ]"
                      />
                      <button
                        v-if="setting.is_encrypted"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none"
                        @click="showPassword = !showPassword"
                      >
                        <Eye v-if="!showPassword" class="w-3.5 h-3.5" />
                        <EyeOff v-else class="w-3.5 h-3.5" />
                      </button>
                    </div>

                    <Select
                      v-else-if="setting.type === 'boolean'"
                      :model-value="['true', '1', true, 1].includes(settingsForm[setting.key] as any) ? 'true' : 'false'"
                      @update:model-value="(val) => settingsForm[setting.key] = val === 'true'"
                    >
                      <SelectTrigger :id="setting.key" class="w-full text-xs">
                        <SelectValue :placeholder="t('system.redis.settings.select')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="true" class="text-xs">
                          {{ t('system.redis.settings.enabled') }}
                        </SelectItem>
                        <SelectItem value="false" class="text-xs">
                          {{ t('system.redis.settings.disabled') }}
                        </SelectItem>
                      </SelectContent>
                    </Select>

                    <p
                      v-if="errors && errors[setting.key]"
                      class="text-xs text-destructive mt-1"
                    >
                      {{ Array.isArray(errors[setting.key]) ? (errors[setting.key] as string[])[0] : errors[setting.key] }}
                    </p>
                  </div>
                </div>
              </AccordionContent>
            </AccordionItem>
          </Accordion>

          <!-- Action Buttons Footer -->
          <div class="mt-6 flex justify-between items-center gap-3">
            <Button
              variant="outline"
              type="button"
              :disabled="syncingEnv || saving"
              class="text-xs font-semibold gap-1.5"
              @click="syncFromEnv"
            >
              <RefreshCw v-if="!syncingEnv" class="w-3.5 h-3.5" />
              <Loader2 v-else class="w-3.5 h-3.5 animate-spin" />
              {{ syncingEnv ? 'Syncing...' : 'Sync from .env' }}
            </Button>
            <div class="flex items-center gap-3">
              <Button
                variant="ghost"
                type="button"
                class="text-xs font-semibold text-muted-foreground hover:text-foreground"
                @click="loadSettings"
              >
                {{ t('system.redis.settings.cancel') }}
              </Button>
              <Button 
                type="submit" 
                :disabled="saving || !isDirty"
                class="min-w-[140px] text-xs font-bold shadow-sm"
              >
                <Loader2
                  v-if="saving"
                  class="w-3.5 h-3.5 mr-2 animate-spin"
                />
                <Save
                  v-else
                  class="w-3.5 h-3.5 mr-2"
                />
                {{ saving ? t('system.redis.settings.saving') : t('system.redis.settings.save') }}
              </Button>
            </div>
          </div>
        </form>
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, onUnmounted, type Component } from 'vue';
import { ConsoleListCard, ConsoleStatCard, PageHeader } from '@/shared/components/shell';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { cn } from '@/shared/utils/lib-utils';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button,
    Input,
    Label,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell
} from '@/shared/components/ui';
import {
  Activity,
  AlertTriangle,
  ArrowRight,
  BarChart3,
  Clock,
  Copy,
  Database,
  Eye,
  EyeOff,
  Flame,
  HardDrive,
  Loader2,
  RefreshCw,
  Route,
  Save,
  Search,
  Settings,
  Target,
  Terminal,
  Trash2,
  Users,
  X,
  Zap,
} from 'lucide-vue-next';

interface RedisStat {
  version: string;
  used_memory: string;
  used_memory_peak?: string;
  mem_fragmentation_ratio?: number;
  total_keys: number;
  uptime_days: string;
  connected_clients: number;
  hit_rate: string;
  operations_per_sec: number;
  total_commands: number;
  hits: number;
  misses: number;
}

interface RedisStatsSnapshot {
  hits: number;
  misses: number;
  total_commands: number;
  captured_at: string;
}

interface CacheKey {
  key: string;
  raw_key?: string;
  size: string;
  ttl: string;
  type?: string;
}

interface CacheStats {
  total_keys: number;
  cache_size: string;
  expired_keys: number;
  top_keys: CacheKey[];
  key_prefix?: string;
}

interface SettingItem {
  key: string;
  value: unknown;
  type: string;
  description?: string;
  is_encrypted?: boolean;
}

interface SettingGroup {
  name: string;
  items: SettingItem[];
}

interface ConnectionStatus {
  type: 'success' | 'error';
  message: string;
}

interface KeyInspectDetails {
  key: string;
  connection: string;
  type: string;
  size: string;
  ttl: string;
  value: unknown;
  is_json?: boolean;
}

type GroupedSettingsPayload = Record<string, SettingItem[] | Record<string, SettingItem> | null | undefined>;

const isSettingItem = (item: unknown): item is SettingItem => {
  return !!item && typeof item === 'object' && typeof (item as SettingItem).key === 'string';
};

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const activeTab = ref('statistics');
const showPassword = ref(false);

// Settings
const settings = ref<Record<string, SettingItem[]>>({});
const settingsForm = ref<Record<string, unknown>>({});
const initialSettingsForm = ref<Record<string, unknown>>({});
const errors = ref<Record<string, string | string[]>>({});
const cacheDriver = ref<string | null>(null);
const globalCacheEnabled = ref<boolean | null>(null);

const isRedisDriverActive = computed(() => {
    const driver = (cacheDriver.value || '').toLowerCase();
    // If global cache is explicitly disabled, Redis cache is not active
    if (globalCacheEnabled.value === false) {
      return false;
    }
    if (!driver) {
      return true;
    }
    return driver === 'redis'
        || driver === 'redis_failover'
        || driver === 'failover'
        || driver.startsWith('redis');
});

const isDirty = computed(() => {
    return JSON.stringify(settingsForm.value) !== JSON.stringify(initialSettingsForm.value);
});

const groupedSettings = computed<SettingGroup[]>(() => {
  const groups: Record<string, SettingGroup> = {};

  Object.entries(settings.value || {}).forEach(([groupName, rawItems]) => {
    const items: SettingItem[] = (Array.isArray(rawItems)
      ? rawItems
      : (rawItems && typeof rawItems === 'object' ? Object.values(rawItems) : [])
    ).filter(isSettingItem);
    if (items.length === 0) return;

    const filteredItems: SettingItem[] = items.filter((item) => item.key !== 'redis_enabled');
    if (filteredItems.length === 0) return;

    if (groupName === 'session' || groupName === 'queue') {
      if (!groups['session_queue']) {
        groups['session_queue'] = {
          name: 'Session & Queue',
          items: []
        };
      }
      groups['session_queue'].items.push(...filteredItems);
    } else {
      groups[groupName] = {
        name: formatGroupName(groupName),
        items: filteredItems
      };
    }
  });
  
  return Object.values(groups);
});

const saving = ref(false);
const testing = ref(false);
const connectionStatus = ref<ConnectionStatus | null>(null);

// Statistics
const EMPTY_STATS: RedisStat = {
  version: '-',
  used_memory: '-',
  used_memory_peak: '-',
  mem_fragmentation_ratio: 1.0,
  total_keys: 0,
  uptime_days: '-',
  connected_clients: 0,
  hit_rate: '0%',
  operations_per_sec: 0,
  total_commands: 0,
  hits: 0,
  misses: 0,
};

const REDIS_STATS_SNAPSHOT_KEY = 'redis:stats:snapshot:v1';

const EMPTY_CACHE_STATS: CacheStats = {
  total_keys: 0,
  cache_size: '-',
  expired_keys: 0,
  top_keys: [],
};

const stats = ref<RedisStat>({ ...EMPTY_STATS });
const statsSnapshot = ref<RedisStatsSnapshot | null>(null);
const loadingStats = ref(false);
const statsInterval = ref<ReturnType<typeof setInterval> | null>(null);

// Cache
const cacheStats = ref<CacheStats>({ ...EMPTY_CACHE_STATS });
const flushing = ref(false);
const warming = ref(false);

// Key Explorer State
const searchPattern = ref('*');
const explorerConnection = ref<'cache' | 'default'>('cache');
const explorerKeys = ref<CacheKey[]>([]);
const searchingKeys = ref(false);
const selectedInspectKey = ref<CacheKey | null>(null);
const inspectDetails = ref<KeyInspectDetails | null>(null);
const loadingKeyDetails = ref(false);

// Methods
const loadSettings = async (): Promise<void> => {
  try {
    const response = await api.get('/manage/redis/settings');
    const payload = response?.data;
    const normalized: Record<string, SettingItem[]> = {};

    if (payload && typeof payload === 'object') {
      Object.entries(payload as GroupedSettingsPayload).forEach(([groupName, rawItems]) => {
        const items = Array.isArray(rawItems)
          ? rawItems
          : (rawItems && typeof rawItems === 'object' ? Object.values(rawItems) : []);

        normalized[groupName] = items.filter((item): item is SettingItem => {
          return !!item && typeof item === 'object' && typeof item.key === 'string';
        });
      });
    }

    settings.value = normalized;

    settingsForm.value = {};
    Object.values(settings.value).forEach((items) => {
      items.forEach(item => {
        settingsForm.value[item.key] = item.value;
      });
    });
    initialSettingsForm.value = JSON.parse(JSON.stringify(settingsForm.value));
  } catch (error: unknown) {
    logger.error('Failed to load Redis settings:', error);
  }
};

const saveSettings = async (): Promise<void> => {
  saving.value = true;
  errors.value = {};
  try {
    const settingsArray = Object.entries(settingsForm.value).map(([key, value]) => ({
      key,
      value
    }));

    await api.put('/manage/redis/settings', {
      settings: settingsArray
    });

    toast.success.save();
    connectionStatus.value = {
      type: 'success',
      message: t('system.redis.messages.saveSuccess')
    };

    // Refresh global cache status to reflect bidirectional sync
    await getCacheStatus();
    // Reload settings to get server-synced values
    await loadSettings();

    setTimeout(() => {
      connectionStatus.value = null;
    }, 3000);
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 422) {
        errors.value = (error.response.data as { errors?: Record<string, string | string[]> })?.errors || {};
      } else {
        toast.error.fromResponse(error);
        connectionStatus.value = {
          type: 'error',
          message: (error.response?.data as { message?: string })?.message || t('system.redis.messages.saveFailed')
        };
      }
    } else {
      logger.error('Failed to save settings:', error);
    }
  } finally {
    saving.value = false;
  }
};

const syncingEnv = ref(false);

const syncFromEnv = async (): Promise<void> => {
  const confirmed = await confirm({
    title: 'Sync Settings from .env',
    message: 'This will reload Redis settings from your server environment file (.env). Any unsaved changes in this form will be replaced with .env values. Continue?',
    variant: 'info',
    confirmText: 'Sync from .env',
  });

  if (!confirmed) {
    return;
  }

  syncingEnv.value = true;
  try {
    const response = await api.post('/manage/redis/sync-env');
    const payload = response?.data;
    const normalized: Record<string, SettingItem[]> = {};

    if (payload && typeof payload === 'object') {
      Object.entries(payload as GroupedSettingsPayload).forEach(([groupName, rawItems]) => {
        const items = Array.isArray(rawItems)
          ? rawItems
          : (rawItems && typeof rawItems === 'object' ? Object.values(rawItems) : []);

        normalized[groupName] = items.filter((item): item is SettingItem => {
          return !!item && typeof item === 'object' && typeof item.key === 'string';
        });
      });
    }

    settings.value = normalized;

    settingsForm.value = {};
    Object.values(settings.value).forEach((items) => {
      items.forEach(item => {
        settingsForm.value[item.key] = item.value;
      });
    });
    initialSettingsForm.value = JSON.parse(JSON.stringify(settingsForm.value));

    toast.success.action('Redis settings successfully synced from .env!');
    await getCacheStatus();
  } catch (error: unknown) {
    logger.error('Failed to sync settings from .env:', error);
    toast.error.fromResponse(error);
  } finally {
    syncingEnv.value = false;
  }
};

const testConnection = async (): Promise<void> => {
  testing.value = true;
  connectionStatus.value = null;

  try {
    const response = await api.post('/manage/redis/test-connection', {
      host: settingsForm.value.redis_host,
      port: settingsForm.value.redis_port,
      username: settingsForm.value.redis_username,
      password: settingsForm.value.redis_password,
      database: settingsForm.value.redis_database
    });
    const payload = response?.data as { message?: string; response_time?: string } | undefined;
    connectionStatus.value = {
      type: 'success',
      message: `✅ ${payload?.message || t('system.redis.messages.testSuccess')} (${payload?.response_time || '-'})`
    };
    setTimeout(() => {
      connectionStatus.value = null;
    }, 3000);

  } catch (error: unknown) {
    let msg = t('system.redis.messages.testFailed');
    let hint = '';
    
    if (axios.isAxiosError(error)) {
      const data = error.response?.data as { message?: string; hint?: string } | undefined;
      msg = data?.message || msg;
      hint = data?.hint || '';
    }
    
    connectionStatus.value = {
      type: 'error',
      message: `❌ ${msg}${hint ? ' - ' + hint : ''}`
    };
  } finally {
    testing.value = false;
  }
};

const loadStats = async (): Promise<void> => {
  loadingStats.value = true;
  try {
    const response = await api.get('/manage/redis/info');
    const payload = response?.data as Partial<RedisStat> | undefined;
    stats.value = { ...EMPTY_STATS, ...(payload || {}) };
  } catch (error: unknown) {
    logger.error('Failed to load Redis stats:', error);
    stats.value = { ...EMPTY_STATS };
  } finally {
    loadingStats.value = false;
  }
};

const readStatsSnapshot = (): RedisStatsSnapshot | null => {
  const raw = localStorage.getItem(REDIS_STATS_SNAPSHOT_KEY);
  if (!raw) return null;

  try {
    const parsed = JSON.parse(raw) as Partial<RedisStatsSnapshot>;
    if (typeof parsed.hits !== 'number' || typeof parsed.misses !== 'number' || typeof parsed.total_commands !== 'number') {
      return null;
    }

    return {
      hits: parsed.hits,
      misses: parsed.misses,
      total_commands: parsed.total_commands,
      captured_at: typeof parsed.captured_at === 'string' ? parsed.captured_at : new Date().toISOString(),
    };
  } catch {
    return null;
  }
};

const persistStatsSnapshot = (snapshot: RedisStatsSnapshot): void => {
  statsSnapshot.value = snapshot;
  localStorage.setItem(REDIS_STATS_SNAPSHOT_KEY, JSON.stringify(snapshot));
};

const captureStatsSnapshot = (): void => {
  persistStatsSnapshot({
    hits: stats.value.hits || 0,
    misses: stats.value.misses || 0,
    total_commands: stats.value.total_commands || 0,
    captured_at: new Date().toISOString(),
  });
};

const statsSinceLastClear = computed(() => {
  const snapshot = statsSnapshot.value;
  if (!snapshot) {
    return {
      hits: 0,
      misses: 0,
      totalCommands: 0,
      hitRate: '0%',
    };
  }

  const hits = Math.max((stats.value.hits || 0) - snapshot.hits, 0);
  const misses = Math.max((stats.value.misses || 0) - snapshot.misses, 0);
  const totalCommands = Math.max((stats.value.total_commands || 0) - snapshot.total_commands, 0);
  const total = hits + misses;
  const hitRate = total > 0 ? `${Math.round((hits / total) * 10000) / 100}%` : '0%';

  return {
    hits,
    misses,
    totalCommands,
    hitRate,
  };
});

const loadCacheStats = async (): Promise<void> => {
  try {
    const response = await api.get('/manage/redis/cache-stats');
    const payload = response?.data as Partial<CacheStats> | undefined;
    cacheStats.value = { ...EMPTY_CACHE_STATS, ...(payload || {}) };
  } catch (error: unknown) {
    logger.error('Failed to load cache stats:', error);
    cacheStats.value = { ...EMPTY_CACHE_STATS };
  }
};

const searchRedisKeys = async (): Promise<void> => {
  searchingKeys.value = true;
  try {
    const response = await api.get('/manage/redis/keys', {
      params: {
        pattern: searchPattern.value || '*',
        connection: explorerConnection.value,
        limit: 80,
      }
    });
    const payload = response?.data as { items?: CacheKey[] } | undefined;
    explorerKeys.value = payload?.items || [];
  } catch (error: unknown) {
    logger.error('Failed to search Redis keys:', error);
    explorerKeys.value = [];
  } finally {
    searchingKeys.value = false;
  }
};

const openKeyInspector = async (item: CacheKey): Promise<void> => {
  selectedInspectKey.value = item;
  inspectDetails.value = null;
  loadingKeyDetails.value = true;
  try {
    const targetKey = item.raw_key || item.key;
    const response = await api.post('/manage/redis/key-details', {
      key: targetKey,
      connection: explorerConnection.value,
    });
    inspectDetails.value = response?.data as KeyInspectDetails;
  } catch (error: unknown) {
    logger.error('Failed to inspect key:', error);
    toast.error.fromResponse(error);
  } finally {
    loadingKeyDetails.value = false;
  }
};

const formattedInspectValue = computed(() => {
  if (!inspectDetails.value) return '';
  const val = inspectDetails.value.value;
  if (typeof val === 'object' && val !== null) {
    return JSON.stringify(val, null, 2);
  }
  return String(val ?? '');
});

const copyInspectPayload = async (): Promise<void> => {
  try {
    await navigator.clipboard.writeText(formattedInspectValue.value);
    toast.success.action(t('system.redis.explorer.inspectModal.copied'));
  } catch {
    toast.error.action('Failed to copy');
  }
};

const handleDeleteKey = async (item: CacheKey): Promise<void> => {
  const targetKey = item.raw_key || item.key;
  const confirmed = await confirm({
    title: t('system.redis.explorer.delete'),
    message: t('system.redis.explorer.deleteConfirm', { key: item.key }),
    variant: 'danger',
    confirmText: t('system.redis.explorer.delete'),
  });

  if (!confirmed) return;

  try {
    await api.delete('/manage/redis/key', {
      data: {
        key: targetKey,
        connection: explorerConnection.value,
      }
    });
    toast.success.action(t('system.redis.explorer.deleteSuccess', { key: item.key }));
    await Promise.all([searchRedisKeys(), loadCacheStats(), loadStats()]);
  } catch (error: unknown) {
    logger.error('Failed to delete key:', error);
    toast.error.fromResponse(error);
  }
};

const getTypeBadgeClass = (type?: string): string => {
  switch (type?.toLowerCase()) {
    case 'string': return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
    case 'hash': return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20';
    case 'set': return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20';
    case 'zset': return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
    case 'list': return 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20';
    default: return 'bg-muted text-muted-foreground border border-border';
  }
};

const resolveCacheDriver = (value: unknown): string | null => {
  if (typeof value !== 'string') return null;
  const normalized = value.trim().toLowerCase();
  return normalized.length > 0 ? normalized : null;
};

const getCacheDriverFromSettings = async (): Promise<string | null> => {
  try {
    const response = await api.get('/manage/system/settings');
    const rows = response?.data;
    if (!Array.isArray(rows)) return null;

    const row = rows.find((item: unknown) => {
      const obj = item as { key?: unknown; group?: unknown };
      return obj?.key === 'cache_driver' && obj?.group === 'performance';
    }) as { value?: unknown } | undefined;

    return resolveCacheDriver(row?.value);
  } catch {
    return null;
  }
};

const getCacheStatus = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/system/cache-status');
        const data = response?.data as { driver?: unknown; enabled?: unknown } | undefined;
        const driver =
          resolveCacheDriver(data?.driver)
          ?? await getCacheDriverFromSettings();
        cacheDriver.value = driver;
        if (typeof data?.enabled === 'boolean') {
          globalCacheEnabled.value = data.enabled;
        } else {
          globalCacheEnabled.value = null;
        }
    } catch (error: unknown) {
        logger.error('Failed to get global cache status:', error);
        if (!cacheDriver.value) {
          cacheDriver.value = await getCacheDriverFromSettings();
        }
    }
};

const flushCache = async (type: string): Promise<void> => {
  const confirmed = await confirm({
    title: t('system.redis.messages.flushTitle'),
    message: `${t('system.redis.messages.flushConfirm', { type })} ${t('system.redis.messages.flushConfirmSafe')}`,
    variant: 'danger',
    confirmText: t('system.redis.messages.flushAction'),
  });

  if (!confirmed) {
    return;
  }

  flushing.value = true;
  try {
    await api.post('/manage/redis/flush-cache', { type });
    toast.success.action(t('system.redis.messages.flushSuccess', { type }));

    await Promise.all([loadCacheStats(), loadStats(), searchRedisKeys()]);
    captureStatsSnapshot();
  } catch (error: unknown) {
    toast.error.fromResponse(error);
  } finally {
    flushing.value = false;
  }
};

const warmCache = async (): Promise<void> => {
  const confirmed = await confirm({
    title: t('system.redis.messages.warmTitle'),
    message: `${t('system.redis.messages.warmConfirm')} ${t('system.redis.messages.warmConfirmSafe')}`,
    variant: 'danger',
    confirmText: t('system.redis.messages.warmAction'),
  });

  if (!confirmed) {
    return;
  }

  warming.value = true;
  try {
    await api.post('/manage/redis/warm-cache');
    toast.success.action(t('system.redis.messages.warmSuccess'));
    await Promise.all([loadCacheStats(), loadStats(), searchRedisKeys()]);
  } catch (error: unknown) {
    toast.error.fromResponse(error);
  } finally {
    warming.value = false;
  }
};

// Helpers
const formatSettingLabel = (key: string) => {
  return key.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ');
};

const formatGroupName = (group: string) => {
  return group.charAt(0).toUpperCase() + group.slice(1);
};

const getGroupName = (groupName: string): string => {
  switch (groupName.toLowerCase()) {
    case 'connection': return t('system.redis.groups.connection.name');
    case 'cache': return t('system.redis.groups.cache.name');
    case 'session & queue': return t('system.redis.groups.sessionQueue.name');
    default: return groupName;
  }
};

const getGroupDescription = (groupName: string): string => {
  switch (groupName.toLowerCase()) {
    case 'connection': return t('system.redis.groups.connection.desc');
    case 'cache': return t('system.redis.groups.cache.desc');
    case 'session & queue': return t('system.redis.groups.sessionQueue.desc');
    default: return '';
  }
};

const getGroupIcon = (groupName: string): Component => {
  const icons: Record<string, Component> = {
    'Connection': Database,
    'Cache': Zap,
    'Session & Queue': Clock
  };
  return (icons[groupName] || Database) as Component;
};

const formatNumber = (num?: number) => {
  if (!num) return 0;
  return new Intl.NumberFormat().format(num);
};

// Lifecycle
onMounted(() => {
  statsSnapshot.value = readStatsSnapshot();
  loadSettings();
  loadStats();
  loadCacheStats();
  searchRedisKeys();
  getCacheStatus();

  statsInterval.value = setInterval(() => {
    if (activeTab.value === 'statistics') {
      loadStats();
    }
  }, 30000);
});

onUnmounted(() => {
  if (statsInterval.value) {
    clearInterval(statsInterval.value);
  }
});
</script>

<style scoped>
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}
</style>
