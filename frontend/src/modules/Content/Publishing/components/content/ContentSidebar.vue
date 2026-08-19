<template>
  <div class="space-y-4">
    <!-- Actions (Mobile/Tablet only) -->
    <div class="lg:hidden flex items-center gap-2 mb-4 px-1">
      <slot name="actions" />
    </div>

    <!-- Main Sidebar Container - Unified Clean Look -->
    <div class="bg-card border border-border shadow-sm rounded-xl overflow-hidden flex flex-col">
      <!-- Publish Status Group -->
      <div class="sidebar-section">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.publishing = !sections.publishing"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-success/10 text-success">
              <FileCheck class="w-3.5 h-3.5" />
            </div>
            <span class="text-sm font-semibold text-foreground">{{ $t('publishing.content.form.publishing') }}</span>
          </div>
          <ChevronDown 
            class="w-4 h-4 text-muted-foreground transition-transform duration-200"
            :class="{ 'rotate-180': sections.publishing }"
          />
        </button>
        <div
          v-show="sections.publishing"
          class="border-t border-border/5 p-5 space-y-5"
        >
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.status') }}</Label>
            <Select
              :model-value="modelValue.status"
              @update:model-value="(val: string) => updateField('status', val)"
            >
              <SelectTrigger class="w-full h-9" :aria-label="t('publishing.content.form.status')">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="draft">
                  {{ $t('publishing.content.status.draft') }}
                </SelectItem>
                <SelectItem value="published">
                  {{ $t('publishing.content.status.published') }}
                </SelectItem>
                <SelectItem value="archived">
                  {{ $t('publishing.content.status.archived') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div
            v-if="modelValue.status === 'published'"
            class="space-y-1.5 animate-in fade-in slide-in-from-top-1"
          >
            <div class="flex items-center justify-between gap-2">
              <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.publishDate') }}</Label>
              <Button
                variant="link"
                size="sm"
                class="h-auto p-0 text-[11px]"
                @click="setPublishNow"
              >
                Publish now
              </Button>
            </div>
            <Input
              :model-value="modelValue.published_at"
              type="datetime-local"
              class="text-xs h-9"
              @update:model-value="(val) => updateField('published_at', val as string)"
            />
          </div>

          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.slug') }}</Label>
            <div class="flex items-center gap-1 group">
              <span class="text-[10px] text-muted-foreground font-mono select-none px-1">/</span>
              <Input
                :model-value="modelValue.slug"
                class="text-xs font-mono h-9"
                :placeholder="$t('publishing.content.form.slugPlaceholder')"
                @update:model-value="(val) => updateField('slug', val as string)"
              />
            </div>
          </div>

          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.type') }}</Label>
            <Select
              :model-value="modelValue.type"
              @update:model-value="(val: string) => updateField('type', val)"
            >
              <SelectTrigger class="w-full h-9 capitalize" :aria-label="t('publishing.content.form.type')">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="post">
                  Post
                </SelectItem>
                <SelectItem value="page">
                  Page
                </SelectItem>
                <SelectItem value="custom">
                  Custom
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Page Template Selector (for Pages) -->
          <div v-if="modelValue.type === 'page'" class="space-y-1.5 animate-in fade-in slide-in-from-top-1">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.template') }}</Label>
            <Select
              :model-value="(modelValue.meta?.template as string) || 'default'"
              @update:model-value="(val: string) => updateMetaField('template', val)"
            >
              <SelectTrigger class="w-full h-9" :aria-label="t('publishing.content.form.template')">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="default">
                  {{ $t('publishing.content.template.default') }}
                </SelectItem>
                <SelectItem value="full_width">
                  {{ $t('publishing.content.template.fullWidth') }}
                </SelectItem>
                <SelectItem value="blank_canvas">
                  {{ $t('publishing.content.template.blankCanvas') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p class="text-[10px] text-muted-foreground">
              {{ modelValue.meta?.template === 'blank_canvas' ? $t('publishing.content.template.blankCanvasDesc') : (modelValue.meta?.template === 'full_width' ? $t('publishing.content.template.fullWidthDesc') : $t('publishing.content.template.defaultDesc')) }}
            </p>
          </div>

          <div class="flex items-center justify-between border border-border/40 rounded-lg p-3 bg-muted/20">
            <div class="space-y-0.5">
              <Label class="text-xs font-medium leading-none">{{ $t('publishing.content.form.featured') }}</Label>
              <p class="text-[10px] text-muted-foreground leading-tight">
                {{ $t('publishing.content.form.featuredDesc') }}
              </p>
            </div>
            <Switch
              :checked="!!modelValue.is_featured"
              :aria-label="t('publishing.content.form.featured')"
              @update:checked="(val) => updateField('is_featured', val)"
            />
          </div>
        </div>
      </div>

      <!-- Menu Settings Group -->
      <div class="sidebar-section border-t border-border/10">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.menu = !sections.menu"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-primary/10 text-primary">
              <MenuSquare class="w-3.5 h-3.5" />
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-foreground">{{ t('publishing.content.form.sidebar.addToMenu') }}</span>
                <Badge
                  v-if="modelValue.menu_item?.add_to_menu"
                  variant="secondary"
                  class="h-5 text-[10px] px-1.5 bg-primary/10 text-primary border-primary/20"
                >
                  {{ t('common.status.enabled') }}
                </Badge>
              </div>
            </div>
            <ChevronDown 
              class="w-4 h-4 text-muted-foreground transition-transform duration-200"
              :class="{ 'rotate-180': sections.menu }"
            />
          </div>
        </button>
        <div
          v-show="sections.menu"
          class="border-t border-border/5 p-5 space-y-5"
        >
          <div class="flex items-center justify-between border border-border/40 rounded-lg p-3 bg-muted/20">
            <div class="space-y-0.5">
              <Label class="text-xs font-medium leading-none">{{ $t('layout.menus.actions.addToMenu') }}</Label>
              <p class="text-[10px] text-muted-foreground leading-tight">
                {{ $t('publishing.content.form.sidebar.addToMenuDesc') }}
              </p>
            </div>
            <Switch
              :checked="!!modelValue.menu_item?.add_to_menu"
              :aria-label="t('layout.menus.actions.addToMenu')"
              @update:checked="(val) => updateMenuField('add_to_menu', val)"
            />
          </div>

          <div
            v-if="modelValue.menu_item?.add_to_menu"
            class="space-y-4 animate-in fade-in slide-in-from-top-1"
          >
            <div class="space-y-1.5">
              <Label class="text-xs font-medium text-muted-foreground">{{ $t('layout.menus.form.selectMenu') }}</Label>
              <Select
                :model-value="modelValue.menu_item.menu_id ? modelValue.menu_item.menu_id.toString() : ''"
                @update:model-value="handleMenuChange"
              >
                <SelectTrigger class="w-full h-9" :aria-label="t('layout.menus.form.selectMenu')">
                  <SelectValue :placeholder="$t('layout.menus.form.selectMenu')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="menu in menus"
                    :key="menu.id"
                    :value="menu.id.toString()"
                  >
                    {{ menu.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label class="text-xs font-medium text-muted-foreground">{{ $t('layout.menus.form.parentItem') }}</Label>
              <Select
                :model-value="modelValue.menu_item.parent_id ? modelValue.menu_item.parent_id.toString() : 'root'"
                :disabled="loadingParentItems"
                @update:model-value="(val: string) => updateMenuField('parent_id', val === 'root' ? null : val)"
              >
                <SelectTrigger class="w-full h-9" :aria-label="t('layout.menus.form.parentItem')">
                  <SelectValue :placeholder="loadingParentItems ? $t('common.messages.loading.default') : $t('layout.menus.form.rootItem')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="root">
                    {{ $t('layout.menus.form.rootItem') }}
                  </SelectItem>
                  <SelectItem
                    v-for="item in menuParentItems"
                    :key="item.id!"
                    :value="item.id!.toString()"
                  >
                    {{ '&nbsp;'.repeat((item.depth || 0) * 2) + (item.title || item.label) }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label class="text-xs font-medium text-muted-foreground">{{ $t('layout.menus.form.label') }}</Label>
              <Input
                :model-value="modelValue.menu_item.title"
                class="text-xs h-9"
                :placeholder="$t('layout.menus.form.labelPlaceholder')"
                @update:model-value="(val) => updateMenuField('title', val as string)"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Taxonomy Group -->
      <div class="sidebar-section border-t border-border/10">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.taxonomy = !sections.taxonomy"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-info/10 text-info">
              <Tags class="w-3.5 h-3.5" />
            </div>
            <span class="text-sm font-semibold text-foreground">{{ $t('publishing.content.form.taxonomy') }}</span>
          </div>
          <ChevronDown 
            class="w-4 h-4 text-muted-foreground transition-transform duration-200"
            :class="{ 'rotate-180': sections.taxonomy }"
          />
        </button>
        <div
          v-show="sections.taxonomy"
          class="border-t border-border/5 p-5 space-y-5"
        >
          <!-- Category -->
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.category') }}</Label>
            <Select
              :model-value="modelValue.category_id ? modelValue.category_id.toString() : ''"
              @update:model-value="(val: string) => updateField('category_id', val ? val : null)"
            >
              <SelectTrigger class="w-full h-9" :aria-label="t('publishing.content.form.category')">
                <SelectValue :placeholder="$t('publishing.content.form.selectCategory')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id.toString()"
                >
                  {{ category.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Tags -->
          <div class="space-y-2">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.tags') }}</Label>
            <div class="flex flex-wrap gap-1.5 mb-2">
              <Badge
                v-for="tag in selectedTags"
                :key="tag.id || tag.name"
                variant="secondary"
                class="pl-2 pr-1 py-0.5 gap-1 text-[10px] font-medium bg-muted/50 border-border/20"
              >
                {{ tag.name }}
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-3 w-3 p-0 hover:text-destructive"
                  @click="removeTag(tag.id || tag.name)"
                >
                  <X class="w-2.5 h-2.5" />
                </Button>
              </Badge>
            </div>
            <!-- Combobox-style tag input -->
            <div class="relative">
              <Input 
                :model-value="tagInput"
                class="w-full text-xs h-9"
                :placeholder="$t('publishing.content.form.tagInputPlaceholder')"
                @input="handleTagInput"
                @keydown.enter.prevent="handleTagEnter"
                @focus="showTagSuggestions = true"
                @blur="handleTagBlur"
              />
              <!-- Suggestions dropdown -->
              <div 
                v-if="showTagSuggestions && filteredTags.length > 0"
                class="absolute z-50 w-full mt-1 bg-background border border-border rounded-md shadow-lg max-h-[200px] overflow-y-auto"
              >
                <div
                  v-for="tag in filteredTags"
                  :key="tag.id || tag.name"
                  class="px-3 py-2 text-xs cursor-pointer hover:bg-accent hover:text-accent-foreground transition-colors"
                  @mousedown.prevent="selectTag(tag)"
                >
                  {{ tag.name }}
                </div>
              </div>
            </div>
            <p class="text-[10px] text-muted-foreground italic">
              {{ $t('publishing.content.form.tagInputHint') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Featured Image Group -->
      <div class="sidebar-section border-t border-border/10">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.image = !sections.image"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-primary/10 text-primary">
              <ImageIcon class="w-3.5 h-3.5" />
            </div>
            <span class="text-sm font-semibold text-foreground">{{ $t('publishing.content.form.featuredImage') }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div
              v-if="modelValue.featured_image"
              class="w-6 h-6 rounded overflow-hidden border border-border/40"
            >
              <img
                :src="modelValue.featured_image"
                class="w-full h-full object-cover"
              >
            </div>
            <ChevronDown 
              class="w-4 h-4 text-muted-foreground transition-transform duration-200"
              :class="{ 'rotate-180': sections.image }"
            />
          </div>
        </button>
        <div
          v-show="sections.image"
          class="border-t border-border/5 p-5 space-y-4"
        >
          <FeaturedImage
            :model-value="modelValue.featured_image || null"
            @update:model-value="(val: string | null) => updateField('featured_image', val)"
          />
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ t('publishing.content.featuredImage.titleLabel') }}</Label>
            <Input
              :model-value="modelValue.featured_image_title || ''"
              class="text-xs h-9"
              :placeholder="t('publishing.content.featuredImage.titlePlaceholder')"
              @update:model-value="(val) => updateField('featured_image_title', val as string)"
            />
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ t('publishing.content.featuredImage.descriptionLabel') }}</Label>
            <Textarea
              :model-value="modelValue.featured_image_caption || ''"
              rows="3"
              class="resize-none text-xs bg-muted/10"
              :placeholder="t('publishing.content.featuredImage.descriptionPlaceholder')"
              @update:model-value="(val) => updateField('featured_image_caption', val as string)"
            />
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">Image position</Label>
            <Select
              :model-value="modelValue.featured_image_position || 'hero'"
              @update:model-value="(val: string) => updateField('featured_image_position', val)"
            >
              <SelectTrigger class="w-full h-9" aria-label="Image position">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="hero">
                  {{ t('publishing.content.featuredImage.positionHero') }}
                </SelectItem>
                <SelectItem value="inline-top">
                  Inline Top
                </SelectItem>
                <SelectItem value="full-bleed">
                  Full Bleed
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <!-- Excerpt Group -->
      <div class="sidebar-section border-t border-border/10">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.excerpt = !sections.excerpt"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-warning/10 text-warning">
              <FileText class="w-3.5 h-3.5" />
            </div>
            <span class="text-sm font-semibold text-foreground">{{ $t('publishing.content.form.excerpt') }}</span>
          </div>
          <ChevronDown 
            class="w-4 h-4 text-muted-foreground transition-transform duration-200"
            :class="{ 'rotate-180': sections.excerpt }"
          />
        </button>
        <div
          v-show="sections.excerpt"
          class="border-t border-border/5 p-5"
        >
          <div class="space-y-1.5 mb-3">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.intro') }}</Label>
            <div class="rounded-md border border-border/50 overflow-hidden bg-background">
              <MarkdownEditor
                :model-value="modelValue.intro || ''"
                @update:model-value="handleIntroUpdate"
              />
            </div>
            <div class="flex items-center justify-between text-[11px]">
              <span class="text-muted-foreground">
                {{ introWordCount }} words
              </span>
              <span :class="isIntroAtLimit ? 'text-warning font-semibold' : 'text-muted-foreground'">
                {{ introCharCount }}/{{ introMaxChars }} chars
              </span>
            </div>
            <p
              v-if="isIntroAtLimit"
              class="text-[11px] text-warning"
            >
              Batas maksimal intro tercapai. Karakter tambahan tidak akan disimpan.
            </p>
          </div>
          <Textarea
            :model-value="modelValue.excerpt"
            rows="4"
            class="resize-none text-sm bg-muted/10"
            :placeholder="$t('publishing.content.form.excerptPlaceholder')"
            @update:model-value="(val) => updateField('excerpt', val as string)"
          />
        </div>
      </div>

      <!-- Discussion Settings Group -->
      <div class="sidebar-section border-t border-border/10">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.discussion = !sections.discussion"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-warning/10 text-warning">
              <MessageSquare class="w-3.5 h-3.5" />
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-foreground">{{ t('publishing.content.form.sidebar.comments') }}</span>
                <Badge
                  v-if="!modelValue.comment_status"
                  variant="secondary"
                  class="h-5 text-[10px] px-1.5 bg-warning/10 text-warning border-warning/20"
                >
                  {{ t('common.status.disabled') }}
                </Badge>
              </div>
            </div>
            <ChevronDown 
              class="w-4 h-4 text-muted-foreground transition-transform duration-200"
              :class="{ 'rotate-180': sections.discussion }"
            />
          </div>
        </button>
        <div
          v-show="sections.discussion"
          class="border-t border-border/5 p-5"
        >
          <div class="flex items-center justify-between border border-border/40 rounded-lg p-3 bg-muted/20">
            <div class="space-y-0.5">
              <Label class="text-xs font-medium leading-none">{{ $t('publishing.content.form.allowComments') }}</Label>
              <p class="text-[10px] text-muted-foreground leading-tight">
                {{ $t('publishing.content.form.allowCommentsDesc') }}
              </p>
            </div>
            <Switch
              :checked="!!modelValue.comment_status"
              :aria-label="t('publishing.content.form.allowComments')"
              @update:checked="(val) => updateField('comment_status', val)"
            />
          </div>
        </div>
      </div>

      <!-- SEO Settings Group -->
      <div class="sidebar-section border-t border-border/10">
        <button 
          type="button"
          class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
          @click="sections.seo = !sections.seo"
        >
          <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-md bg-destructive/10 text-destructive">
              <Search class="w-3.5 h-3.5" />
            </div>
            <span class="text-sm font-semibold text-foreground">{{ $t('publishing.content.form.seoSettings') }}</span>
          </div>
          <ChevronDown 
            class="w-4 h-4 text-muted-foreground transition-transform duration-200"
            :class="{ 'rotate-180': sections.seo }"
          />
        </button>
        <div
          v-show="sections.seo"
          class="border-t border-border/5 p-5 space-y-5"
        >
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.seo.metaTitle') }}</Label>
            <Input
              :model-value="modelValue.meta_title"
              class="text-xs h-9"
              @update:model-value="(val) => updateField('meta_title', val as string)"
            />
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.seo.metaDescription') }}</Label>
            <Textarea
              :model-value="modelValue.meta_description"
              rows="3"
              class="resize-none text-xs bg-muted/10"
              @update:model-value="(val) => updateField('meta_description', val as string)"
            />
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.metaKeywords') }}</Label>
            <Textarea
              :model-value="modelValue.meta_keywords"
              rows="2"
              class="resize-none text-xs bg-muted/10"
              :placeholder="$t('publishing.content.form.keywordsPlaceholder')"
              @update:model-value="(val) => updateField('meta_keywords', val as string)"
            />
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs font-medium text-muted-foreground">{{ $t('publishing.content.form.ogImage') }}</Label>
            <MediaPicker
              :label="$t('publishing.content.form.selectOgImage')"
              :constraints="{
                allowedExtensions: settings.allowed_image_types ? String(settings.allowed_image_types).split(',').map(s => s.trim()) : ['jpg', 'jpeg', 'png', 'webp'],
                minWidth: 1200,
                minHeight: 630
              }"
              @selected="(media: { url?: string } | string | null) => updateField('og_image', (typeof media === 'object' ? media?.url : media) || null)"
            >
              <template #trigger="{ open }">
                <div
                  v-if="modelValue.og_image"
                  class="relative group aspect-video w-full rounded-md overflow-hidden border border-border/40 cursor-pointer"
                  @click="open"
                >
                  <img
                    :src="modelValue.og_image"
                    class="w-full h-full object-cover"
                  >
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-xs text-white font-medium">{{ $t('common.actions.change') }}</span>
                  </div>
                </div>
                <Button
                  v-else
                  variant="outline"
                  size="sm"
                  class="w-full text-xs h-9"
                  @click="open"
                >
                  <ImageIcon class="w-3 h-3 mr-2" /> {{ $t('publishing.content.form.selectOgImage') }}
                </Button>
              </template>
            </MediaPicker>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import api from '@/engine/api/client';
import {
    Label,
    Input,
    Textarea,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Badge,
    Button,
    Switch
} from '@/shared/components/ui';
import {
  ChevronDown,
  FileCheck,
  FileText,
  ImageIcon,
  MenuSquare,
  MessageSquare,
  Search,
  Tags,
  X,
} from 'lucide-vue-next';
import FeaturedImage from '@/modules/Content/Publishing/components/content/FeaturedImage.vue';
import MediaPicker from '@/modules/Content/Media/components/picker/MediaPicker.vue';
import MarkdownEditor from '@/shared/components/editor/MarkdownEditor.vue';
import type { Menu, MenuItem } from '@/modules/Content/Layout/types/menu';
import type { Category } from '@/modules/Content/Publishing/types/taxonomy';
import type { Tag } from '@/modules/Content/Library/types/taxonomy';
import type { ContentForm } from '@/modules/Content/Publishing/types/content';

const systemStore = useSystemStore();
const { settings } = storeToRefs(systemStore);

const { t } = useI18n();

const props = withDefaults(defineProps<{
    modelValue: ContentForm;
    categories?: Category[];
    tags?: Tag[];
    selectedTags?: Tag[];
    menus?: Menu[];
}>(), {
    categories: () => [],
    tags: () => [],
    selectedTags: () => [],
    menus: () => []
});

const emit = defineEmits<{
    'update:modelValue': [value: ContentForm];
    'update:selectedTags': [tags: Tag[]];
    'search-tags': [query: string];
}>();

// Collapsible section states
const sections = ref({
    publishing: true,
    menu: false,
    taxonomy: true,
    image: false,
    excerpt: false,
    seo: false,
    discussion: false
});

// Tag input state
const tagInput = ref('');
const showTagSuggestions = ref(false);

const menuParentItems = ref<MenuItem[]>([]);
const loadingParentItems = ref(false);

// Filter available tags based on input and already selected
const availableTags = computed(() => {
    if (!Array.isArray(props.tags) || !Array.isArray(props.selectedTags)) {
        return [];
    }
    return props.tags.filter(tag => !props.selectedTags.find(st => st.id === tag.id || st.name === tag.name));
});

// Filter tags based on current input for suggestions
const filteredTags = computed(() => {
    const query = tagInput.value.trim().toLowerCase();
    
    if (!query) return availableTags.value.slice(0, 10);
    
    return availableTags.value.slice(0, 10);
});

const updateField = <K extends keyof ContentForm>(field: K, value: ContentForm[K]) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateMetaField = (key: string, value: unknown) => {
    const currentMeta = props.modelValue.meta || {};
    emit('update:modelValue', {
        ...props.modelValue,
        meta: {
            ...currentMeta,
            [key]: value
        }
    });
};

const introMaxChars = 500;
const stripMarkdown = (text: string): string => text
    .replace(/```[\s\S]*?```/g, ' ')
    .replace(/`[^`]*`/g, ' ')
    .replace(/!\[[^\]]*]\([^)]*\)/g, ' ')
    .replace(/\[([^\]]+)]\([^)]*\)/g, '$1')
    .replace(/[*_~>#-]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
const introValue = computed(() => String(props.modelValue.intro || ''));
const introCharCount = computed(() => introValue.value.length);
const introWordCount = computed(() => {
    const plain = stripMarkdown(introValue.value);
    if (!plain) return 0;
    return plain.split(' ').filter(Boolean).length;
});
const isIntroAtLimit = computed(() => introCharCount.value >= introMaxChars);
const handleIntroUpdate = (value: string) => {
    const next = String(value || '').slice(0, introMaxChars);
    updateField('intro', next);
};

const setPublishNow = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

    emit('update:modelValue', {
        ...props.modelValue,
        status: 'published',
        published_at: localDateTime,
    });
};

// Handle Enter key to add tag (existing or new)
const handleTagEnter = () => {
    const name = tagInput.value.trim();
    if (!name) return;
    
    // Check if tag already selected
    if (props.selectedTags.find(st => st.name.toLowerCase() === name.toLowerCase())) {
        tagInput.value = '';
        return;
    }
    
    // Check if existing tag matches
    const existingTag = props.tags.find(t => t.name.toLowerCase() === name.toLowerCase());
    if (existingTag) {
        emit('update:selectedTags', [...props.selectedTags, existingTag]);
    } else {
        // Create new tag (temporary, will be created on backend during save)
        const newTag: Tag = { 
            id: "0", 
            name: name, 
            slug: name.toLowerCase().replace(/\s+/g, '-'),
            isNew: true 
        };
        emit('update:selectedTags', [...props.selectedTags, newTag]);
    }
    
    tagInput.value = '';
    showTagSuggestions.value = false;
};

const timer = ref<ReturnType<typeof setTimeout> | null>(null);

const handleTagInput = (e: Event) => {
    const val = (e.target as HTMLInputElement).value;
    tagInput.value = val;
    
    if (timer.value) clearTimeout(timer.value);
    
    timer.value = setTimeout(() => {
        emit('search-tags', val);
    }, 300);
    
    showTagSuggestions.value = true;
};

// Handle blur to close suggestions with delay (allow click on suggestion)
const handleTagBlur = () => {
    setTimeout(() => {
        showTagSuggestions.value = false;
    }, 200);
};

// Select tag from suggestions
const selectTag = (tag: Tag) => {
    if (!props.selectedTags.find(st => st.id === tag.id)) {
        emit('update:selectedTags', [...props.selectedTags, tag]);
    }
    tagInput.value = '';
    showTagSuggestions.value = false;
};

// Remove tag (support both id and name for new tags)
const removeTag = (tagIdOrName: string | null) => {
    emit('update:selectedTags', props.selectedTags.filter(t => 
        t.id ? t.id !== tagIdOrName : t.name !== tagIdOrName
    ));
};

const updateMenuField = <K extends keyof MenuItem>(field: K, value: MenuItem[K]) => {
    emit('update:modelValue', {
        ...props.modelValue,
        menu_item: {
            ...props.modelValue.menu_item,
            [field]: value
        }
    });
};

const handleMenuChange = (val: string) => {
    const id = val || null;
    updateMenuField('menu_id', id);
    fetchMenuParentItems(val);
};

const fetchMenuParentItems = async (menuId: string) => {
    if (!menuId) {
        menuParentItems.value = [];
        return;
    }
    
    loadingParentItems.value = true;
    try {
        const response = await api.get(`/manage/layout/menus/${menuId}`);
        const data = response.data || [];
        const flatItems = Array.isArray(data) ? data : [];
        
        if (flatItems.length > 0 && !flatItems[0].depth) {
             menuParentItems.value = flattenTreeForSelect(buildTree(flatItems));
        } else {
             menuParentItems.value = flatItems;
        }
    } catch (error) {
        logger.error('Failed to fetch menu items:', error);
    } finally {
        loadingParentItems.value = false;
    }
};

const buildTree = (items: MenuItem[], parentId: string | null = null): MenuItem[] => {
    return items
        .filter(item => item.parent_id === parentId)
        .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
        .map(item => ({
            ...item,
            children: buildTree(items, item.id || null)
        }));
};

const flattenTreeForSelect = (items: MenuItem[], depth = 0): MenuItem[] => {
    let result: MenuItem[] = [];
    items.forEach(item => {
        result.push({ ...item, depth });
        if (item.children) {
            result = result.concat(flattenTreeForSelect(item.children, depth + 1));
        }
    });
    return result;
};

// Initial load of parent items if menu_id is set
watch(() => props.modelValue.menu_item?.menu_id, (newVal) => {
    if (newVal && menuParentItems.value.length === 0) {
        fetchMenuParentItems(newVal);
    }
}, { immediate: true });
</script>
