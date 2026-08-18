<template>
  <TooltipProvider :delay-duration="400">
    <div
      v-if="editor"
      class="editor-toolbar bg-transparent flex flex-col overflow-visible"
    >
      <Tabs
        v-model="activeTab"
        class="w-full flex flex-col"
      >
        <!-- Tabs Header -->
        <div class="relative z-50 flex items-center justify-between px-3 py-1.5 bg-muted/5 h-10 shrink-0 overflow-visible">
          <TabsList class="bg-muted/40 h-7 p-1 gap-1 rounded-md">
            <TabsTrigger
              value="home"
              class="h-5 px-3 text-[11px] font-medium rounded-sm text-foreground/85 transition-colors data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
            >
              {{ t('publishing.editor.toolbar.tabs.home') }}
            </TabsTrigger>
            <TabsTrigger
              value="insert"
              class="h-5 px-3 text-[11px] font-medium rounded-sm text-foreground/85 transition-colors data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
            >
              {{ t('publishing.editor.toolbar.tabs.insert') }}
            </TabsTrigger>
            <TabsTrigger
              value="layout"
              class="h-5 px-3 text-[11px] font-medium rounded-sm text-foreground/85 transition-colors data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
            >
              {{ t('publishing.editor.toolbar.tabs.layout') }}
            </TabsTrigger>
            <TabsTrigger 
              v-if="editor.isActive('table')" 
              value="table" 
              class="h-5 px-3 text-[11px] font-medium rounded-sm transition-colors data-[state=active]:bg-background data-[state=active]:text-primary data-[state=active]:shadow-sm"
            >
              {{ t('publishing.editor.toolbar.tabs.table') }}
            </TabsTrigger>
          </TabsList>

          <!-- Global Actions -->
          <div class="flex items-center gap-0.5 px-2">
            <Tooltip>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm"
                  :aria-label="t('publishing.editor.toolbar.undo')"
                  :disabled="!editor.can().undo()"
                  @click="editor.chain().focus().undo().run()"
                >
                  <Undo class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
<TooltipContent side="top">
                  {{ t('publishing.editor.toolbar.undo') }}
                </TooltipContent>
</Tooltip>

            <Tooltip>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm"
                  :aria-label="t('publishing.editor.toolbar.redo')"
                  :disabled="!editor.can().redo()"
                  @click="editor.chain().focus().redo().run()"
                >
                  <Redo class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
<TooltipContent side="top">
                  {{ t('publishing.editor.toolbar.redo') }}
                </TooltipContent>
</Tooltip>

            <div class="w-px h-4 bg-border/60 mx-1" />
                        
            <AiAssistPopover
              :context="getEditorContext"
              :disabled="!isAiEnabled"
              @result="handleAiResult"
            >
              <template #trigger>
                <Button
                  :disabled="!isAiEnabled"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm text-indigo-800 hover:text-indigo-900 hover:bg-indigo-500/15"
                  :aria-label="t('publishing.editor.toolbar.aiAssist')"
                >
                  <Sparkles class="w-3.5 h-3.5" />
                </Button>
              </template>
            </AiAssistPopover>

            <div class="w-px h-4 bg-border/60 mx-1" />
                        
            <Tooltip>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm"
                  :class="{ 'text-primary': showHtmlView }"
                  :aria-label="t('publishing.editor.toolbar.viewHtml')"
                  @click="$emit('toggleHtml')"
                >
                  <FileCode class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
<TooltipContent side="top">
                  {{ t('publishing.editor.toolbar.viewHtml') }}
                </TooltipContent>
