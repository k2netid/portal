# E2E Demo Pack

A Vue-based theme for Jejakawan.

## Installation

This theme is automatically loaded when placed in the themes directory.

## Structure

```
e2e-demo-pack/
├── assets/css/           # CSS variables and styles
├── components/           # Vue SFC components
├── composables/          # Vue composables (optional)
└── theme.json            # Theme configuration
```

## Customization

- `assets/css/variables.css` - CSS variables for theming
- `components/` - Vue components (Header, Footer, etc.)
- `theme.json` - Theme manifest and settings schema

## Settings

Configure theme settings in the admin panel under Themes > Settings.

## Development

Create Vue components in the `components/` directory and reference them
in the `theme.json` manifest. The theme system will dynamically load
them via the application's composables.