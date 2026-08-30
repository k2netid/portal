import { menuItemRegistry } from './MenuItemRegistry';
import itemDefinitions from './types';

// Register all item types
menuItemRegistry.registerAll(itemDefinitions);

export { menuItemRegistry };
