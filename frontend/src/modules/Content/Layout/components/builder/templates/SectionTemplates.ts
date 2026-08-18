/**
 * Janari Section Templates
 * Pre-built section designs matching the Janari theme
 */

// Helper to generate unique IDs
const generateId = (): string => `tpl_${Math.random().toString(36).substr(2, 9)}`

interface TemplateNode {
    id: string;
    type: string;
    settings?: Record<string, unknown>;
    children?: TemplateNode[];
}

/**
 * Hero Section - Gradient with Badge, Title, Subtitle, CTAs
 */
export const heroGradient = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: {
            type: 'gradient',
            gradient: 'linear-gradient(135deg, hsl(var(--primary) / 0.1) 0%, hsl(var(--background)) 100%)'
        },
        padding: { top: '120', right: '0', bottom: '120', left: '0' },
        overflow: 'hidden'
    },
    children: [{
        id: generateId(),
        type: 'row',
        settings: { columns: '1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto' } },
        children: [{
            id: generateId(),
            type: 'column',
            settings: { textAlign: 'center', padding: { left: '16', right: '16' } },
            children: [
                {
                    id: generateId(),
                    type: 'text',
                    settings: {
                        content: '<span style="display: inline-flex; align-items: center; padding: 6px 16px; font-size: 14px; font-weight: 600; background: hsl(var(--primary) / 0.1); color: hsl(var(--primary)); border-radius: 9999px; margin-bottom: 32px;">🚀 v1.0 Janari Edition</span>'
                    }
                },
                {
                    id: generateId(),
                    type: 'heading',
                    settings: {
                        text: 'Build Amazing Websites',
                        tag: 'h1',
                        alignment: 'center',
                        fontSize: '56px',
                        fontWeight: '700',
                        lineHeight: '1.1',
                        margin: { bottom: '24' }
                    }
                },
                {
                    id: generateId(),
                    type: 'text',
                    settings: {
                        content: '<p style="font-size: 20px; color: hsl(var(--muted-foreground)); max-width: 600px; margin: 0 auto 40px;">Built with Laravel & Vue.js for speed, flexibility, and a premium developer experience.</p>'
                    }
                },
                {
                    id: generateId(),
                    type: 'button',
                    settings: {
                        text: 'Get Started Free',
                        url: '/register',
                        alignment: 'center',
                        bgColor: 'hsl(var(--primary))',
                        textColor: 'hsl(var(--primary-foreground))',
                        borderRadius: '9999px',
                        padding: { top: '16', right: '32', bottom: '16', left: '32' },
                        fontWeight: '600'
                    }
                }
            ]
        }]
    }]
})

/**
 * Features Grid - 3 columns with icon cards
 */
export const featuresGrid = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: { type: 'color', color: 'transparent' },
        padding: { top: '96', right: '0', bottom: '96', left: '0' }
    },
    children: [
        {
            id: generateId(),
            type: 'row',
            settings: { columns: '1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto', bottom: '48' } },
            children: [{
                id: generateId(),
                type: 'column',
                settings: { textAlign: 'center', padding: { left: '16', right: '16' } },
                children: [
                    {
                        id: generateId(),
                        type: 'text',
                        settings: {
                            content: '<span style="color: hsl(var(--primary)); font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.1em;">Features</span>'
                        }
                    },
                    {
                        id: generateId(),
                        type: 'heading',
                        settings: {
                            text: 'Powerful Features',
                            tag: 'h2',
                            alignment: 'center',
                            fontSize: '36px',
                            fontWeight: '700',
                            margin: { top: '16', bottom: '16' }
                        }
                    },
                    {
                        id: generateId(),
                        type: 'text',
                        settings: {
                            content: '<p style="font-size: 18px; color: hsl(var(--muted-foreground)); max-width: 600px; margin: 0 auto;">Everything you need to build and manage modern websites</p>'
                        }
                    }
                ]
            }]
        },
        {
            id: generateId(),
            type: 'row',
            settings: { columns: '1-1-1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto' }, gutterWidth: '32' },
            children: [
                createFeatureCard('Zap', 'Blazing Fast', 'Optimized for speed with smart caching, lazy loading, and efficient database queries.'),
                createFeatureCard('Palette', 'Theme System', 'Flexible theming with dark mode, custom colors, and complete customization.'),
                createFeatureCard('Layers', 'Block Builder', 'Visual page builder with drag-and-drop blocks. No coding required.')
            ]
        }
    ]
})

