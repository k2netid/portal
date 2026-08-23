import CustomLinkItem from './CustomLinkItem';
import PageItem from './PageItem';
import PostItem from './PostItem';
import CategoryItem from './CategoryItem';
import ColumnGroupItem from './ColumnGroupItem';
import type { MenuItemDefinition } from '@/modules/Layout/types/menu';

const definitions: MenuItemDefinition[] = [
    CustomLinkItem,
    PageItem,
    PostItem,
    CategoryItem,
    ColumnGroupItem
];

export default definitions;
