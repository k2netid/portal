import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';

import Alert from '@/shared/components/ui/Alert.vue';
import AlertTitle from '@/shared/components/ui/AlertTitle.vue';
import AlertDescription from '@/shared/components/ui/AlertDescription.vue';

import Card from '@/shared/components/ui/Card.vue';
import CardHeader from '@/shared/components/ui/CardHeader.vue';
import CardTitle from '@/shared/components/ui/CardTitle.vue';
import CardDescription from '@/shared/components/ui/CardDescription.vue';
import CardContent from '@/shared/components/ui/CardContent.vue';
import CardFooter from '@/shared/components/ui/CardFooter.vue';

import Table from '@/shared/components/ui/Table.vue';
import TableHeader from '@/shared/components/ui/TableHeader.vue';
import TableBody from '@/shared/components/ui/TableBody.vue';
import TableRow from '@/shared/components/ui/TableRow.vue';
import TableHead from '@/shared/components/ui/TableHead.vue';
import TableCell from '@/shared/components/ui/TableCell.vue';

describe('UI Atoms', () => {
    describe('Alert Suite', () => {
        it('renders Alert, AlertTitle, AlertDescription with slots', () => {
            const wrapper = mount(Alert, {
                props: { variant: 'destructive' },
                slots: {
                    default: '<div class="inner">Danger message</div>',
                },
            });
            expect(wrapper.attributes('role')).toBe('alert');
            expect(wrapper.classes()).toContain('border-destructive/50');
            expect(wrapper.text()).toContain('Danger message');

            const title = mount(AlertTitle, { slots: { default: 'Alert Heading' } });
            expect(title.text()).toBe('Alert Heading');

            const desc = mount(AlertDescription, { slots: { default: 'Details here' } });
            expect(desc.text()).toBe('Details here');
        });
    });

    describe('Card Suite', () => {
        it('renders complete Card hierarchy', () => {
            const card = mount(Card, {
                props: { size: 'sm' },
                slots: { default: 'Card body' },
            });
            expect(card.attributes('data-slot')).toBe('card');
            expect(card.attributes('data-size')).toBe('sm');
            expect(card.text()).toBe('Card body');

            const header = mount(CardHeader, { slots: { default: 'Header' } });
            expect(header.text()).toBe('Header');

            const title = mount(CardTitle, { slots: { default: 'Title' } });
            expect(title.text()).toBe('Title');

            const desc = mount(CardDescription, { slots: { default: 'Description' } });
            expect(desc.text()).toBe('Description');

            const content = mount(CardContent, { slots: { default: 'Content' } });
            expect(content.text()).toBe('Content');

            const footer = mount(CardFooter, { slots: { default: 'Footer' } });
            expect(footer.text()).toBe('Footer');
        });
    });

    describe('Table Suite', () => {
        it('renders Table with header, body, row, head, and cell', () => {
            const table = mount(Table, { slots: { default: 'table content' } });
            expect(table.find('table').exists()).toBe(true);

            const header = mount(TableHeader, { slots: { default: 'th' } });
            expect(header.find('thead').exists()).toBe(true);

            const body = mount(TableBody, { slots: { default: 'tb' } });
            expect(body.find('tbody').exists()).toBe(true);

            const row = mount(TableRow, { slots: { default: 'tr' } });
            expect(row.find('tr').exists()).toBe(true);

            const head = mount(TableHead, { slots: { default: 'th content' } });
            expect(head.find('th').exists()).toBe(true);

            const cell = mount(TableCell, { slots: { default: 'cell text' } });
            expect(cell.find('td').exists()).toBe(true);
            expect(cell.text()).toBe('cell text');
        });
    });

    describe('Tabs Suite', () => {
        it('renders Tabs, TabsList, TabsTrigger, and TabsContent', async () => {
            const Tabs = (await import('@/shared/components/ui/Tabs.vue')).default;
            const TabsList = (await import('@/shared/components/ui/TabsList.vue')).default;
            const TabsTrigger = (await import('@/shared/components/ui/TabsTrigger.vue')).default;
            const TabsContent = (await import('@/shared/components/ui/TabsContent.vue')).default;

            const wrapper = mount(Tabs, {
                props: { defaultValue: 'tab1' },
                slots: {
                    default: `
                        <TabsList>
                            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
                        </TabsList>
                        <TabsContent value="tab1">Content 1</TabsContent>
                    `,
                },
                global: {
                    components: { TabsList, TabsTrigger, TabsContent },
                },
            });

            expect(wrapper.exists()).toBe(true);
            expect(wrapper.text()).toContain('Tab 1');
            expect(wrapper.text()).toContain('Content 1');
        });
    });
});
