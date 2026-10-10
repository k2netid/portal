<template>
  <MemberPage
    :title="t('member.billing.title')"
    :subtitle="t('member.billing.subtitle')"
  >
    <template #actions>
      <Button
        variant="outline"
        size="sm"
        @click="scrollToPackages"
      >
        <Package class="w-4 h-4 mr-2" />
        {{ t('member.billing.viewPlans') }}
      </Button>
    </template>

    <div class="space-y-8">
      <!-- Loading skeleton -->
      <div
        v-if="loading"
        class="space-y-4"
      >
        <div class="h-48 rounded-xl bg-muted/40 animate-pulse border border-border/60" />
        <div class="h-64 rounded-xl bg-muted/40 animate-pulse border border-border/60" />
      </div>

      <!-- Error message -->
      <div
        v-else-if="error"
        class="rounded-xl border border-destructive/30 bg-destructive/5 p-5 text-sm text-destructive flex items-start gap-3"
      >
        <AlertCircle class="w-5 h-5 shrink-0 mt-0.5" />
        <div class="flex-1">
          <p class="font-medium">
            {{ error }}
          </p>
          <Button
            variant="ghost"
            size="sm"
            class="mt-2 text-destructive hover:bg-destructive/10"
            @click="loadBilling"
          >
            Coba Lagi
          </Button>
        </div>
      </div>

      <template v-else>
        <!-- 1. Active Subscription Card -->
        <Card class="border-border/60 shadow-sm overflow-hidden bg-card">
          <CardHeader class="border-b border-border/40 bg-muted/20 pb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-primary/10 text-primary">
                  <CreditCard class="w-5 h-5" />
                </div>
                <div>
                  <CardTitle class="text-lg font-semibold text-foreground">
                    {{ summary?.subscription?.name ?? summary?.package?.name ?? t('member.billing.currentPlan') }}
                  </CardTitle>
                  <CardDescription class="text-xs text-muted-foreground mt-0.5">
                    {{ summary?.subscription?.domain ?? t('member.billing.subtitle') }}
                  </CardDescription>
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <Badge
                  :variant="badgeVariant(summary?.subscription?.status ?? 'none')"
                  class="capitalize px-2.5 py-1 text-xs"
                >
                  {{ statusLabel(summary?.subscription?.status ?? 'none') }}
                </Badge>
                <Badge
                  v-if="(summary?.pending_count ?? 0) > 0"
                  variant="outline"
                  class="border-amber-500/40 text-amber-500 bg-amber-500/10 px-2.5 py-1 text-xs"
                >
                  {{ t('member.billing.pendingCount', { n: summary?.pending_count ?? 0 }) }}
                </Badge>
              </div>
            </div>
          </CardHeader>

          <CardContent class="pt-5 pb-6">
            <div
              v-if="summary?.has_subscription && summary.subscription"
              class="space-y-5"
            >
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-3.5 rounded-lg bg-muted/30 border border-border/40">
                  <p class="text-xs text-muted-foreground">
                    {{ t('member.billing.expires') }}
                  </p>
                  <p class="text-sm font-semibold text-foreground mt-1">
                    {{ summary.subscription.expires_at ? formatDate(summary.subscription.expires_at) : '—' }}
                  </p>
                </div>
                <div class="p-3.5 rounded-lg bg-muted/30 border border-border/40">
                  <p class="text-xs text-muted-foreground">
                    {{ t('member.billing.usersIncluded') }}
                  </p>
                  <p class="text-sm font-semibold text-foreground mt-1">
                    {{ summary.subscription.user_limit }} Pengguna
                  </p>
                </div>
                <div class="p-3.5 rounded-lg bg-muted/30 border border-border/40">
                  <p class="text-xs text-muted-foreground">
                    {{ t('member.billing.storage') }}
                  </p>
                  <p class="text-sm font-semibold text-foreground mt-1">
                    {{ formatStorage(summary.subscription.storage_limit_mb) }}
                  </p>
                </div>
              </div>

              <!-- Feature highlights -->
              <div
                v-if="summary.package?.feature_highlights?.length"
                class="pt-3 border-t border-border/40"
              >
                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2.5">
                  Fitur Unggulan Paket
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-muted-foreground">
                  <div
                    v-for="(feat, idx) in summary.package.feature_highlights"
                    :key="idx"
                    class="flex items-center gap-2"
                  >
                    <CheckCircle2 class="w-4 h-4 text-primary shrink-0" />
                    <span>{{ feat }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- No subscription empty state -->
            <div
              v-else
              class="py-8 text-center space-y-3"
            >
              <div class="w-12 h-12 rounded-full bg-primary/10 text-primary mx-auto flex items-center justify-center">
                <Sparkles class="w-6 h-6" />
              </div>
              <div>
                <p class="font-semibold text-foreground">
                  {{ t('member.billing.noActivePlan') }}
                </p>
                <p class="text-sm text-muted-foreground max-w-md mx-auto mt-1">
                  {{ t('member.billing.noActivePlanDesc') }}
                </p>
              </div>
              <Button
                size="sm"
                class="mt-2"
                @click="scrollToPackages"
              >
                {{ t('member.billing.viewPlans') }}
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- 2. Available Packages Catalog -->
        <div
          id="billing-packages"
          class="space-y-4"
        >
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-foreground">
                Pilihan Paket Langganan
              </h2>
              <p class="text-xs text-muted-foreground">
                Tingkatkan bandwidth dan kapasitas sesuai kebutuhan Anda.
              </p>
            </div>

            <!-- Billing period toggle -->
            <div class="inline-flex items-center p-1 rounded-lg bg-muted/60 border border-border/50 text-xs font-medium">
              <button
                type="button"
                class="px-3 py-1.5 rounded-md transition-all"
                :class="billingPeriod === 'monthly' ? 'bg-card text-foreground shadow-sm font-semibold' : 'text-muted-foreground hover:text-foreground'"
                @click="billingPeriod = 'monthly'"
              >
                {{ t('member.billing.monthly') }}
              </button>
              <button
                type="button"
                class="px-3 py-1.5 rounded-md transition-all"
                :class="billingPeriod === 'yearly' ? 'bg-card text-foreground shadow-sm font-semibold' : 'text-muted-foreground hover:text-foreground'"
                @click="billingPeriod = 'yearly'"
              >
                {{ t('member.billing.yearly') }}
              </button>
            </div>
          </div>

          <!-- Packages grid -->
          <div
            v-if="packages.length > 0"
            class="grid grid-cols-1 md:grid-cols-3 gap-5"
          >
            <Card
              v-for="pkg in packages"
              :key="pkg.id"
              class="border-border/60 shadow-sm flex flex-col justify-between hover:border-primary/50 transition-colors"
              :class="{ 'ring-2 ring-primary/20 border-primary/40': summary?.package?.id === pkg.id }"
            >
              <CardHeader class="pb-3">
                <div class="flex items-center justify-between gap-2">
                  <CardTitle class="text-base font-semibold">
                    {{ pkg.name }}
                  </CardTitle>
                  <Badge
                    v-if="pkg.product_name"
                    variant="outline"
                    class="text-[10px]"
                  >
                    {{ pkg.product_name }}
                  </Badge>
                </div>
                <div class="mt-3">
                  <span class="text-2xl font-bold text-foreground">
                    {{ formatIdr(billingPeriod === 'yearly' ? pkg.price_yearly : pkg.price_monthly) }}
                  </span>
                  <span class="text-xs text-muted-foreground ml-1">
                    {{ billingPeriod === 'yearly' ? t('member.billing.perYear') : t('member.billing.perMonth') }}
                  </span>
                </div>
              </CardHeader>

              <CardContent class="py-2 flex-1">
                <div class="space-y-2 text-xs text-muted-foreground border-t border-border/40 pt-3">
                  <div class="flex items-center justify-between">
                    <span>Kapasitas Pengguna</span>
                    <span class="font-medium text-foreground">{{ pkg.user_limit }} akun</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Penyimpanan</span>
                    <span class="font-medium text-foreground">{{ formatStorage(pkg.storage_limit_mb) }}</span>
                  </div>
                  <div
                    v-if="pkg.feature_highlights?.length"
                    class="pt-2 space-y-1.5"
                  >
                    <div
                      v-for="(feat, fIdx) in pkg.feature_highlights"
                      :key="fIdx"
                      class="flex items-start gap-1.5"
                    >
                      <CheckCircle2 class="w-3.5 h-3.5 text-primary shrink-0 mt-0.5" />
                      <span>{{ feat }}</span>
                    </div>
                  </div>
                </div>
              </CardContent>

              <CardFooter class="pt-4 border-t border-border/40">
                <Button
                  class="w-full"
                  size="sm"
                  :disabled="orderingId === pkg.id"
                  @click="handleOrder(pkg.id)"
                >
                  <Spinner
                    v-if="orderingId === pkg.id"
                    class="w-4 h-4 mr-2"
                  />
                  <span>{{ orderingId === pkg.id ? t('member.billing.ordering') : t('member.billing.selectPlan') }}</span>
                </Button>
              </CardFooter>
            </Card>
          </div>
        </div>

        <!-- 3. Invoices / Recent Transactions Card -->
        <Card class="border-border/60 shadow-sm overflow-hidden bg-card">
          <CardHeader class="border-b border-border/40 bg-muted/20 pb-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-primary/10 text-primary">
                  <Receipt class="w-5 h-5" />
                </div>
                <div>
                  <CardTitle class="text-base font-semibold">
                    {{ t('member.billing.recentInvoices') }}
                  </CardTitle>
                  <CardDescription class="text-xs text-muted-foreground">
                    Riwayat pembayaran dan faktur langganan Anda.
                  </CardDescription>
                </div>
              </div>
            </div>
          </CardHeader>

          <CardContent class="p-0">
            <div
              v-if="summary?.transactions && summary.transactions.length > 0"
              class="divide-y divide-border/40"
            >
              <div
                v-for="txn in summary.transactions"
                :key="txn.id"
                class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-muted/10 transition-colors"
              >
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <p class="font-mono text-xs font-medium text-foreground">
                      {{ txn.invoice_number }}
                    </p>
                    <Badge
                      :variant="badgeVariant(txn.payment_status)"
                      class="text-[10px] uppercase px-1.5 py-0.5"
                    >
                      {{ txn.payment_status }}
                    </Badge>
                  </div>
                  <p class="text-base font-bold text-foreground">
                    {{ txn.amount_formatted ?? formatIdr(txn.amount) }}
                  </p>
                  <p
                    v-if="txn.package_name"
                    class="text-xs text-muted-foreground"
                  >
                    {{ txn.package_name }} • {{ txn.created_at ? formatDate(txn.created_at) : '—' }}
                  </p>
                </div>

                <div class="flex items-center gap-2">
                  <Button
                    v-if="txn.can_checkout"
                    size="sm"
                    :disabled="checkoutId === txn.id"
                    @click="startCheckout(txn.id)"
                  >
                    <Spinner
                      v-if="checkoutId === txn.id"
                      class="w-4 h-4 mr-2"
                    />
                    {{ checkoutId === txn.id ? t('member.billing.checkoutWorking') : t('member.billing.payNow') }}
                  </Button>
                </div>
              </div>
            </div>

            <!-- Empty invoices -->
            <div
              v-else
              class="p-8 text-center text-sm text-muted-foreground"
            >
              {{ t('member.billing.noInvoices') }}
            </div>
          </CardContent>
        </Card>
      </template>
    </div>
  </MemberPage>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MemberPage from '@/modules/Member/components/MemberPage.vue';
import {
    Button,
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
    Badge,
    Spinner,
} from '@/shared/components/ui';
import {
    AlertCircle,
    CheckCircle2,
    CreditCard,
    Package,
    Receipt,
    Sparkles,
} from 'lucide-vue-next';
import {
    billingService,
    type BillingPackage,
    type BillingSummaryResponse,
} from '../services/billingService';

const { t } = useI18n();

const loading = ref(true);
const error = ref('');
const summary = ref<BillingSummaryResponse | null>(null);
const packages = ref<BillingPackage[]>([]);
const billingPeriod = ref<'monthly' | 'yearly'>('monthly');

const checkoutId = ref<string | null>(null);
const orderingId = ref<string | null>(null);

const formatIdr = (amount: number): string => {
    if (!Number.isFinite(amount) || amount <= 0) return '—';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatStorage = (mb: number): string => {
    if (mb >= 1024) {
        return `${Math.round(mb / 1024)} GB`;
    }
    return `${mb} MB`;
};

const formatDate = (iso: string): string => {
    try {
        return new Date(iso).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    } catch {
        return iso;
    }
};

const badgeVariant = (status: string): 'default' | 'outline' | 'secondary' | 'destructive' => {
    switch (status.toLowerCase()) {
        case 'active':
        case 'paid':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'expired':
        case 'canceled':
        case 'refunded':
            return 'destructive';
        default:
            return 'outline';
    }
};

const statusLabel = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'Aktif';
        case 'pending':
            return 'Menunggu Pembayaran';
        case 'expired':
            return 'Kedaluwarsa';
        case 'suspended':
            return 'Ditangguhkan';
        case 'none':
            return 'Tidak Ada Paket';
        default:
            return status;
    }
};