// Helper to create feature card
function createFeatureCard(icon: string, title: string, description: string): TemplateNode {
    return {
        id: generateId(),
        type: 'column',
        settings: { padding: { left: '16', right: '16' } },
        children: [{
            id: generateId(),
            type: 'blurb',
            settings: {
                icon: icon,
                title: title,
                content: description,
                iconSize: '28',
                iconBgColor: 'hsl(var(--primary) / 0.2)',
                iconColor: 'hsl(var(--primary))',
                titleSize: '20px',
                titleWeight: '700',
                padding: { top: '32', right: '32', bottom: '32', left: '32' },
                background: 'hsl(var(--card))',
                borderRadius: '16px',
                border: '1px solid hsl(var(--border))'
            }
        }]
    }
}

/**
 * About Split - Text with stats on left, image on right
 */
export const aboutSplit = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: { type: 'color', color: 'hsl(var(--muted) / 0.5)' },
        padding: { top: '96', right: '0', bottom: '96', left: '0' }
    },
    children: [{
        id: generateId(),
        type: 'row',
        settings: { columns: '1-1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto' }, gutterWidth: '64' },
        children: [
            {
                id: generateId(),
                type: 'column',
                settings: { padding: { left: '16', right: '16' } },
                children: [
                    {
                        id: generateId(),
                        type: 'text',
                        settings: {
                            content: '<span style="color: hsl(var(--primary)); font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.1em;">About Us</span>'
                        }
                    },
                    {
                        id: generateId(),
                        type: 'heading',
                        settings: {
                            text: 'Modern CMS for Digital Era',
                            tag: 'h2',
                            fontSize: '36px',
                            fontWeight: '700',
                            margin: { top: '16', bottom: '24' }
                        }
                    },
                    {
                        id: generateId(),
                        type: 'text',
                        settings: {
                            content: '<p style="font-size: 18px; color: hsl(var(--muted-foreground)); line-height: 1.7; margin-bottom: 16px;">We build next-generation Content Management Systems using the latest technology. Combining Laravel backend power with Vue.js frontend for a seamless experience.</p><p style="color: hsl(var(--muted-foreground)); line-height: 1.7;">Designed for developers who want speed, flexibility, and easy customization without sacrificing performance.</p>'
                        }
                    }
                ]
            },
            {
                id: generateId(),
                type: 'column',
                settings: { padding: { left: '16', right: '16' } },
                children: [{
                    id: generateId(),
                    type: 'image',
                    settings: {
                        src: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80',
                        alt: 'Team working',
                        borderRadius: '16px'
                    }
                }]
            }
        ]
    }]
})

/**
 * CTA Dark - Dark background with prominent buttons
 */
export const ctaDark = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: {
            type: 'gradient',
            gradient: 'linear-gradient(135deg, hsl(222, 47%, 11%) 0%, hsl(217, 33%, 17%) 100%)'
        },
        padding: { top: '96', right: '0', bottom: '96', left: '0' }
    },
    children: [{
        id: generateId(),
        type: 'row',
        settings: { columns: '1', maxWidth: '800px', margin: { left: 'auto', right: 'auto' } },
        children: [{
            id: generateId(),
            type: 'column',
            settings: { textAlign: 'center', padding: { left: '16', right: '16' } },
            children: [
                {
                    id: generateId(),
                    type: 'heading',
                    settings: {
                        text: 'Ready to Get Started?',
                        tag: 'h2',
                        alignment: 'center',
                        fontSize: '42px',
                        fontWeight: '700',
                        color: '#ffffff',
                        margin: { bottom: '24' }
                    }
                },
                {
                    id: generateId(),
                    type: 'text',
                    settings: {
                        content: '<p style="font-size: 20px; color: rgba(255,255,255,0.8); margin-bottom: 40px;">Start building your dream website today. Free, open source, and powerful.</p>'
                    }
                },
                {
                    id: generateId(),
                    type: 'button',
                    settings: {
                        text: 'Sign Up Now',
                        url: '/register',
                        alignment: 'center',
                        bgColor: '#ffffff',
                        textColor: '#0f172a',
                        borderRadius: '9999px',
                        padding: { top: '16', right: '32', bottom: '16', left: '32' },
                        fontWeight: '700'
                    }
                }
            ]
        }]
    }]
})

