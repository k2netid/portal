<template>
  <aside class="space-y-8">
    <!-- Search Box -->
    <div class="sarangenge-panel p-6">
      <h3 class="text-base font-bold text-foreground font-heading mb-3">
        {{ t('common.search', 'Cari Berita & Agenda') }}
      </h3>
      <form
        class="flex gap-2"
        @submit.prevent="handleSearch"
      >
        <Input
          v-model="searchQuery"
          :placeholder="t('common.searchPlaceholder', 'Ketik kata kunci...')"
          class="flex-1"
        />
        <Button
          type="submit"
          variant="primary"
          size="sm"
          class="!px-3.5"
        >
          <Search class="w-4 h-4" />
        </Button>
      </form>
    </div>

    <!-- Quick PPDB Banner -->
    <div class="sarangenge-bento__cell sarangenge-bento__cell--teal !p-6">
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

    <!-- Categories -->
    <div
      v-if="categories && categories.length > 0"
      class="sarangenge-panel p-6 space-y-4"
    >
      <h3 class="text-base font-bold text-foreground font-heading border-b border-border/60 pb-2">
        {{ t('blog.categories', 'Kategori Berita') }}
      </h3>
      <ul class="space-y-2 text-sm">
        <li
          v-for="cat in categories"
          :key="cat.slug"
        >
          <button
            type="button"
            class="flex items-center justify-between w-full py-1.5 px-2 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors text-left"
            :class="{ '!text-[var(--sarangenge-teal,#0f766e)] !font-bold !bg-[var(--sarangenge-teal,#0f766e)]/10': activeCategory === cat.slug }"
            @click="$emit('selectCategory', cat.slug)"
          >
            <span>{{ cat.name }}</span>
            <span
              v-if="cat.count !== undefined"
              class="text-xs bg-muted px-2 py-0.5 rounded-full"
            >
              {{ cat.count }}
            </span>
          </button>
        </li>
      </ul>
    </div>

    <!-- School Contact Card -->
    <div class="sarangenge-panel p-6 space-y-3 text-xs text-muted-foreground">
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Search } from 'lucide-vue-next';
import { Input, Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

interface Category {
  name: string;
  slug: string;
  count?: number;
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

const emit = defineEmits<{
  (e: 'selectCategory', slug: string): void;
  (e: 'search', query: string): void;
}>();

const { t } = useThemeI18n('sarangenge');
const router = useRouter();
const searchQuery = ref('');
const { displaySchoolName, displayAddress, displayPhone, displayEmail, displayAccreditation } = useSarangengeIdentity();

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    emit('search', searchQuery.value.trim());
    router.push({ path: '/search', query: { q: searchQuery.value.trim() } });
  }
};
</script>
