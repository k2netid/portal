<template>
  <div
    v-if="totalItems > 0"
    :class="cn(
      'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4',
      embedded
        ? 'px-0 py-0 bg-transparent border-0 shadow-none rounded-none'
        : 'px-4 py-3 sm:px-6 sm:py-4 bg-card border border-border rounded-lg',
      props.class,
    )"
    data-slot="console-pagination"
  >
    <div
      class="flex w-full flex-col items-center gap-2 text-xs text-muted-foreground sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-4 sm:gap-y-2 sm:justify-start"
    >
      <p class="text-center text-foreground/80 sm:text-left">
        <span class="sm:hidden">{{ compactShowingLabel }}</span>
        <span class="hidden sm:inline">{{ fullShowingLabel }}</span>
      </p>
      <div
        v-if="showPerPage"
        class="flex items-center justify-center gap-2 sm:justify-start"
      >
        <span class="hidden sm:inline">{{ $t('common.pagination.rowsPerPage') }}</span>
        <span class="text-muted-foreground sm:hidden">{{ $t('common.pagination.perPage') }}</span>
        <Select v-model="selectValue">
          <SelectTrigger
            class="h-8 w-[70px] bg-background border-border/50 text-xs"
            :aria-label="$t('common.pagination.rowsPerPage')"
          >
            <SelectValue :placeholder="String(perPage)" />
          </SelectTrigger>
          <SelectContent
            side="top"
            :side-offset="5"
          >
            <SelectItem
              v-for="option in perPageOptions"
              :key="option"
              :value="String(option)"
            >
              {{ option }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <nav
      class="flex w-full flex-wrap items-center justify-center gap-1.5 sm:w-auto sm:justify-end sm:gap-2"
      :aria-label="$t('common.pagination.page')"
    >
      <Button
        v-if="showFirstLast && totalPages > 2"
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        class="hidden h-8 w-8 p-0 sm:inline-flex"
        :aria-label="$t('common.pagination.first')"
        @click="goToPage(1)"
      >
        <ChevronsLeft class="w-4 h-4" />
      </Button>

      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        class="h-8 gap-1 px-2 sm:px-3"
        :aria-label="$t('common.pagination.previous')"
        @click="goToPage(currentPage - 1)"
      >
        <ChevronLeft class="w-4 h-4 shrink-0" />
        <span class="hidden sm:inline">{{ $t('common.pagination.previous') }}</span>
      </Button>

      <span
        v-if="showPageNumbers && totalPages > 1"
        class="min-w-[4.5rem] px-1 text-center text-sm font-medium tabular-nums text-foreground sm:hidden"
        :aria-label="pageSummaryAriaLabel"
      >
        {{ pageSummaryLabel }}
      </span>

      <div
        v-if="showPageNumbers"
        class="hidden items-center gap-1 sm:flex"
      >
        <template
          v-for="page in visiblePages"
          :key="String(page)"
        >
          <Button
            v-if="page !== '...'"
            :variant="page === currentPage ? 'default' : 'outline'"
            size="sm"
            class="h-8 min-w-8 px-0"
            :aria-current="page === currentPage ? 'page' : undefined"
            :aria-label="$t('common.pagination.page') + ' ' + page"
            @click="goToPage(page)"
          >
            {{ page }}
          </Button>
          <span
            v-else
            class="px-1.5 text-muted-foreground select-none"
            aria-hidden="true"
          >…</span>
        </template>
      </div>

      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        class="h-8 gap-1 px-2 sm:px-3"
        :aria-label="$t('common.pagination.next')"
        @click="goToPage(currentPage + 1)"
      >
        <span class="hidden sm:inline">{{ $t('common.pagination.next') }}</span>
        <ChevronRight class="w-4 h-4 shrink-0" />
      </Button>

      <Button
        v-if="showFirstLast && totalPages > 2"
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        class="hidden h-8 w-8 p-0 sm:inline-flex"
        :aria-label="$t('common.pagination.last')"
        @click="goToPage(totalPages)"
      >
        <ChevronsRight class="w-4 h-4" />
      </Button>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
} from 'lucide-vue-next';
import Button from './Button.vue';
import Select from './Select.vue';
import SelectContent from './SelectContent.vue';
import SelectItem from './SelectItem.vue';
import SelectTrigger from './SelectTrigger.vue';
import SelectValue from './SelectValue.vue';
import { cn } from '@/shared/utils/lib-utils';

const { t } = useI18n();

const props = withDefaults(defineProps<{
  currentPage: number;
  totalItems: number;
  perPage?: number;
  perPageOptions?: number[];
  showPerPage?: boolean;
  showPageNumbers?: boolean;
  showFirstLast?: boolean;
  maxVisiblePages?: number;
  /** Strip outer card chrome when rendered inside ConsoleListCard #footer */
  embedded?: boolean;
  class?: HTMLAttributes['class'];
}>(), {
  perPage: 10,
  perPageOptions: () => [5, 10, 15, 20, 25, 50, 100],
  showPerPage: true,
  showPageNumbers: false,
  showFirstLast: false,
  maxVisiblePages: 5,
  embedded: false,
  class: '',
});

const emit = defineEmits<{
  'update:currentPage': [page: number];
  'update:perPage': [perPage: number];
  'page-change': [page: number];
  'per-page-change': [perPage: number];
}>();

const totalPages = computed(() => Math.ceil(props.totalItems / props.perPage) || 1);

const from = computed(() => (props.currentPage - 1) * props.perPage + 1);

const to = computed(() => Math.min(props.currentPage * props.perPage, props.totalItems));

const fullShowingLabel = computed(() =>
  t('common.pagination.showingRange', {
    from: from.value,
    to: to.value,
    total: props.totalItems,
  }),
);

const compactShowingLabel = computed(() =>
  t('common.pagination.showingCompact', {
    from: from.value,
    to: to.value,
    total: props.totalItems,
  }),
);

const pageSummaryLabel = computed(() =>
  t('common.pagination.pageSummary', {
    current: props.currentPage,
    total: totalPages.value,
  }),
);

const pageSummaryAriaLabel = computed(() =>
  t('common.labels.showing_page', {
    current: props.currentPage,
    total: totalPages.value,
  }),
);

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = props.currentPage;
  const max = props.maxVisiblePages;

  if (total <= max) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  const pages: (number | string)[] = [1];
  const windowStart = Math.max(2, current - 1);
  const windowEnd = Math.min(total - 1, current + 1);

  if (windowStart > 2) {
    pages.push('...');
  }

  for (let i = windowStart; i <= windowEnd; i++) {
    pages.push(i);
  }

  if (windowEnd < total - 1) {
    pages.push('...');
  }

  if (total > 1) {
    pages.push(total);
  }

  return pages;
});

function goToPage(page: number | string) {
  if (typeof page === 'string') return;
  if (page < 1 || page > totalPages.value || page === props.currentPage) return;
  emit('update:currentPage', page);
  emit('page-change', page);
}

const selectValue = computed({
  get: () => String(props.perPage),
  set: (val: string) => handlePerPageChange(val),
});

function handlePerPageChange(value: string) {
  const newPerPage = parseInt(value, 10);
  emit('update:perPage', newPerPage);
  emit('per-page-change', newPerPage);
  emit('update:currentPage', 1);
  emit('page-change', 1);
}
</script>
