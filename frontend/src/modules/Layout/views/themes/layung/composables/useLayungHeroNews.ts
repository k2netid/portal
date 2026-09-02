import { ref, computed, watch, onMounted, type Ref } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import { resolveLayungLocalizedCopy } from '@/modules/Layout/views/themes/layung/composables/resolveLayungLocalizedCopy';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';

export type LayungHeroNewsItem = {
  id: string | number;
  title: string;
  category: string;
  date: string;
  url: string;
  image?: string;
};

function sanitizeImageUrl(url: unknown): string | undefined {
  if (!url || typeof url !== 'string') return undefined;
  const trimmed = url.trim();
  if (!trimmed) return undefined;
  if (trimmed.includes('unsplash.com') || trimmed.includes('placehold.co')) return undefined;
  return trimmed;
}

function resolvePostImage(item: Record<string, unknown>, raw: Record<string, unknown>): string | undefined {
  return sanitizeImageUrl(
    item.thumbnail
      || item.featured_image
      || item.image
      || raw.thumbnail
      || raw.featured_image
      || raw.image,
  );
}

function isPostLike(item: Record<string, unknown>): boolean {
  const type = String(item?.type || (item._raw as Record<string, unknown> | undefined)?.type || '').toLowerCase();
  if (type === 'page') return false;
  if (type === 'post') return true;
  return Boolean(item?.title || item?._raw) && Boolean(item?.slug || (item._raw as Record<string, unknown> | undefined)?.slug);
}

function formatNewsDate(raw: unknown, locale: string): string {
  if (!raw) return '';
  const date = new Date(String(raw));
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleDateString(locale, { day: 'numeric', month: 'short', year: 'numeric' });
}

export function useLayungHeroNews(slotCountOverride?: Ref<number>) {
  const { t, locale } = useThemeI18n('layung');
  const { getSetting } = useTheme();
  const { data: dynamicHeroNews, hasBinding: hasHeroNewsBinding } = useThemeDataBindings('hero', 'news');

  const heroNewsEnabled = computed(() => getSetting('hero_news_enabled', true) !== false);

  const heroNewsCategory = computed(() => String(getSetting('hero_news_category', '') || '').trim());

  const heroNewsLimit = computed(() => {
    const parsed = parseInt(String(getSetting('hero_news_limit', 4)), 10);
    if (!Number.isFinite(parsed)) return 4;
    return Math.min(6, Math.max(2, parsed));
  });

  const heroNewsAutoplayMs = computed(() => {
    const parsed = parseInt(String(getSetting('hero_news_autoplay_interval', 4)), 10);
    const seconds = Number.isFinite(parsed) ? Math.min(15, Math.max(3, parsed)) : 4;
    return seconds * 1000;
  });

  const viewAllHref = computed(() => {
    const raw = getSetting('hero_news_view_all_url', '');
    return typeof raw === 'string' && raw.trim() ? raw.trim() : '/blog';
  });

  const viewAllLabel = computed(() =>
    resolveLayungLocalizedCopy({
      getSetting,
      locale: String(locale.value),
      key: 'hero_news_view_all_text',
      fallback: t('hero.newsViewAll', 'Semua warta'),
    }),
  );

  const sectionLabel = computed(() =>
    resolveLayungLocalizedCopy({
      getSetting,
      locale: String(locale.value),
      key: 'hero_news_section_label',
      fallback: t('hero.newsSection', 'Warta & promo'),
    }),
  );

  const effectiveLimit = computed(() => {
    const override = slotCountOverride?.value;
    if (typeof override === 'number' && override > 0) {
      return Math.min(heroNewsLimit.value, override);
    }
    return heroNewsLimit.value;
  });

  const news = ref<LayungHeroNewsItem[]>([]);
  const currentIndex = ref(0);

  const carouselItems = computed(() => {
    const items = news.value;
    const count = effectiveLimit.value;
    if (items.length === 0) return [];
    const slots = Math.min(count, items.length);
    if (items.length === 1) return items.slice(0, 1);
    const windowed: LayungHeroNewsItem[] = [];
    for (let i = 0; i < slots; i += 1) {
      windowed.push(items[(currentIndex.value + i) % items.length]!);
    }
    return windowed;
  });

  const canRotate = computed(() => news.value.length > 1);

  const nextIndex = () => {
    if (news.value.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % news.value.length;
  };

  const prevIndex = () => {
    if (news.value.length === 0) return;
    currentIndex.value = (currentIndex.value - 1 + news.value.length) % news.value.length;
  };

  watch(
    () => news.value.length,
    () => {
      currentIndex.value = 0;
    },
  );

  const mapPostsToNews = (posts: Record<string, unknown>[]): LayungHeroNewsItem[] =>
    posts.map((item, idx) => {
      const raw = (item._raw as Record<string, unknown> | undefined) || item;
      const slug = String(item.slug || raw.slug || idx);
      const publishedAt = item.published_at || raw.published_at;
      const categoryRaw = item.category || raw.category;
      const categoryName =
        typeof categoryRaw === 'object' && categoryRaw && 'name' in categoryRaw
          ? String((categoryRaw as { name?: string }).name || '')
          : typeof categoryRaw === 'string'
            ? categoryRaw
            : '';

      return {
        id: (item.id || raw.id || slug || idx) as string | number,
        title: String(item.title || raw.title || t('hero.newsDefaultTitle', 'Pembaruan K2NET')),
        category: categoryName || t('hero.newsDefaultCategory', 'Info'),
        date: formatNewsDate(publishedAt, String(locale.value)),
        url: `/blog/${slug}`,
        image: resolvePostImage(item, raw),
      };
    });

  const loadNews = async () => {
    if (!heroNewsEnabled.value) {
      news.value = [];
      return;
    }

    const bound = Array.isArray(dynamicHeroNews.value) ? dynamicHeroNews.value : [];
    const boundPosts = bound.filter((item) => isPostLike(item as Record<string, unknown>));

    if (hasHeroNewsBinding.value && boundPosts.length > 0) {
      news.value = mapPostsToNews(boundPosts.slice(0, 10) as Record<string, unknown>[]);
      return;
    }

    try {
      const params: Record<string, unknown> = {
        type: 'post',
        status: 'published',
        sort: '-published_at',
        per_page: 10,
      };
      if (heroNewsCategory.value) params.category = heroNewsCategory.value;

      const res = await api.get(publishingPaths.publicContents, { params });
      const rawData = res.data;
      const posts = Array.isArray(rawData) ? rawData : rawData?.data || [];

      news.value = posts.length > 0 ? mapPostsToNews(posts as Record<string, unknown>[]) : [];
    } catch (e: unknown) {
      const err = e as { name?: string; code?: string; message?: string };
      if (err.name === 'CanceledError' || err.code === 'ERR_CANCELED' || err.message?.includes('aborted')) return;
      if (news.value.length === 0) news.value = [];
    }
  };

  watch(dynamicHeroNews, () => {
    if (dynamicHeroNews.value?.length) void loadNews();
  }, { deep: true });

  watch([heroNewsCategory, heroNewsEnabled, locale], () => {
    void loadNews();
  });

  onMounted(() => {
    void loadNews();
  });

  return {
    heroNewsEnabled,
    carouselItems,
    canRotate,
    heroNewsAutoplayMs,
    currentIndex,
    nextIndex,
    prevIndex,
    viewAllHref,
    viewAllLabel,
    sectionLabel,
    refreshNews: loadNews,
  };
}