</Tooltip>
          </div>
        </div>

        <!-- Tab Content (Ribbon Content) -->
        <!-- ... existing content ... -->
        <div class="bg-background flex-1 overflow-x-auto overflow-y-visible custom-scrollbar relative z-40">
          <!-- ... tabs content ... -->
          <TabsContent
            value="home"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4"
          >
            <!-- ... -->
            <div class="ribbon-group border-r border-border/40">
              <!-- ... Font group ... -->
              <div class="ribbon-group-content">
                <Select
                  :model-value="getHeaderLevel()"
                  @update:model-value="setHeaderLevel"
                >
                  <SelectTrigger class="w-[100px] h-7 text-[11px] bg-transparent border-none shadow-none hover:bg-muted/40" :aria-label="t('publishing.editor.toolbar.placeholders.paragraph')">
                    <SelectValue :placeholder="t('publishing.editor.toolbar.placeholders.paragraph')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="p">
                      {{ t('publishing.editor.toolbar.placeholders.paragraph') }}
                    </SelectItem>
                    <SelectItem value="1">
                      {{ t('publishing.editor.toolbar.headings.h1') }}
                    </SelectItem>
                    <SelectItem value="2">
                      {{ t('publishing.editor.toolbar.headings.h2') }}
                    </SelectItem>
                    <SelectItem value="3">
                      {{ t('publishing.editor.toolbar.headings.h3') }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                                
                <Select
                  :model-value="getFontFamily()"
                  @update:model-value="setFontFamily"
                >
                  <SelectTrigger class="w-[80px] h-7 text-[11px] bg-transparent border-none shadow-none hover:bg-muted/40" :aria-label="t('publishing.editor.toolbar.placeholders.font')">
                    <SelectValue :placeholder="t('publishing.editor.toolbar.placeholders.font')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Inter">
                      Inter
                    </SelectItem>
                    <SelectItem value="serif">
                      Serif
                    </SelectItem>
                    <SelectItem value="monospace">
                      Mono
                    </SelectItem>
                  </SelectContent>
                </Select>

                <!-- Text Color Picker (Pop-over) -->
                <Tooltip>
                  <TooltipTrigger as-child>
                    <div class="flex items-center -ml-1">
                      <ColorPicker
                        v-model="selectedColor"
                        :title="t('publishing.editor.toolbar.textColorTitle')"
                      >
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-7 w-7 relative"
                          :class="{ 'text-primary': selectedColor }"
                          :aria-label="t('publishing.editor.toolbar.textColor')"
                        >
                          <Palette class="w-4 h-4" />
                          <div 
                            class="absolute bottom-1.5 w-3 h-0.5 rounded-full" 
                            :style="{ backgroundColor: selectedColor || 'currentColor', opacity: selectedColor ? 1 : 0.3 }" 
                          />
                        </Button>
                      </ColorPicker>
                    </div>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.textColor') }}
                    </TooltipContent>
</Tooltip>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.fontGroup') }}
              </div>
            </div>
                        
            <!-- ... Style group ... -->
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content grid grid-cols-3 gap-x-0.5 gap-y-0.5 items-center">
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :aria-label="t('publishing.editor.toolbar.bold')"
                      :class="{ 'text-primary': editor.isActive('bold') }"
                      @click="editor.chain().focus().toggleBold().run()"
                    >
                      <Bold class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.bold') }}
                    </TooltipContent>
</Tooltip>

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :aria-label="t('publishing.editor.toolbar.italic')"
                      :class="{ 'text-primary': editor.isActive('italic') }"
                      @click="editor.chain().focus().toggleItalic().run()"
                    >
                      <Italic class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.italic') }}
                    </TooltipContent>
</Tooltip>

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :aria-label="t('publishing.editor.toolbar.underline')"
                      :class="{ 'text-primary': editor.isActive('underline') }"
                      @click="editor.chain().focus().toggleUnderline().run()"
                    >
                      <UnderlineIcon class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.underline') }}
                    </TooltipContent>
</Tooltip>

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :aria-label="t('publishing.editor.toolbar.strikethrough')"
                      :class="{ 'text-primary': editor.isActive('strike') }"
                      @click="editor.chain().focus().toggleStrike().run()"
                    >
                      <Strikethrough class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.strikethrough') }}
                    </TooltipContent>
</Tooltip>

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :aria-label="t('publishing.editor.toolbar.code')"
                      :class="{ 'text-primary': editor.isActive('code') }"
                      @click="editor.chain().focus().toggleCode().run()"
                    >
                      <Code class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.code') }}
                    </TooltipContent>
</Tooltip>

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :aria-label="t('publishing.editor.toolbar.clearStyle')"
                      @click="editor.chain().focus().unsetAllMarks().run()"
                    >
                      <RemoveFormatting class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.clearStyle') }}
                    </TooltipContent>
</Tooltip>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.styleGroup') }}
              </div>
            </div>

            <!-- ... Paragraph group ... -->
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content grid grid-cols-4 gap-x-0.5 gap-y-0.5 items-center">
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :aria-label="t('publishing.editor.toolbar.alignLeft')"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'left' }) }"
                      @click="editor.chain().focus().setTextAlign('left').run()"
                    >
                      <AlignLeft class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.alignLeft') }}
                    </TooltipContent>
</Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :aria-label="t('publishing.editor.toolbar.alignCenter')"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'center' }) }"
                      @click="editor.chain().focus().setTextAlign('center').run()"
                    >
                      <AlignCenter class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.alignCenter') }}
                    </TooltipContent>
</Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :aria-label="t('publishing.editor.toolbar.alignRight')"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'right' }) }"
                      @click="editor.chain().focus().setTextAlign('right').run()"
                    >
                      <AlignRight class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.alignRight') }}
                    </TooltipContent>
</Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :aria-label="t('publishing.editor.toolbar.alignJustify')"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'justify' }) }"
                      @click="editor.chain().focus().setTextAlign('justify').run()"
                    >
                      <AlignJustify class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.alignJustify') }}
                    </TooltipContent>
</Tooltip>
                                
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :aria-label="t('publishing.editor.toolbar.bullets')"
                      :class="{ 'text-primary': editor.isActive('bulletList') }"
                      @click="editor.chain().focus().toggleBulletList().run()"
                    >
                      <List class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.bullets') }}
                    </TooltipContent>
</Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :aria-label="t('publishing.editor.toolbar.numbers')"
                      :class="{ 'text-primary': editor.isActive('orderedList') }"
                      @click="editor.chain().focus().toggleOrderedList().run()"
                    >
                      <ListOrdered class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.numbers') }}
                    </TooltipContent>
</Tooltip>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.paragraphGroup') }}
              </div>
            </div>

            <!-- ... Special group ... -->
            <div class="ribbon-group">
              <div class="ribbon-group-content flex gap-1">
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8"
                      :aria-label="t('publishing.editor.toolbar.blockquote')"
                      :class="{ 'text-primary': editor.isActive('blockquote') }"
                      @click="editor.chain().focus().toggleBlockquote().run()"
                    >
                      <Quote class="w-4 h-4" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.blockquote') }}
                    </TooltipContent>
</Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8"
                      :aria-label="t('publishing.editor.toolbar.dropcap')"
                      :class="{ 'text-primary': editor.isActive('paragraph', { dropcap: true }) }"
                      @click="editor.chain().focus().toggleDropcap().run()"
                    >
                      <DropcapIcon class="w-4 h-4" />
                    </Button>
                  </TooltipTrigger>
<TooltipContent side="bottom" :side-offset="6">
                      {{ t('publishing.editor.toolbar.dropcap') }}
                    </TooltipContent>
