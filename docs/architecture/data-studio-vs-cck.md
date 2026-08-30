# Data Studio vs CMS CCK vs media

**Status:** P4 decision — 2026-08-27  
**Audience:** agents and humans adding fields, entities, or vertical modules.

Three similar-looking “custom field” surfaces exist. They are **not** interchangeable.

| Layer | What it is | Tables / UI | Use for |
| :--- | :--- | :--- | :--- |
| **Data Studio** | Kernel operational entity builder | `sys_content_types` + `sys_dynamic_records` · console Data Models | Operator-defined records (inventory, directory, CRM-ish rows). Not editorial pages. |
| **Library CCK** | CMS field kit on Publishing content | `lib_fields` + field groups · Library → Custom Fields | Extra columns on posts/pages. Lives only while pack **library** is product-active. |
| **Media** | File library | Media pack | Binaries and captions. Not a document-management product and not Data Studio. |

## Rules

1. **Do not model blog posts, pages, categories, or members as Data Studio types.** Publishing, Library, and Member already own those.
2. Data Studio slugs **cannot** collide with CMS/kernel names (`post`/`posts`, `page`/`pages`, `content`/`contents`, `category`/`categories`, `tag`/`tags`, `media`, `comment`/`comments`, `member`/`members`, `user`/`users`, `form`/`forms`, `mail`, `newsletter`, `site`/`sites`). API returns `DATA_MODEL_SLUG_RESERVED`. Existing reserved rows are renamed once (`posts` → `posts_studio`, collision suffix `_2`) by `ContentType::grandfatherReservedSlugs()` (migration `2026_08_28_000001_grandfather_data_studio_reserved_slugs`).
3. Library custom fields must not import Data Studio schemas. Content remains `pub_contents` + `lib_fields`.
4. Vertical products (P5) add **modules**, or compose Data Studio types for *their* operational records. They do not fork CCK into Core.
5. The PHP class `ContentType` is a historical name for a Data Studio schema. Treat it as a data model, not a CMS content type.

## Why both exist

Data Studio is a **kernel** capability (downstream apps need ad-hoc entities without a CMS). Library CCK is a **CMS pack** concern (editors extending articles). Merging them would drag Publishing into the kernel and break registry on/off.

## Related

- Data Studio UI: `frontend/src/modules/Core/Infra/views/models/`
- Library fields: `frontend/src/modules/Library/views/custom-fields/`
- Reserved slug guard: `Modules\Core\System\Models\ContentType::RESERVED_SLUGS`
