import { describe, expect, it, beforeEach } from 'vitest';
import { defineComponent, h } from 'vue';
import {
  getAppBlocksForSlot,
  registerAppBlock,
  resetAppBlocksRegistry,
  unregisterAppBlocksForPlugin,
} from '@/engine/plugins/pluginRegistry';

const Stub = defineComponent({
  render: () => h('span', 'stub'),
});

describe('pluginRegistry', () => {
  beforeEach(() => {
    resetAppBlocksRegistry();
  });

  it('registers blocks by priority', () => {
    registerAppBlock('after_header', { pluginSlug: 'b', component: Stub, priority: 20 });
    registerAppBlock('after_header', { pluginSlug: 'a', component: Stub, priority: 5 });
    const blocks = getAppBlocksForSlot('after_header');
    expect(blocks.map((b) => b.pluginSlug)).toEqual(['a', 'b']);
  });

  it('unregisters all blocks for a plugin', () => {
    registerAppBlock('after_header', { pluginSlug: 'demo', component: Stub });
    registerAppBlock('before_footer', { pluginSlug: 'demo', component: Stub });
    unregisterAppBlocksForPlugin('demo');
    expect(getAppBlocksForSlot('after_header')).toHaveLength(0);
    expect(getAppBlocksForSlot('before_footer')).toHaveLength(0);
  });
});