</Tooltip>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.specialGroup') }}
              </div>
            </div>
          </TabsContent>
          <TabsContent
            value="insert"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4"
          >
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="$emit('openMedia')"
                >
                  <ImageIcon class="w-5 h-5 opacity-70" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.image') }}</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="$emit('insertTable')"
                >
                  <TableIcon class="w-5 h-5 opacity-70" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.table') }}</span>
                </Button>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.commonGroup') }}
              </div>
            </div>

            <div class="ribbon-group">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="$emit('insertHtml')"
                >
                  <FileCode2 class="w-5 h-5 opacity-70" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.embed') }}</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="editor.chain().focus().setHorizontalRule().run()"
                >
                  <Minus class="w-5 h-5 opacity-50" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.line') }}</span>
                </Button>
                <div class="flex flex-col items-center justify-center">
                  <IconPicker
                    model-value=""
                    @update:model-value="handleIconSelect"
                  >
                    <template #trigger>
                      <Button
                        variant="ghost"
                        size="sm"
                        class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                      >
                        <Smile class="w-5 h-5 opacity-70" />
                        <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.icon') }}</span>
                      </Button>
                    </template>
                  </IconPicker>
                </div>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.embedsGroup') }}
              </div>
            </div>
          </TabsContent>
          <TabsContent
            value="layout"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4"
          >
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  :class="{ 'text-primary': editor.isActive('textColumns', { count: 2 }) }"
                  @click="editor.chain().focus().toggleTextColumns(2).run()"
                >
                  <Columns class="w-4 h-4 opacity-70" />
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.columns2') }}</span>
                    <span class="text-[9px] opacity-60">{{ t('publishing.editor.toolbar.flowLayout') }}</span>
                  </div>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  :class="{ 'text-primary': editor.isActive('textColumns', { count: 3 }) }"
                  @click="editor.chain().focus().toggleTextColumns(3).run()"
                >
                  <div class="flex gap-0.5 opacity-50">
                    <div class="w-0.5 h-3 bg-current rounded-full" />
                    <div class="w-0.5 h-3 bg-current rounded-full" />
                    <div class="w-0.5 h-3 bg-current rounded-full" />
                  </div>
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.columns3') }}</span>
                    <span class="text-[9px] opacity-60">{{ t('publishing.editor.toolbar.flowLayout') }}</span>
                  </div>
                </Button>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.newspaperStyle') }}
              </div>
            </div>
            <div class="ribbon-group">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  @click="editor.chain().focus().insertColumns({ count: 2 }).run()"
                >
                  <LayoutGrid class="w-4 h-4 opacity-50" />
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.grid2') }}</span>
                    <span class="text-[9px] opacity-60">{{ t('publishing.editor.toolbar.containers') }}</span>
                  </div>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  @click="editor.chain().focus().insertColumns({ count: 3 }).run()"
                >
                  <div class="grid grid-cols-3 gap-0.5 opacity-50">
                    <div class="w-1 h-3 bg-current rounded-sm" />
                    <div class="w-1 h-3 bg-current rounded-sm" />
                    <div class="w-1 h-3 bg-current rounded-sm" />
                  </div>
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.grid3') }}</span>
                    <span class="text-[9px] opacity-60">{{ t('publishing.editor.toolbar.containers') }}</span>
                  </div>
                </Button>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.gridSystem') }}
              </div>
            </div>
          </TabsContent>
          <TabsContent
            v-if="editor.isActive('table')"
            value="table"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4 animate-in fade-in slide-in-from-top-1"
          >
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content gap-1">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center"
                  @click="editor.chain().focus().addColumnAfter().run()"
                >
                  <div class="relative">
                    <Table2 class="w-5 h-5 opacity-50" />
                    <Plus class="w-2 absolute -right-0.5 -bottom-0.5 bg-background rounded-full border border-primary text-primary" />
                  </div>
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.addCol') }}</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center"
                  @click="editor.chain().focus().addRowAfter().run()"
                >
                  <div class="relative">
                    <Table2 class="w-5 h-5 opacity-50 rotate-90" />
                    <Plus class="w-2 absolute -right-0.5 -bottom-0.5 bg-background rounded-full border border-primary text-primary" />
                  </div>
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.addRow') }}</span>
                </Button>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.structure') }}
              </div>
            </div>

            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content">
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7"
                  @click="editor.chain().focus().mergeCells().run()"
                >
                  <Merge class="w-3.5 h-3.5 opacity-70" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7"
                  @click="editor.chain().focus().splitCell().run()"
                >
                  <Split class="w-3.5 h-3.5 opacity-70" />
                </Button>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.merge') }}
              </div>
            </div>

            <div class="ribbon-group">
              <div class="ribbon-group-content gap-1">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center text-destructive"
                  @click="editor.chain().focus().deleteColumn().run()"
                >
                  <Minus class="w-4 h-4" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.delCol') }}</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center text-destructive"
                  @click="editor.chain().focus().deleteRow().run()"
                >
                  <Minus class="w-4 h-4" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.delRow') }}</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center text-destructive border-l ml-1 pl-3"
                  @click="editor.chain().focus().deleteTable().run()"
                >
                  <Trash2 class="w-4 h-4" />
                  <span class="text-[10px] font-medium">{{ t('publishing.editor.toolbar.deleteTable') }}</span>
                </Button>
              </div>
              <div class="ribbon-group-label">
                {{ t('publishing.editor.toolbar.danger') }}
              </div>
            </div>
          </TabsContent>
        </div>
      </Tabs>
    </div>
  </TooltipProvider>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Button, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem, 
    Tabs, 
    TabsList, 
    TabsTrigger, 
    TabsContent, 
    ColorPicker, 
    IconPicker 
} from '@/shared/components/ui';
import { TooltipProvider, TooltipTrigger } from 'radix-vue';
import { Tooltip, TooltipContent } from '@/shared/components/ui';

import {
  AlignCenter,
  AlignJustify,
  AlignLeft,
  AlignRight,
  Bold,
  Code,
  Columns,
  FileCode,
  FileCode2,
  ImageIcon,
  Italic,
  LayoutGrid,
  List,
  ListOrdered,
  Merge,
  Minus,
  Palette,
  Plus,
  Quote,
  Redo,
  RemoveFormatting,
  Smile,
  Sparkles,
  Split,
  Strikethrough,
  Table2,
  TableIcon,
  Trash2,
  Type as DropcapIcon,
  UnderlineIcon,
  Undo,
} from 'lucide-vue-next';

