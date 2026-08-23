import {
    heroGradient,
    featuresGrid,
    ctaDark,
    aboutSplit,
    teamGrid,
    blogHeader,
    contactSection,
    pricingSection,
    testimonialsSection,
    faqSection,
    videoSection,
    countdownSection
} from '@/modules/Layout/components/builder/templates/SectionTemplates';

interface TemplateNode {
    id: string;
    type: string;
    settings?: Record<string, unknown>;
    children?: TemplateNode[];
}

export const saasLandingPage = (): TemplateNode[] => [
    heroGradient(),
    featuresGrid(),
    videoSection(),
    pricingSection(),
    testimonialsSection(),
    faqSection(),
    countdownSection(),
    ctaDark()
];

export const homePage = (): TemplateNode[] => [
    heroGradient(),
    featuresGrid(),
    ctaDark()
];

export const aboutPage = (): TemplateNode[] => [
    aboutSplit(),
    teamGrid(),
    ctaDark()
];

export const blogPage = (): TemplateNode[] => [
    blogHeader(),
    (() => {
        const id = `tpl_${Math.random().toString(36).substr(2, 9)}`;
        return {
            id: id,
            type: 'section',
            settings: { padding: { top: '80', bottom: '80' } },
            children: [{
                id: `tpl_${Math.random().toString(36).substr(2, 9)}`,
                type: 'row',
                settings: { columns: '1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto' } },
                children: [{
                    id: `tpl_${Math.random().toString(36).substr(2, 9)}`,
                    type: 'column',
                    children: [{
                        id: `tpl_${Math.random().toString(36).substr(2, 9)}`,
                        type: 'blog',
                        settings: {
                            layout: 'grid',
                            columns: 3,
                            postsPerPage: 6,
                            showFeaturedImage: true,
                            showExcerpt: true
                        }
                    }]
                }]
            }]
        };
    })()
];

export const contactPage = (): TemplateNode[] => [
    contactSection(),
    faqSection()
];

export interface PageTemplate {
    id: string;
    name: string;
    description: string;
    thumbnail: string;
    factory: () => TemplateNode[];
}

export const pageTemplates: PageTemplate[] = [
    {
        id: 'janari-saas-landing',
        name: 'SaaS / Product Landing Page',
        description: 'Complete high-converting landing page: Hero, Features, Video, Pricing, Testimonials, FAQ & Countdown',
        thumbnail: 'saas-landing',
        factory: saasLandingPage
    },
    {
        id: 'janari-home',
        name: 'Home Page',
        description: 'Clean starter home page with Hero, Features, and CTA',
        thumbnail: 'home-page',
        factory: homePage
    },
    {
        id: 'janari-about',
        name: 'About Page',
        description: 'About us page with mission and team grid',
        thumbnail: 'about-page',
        factory: aboutPage
    },
    {
        id: 'janari-blog',
        name: 'Blog Page',
        description: 'Blog listing page with header and post grid',
        thumbnail: 'blog-page',
        factory: blogPage
    },
    {
        id: 'janari-contact',
        name: 'Contact Page',
        description: 'Contact page with form, info, and FAQ accordion',
        thumbnail: 'contact-page',
        factory: contactPage
    }
];

export default pageTemplates;
