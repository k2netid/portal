<template>
  <div class="space-y-8">
    <!-- Header / Overview Card -->
    <div class="rounded-xl border border-border/80 bg-gradient-to-br from-card via-card to-primary/5 p-6 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-start gap-4">
          <div 
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl shadow-inner transition-colors"
            :class="tierBadgeClass.iconWrapper"
          >
            <Crown v-if="licenseData.tier === 'enterprise' || licenseData.tier === 'white_label'" class="h-7 w-7 text-amber-500" />
            <Sparkles v-else-if="licenseData.tier === 'pro'" class="h-7 w-7 text-indigo-500" />
            <ShieldCheck v-else-if="licenseData.tier === 'starter'" class="h-7 w-7 text-blue-500" />
            <Box v-else class="h-7 w-7 text-muted-foreground" />
          </div>
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h3 class="text-xl font-bold tracking-tight text-foreground">
                {{ licenseData.license_name || (licenseData.tier ? licenseData.tier.toUpperCase() : 'COMMUNITY') }}
              </h3>
              <Badge :class="tierBadgeClass.badge">
                {{ (licenseData.tier || 'community').toUpperCase() }}
              </Badge>
              <Badge :class="statusBadgeClass">
                <span class="mr-1.5 inline-block h-2 w-2 rounded-full" :class="statusDotClass" />
                {{ (licenseData.status || 'active').replace('_', ' ').toUpperCase() }}
              </Badge>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
              {{ licenseDescription }}
            </p>
          </div>
        </div>

        <!-- Sync Actions -->
        <div class="flex items-center gap-2.5 shrink-0">
          <Button
            type="button"
            variant="outline"
            size="sm"
            class="gap-2"
            :disabled="syncing || loading"
            @click="refreshLicense"
          >
            <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': syncing }" />
            {{ syncing ? 'Syncing...' : 'Sync with JA-CP' }}
          </Button>
          <Button
            v-if="licenseData.tier !== 'community' && licenseData.masked_key"
            type="button"
            variant="ghost"
            size="sm"
            class="gap-2 text-destructive hover:bg-destructive/10 hover:text-destructive"
            :disabled="deactivating || loading"
            @click="handleDeactivate"
          >
            <Unlink class="h-4 w-4" />
            Deactivate
          </Button>
        </div>
      </div>

      <!-- Grace Period Warning Alert -->
      <div 
        v-if="licenseData.status === 'grace_period'"
        class="mt-5 rounded-lg border border-amber-500/40 bg-amber-500/10 p-4 text-amber-900 dark:text-amber-200 flex items-start gap-3"
      >
        <AlertTriangle class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" />
        <div class="text-sm">
          <strong class="font-semibold">Offline Grace Period Active:</strong> 
          We could not reach the JA-CP Licensing Server during the last sync. Your site continues to operate with full Pro features for up to 30 days. Please verify internet connectivity or sync manually.
        </div>
      </div>

      <!-- License Details Meta Grid -->
      <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border-t border-border/50 pt-5 text-sm">
        <div>
          <span class="text-xs font-medium text-muted-foreground block">Active Key</span>
          <span class="font-mono font-semibold text-foreground">
            {{ licenseData.masked_key || 'No commercial key applied' }}
          </span>
        </div>
        <div>
          <span class="text-xs font-medium text-muted-foreground block">Bound Domain</span>
          <span class="font-medium text-foreground">
            {{ licenseData.domain || currentHost || 'localhost' }}
          </span>
        </div>
        <div>
          <span class="text-xs font-medium text-muted-foreground block">Last Verified</span>
          <span class="font-medium text-foreground">
            {{ formatDate(licenseData.last_checked_at) }}
          </span>
        </div>
        <div>
          <span class="text-xs font-medium text-muted-foreground block">Expires / Renewal</span>
          <span class="font-medium text-foreground">
            {{ licenseData.expires_at ? formatDate(licenseData.expires_at) : 'Lifetime / Perpetual' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Activation / Key Update Card -->
    <div class="rounded-xl border border-border/70 bg-card p-6 shadow-sm">
      <div class="mb-4">
        <h4 class="text-base font-semibold text-foreground">
          {{ licenseData.masked_key ? 'Update License Key' : 'Activate Core Engine License' }}
        </h4>
        <p class="text-sm text-muted-foreground">
          Enter your license key from the JA-CP (Jejakawan licensing hub) dashboard to unlock Pro extensions, white-label console, and enterprise features.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 max-w-2xl">
        <div class="relative flex-1">
          <KeyRound class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <input
            v-model="inputKey"
            type="text"
            placeholder="JACP-PRO-XXXX-XXXX-XXXX"
            class="w-full rounded-md border border-input bg-background pl-9 pr-4 py-2 text-sm font-mono ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            :disabled="activating"
            @keyup.enter="handleActivate"
          />
        </div>
        <Button
          type="button"
          class="gap-2 shrink-0 bg-gradient-to-r from-primary to-primary/90"
          :disabled="!inputKey.trim() || activating"
          @click="handleActivate"
        >
          <CheckCircle2 v-if="!activating" class="h-4 w-4" />
          <RefreshCw v-else class="h-4 w-4 animate-spin" />
          {{ activating ? 'Activating...' : 'Apply License' }}
        </Button>
      </div>
    </div>

    <!-- Entitlements & Features Matrix -->
    <div class="rounded-xl border border-border/70 bg-card p-6 shadow-sm">
      <div class="mb-5">
        <h4 class="text-base font-semibold text-foreground">
          Current Tier Capabilities
        </h4>
        <p class="text-sm text-muted-foreground">
          Features enabled for the <strong class="text-foreground font-semibold">{{ (licenseData.tier || 'community').toUpperCase() }}</strong> tier on this instance.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="feat in featureList" 
          :key="feat.key"
          class="flex items-start gap-3 rounded-lg border p-3.5 transition-colors"
          :class="feat.enabled ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-border/50 bg-muted/20 opacity-60'"
        >
          <div 
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
            :class="feat.enabled ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-muted text-muted-foreground'"
          >
            <Check v-if="feat.enabled" class="h-4 w-4" />
            <Lock v-else class="h-3.5 w-3.5" />
          </div>
          <div class="text-sm">
            <span class="font-medium block" :class="feat.enabled ? 'text-foreground' : 'text-muted-foreground'">
              {{ feat.name }}
            </span>
            <span class="text-xs text-muted-foreground">
              {{ feat.description }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  Crown, 
  Sparkles, 
  ShieldCheck, 
  Box, 
  RefreshCw, 
  Unlink, 
  AlertTriangle, 
  KeyRound, 
  CheckCircle2, 
  Check, 
  Lock 
} from 'lucide-vue-next';
import { Badge, Button } from '@/shared/components/ui';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { useConfirm } from '@/shared/composables/useConfirm';
import { logger } from '@/shared/utils/logger';

interface LicenseState {
  tier: string;
  status: string;
  license_name?: string;
  masked_key?: string;
  domain?: string;
  expires_at?: string | null;
  last_checked_at?: string | null;
  features?: Record<string, boolean>;
  control_plane_url?: string;
}

const { confirm } = useConfirm();

const loading = ref(false);
const activating = ref(false);
const syncing = ref(false);
const deactivating = ref(false);
const inputKey = ref('');

const currentHost = typeof window !== 'undefined' ? window.location.hostname : '';

const licenseData = ref<LicenseState>({
  tier: 'community',
  status: 'active',
  features: {},
});

const tierBadgeClass = computed(() => {
  const t = licenseData.value.tier;
  switch (t) {
    case 'white_label':
    case 'enterprise':
      return {
        iconWrapper: 'bg-amber-500/15 border border-amber-500/30',
        badge: 'bg-gradient-to-r from-amber-500 to-amber-700 text-white font-bold border-0',
      };
    case 'pro':
      return {
        iconWrapper: 'bg-indigo-500/15 border border-indigo-500/30',
        badge: 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold border-0',
      };
    case 'starter':
      return {
        iconWrapper: 'bg-blue-500/15 border border-blue-500/30',
        badge: 'bg-blue-600 text-white font-semibold border-0',
      };
    default:
      return {
        iconWrapper: 'bg-muted border border-border',
        badge: 'bg-secondary text-secondary-foreground font-medium',
      };
  }
});

const statusBadgeClass = computed(() => {
  const s = licenseData.value.status;
  if (s === 'active') return 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 font-medium';
  if (s === 'grace_period') return 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/30 font-medium';
  if (s === 'expired') return 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30 font-medium';
  return 'bg-secondary text-secondary-foreground';
});

const statusDotClass = computed(() => {
  const s = licenseData.value.status;
  if (s === 'active') return 'bg-emerald-500';
  if (s === 'grace_period') return 'bg-amber-500 animate-pulse';
  if (s === 'expired') return 'bg-rose-500';
  return 'bg-slate-400';
});

const licenseDescription = computed(() => {
  const t = licenseData.value.tier;
  if (t === 'enterprise' || t === 'white_label') return 'Full enterprise access with custom branding, white-labeling, unlimited sites and priority syncing.';
  if (t === 'pro') return 'Professional plan with premium extensions, advanced builder modules, custom code injection, and watermark removal.';
  if (t === 'starter') return 'Starter license with custom styling and extended module options.';
  return 'Free community edition. Upgrade via JA-CP to unlock premium extensions and custom branding.';
});

const featureDefinitions = [
  { key: 'premium_themes', name: 'Premium Extensions', description: 'Access to marketplace extensions and pro modules.' },
  { key: 'pro_builder_modules', name: 'Advanced Builder Modules', description: 'Dynamic components, sliders, portfolios, and pricing blocks.' },
  { key: 'custom_code_injection', name: 'Custom Code Injection', description: 'Inject custom header/footer CSS and JS snippets.' },
  { key: 'remove_watermark', name: 'Watermark Removal', description: 'Remove Core Engine brand footers and watermarks.' },
  { key: 'white_label', name: 'White Label Console', description: 'Custom console logos, brand naming, and admin customization.' },
  { key: 'multi_site', name: 'Multi-Site Fleet Management', description: 'Centralized management across multiple tenant instances.' },
  { key: 'priority_updates', name: 'Priority Updates & Support', description: 'Instant OTA patches and direct JA-CP sync.' },
  { key: 'theme_upload', name: 'Custom Theme Import', description: 'Upload and install external theme packages (.zip).' },
  { key: 'plugin_upload', name: 'Custom Plugin/Extension Import', description: 'Upload and install external extensions and plugins (.zip).' },
  { key: 'theme_export', name: 'Theme Package Export', description: 'Export active or custom themes into redistributable ZIP archives.' },
  { key: 'plugin_export', name: 'Plugin Package Export', description: 'Export installed extensions and plugins into certified ZIP packages.' },
];

const featureList = computed(() => {
  const currentFeatures = licenseData.value.features || {};
  return featureDefinitions.map(f => ({
    ...f,
    enabled: !!currentFeatures[f.key],
  }));
});

const formatDate = (dateStr?: string | null) => {
  if (!dateStr) return 'Never';
  try {
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  } catch {
    return dateStr;
  }
};

const fetchLicense = async () => {
  loading.value = true;
  try {
    const res = await api.get('/manage/system/license');
    const data = (res.data?.data ?? res.data) as LicenseState | undefined;
    if (data && typeof data === 'object' && data.tier) {
      licenseData.value = data;
    }
  } catch (err) {
    logger.error('Failed to fetch license:', err);
  } finally {
    loading.value = false;
  }
};

const handleActivate = async () => {
  if (!inputKey.value.trim()) return;
  activating.value = true;
  try {
    const res = await api.post('/manage/system/license/activate', {
      license_key: inputKey.value.trim(),
    });
    toast.success(res.data?.message || 'License activated successfully!');
    inputKey.value = '';
    await fetchLicense();
  } catch (err: unknown) {
    const axiosErr = err as { response?: { data?: { message?: string } }; message?: string };
    const message = axiosErr.response?.data?.message || axiosErr.message || 'Failed to activate license.';
    toast.error(message);
  } finally {
    activating.value = false;
  }
};

const refreshLicense = async () => {
  syncing.value = true;
  try {
    const res = await api.post('/manage/system/license/refresh');
    toast.success(res.data?.message || 'License verified with JA-CP!');
    await fetchLicense();
  } catch (err: unknown) {
    const axiosErr = err as { response?: { data?: { message?: string } }; message?: string };
    const message = axiosErr.response?.data?.message || axiosErr.message || 'Failed to sync with JA-CP.';
    toast.error(message);
  } finally {
    syncing.value = false;
  }
};

const handleDeactivate = async () => {
  const confirmed = await confirm({
    title: 'Deactivate License',
    message: 'Are you sure you want to deactivate this license? This instance will revert to the Community tier.',
    confirmText: 'Deactivate',
    variant: 'destructive',
  });

  if (!confirmed) return;

  deactivating.value = true;
  try {
    const res = await api.post('/manage/system/license/deactivate');
    toast.success(res.data?.message || 'License deactivated.');
    await fetchLicense();
  } catch (err: unknown) {
    const axiosErr = err as { response?: { data?: { message?: string } }; message?: string };
    const message = axiosErr.response?.data?.message || axiosErr.message || 'Failed to deactivate.';
    toast.error(message);
  } finally {
    deactivating.value = false;
  }
};

onMounted(() => {
  fetchLicense();
});
</script>
