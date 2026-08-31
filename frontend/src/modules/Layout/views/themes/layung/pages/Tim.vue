<template>
  <div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
    <Breadcrumb :items="[{ name: t('pages.team.title', 'Tim Engineer') }]" />

    <!-- CMS Customizer / Builder Override -->
    <template v-if="hasBuilderBlocks">
      <BlockRenderer :blocks="builderBlocks" />
    </template>

    <template v-else-if="cmsBody">
      <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
        <ThemeSafeHtml :html="cmsBody" />
      </div>
    </template>

    <template v-else>
      <div class="space-y-4 max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 font-mono uppercase">
          Pakar Telekomunikasi & Keamanan
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.team.title', 'Direktori Tim Engineer & Arsitek Jaringan') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.team.subtitle', 'Didukung oleh profesional bersertifikasi CCIE, CCNP, CEH, dan ITIL yang siaga menjaga jaringan Anda setiap detik.') }}
        </p>
      </div>

      <!-- Team Members Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div
          v-for="(member, idx) in engineers"
          :key="idx"
          class="layung-panel p-6 space-y-4 text-center flex flex-col items-center group hover:-translate-y-1 transition-all"
        >
          <div class="w-20 h-20 rounded-2xl bg-slate-900 border-2 border-slate-700/80 text-orange-400 flex items-center justify-center font-black text-xl font-heading shadow-md group-hover:scale-105 transition-transform">
            {{ staffInitials(member.name) }}
          </div>
          <div class="space-y-1">
            <h4 class="text-base font-bold text-foreground font-heading">
              {{ member.name }}
            </h4>
            <p class="text-xs text-orange-500 font-semibold">
              {{ member.role }}
            </p>
            <p class="text-[11px] font-mono text-muted-foreground pt-1">
              {{ member.cert }}
            </p>
          </div>
        </div>
      </div>

      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import CtaSection from '../components/sections/CtaSection.vue';

const { t } = useThemeI18n('layung');
const { cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('tim');

const staffInitials = (name: string) => {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  const first = parts[0]?.charAt(0) ?? '';
  const second = parts[1]?.charAt(0) ?? '';
  return `${first}${second}`;
};

const engineers = [
  {
    name: 'Andi Dharmawan, S.Kom.',
    role: 'Head of Network Architecture',
    cert: 'CCIE #48291 · JNCIE',
  },
  {
    name: 'Rian Kurniawan, M.T.',
    role: 'Principal Cyber SOC Lead',
    cert: 'CISSP · CEH Master',
  },
  {
    name: 'Mega Suryani, S.T.',
    role: 'Senior Cloud Solutions Architect',
    cert: 'AWS Solutions Architect Pro',
  },
  {
    name: 'Fikri Hidayat',
    role: 'NOC Tier-3 Operations Lead',
    cert: 'CCNP Enterprise · ITIL v4',
  },
];
</script>
