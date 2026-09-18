<template>
  <section
    id="section-terminal"
    class="py-20 md:py-28 relative z-10 bg-background/50 border-t border-border/40"
  >
    <div class="container mx-auto px-4 md:px-8 max-w-5xl">
      <!-- Section Header -->
      <div class="text-center max-w-2xl mx-auto mb-14">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary/10 border border-secondary/30 text-secondary text-[11px] font-mono uppercase tracking-widest mb-4">
          <Terminal class="w-3.5 h-3.5" />
          <span>{{ t('terminal.badge') }}</span>
        </div>
        <h2 class="text-3xl md:text-5xl font-heading font-black tracking-tight text-foreground uppercase mb-4">
          {{ t('terminal.title') }}
        </h2>
        <p class="text-muted-foreground text-base leading-relaxed">
          {{ t('terminal.subtitle') }}
        </p>
      </div>

      <!-- Developer Terminal Showcase Box -->
      <div class="rounded-2xl bg-card border border-border/70 shadow-2xl overflow-hidden backdrop-blur-xl">
        <!-- Terminal Title Bar -->
        <div class="flex items-center justify-between px-4 py-3 bg-muted/40 border-b border-border/40">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block" />
            <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block" />
            <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block" />
            <span class="text-xs font-mono text-muted-foreground ml-2">jejakawan-console ~ bash</span>
          </div>

          <!-- Tab Selection Buttons -->
          <div class="flex items-center gap-1.5">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              :class="[
                'px-3 py-1 rounded-lg text-xs font-mono transition-all',
                activeTab === tab.id
                  ? 'bg-primary/15 text-primary border border-primary/30 font-semibold'
                  : 'text-muted-foreground hover:text-foreground hover:bg-muted/30'
              ]"
              @click="activeTab = tab.id"
            >
              {{ tab.label }}
            </button>
          </div>
        </div>

        <!-- Terminal Body -->
        <div class="p-6 md:p-8 font-mono text-xs md:text-sm overflow-x-auto bg-card/95">
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-border/30 text-muted-foreground text-xs">
            <span class="text-emerald-400 font-semibold">● Ready</span>
            <button
              type="button"
              class="flex items-center gap-1.5 text-xs text-muted-foreground hover:text-primary transition-colors cursor-pointer"
              @click="copySnippet"
            >
              <Copy class="w-3.5 h-3.5" />
              <span>{{ isCopied ? t('common.copied') : t('common.clickToCopy') }}</span>
            </button>
          </div>

          <!-- Code Snippets per Tab -->
          <div v-if="activeTab === 'cli'" class="space-y-2 text-foreground/90 leading-relaxed">
            <p class="text-muted-foreground"># Install Jejakawan Engine CLI globally</p>
            <p class="text-primary font-semibold">$ npm install -g @jejakawan/cli</p>
            <p class="text-muted-foreground mt-4"># Initialize a new high-performance cloud project</p>
            <p class="text-foreground">$ ja init my-portal --template=sareupna</p>
            <p class="text-muted-foreground mt-4"># Deploy cluster with zero downtime</p>
            <p class="text-foreground">$ ja deploy --env=production --cluster=jkt-main</p>
            <p class="text-emerald-400 mt-2">✓ Cluster deployed successfully [Build ID: 20260907-live]</p>
          </div>

          <div v-else-if="activeTab === 'api'" class="space-y-2 text-foreground/90 leading-relaxed">
            <p class="text-muted-foreground"># Fetch active publishing pipeline via REST</p>
            <p class="text-secondary font-semibold">curl -X GET "https://api.jejakawan.com/v1/publishing/contents" \</p>
            <p class="pl-4 text-foreground/80">-H "Authorization: Bearer ja_live_token_77a9" \</p>
            <p class="pl-4 text-foreground/80">-H "Accept: application/json"</p>
            <p class="text-emerald-400 mt-4">{ "status": "success", "latency_ms": 11.2, "nodes_active": 4 }</p>
          </div>

          <div v-else-if="activeTab === 'sdk'" class="space-y-2 text-foreground/90 leading-relaxed">
            <p class="text-muted-foreground">// TypeScript SDK Quickstart</p>
            <p><span class="text-purple-400">import</span> { <span class="text-cyan-400">JejakawanClient</span> } <span class="text-purple-400">from</span> <span class="text-emerald-300">'@jejakawan/sdk'</span>;</p>
            <p><span class="text-purple-400">const</span> client = <span class="text-purple-400">new</span> <span class="text-cyan-400">JejakawanClient</span>({ apiKey: process.env.<span class="text-yellow-300">JA_KEY</span> });</p>
            <p><span class="text-purple-400">const</span> telemetry = <span class="text-purple-400">await</span> client.system.<span class="text-cyan-400">getHealth</span>();</p>
            <p class="text-emerald-400 mt-2">// Telemetry result: { status: 'healthy', cluster: 'active' }</p>
          </div>

          <div v-else class="space-y-2 text-foreground/90 leading-relaxed">
            <p class="text-muted-foreground"># Pull and run Jejakawan Core Engine Container</p>
            <p class="text-primary font-semibold">$ docker pull ghcr.io/jejakawan/engine:latest</p>
            <p class="text-foreground mt-2">$ docker run -d -p 8082:8082 --name ja-core \</p>
            <p class="pl-4 text-foreground/80">-e APP_ENV=production \</p>
            <p class="pl-4 text-foreground/80">ghcr.io/jejakawan/engine:latest</p>
            <p class="text-emerald-400 mt-2">Container ja-core started on port :8082 (healthy)</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Terminal, Copy } from 'lucide-vue-next';

const { t } = useThemeI18n('sareupna');

const activeTab = ref<'cli' | 'api' | 'sdk' | 'docker'>('cli');
const isCopied = ref(false);

const tabs = computed(() => [
  { id: 'cli' as const, label: t('terminal.tabCli') },
  { id: 'api' as const, label: t('terminal.tabApi') },
  { id: 'sdk' as const, label: t('terminal.tabSdk') },
  { id: 'docker' as const, label: t('terminal.tabDocker') },
]);

const copySnippet = async () => {
  const snippets: Record<string, string> = {
    cli: 'npm install -g @jejakawan/cli && ja init my-portal --template=sareupna',
    api: 'curl -X GET "https://api.jejakawan.com/v1/publishing/contents" -H "Authorization: Bearer <token>"',
    sdk: "import { JejakawanClient } from '@jejakawan/sdk';",
    docker: 'docker run -d -p 8082:8082 --name ja-core ghcr.io/jejakawan/engine:latest',
  };
  try {
    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(snippets[activeTab.value] || '');
      isCopied.value = true;
      setTimeout(() => {
        isCopied.value = false;
      }, 2000);
    }
  } catch {
    // Silent
  }
};
</script>
