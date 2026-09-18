# Architecture Decision Records (ADRs)

Keputusan arsitektur resmi untuk **Jejakawan Core Engine**.  
Kebijakan: [`DOCUMENTATION.md`](../DOCUMENTATION.md) — nomor ADR **abadi**, jangan renumber.

## Core (`docs/adr/`)

| Nomor | Judul | Status | Scope |
| :--- | :--- | :--- | :--- |
| **[ADR-002](./ADR-002-customizer-page-isolation-and-site-branding.md)** | Customizer Page Isolation & Site Branding | Accepted | core |
| **[ADR-003](./ADR-003-customizer-canvas-proportional-zoom-and-mockup-frames.md)** | Customizer Canvas Proportional Zoom & Mockup Frames | Accepted | core |
| **[ADR-004](./ADR-004-floating-social-dock-and-customizer-controls.md)** | Floating Social Dock & Customizer Controls | Accepted | core |
| **[ADR-005](./ADR-005-brand-visual-styles-live-preview-sync-and-smart-poppers.md)** | Brand Visual Styles Live Preview Sync & Smart Poppers | Accepted | core |
| **[ADR-006](./ADR-006-master-layout-modes-and-homepage-sections.md)** | Master Layout Modes & Homepage Sections | Accepted | core |
| **[ADR-010](./ADR-010-builder-viewport-preview-scaling-and-adaptive-responsive-toolbar.md)** | Builder Viewport Preview Scaling & Adaptive Responsive Toolbar | Accepted | core |
| **[ADR-011](./ADR-011-visual-builder-modular-extension-and-license-gating.md)** | Visual Builder Modular Extension & License Gating | Accepted | core |
| **[ADR-012](./ADR-012-data-model-studio-modular-extension-and-license-gating.md)** | Data Model Studio Modular Extension & License Gating | Accepted | core |
| **[ADR-013](./ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)** | Console Sidebar Database Menu Preloading & Reactive Hydration | Accepted | core |
| **[ADR-014](./ADR-014-three-tier-identity-whitelabel-brand-and-smart-sync.md)** | 3-Tier Identity, White Label & Smart Brand Sync | Accepted | core |
| **[ADR-015](./ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)** | Favicon Isolation Prepaint Guards & Zero-Race Lifecycle | Accepted | core |
| **[ADR-016](./ADR-016-module-aware-site-identity-and-dependency-orchestration.md)** | Module-Aware Site Identity & Dependency Orchestration | Accepted | core |
| **[ADR-017](./ADR-017-console-appearance-dual-mode-workspace-and-license-gating.md)** | Console Appearance Dual-Mode Workspace & License Gating | Accepted | core |
| **[ADR-018](./ADR-018-theme-sarangenge-side-nav-presets-and-viewport-scroll-snap.md)** | Plugin SoC/SoT — Cinematic Nav, Floating Extensions & Slots | Accepted | core |
| **[ADR-019](./ADR-019-user-email-verification-and-privilege-lifecycle.md)** | User Email Verification & Privilege Lifecycle | Accepted | core |
| **[ADR-020](./ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)** | RBAC Hierarchy, Route Hardening & UI Primitives | Accepted | core |
| **[ADR-021](./ADR-021-capability-registry-auto-discovery-and-sso-scope-mapping.md)** | Capability Registry, Manifest & SSO Scope Mapping | Accepted | core |
| **[ADR-022](./ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md)** | Upstream Curation & Generic Theme Demo Seeders | Accepted | core |
| **[ADR-023](./ADR-023-first-party-themes-as-registry-packs-and-license-quotas.md)** | First-party themes as Registry packs + license quotas | Accepted | core |

## Theme-scoped / downstream (stub di folder ini)

| Nomor | Lokasi kanonis | Scope |
| :--- | :--- | :--- |
| **[ADR-001](./ADR-001-about-page-layung-refactor.md)** → | [`themes/layung/ADR-001-...`](../themes/layung/ADR-001-about-page-layung-refactor.md) | theme:layung |
| **[ADR-007](./ADR-007-hero-section-telemetry-animations-slider-and-customizer-isolation.md)** → | [`themes/layung/ADR-007-...`](../themes/layung/ADR-007-hero-section-telemetry-animations-slider-and-customizer-isolation.md) | theme:layung |
| **[ADR-008](./ADR-008-navigation-hierarchy-alignment-homepage-anchors-and-footer-refinement.md)** → | [`themes/layung/ADR-008-...`](../themes/layung/ADR-008-navigation-hierarchy-alignment-homepage-anchors-and-footer-refinement.md) | theme:layung |
| **[ADR-009](./ADR-009-interactive-bandwidth-simulator-and-k2net-package-mapping.md)** → | `k2net-portal/docs/adr/ADR-009-...` | downstream:k2net-portal |

Indeks tema: [`docs/themes/README.md`](../themes/README.md).
