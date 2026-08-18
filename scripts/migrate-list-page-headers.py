#!/usr/bin/env python3
from __future__ import annotations
import re
from pathlib import Path

ROOT = Path('/opt/ja-control-plane/frontend/src/modules')
FILES = sorted(ROOT.rglob('Index.vue'))
IMPORT = "import { PageHeader } from '@/shared/components/shell';\n"
END_MARKERS = re.compile(
    r'\n\n    (?:<!-- (?!Header)|<Card|<div class="(?:grid|w-full|flex-1|flex gap)|<MediaStats|<FileSidebar|<TooltipProvider|<div class="file-manager)',
)
MANUAL: dict[str, tuple[str, str]] = {}

def i18n_fn(text: str) -> str:
    head = text[:5000]
    return '$t' if head.count('$t(') >= head.count("t('") else 't'

def add_import(text: str) -> str:
    if "from '@/shared/components/shell'" in text:
        return text
    if '<script setup' in text:
        return text.replace('<script setup lang="ts">', '<script setup lang="ts">\n' + IMPORT, 1)
    return IMPORT + text

def apply_root(text: str) -> str:
    text = text.replace('container mx-auto p-6 space-y-8', 'space-y-6')
    text = text.replace('class="container mx-auto p-6"', 'class="space-y-6"')
    if re.search(r'<template>\n  <div>(?!\s+class)', text):
        text = re.sub(r'<template>\n  <div>', '<template>\n  <div class="space-y-6">', text, count=1)
    return text

def extract_header(text: str):
    markers = ['<!-- Header Section -->', '<!-- Header -->', '<div class="mb-10 flex', '<div class="mb-6 flex', '<div class="mb-6">', '<div class="mb-4 shrink-0">', '<div class="flex flex-col sm:flex-row justify-between', '<div class="flex flex-wrap items-center justify-between']
    starts = [text.find(m) for m in markers if 0 <= text.find(m) < 3500]
    if not starts:
        return None
    start = min(starts)
    chunk = text[start:]
    em = END_MARKERS.search(chunk)
    if not em:
        return None
    return chunk[:em.start()], start

def parse_title_subtitle(header: str, fn: str):
    if 'trans.title' in header:
        sub = '\n      :subtitle="trans.subtitle"' if 'trans.subtitle' in header else ''
        return ':title="trans.title"', sub, True
    if 'contentType' in header:
        return None
    h1 = re.search(r"\{\{\s*\$?t\(['\"]([^'\"]+)['\"]\)\s*\}\}", header)
    if not h1:
        return None
    title_expr = f':title="{fn}(\'{h1.group(1)}\')"'
    subm = re.search(r"</h1>[\s\S]*?<p[^>]*>\s*\{\{\s*\$?t\(['\"]([^'\"]+)['\"]\)", header)
    sub_expr = f'\n      :subtitle="{fn}(\'{subm.group(1)}\')"' if subm else ''
    return title_expr, sub_expr, False

def extract_actions(header: str):
    patterns = [
        r'</div>\s*\n\s*(<div class="flex[^"]*"[^>]*>[\s\S]*?</div>)\s*\n\s*</div>\s*$',
        r'</p>\s*\n\s*</div>\s*\n\s*(<div[^>]*>[\s\S]*?</div>)\s*$',
        r'</h1>\s*\n\s*((?:<router-link|<Button)[\s\S]*?)\s*\n\s*</div>\s*$',
    ]
    for pat in patterns:
        m = re.search(pat, header)
        if m:
            block = m.group(1).strip()
            if not block.startswith('<div'):
                block = f'<div class="flex items-center gap-2">{block}</div>'
            block = block.replace('class="gap-2 rounded-xl"', 'size="sm" class="gap-2"')
            block = re.sub(r'<Button>', '<Button size="sm">', block)
            return block
    return None

def make_page_header(header: str, text: str, borderless: bool = False):
    parsed = parse_title_subtitle(header, i18n_fn(text))
    if not parsed:
        return None
    title_expr, sub_expr, is_trans = parsed
    bl = '\n      borderless' if borderless else ''
    actions = extract_actions(header)
    if is_trans:
        base = f'    <PageHeader\n      :title="trans.title"\n      :subtitle="trans.subtitle"\n      borderless\n    >'
    else:
        base = f'    <PageHeader\n      {title_expr}{sub_expr}{bl}\n    >'
    if actions:
        indented = '\n        '.join(actions.split('\n'))
        return base[:-4] + f'\n      <template #actions>\n        {indented}\n      </template>\n    </PageHeader>'
    return base if base.endswith('PageHeader>') else base + '\n    </PageHeader>'

def migrate_file(path: Path) -> str:
    text = path.read_text()
    if 'PageHeader' in text:
        return 'skip'
    key = str(path)
    if key in MANUAL:
        old, new = MANUAL[key]
        if old not in text:
            return 'manual-miss'
        text = text.replace(old, new, 1)
        path.write_text(apply_root(add_import(text)))
        return 'manual-ok'
    extracted = extract_header(text)
    if not extracted:
        return 'no-header'
    header_block, start = extracted
    new_header = make_page_header(header_block, text)
    if not new_header:
        return 'parse-fail'
    text = text[:start] + new_header + text[start + len(header_block):]
    path.write_text(apply_root(add_import(text)))
    return 'ok'

ok, skip, fail = [], [], []
for path in FILES:
    if 'Operational/Member' in str(path):
        continue
    status = migrate_file(path)
    if status in ('ok', 'manual-ok'):
        ok.append(path)
    elif status == 'skip':
        skip.append(path)
    else:
        fail.append((path, status))
print(f'OK {len(ok)} SKIP {len(skip)} FAIL {len(fail)}')
for p, s in fail:
    print(f'  {p} -> {s}')
