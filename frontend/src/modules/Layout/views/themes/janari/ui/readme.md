# Janari UI

Presentation components for the Janari theme only. They do not import `@/shared/components/ui` or console layouts.

## Usage

```ts
import { ThemeToggle, Button, DropdownMenu } from '@/modules/Layout/views/themes/janari/ui';
```

## Host dependencies inside UI

Some primitives call **host composables** (not console UI):

- `ThemeToggle` → `useDarkMode('frontend')` from `@/shared/composables/useDarkMode`

See [Theme Host Contract](../theme-host-contract.md).