/**
 * Team Grid - 4 team member cards
 */
export const teamGrid = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: { type: 'color', color: 'transparent' },
        padding: { top: '96', right: '0', bottom: '96', left: '0' }
    },
    children: [
        {
            id: generateId(),
            type: 'row',
            settings: { columns: '1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto', bottom: '48' } },
            children: [{
                id: generateId(),
                type: 'column',
                settings: { textAlign: 'center', padding: { left: '16', right: '16' } },
                children: [
                    {
                        id: generateId(),
                        type: 'text',
                        settings: {
                            content: '<span style="color: hsl(var(--primary)); font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.1em;">Our Team</span>'
                        }
                    },
                    {
                        id: generateId(),
                        type: 'heading',
                        settings: {
                            text: 'Meet the Team',
                            tag: 'h2',
                            alignment: 'center',
                            fontSize: '36px',
                            fontWeight: '700',
                            margin: { top: '16', bottom: '16' }
                        }
                    }
                ]
            }]
        },
        {
            id: generateId(),
            type: 'row',
            settings: { columns: '1-1-1-1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto' }, gutterWidth: '24' },
            children: [
                createTeamMember('AN', 'Ari Nurcahya', 'Lead Developer'),
                createTeamMember('SA', 'Sarah Amira', 'Frontend Engineer'),
                createTeamMember('BS', 'Budi Santoso', 'Backend Engineer'),
                createTeamMember('MP', 'Maya Putri', 'UI/UX Designer')
            ]
        }
    ]
})

function createTeamMember(initials: string, name: string, role: string): TemplateNode {
    return {
        id: generateId(),
        type: 'column',
        settings: { textAlign: 'center', padding: { left: '16', right: '16' } },
        children: [{
            id: generateId(),
            type: 'person',
            settings: {
                name: name,
                role: role,
                initials: initials,
                imageAspect: '4/5',
                imageBg: 'hsl(var(--muted))',
                borderRadius: '16px'
            }
        }]
    }
}

/**
 * Blog Header - Gradient header for blog page
 */
export const blogHeader = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: {
            type: 'gradient',
            gradient: 'linear-gradient(180deg, hsl(var(--primary) / 0.1) 0%, hsl(var(--background)) 100%)'
        },
        padding: { top: '80', right: '0', bottom: '80', left: '0' }
    },
    children: [{
        id: generateId(),
        type: 'row',
        settings: { columns: '1', maxWidth: '800px', margin: { left: 'auto', right: 'auto' } },
        children: [{
            id: generateId(),
            type: 'column',
            settings: { textAlign: 'center', padding: { left: '16', right: '16' } },
            children: [
                {
                    id: generateId(),
                    type: 'text',
                    settings: {
                        content: '<span style="color: hsl(var(--primary)); font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.1em;">Blog</span>'
                    }
                },
                {
                    id: generateId(),
                    type: 'heading',
                    settings: {
                        text: 'The Journal',
                        tag: 'h1',
                        alignment: 'center',
                        fontSize: '48px',
                        fontWeight: '700',
                        margin: { top: '16', bottom: '16' }
                    }
                },
                {
                    id: generateId(),
                    type: 'text',
                    settings: {
                        content: '<p style="font-size: 18px; color: hsl(var(--muted-foreground));">Insights, tutorials, and the latest updates from our team.</p>'
                    }
                }
            ]
        }]
    }]
})

/**
 * Contact Section - Form and Info
 */
