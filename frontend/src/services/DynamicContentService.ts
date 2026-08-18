import { logger } from '@/utils/logger';
import { ref, type Ref } from 'vue';
import type { RenderingContext } from './ConditionEvaluator';

export interface DynamicContentSource {
    id: string;
    label: string;
    group: string;
    icon?: string;
    handler: (context: RenderingContext) => unknown;
}

class DynamicContentService {
    private sources: Ref<DynamicContentSource[]>;

    constructor() {
        this.sources = ref<DynamicContentSource[]>([]);
        this.initializeDefaultSources();
    }

    public register(source: DynamicContentSource): void {
        this.sources.value.push(source);
    }

    public getSources(): DynamicContentSource[] {
        return this.sources.value;
    }

    public resolve(sourceId: string, context: RenderingContext): unknown {
        const source = this.sources.value.find(s => s.id === sourceId);
        if (!source) return null;
        try {
            return source.handler(context);
        } catch (e) {
            logger.warning(`Failed to resolve dynamic content: ${sourceId}`, e);
            return null;
        }
    }

    private initializeDefaultSources(): void {
        // Post Data
        this.register({
            id: 'post.title',
            label: 'Post Title',
            group: 'Post',
            icon: 'FileText',
            handler: (ctx) => ctx?.post?.title || 'Current Post Title'
        });

        this.register({
            id: 'post.excerpt',
            label: 'Post Excerpt',
            group: 'Post',
            icon: 'AlignLeft',
            handler: (ctx) => ctx?.post?.excerpt || 'Post excerpt goes here...'
        });

        this.register({
            id: 'post.date',
            label: 'Publish Date',
            group: 'Post',
            icon: 'Calendar',
            handler: (ctx) => ctx?.post?.published_at || new Date().toLocaleDateString()
        });

        this.register({
            id: 'post.author',
            label: 'Author Name',
            group: 'Post',
            icon: 'User',
            handler: (ctx) => ctx?.post?.author?.name || 'Author Name'
        });

        this.register({
            id: 'post.category',
            label: 'Post Category',
            group: 'Post',
            icon: 'Tag',
            handler: (ctx) => ctx?.post?.category?.name || 'Category'
        });

        this.register({
            id: 'post.featured_image',
            label: 'Featured Image',
            group: 'Post',
            icon: 'Image',
            handler: (ctx) => ctx?.post?.featured_image || 'https://images.unsplash.com/photo-1491841573634-28140fc7ced7?q=80&w=1200'
        });

        // WooCommerce Product Data
        this.register({
            id: 'product.title',
            label: 'Product Title',
            group: 'Shop',
            icon: 'Package',
            handler: (ctx) => ctx?.product?.name || ctx?.post?.title || 'Product Title'
        });

        this.register({
            id: 'product.price',
            label: 'Product Price',
            group: 'Shop',
            icon: 'DollarSign',
            handler: (ctx) => {
                const p = ctx?.product || ctx?.post;
                return p ? `${p.currency || '$'}${p.price || p.regular_price || '0.00'}` : '$0.00';
            }
        });

        this.register({
            id: 'product.sku',
            label: 'Product SKU',
            group: 'Shop',
            icon: 'Hash',
            handler: (ctx) => ctx?.product?.sku || 'SKU-001'
        });

        this.register({
            id: 'product.description',
            label: 'Product Description',
            group: 'Shop',
            icon: 'AlignLeft',
            handler: (ctx) => ctx?.product?.description || ctx?.post?.body || 'Product description goes here...'
        });

        this.register({
            id: 'product.rating',
            label: 'Product Rating',
            group: 'Shop',
            icon: 'Star',
            handler: (ctx) => ctx?.product?.rating || '4.5'
        });

        // Site Data
        this.register({
            id: 'site.name',
            label: 'Site Name',
            group: 'Site',
            icon: 'Globe',
            handler: (ctx) => ctx?.site?.name || 'My Website'
        });

        this.register({
            id: 'site.tagline',
            label: 'Site Tagline',
            group: 'Site',
            icon: 'Quote',
            handler: (ctx) => ctx?.site?.tagline || 'Just another JA-CMS site'
        });
    }
}

export const dynamicContent = new DynamicContentService();
