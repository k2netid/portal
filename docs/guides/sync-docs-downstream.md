# How-to: Sync guides/reference to downstream

Mirror `docs/guides/` + `docs/reference/` + **`docs/architecture/`** + policy (`DOCUMENTATION` / `WORKFLOW` / `COMPLETENESS`) dari **ja-core_engine** ke portal downstream.  
SoT tetap di core — salinan downstream hanya untuk navigasi lokal agen.

## Prerequisites

- Repo sibling di `/home/jejakawan/dev/`: `k2net-portal`, `smkn6-portal`, `smkn1cijulang-portal`, `ja-cms`
- Sudah mengedit di **core** dulu

## Steps

```bash
cd /home/jejakawan/dev/ja-core_engine
npm run docs:sync-downstream
```

## Verify

```bash
ls ../k2net-portal/docs/architecture
ls ../smkn6-portal/docs/architecture
ls ../smkn1cijulang-portal/docs/architecture
ls ../ja-cms/docs/architecture
head -n 3 ../k2net-portal/docs/architecture/01-overview-and-tier-design.md
```

## Related

- Kebijakan: [DOCUMENTATION.md](../DOCUMENTATION.md)
- Completeness: [COMPLETENESS.md](../COMPLETENESS.md)
- Branching multi-repo: [branching.md](../branching.md)