export const contactSection = (): TemplateNode => ({
    id: generateId(),
    type: 'section',
    settings: {
        background: { type: 'color', color: 'transparent' },
        padding: { top: '80', right: '0', bottom: '80', left: '0' }
    },
    children: [{
        id: generateId(),
        type: 'row',
        settings: { columns: '1-1', maxWidth: '1280px', margin: { left: 'auto', right: 'auto' }, gutterWidth: '48' },
        children: [
            {
                id: generateId(),
                type: 'column',
                settings: { padding: { left: '16', right: '16' } },
                children: [
                    {
                        id: generateId(),
                        type: 'heading',
                        settings: {
                            text: 'Send a Message',
                            tag: 'h2',
                            fontSize: '28px',
                            fontWeight: '600',
                            margin: { bottom: '24' }
                        }
                    },
                    {
                        id: generateId(),
                        type: 'contactform',
                        settings: {
                            submitText: 'Send Message',
                            successMessage: 'Thank you! We\'ll get back to you soon.',
                            fields: ['name', 'email', 'subject', 'message'],
                            submitBtnColor: 'hsl(var(--primary))',
                            submitBtnTextColor: '#ffffff'
                        }
                    }
                ]
            },
            {
                id: generateId(),
                type: 'column',
                settings: { padding: { left: '16', right: '16' } },
                children: [
                    {
                        id: generateId(),
                        type: 'heading',
                        settings: {
                            text: 'Get in Touch',
                            tag: 'h2',
                            fontSize: '28px',
                            fontWeight: '600',
                            margin: { bottom: '24' }
                        }
                    },
                    {
                        id: generateId(),
                        type: 'iconlist',
                        settings: {
                            items: [
                                { icon: 'Mail', text: 'hello@example.com' },
                                { icon: 'Phone', text: '+1 (555) 123-4567' },
                                { icon: 'MapPin', text: '123 Business Street, City' }
                            ],
                            iconColor: 'hsl(var(--primary))',
                            textColor: 'hsl(var(--foreground))',
                            gap: '16'
                        }
                    },
                    {
                        id: generateId(),
                        type: 'heading',
                        settings: {
                            text: 'Follow Us',
                            tag: 'h3',
                            fontSize: '20px',
                            fontWeight: '600',
                            margin: { top: '32', bottom: '16' }
                        }
                    },
                    {
                        id: generateId(),
                        type: 'sociallinks',
                        settings: {
                            links: [
                                { platform: 'facebook', url: '#' },
                                { platform: 'twitter', url: '#' },
                                { platform: 'instagram', url: '#' },
                                { platform: 'linkedin', url: '#' }
                            ],
                            iconColor: 'hsl(var(--muted-foreground))',
                            iconHoverColor: 'hsl(var(--primary))'
                        }
                    }
                ]
            }
        ]
    }]
})

export interface SectionTemplate {
    id: string;
    name: string;
    category: string;
    description: string;
    thumbnail: string;
    factory: () => TemplateNode;
}

/**
 * Export all templates with metadata for UI display
 */
export const sectionTemplates: SectionTemplate[] = [
    {
        id: 'janari-hero-gradient',
        name: 'Hero Gradient',
        category: 'hero',
        description: 'Gradient hero with badge, title, and CTA buttons',
        thumbnail: 'hero-gradient',
        factory: heroGradient
    },
    {
        id: 'janari-features-grid',
        name: 'Features Grid',
        category: 'features',
        description: '3-column feature cards with icons',
        thumbnail: 'features-grid',
        factory: featuresGrid
    },
    {
        id: 'janari-about-split',
        name: 'About Split',
        category: 'content',
        description: 'Two columns: text with stats and image',
        thumbnail: 'about-split',
        factory: aboutSplit
    },
    {
        id: 'janari-cta-dark',
        name: 'CTA Dark',
        category: 'cta',
        description: 'Dark CTA section with prominent button',
        thumbnail: 'cta-dark',
        factory: ctaDark
    },
    {
        id: 'janari-team-grid',
        name: 'Team Grid',
        category: 'team',
        description: '4-column team member showcase',
        thumbnail: 'team-grid',
        factory: teamGrid
    },
    {
        id: 'janari-blog-header',
        name: 'Blog Header',
        category: 'header',
        description: 'Gradient page header for blog',
        thumbnail: 'blog-header',
        factory: blogHeader
    },
    {
        id: 'janari-contact-section',
        name: 'Contact Section',
        category: 'content',
        description: 'Contact form with info and social links',
        thumbnail: 'contact-section',
        factory: contactSection
    }
]

export default sectionTemplates