const scrollToPackages = (): void => {
    const el = document.getElementById('billing-packages');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};

async function loadBilling(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const [billingRes, packagesRes] = await Promise.all([
            billingService.fetchBilling(),
            billingService.fetchPackages(),
        ]);

        summary.value = billingRes.data?.data ?? null;
        packages.value = packagesRes.data?.data ?? [];
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('member.billing.loadFailed');
    } finally {
        loading.value = false;
    }
}

async function handleOrder(packageId: string): Promise<void> {
    orderingId.value = packageId;
    try {
        const response = await billingService.orderPackage({
            package_id: packageId,
            billing_period: billingPeriod.value,
            payment_method: 'midtrans',
        });

        const data = response.data?.data;
        if (data?.checkout?.redirect_url && typeof window !== 'undefined') {
            window.location.href = data.checkout.redirect_url;
            return;
        }

        await loadBilling();
    } catch (e: unknown) {
        alert(e instanceof Error ? e.message : 'Gagal membuat pesanan langganan.');
    } finally {
        orderingId.value = null;
    }
}

async function startCheckout(transactionId: string): Promise<void> {
    checkoutId.value = transactionId;
    try {
        const response = await billingService.checkoutTransaction(transactionId, billingPeriod.value);
        const data = response.data?.data as Record<string, unknown> | undefined;
        if (typeof data?.redirect_url === 'string' && typeof window !== 'undefined') {
            window.location.href = data.redirect_url;
            return;
        }

        await loadBilling();
    } catch (e: unknown) {
        alert(e instanceof Error ? e.message : t('member.billing.checkoutFailed'));
    } finally {
        checkoutId.value = null;
    }
}

onMounted(() => {
    void loadBilling();
});
</script>
