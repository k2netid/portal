<template>
  <aside class="space-y-6">
    <!-- Search Widget (Universal) -->
    <SearchWidget @search="$emit('search', $event)" />

    <!-- Quick PPDB Banner -->
    <div class="sarangenge-bento__cell sarangenge-bento__cell--teal !p-6 rounded-2xl shadow-sm">
      <div class="space-y-3">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[var(--sarangenge-sun,#e8a317)] text-[var(--sarangenge-teal-deep,#115e59)] uppercase tracking-wider inline-block">
          PPDB Online
        </span>
        <h4 class="text-lg font-bold font-heading text-white">
          {{ t('pages.home.heroBadge', 'Penerimaan Siswa Baru') }}
        </h4>
        <p class="text-xs text-white/80 leading-relaxed">
          {{ t('pages.home.tilePpdbDesc', 'Dapatkan panduan, jadwal gelombang, dan formulir pendaftaran.') }}
        </p>
        <Button
          as="router-link"
          to="/contact"
          variant="secondary"
          size="sm"
          class="w-full mt-2 !bg-white !text-[var(--sarangenge-teal-deep,#115e59)] hover:!bg-white/90"
        >
          {{ t('pages.home.heroCta', 'Info & Pendaftaran PPDB') }}
        </Button>
      </div>
    </div>

    <!-- Categories Widget (Universal) -->
    <CategoriesWidget
      :categories="categories"
      :active-category="activeCategory"
      @select-category="$emit('selectCategory', $event)"
    />

    <!-- School Contact Card -->
    <div class="sarangenge-panel p-5 rounded-2xl border border-border/70 bg-card shadow-sm space-y-3 text-xs text-muted-foreground">
      <h4 class="font-bold text-foreground text-sm font-heading">
        {{ displaySchoolName }}
      </h4>
      <p class="leading-relaxed">
        {{ displayAddress }}
      </p>
      <div class="pt-2 space-y-1">
        <p><strong class="text-foreground">Telepon:</strong> {{ displayPhone }}</p>
        <p><strong class="text-foreground">Email:</strong> {{ displayEmail }}</p>
        <p><strong class="text-foreground">Akreditasi:</strong> {{ displayAccreditation }}</p>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import SearchWidget from '@/modules/Layout/components/widgets/SearchWidget.vue';
import CategoriesWidget from '@/modules/Layout/components/widgets/CategoriesWidget.vue';

interface Category {
  name: string;
  slug: string;
  count?: number;
  contents_count?: number;
}

withDefaults(
  defineProps<{
    categories?: Category[];
    activeCategory?: string;
  }>(),
  {
    categories: () => [],
    activeCategory: '',
  }
);

defineEmits<{
  (e: 'selectCategory', slug: string): void;
  (e: 'search', query: string): void;
}>();

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName, displayAddress, displayPhone, displayEmail, displayAccreditation } = useSarangengeIdentity();
</script>
