import { defineAsyncComponent } from 'vue';

// Basic UI (Keep synchronous - used immediately on most pages)
export { default as Button } from './Button.vue';
export { default as Input } from './Input.vue';
export { default as Checkbox } from './Checkbox.vue';
export { default as Switch } from './Switch.vue';
export { default as Textarea } from './Textarea.vue';
export { default as Label } from './Label.vue';
export { default as Badge } from './Badge.vue';
export { default as Spinner } from './Spinner.vue';
export { default as LazyImage } from './LazyImage.vue';
// Keep this async to avoid eagerly loading the whole lucide map.
export const LucideIcon = defineAsyncComponent(() => import('./LucideIcon.vue'));

// Cards (Keep synchronous - used on many pages)
export { default as Card } from './Card.vue';
export { default as CardHeader } from './CardHeader.vue';
export { default as CardTitle } from './CardTitle.vue';
export { default as CardDescription } from './CardDescription.vue';
export { default as CardContent } from './CardContent.vue';
export { default as CardFooter } from './CardFooter.vue';

// Dialogs & Modals - Core dialog components sync (wrappers), content async
export { default as FilePreviewModal } from './FilePreviewModal.vue';
export { default as Dialog } from './Dialog.vue';
export { default as DialogContent } from './DialogContent.vue';
export { default as DialogHeader } from './DialogHeader.vue';
export { default as DialogTitle } from './DialogTitle.vue';
export { default as DialogDescription } from './DialogDescription.vue';
export { default as DialogFooter } from './DialogFooter.vue';
// Heavy modals - async loaded when needed
export const ConfirmModal = defineAsyncComponent(() => import('./ConfirmModal.vue'));
export const GlobalErrorModal = defineAsyncComponent(() => import('./GlobalErrorModal.vue'));
export const SessionTimeoutModal = defineAsyncComponent(() => import('./SessionTimeoutModal.vue'));

// Context Menu (Async - used on right-click only)
export const ContextMenu = defineAsyncComponent(() => import('./ContextMenu.vue'));
export const ContextMenuTrigger = defineAsyncComponent(() => import('./ContextMenuTrigger.vue'));
export const ContextMenuContent = defineAsyncComponent(() => import('./ContextMenuContent.vue'));
export const ContextMenuItem = defineAsyncComponent(() => import('./ContextMenuItem.vue'));
export const ContextMenuSeparator = defineAsyncComponent(() => import('./ContextMenuSeparator.vue'));

// Tabs (Keep sync - commonly used)
export { default as Tabs } from './Tabs.vue';
export { default as TabsList } from './TabsList.vue';
export { default as TabsTrigger } from './TabsTrigger.vue';
export { default as TabsContent } from './TabsContent.vue';

// Accordion (Async - not used on every page)
export const Accordion = defineAsyncComponent(() => import('./Accordion.vue'));
export const AccordionItem = defineAsyncComponent(() => import('./AccordionItem.vue'));
export const AccordionTrigger = defineAsyncComponent(() => import('./AccordionTrigger.vue'));
export const AccordionContent = defineAsyncComponent(() => import('./AccordionContent.vue'));

// Select (Keep sync - common form element)
export { default as Select } from './Select.vue';
export { default as SelectTrigger } from './SelectTrigger.vue';
export { default as SelectValue } from './SelectValue.vue';
export { default as SelectContent } from './SelectContent.vue';
export { default as SelectItem } from './SelectItem.vue';

// Popover (Keep sync - used in many places)
export { default as Popover } from './Popover.vue';
export { default as PopoverTrigger } from './PopoverTrigger.vue';
export { default as PopoverContent } from './PopoverContent.vue';

// Tooltip (Keep sync - used everywhere)
export { default as Tooltip } from './Tooltip.vue';
export { default as TooltipProvider } from './TooltipProvider.vue';
export { default as TooltipTrigger } from './TooltipTrigger.vue';
export { default as TooltipContent } from './TooltipContent.vue';

// Table (Keep sync - used on list pages)
export { default as Table } from './Table.vue';
export { default as TableHeader } from './TableHeader.vue';
export { default as TableRow } from './TableRow.vue';
export { default as TableHead } from './TableHead.vue';
export { default as TableBody } from './TableBody.vue';
export { default as TableCell } from './TableCell.vue';
export { default as DataTable } from './DataTable.vue';


// Misc
export { default as Avatar } from './Avatar.vue';
export { default as AvatarImage } from './AvatarImage.vue';
export { default as AvatarFallback } from './AvatarFallback.vue';
export { default as Alert } from './Alert.vue';
export { default as AlertTitle } from './AlertTitle.vue';
export { default as AlertDescription } from './AlertDescription.vue';
export { default as BackToTop } from './BackToTop.vue';
export { default as Toast } from './Toast.vue';
export { default as Separator } from './Separator.vue';

// Heavy UI (Always Async)
export const Pagination = defineAsyncComponent(() => import('./Pagination.vue'));
export const ColorPicker = defineAsyncComponent(() => import('./ColorPicker.vue'));
export const IconPicker = defineAsyncComponent(() => import('./IconPicker.vue'));

// Dropdown Menu (shadcn-vue)
export {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from './dropdown-menu';