import { useSystemStore } from '@/modules/Core/System/stores/system';
import AiAssistPopover from '@/shared/components/editor/AiAssistPopover.vue';
import type { Editor } from '@tiptap/vue-3';

const props = defineProps<{
    editor: Editor | undefined;
    showHtmlView: boolean;
}>();

const emit = defineEmits<{
    (e: 'insertTable'): void;
    (e: 'openMedia'): void;
    (e: 'insertHtml'): void;
    (e: 'toggleHtml'): void;
    (e: 'insertIcon', iconName: string): void;
}>();

const { t } = useI18n();
const systemStore = useSystemStore();
const activeTab = ref('home');

const handleIconSelect = (iconName: string) => {
    emit('insertIcon', iconName);
};

const handleAiResult = (text: string) => {
    if (!props.editor) return;
    
    const { from, to } = props.editor.state.selection;
    
    // If text was selected, replace it. Otherwise insert at cursor.
    if (from !== to) {
        props.editor.chain().focus().deleteSelection().insertContent(text).run();
    } else {
        props.editor.chain().focus().insertContent(text).run();
    }
};

const getEditorContext = computed(() => {
    if (!props.editor) return '';
    
    const { from, to } = props.editor.state.selection;
    
    // If text is selected, use that as context
    if (from !== to) {
        return props.editor.state.doc.textBetween(from, to, ' ');
    }
    
    // Otherwise use up to 5000 chars
    return props.editor.getText().slice(0, 5000);
});

// Sync selected color with editor state
const selectedColor = computed({
    get: () => props.editor?.getAttributes('textStyle').color || '',
    set: (color: string) => {
        if (!props.editor) return;
        if (color) {
            props.editor.chain().focus().setColor(color).run();
        } else {
            props.editor.chain().focus().unsetColor().run();
        }
    }
});

// Watch for table activity and auto-switch tab
watch(() => props.editor?.isActive('table'), (isInTable) => {
    if (isInTable) {
        activeTab.value = 'table';
    } else if (activeTab.value === 'table') {
        activeTab.value = 'home';
    }
}, { immediate: true });

const isAiEnabled = computed(() => {
    const enabled = systemStore.settings['ai_enabled'];
    const isEnabled = enabled && enabled !== '0' && enabled !== 'false';
    if (!isEnabled) return false;
    
    // Check if default provider has key
    const provider = (systemStore.settings['ai_default_provider'] as string) || 'gemini';
    return !!systemStore.settings[`${provider}_api_key`];
});

onMounted(() => {
    // Check if store has ai settings, if not fetch them
    if (!systemStore.settings['gemini_api_key']) {
        systemStore.fetchSettingsGroup('ai');
    }
});

function getHeaderLevel() {
    if (props.editor?.isActive('heading', { level: 1 })) return '1';
    if (props.editor?.isActive('heading', { level: 2 })) return '2';
    if (props.editor?.isActive('heading', { level: 3 })) return '3';
    return 'p';
}

function setHeaderLevel(val: string) {
    if (!props.editor) return;
    if (val === 'p') {
         props.editor.chain().focus().setParagraph().run();
    } else {
         props.editor.chain().focus().toggleHeading({ level: parseInt(val) as 1 | 2 | 3 | 4 | 5 | 6 }).run();
    }
}

function getFontFamily() {
    return props.editor?.getAttributes('textStyle').fontFamily || 'Inter';
}

function setFontFamily(val: string) {
    if (!props.editor) return;
    props.editor.chain().focus().setFontFamily(val).run();
}
</script>



<style scoped>
.editor-toolbar {
    transition: all 0.3s ease;
}

/* Ribbon Group Styling */
.ribbon-group {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-width: max-content;
    padding: 0.25rem 0.5rem 0.125rem;
}

.ribbon-group-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.ribbon-group-label {
    text-align: center;
    font-size: 10px;
    font-weight: 500;
    color: hsl(var(--muted-foreground));
    margin-top: 0.125rem;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Scrollbar Styling for Ribbon Overflow */
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--border) / 50%);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--border));
}

/* Refined Tab Styling */
[role="tab"] {
    position: relative;
}

/* keyboard support */
button:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px hsl(var(--background)), 0 0 0 4px hsl(var(--primary));
}
</style>
