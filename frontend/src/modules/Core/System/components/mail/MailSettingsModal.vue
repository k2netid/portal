<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent
      class="!p-0 !gap-0 max-w-4xl w-[96vw] h-auto max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden"
      @pointer-down-outside.prevent
      @interact-outside.prevent
    >
      <!-- Header -->
      <div class="h-12 px-5 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-sm font-bold text-foreground flex items-center gap-2">
          <Settings class="w-4 h-4 text-primary" />
          <span>{{ $t('system.mail.settings_title') }}</span>
        </DialogTitle>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Navigation Tabs (Pill style, smooth horizontal scroll) -->
      <div class="px-5 pt-3 border-b border-border/40 shrink-0 bg-background/50">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 scrollbar-thin">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            :class="[
              'px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 shrink-0 select-none whitespace-nowrap',
              activeTab === tab.id
                ? 'bg-primary text-primary-foreground shadow-xs font-bold'
                : 'bg-muted/50 text-muted-foreground hover:bg-muted hover:text-foreground'
            ]"
            @click="activeTab = tab.id"
          >
            <component :is="tab.icon" class="w-3.5 h-3.5" />
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.id === 'ai' && globalAiState.enabled"
              class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"
              title="Global AI Active"
            />
          </button>
        </div>
      </div>

      <!-- Tab Contents (Responsive & Scrollable) -->
      <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar max-h-[calc(88vh-135px)] min-h-[320px]">
        <!-- Tab 1: General Preferences & Security -->
        <div v-if="activeTab === 'general'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Messages per page -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Messages per Page</label>
              <p class="text-[11px] text-muted-foreground">Default number of emails per page.</p>
              <Select
                :model-value="String(settingsData.per_page)"
                @update:model-value="v => settingsData.per_page = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select items" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10 messages per page</SelectItem>
                  <SelectItem value="25">25 messages per page (Default)</SelectItem>
                  <SelectItem value="50">50 messages per page</SelectItem>
                  <SelectItem value="100">100 messages per page</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Auto-check interval -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-check Mail Interval</label>
              <p class="text-[11px] text-muted-foreground">Background sync frequency.</p>
              <Select
                :model-value="String(settingsData.auto_check_interval)"
                @update:model-value="v => settingsData.auto_check_interval = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select interval" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="1">Every 1 minute (Real-time)</SelectItem>
                  <SelectItem value="5">Every 5 minutes (Recommended)</SelectItem>
                  <SelectItem value="15">Every 15 minutes</SelectItem>
                  <SelectItem value="0">Manual only</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Storage Quota Allocation -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Mail Storage Quota</label>
              <p class="text-[11px] text-muted-foreground">Total mailbox capacity allocated.</p>
              <Select
                :model-value="String(settingsData.storage_quota_gb)"
                @update:model-value="v => settingsData.storage_quota_gb = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select quota" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="5">5 GB Storage</SelectItem>
                  <SelectItem value="15">15 GB Storage (Default)</SelectItem>
                  <SelectItem value="30">30 GB Storage</SelectItem>
                  <SelectItem value="50">50 GB Storage</SelectItem>
                  <SelectItem value="100">100 GB Storage</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Trash Retention -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-Purge Trash Retention</label>
              <p class="text-[11px] text-muted-foreground">Automatically clean deleted emails.</p>
              <Select
                :model-value="String(settingsData.trash_retention_days)"
                @update:model-value="v => settingsData.trash_retention_days = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select retention" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="7">After 7 days</SelectItem>
                  <SelectItem value="14">After 14 days</SelectItem>
                  <SelectItem value="30">After 30 days (Recommended)</SelectItem>
                  <SelectItem value="90">After 90 days</SelectItem>
                  <SelectItem value="0">Never (Manual delete)</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Mark as Read Behavior -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Mark as Read Behavior</label>
            <p class="text-[11px] text-muted-foreground">When viewing an unread email in message viewer.</p>
            <Select
              :model-value="String(settingsData.auto_read_delay)"
              @update:model-value="v => settingsData.auto_read_delay = Number(v)"
            >
              <SelectTrigger class="h-8 text-xs">
                <SelectValue placeholder="Select behavior" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Immediately upon selection</SelectItem>
                <SelectItem value="3">After 3 seconds delay</SelectItem>
                <SelectItem value="-1">Manual only (do not auto-mark)</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Security & Privacy: Block Remote Images -->
          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                <ShieldCheck class="w-3.5 h-3.5 text-primary" />
                <span>Privacy Shield: Block Remote Images</span>
              </p>
              <p class="text-[11px] text-muted-foreground">Blocks external tracking pixels and remote image downloads until approved.</p>
            </div>
            <Switch v-model="settingsData.block_remote_images" />
          </div>

          <!-- Sound Notifications -->
          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground">Sound & Push Notifications</p>
              <p class="text-[11px] text-muted-foreground">Play a subtle chime and show toast alert when new messages arrive.</p>
            </div>
            <Switch v-model="settingsData.sound_notifications" />
          </div>
        </div>

        <!-- Tab 2: Identity & Signature (With Modal Logo Selector) -->
        <div v-if="activeTab === 'signature'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Company / Organization</label>
              <Input
                v-model="settingsData.signature_company"
                type="text"
                placeholder="e.g. Jejakawan Cloud Technologies"
                class="h-8 text-xs"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Reply-To Address</label>
              <Input
                v-model="settingsData.reply_to"
                type="email"
                placeholder="support@company.com"
                class="h-8 text-xs"
              />
            </div>
          </div>

          <!-- Logo Selector (Modal Select & Quick Presets) -->
          <div class="space-y-2 p-3 rounded-xl bg-muted/20 border border-border/60">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold text-foreground flex items-center gap-1.5">
                <ImageIcon class="w-3.5 h-3.5 text-primary" />
                <span>Signature Brand Logo</span>
              </label>

              <!-- MediaPicker Trigger -->
              <MediaPicker @select="handleMediaSelect">
                <template #trigger="{ open }">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-7 text-xs gap-1.5 shadow-xs"
                    @click="open"
                  >
                    <Upload class="w-3 h-3" />
                    <span>Choose from Media Library</span>
                  </Button>
                </template>
              </MediaPicker>
            </div>

            <!-- Logo Quick Presets & Selected State -->
            <div class="flex items-center gap-2 pt-1 flex-wrap">
              <div
                v-if="settingsData.signature_logo"
                class="flex items-center gap-2 p-1.5 pr-3 rounded-lg border border-primary/40 bg-primary/5 text-xs"
              >
                <div class="w-8 h-8 rounded border border-border/60 p-0.5 flex items-center justify-center bg-card shrink-0">
                  <img :src="settingsData.signature_logo" class="max-h-full max-w-full object-contain" alt="Selected Logo">
                </div>
                <span class="text-[11px] font-medium text-foreground truncate max-w-[150px]">Selected Logo</span>
                <button
                  type="button"
                  class="text-muted-foreground hover:text-destructive p-0.5"
                  title="Remove Logo"
                  @click="settingsData.signature_logo = ''"
                >
                  <X class="w-3.5 h-3.5" />
                </button>
              </div>

              <!-- Quick Presets -->
              <div class="flex items-center gap-1.5 text-xs">
                <span class="text-[10px] text-muted-foreground">Presets:</span>
                <button
                  type="button"
                  class="px-2 py-0.5 rounded border border-border/60 bg-muted/40 hover:bg-muted text-[10px] font-semibold transition-colors"
                  @click="settingsData.signature_logo = '/assets/branding/logo.svg'"
                >
                  System Brand
                </button>
                <button
                  type="button"
                  class="px-2 py-0.5 rounded border border-border/60 bg-muted/40 hover:bg-muted text-[10px] font-semibold transition-colors"
                  @click="settingsData.signature_logo = '/favicon.ico'"
                >
                  App Icon
                </button>
              </div>
            </div>
          </div>

          <!-- Signature Text -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Signature Text</label>
            <Textarea
              v-model="settingsData.signature"
              :rows="3"
              placeholder="Best regards,&#10;Your Name | Lead Engineer&#10;Direct: +62 812-3456-7890"
              class="text-xs rounded-xl resize-none leading-relaxed"
            />
          </div>

          <!-- Live Signature Preview Box -->
          <div class="space-y-1.5 pt-1">
            <label class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              Live Signature Preview
            </label>
            <div class="p-3.5 rounded-xl border border-border/60 bg-muted/20">
              <div class="flex items-center gap-3">
                <div
                  v-if="settingsData.signature_logo"
                  class="w-12 h-12 rounded-xl border border-border/60 p-1 flex items-center justify-center bg-card shrink-0 shadow-xs"
                >
                  <img :src="settingsData.signature_logo" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div class="text-xs leading-relaxed">
                  <p v-if="settingsData.signature_company" class="font-bold text-foreground">
                    {{ settingsData.signature_company }}
                  </p>
                  <p class="text-muted-foreground whitespace-pre-line text-[11px]">
                    {{ settingsData.signature || 'Your Name | Title\ncontact@example.com' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 3: Canned Email Templates & Snippets Manager -->
        <div v-if="activeTab === 'templates'" class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-bold text-foreground">Canned Response Templates</p>
              <p class="text-[11px] text-muted-foreground">Create and manage reusable message snippets for quick composition.</p>
            </div>
            <Button
              v-if="!isEditingTemplate"
              size="sm"
              class="h-7 text-xs gap-1.5 px-3"
              @click="createNewTemplate"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>New Template</span>
            </Button>
          </div>

          <!-- Inline Template Editor Form -->
          <div v-if="isEditingTemplate" class="p-4 rounded-xl bg-muted/20 border border-primary/40 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-foreground flex items-center gap-1.5">
                <Bookmark class="w-3.5 h-3.5 text-primary" />
                <span>{{ currentTemplate.id ? 'Edit Template' : 'Create New Template' }}</span>
              </span>
              <Button
                variant="ghost"
                size="icon"
                class="h-6 w-6 text-muted-foreground hover:text-foreground"
                @click="isEditingTemplate = false"
              >
                <X class="w-3.5 h-3.5" />
              </Button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-[11px] font-bold text-foreground">Template Title</label>
                <Input
                  v-model="currentTemplate.title"
                  placeholder="e.g. Project Delivery Handover"
                  class="h-8 text-xs"
                />
              </div>
              <div class="space-y-1">
                <label class="text-[11px] font-bold text-foreground">Short Snippet / Description</label>
                <Input
                  v-model="currentTemplate.snippet"
                  placeholder="e.g. Delivery notice with links..."
                  class="h-8 text-xs"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-[11px] font-bold text-foreground">Email Message Content (Rich HTML)</label>
              <TiptapEditor
                v-model="currentTemplate.body"
                :compact="true"
                :resizable="false"
                placeholder="Dear Client, write your formatted email template content here..."
                class="border-border/40 rounded-xl overflow-hidden"
              />
            </div>

            <div class="flex justify-end gap-2 pt-1">
              <Button
                variant="ghost"
                size="sm"
                class="h-7 text-xs"
                @click="isEditingTemplate = false"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                class="h-7 text-xs font-semibold px-3 gap-1"
                @click="saveCurrentTemplate"
              >
                <Save class="w-3 h-3" />
                <span>Save Template</span>
              </Button>
            </div>
          </div>

          <!-- Templates List -->
          <div class="space-y-2.5">
            <div
              v-for="tpl in templateList"
              :key="tpl.id"
              class="p-3.5 rounded-xl border border-border/60 bg-muted/10 hover:bg-muted/20 transition-all flex items-start justify-between gap-3 group"
            >
              <div class="space-y-1 flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <Bookmark class="w-3.5 h-3.5 text-primary shrink-0" />
                  <span class="text-xs font-bold text-foreground truncate">{{ tpl.title }}</span>
                </div>
                <p class="text-[11px] text-muted-foreground line-clamp-1 font-medium">{{ tpl.snippet }}</p>
                <p class="text-[10px] text-muted-foreground/80 line-clamp-2 whitespace-pre-line font-mono bg-background/50 p-2 rounded-lg border border-border/40 mt-1.5">
                  {{ tpl.body }}
                </p>
              </div>

              <div class="flex items-center gap-1 shrink-0 pt-0.5">
                <Button
                  variant="outline"
                  size="sm"
                  class="h-6 text-[10px] px-2"
                  @click="editTemplate(tpl)"
                >
                  Edit
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-6 w-6 text-muted-foreground hover:text-destructive"
                  @click="deleteTemplate(tpl.id)"
                >
                  <Trash2 class="w-3 h-3" />
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 4: AI Copilot & Governance Scope (With Global AI Check & Logic) -->
        <div v-if="activeTab === 'ai'" class="space-y-4">
          <!-- Global AI Inactive Notice -->
          <div
            v-if="!globalAiState.enabled"
            class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 space-y-3"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                <span class="text-xs font-bold text-amber-900 dark:text-amber-200">Global AI Integration Disabled</span>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-800 dark:text-amber-300">
                Disabled in System
              </span>
            </div>
            <p class="text-[11px] text-amber-800/90 dark:text-amber-300/90 leading-relaxed">
              Global AI integration is currently turned off in system configuration. To enable AI drafting, email thread summarization, and smart replies in Webmail, please enable AI in Global Settings.
            </p>
            <Button
              variant="outline"
              size="sm"
              class="h-7 text-xs gap-1.5 border-amber-500/30 text-amber-900 dark:text-amber-200 hover:bg-amber-500/20"
              @click="goToAiSettings"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Configure Global AI Settings</span>
            </Button>
          </div>

          <!-- Active AI Header Banner -->
          <div
            v-else
            class="flex items-center justify-between p-3.5 rounded-xl bg-primary/5 border border-primary/20"
          >
            <div>
              <div class="flex items-center gap-2">
                <p class="text-xs font-bold text-primary flex items-center gap-1.5">
                  <Sparkles class="w-4 h-4 text-amber-500" />
                  <span>AI Email Copilot Integration</span>
                </p>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                  Active ({{ globalAiState.default_provider }})
                </span>
              </div>
              <p class="text-[11px] text-muted-foreground mt-0.5">Enable LLM-assisted drafting, summarizing, and smart replies in Webmail.</p>
            </div>
            <Switch v-model="settingsData.ai_enabled" />
          </div>

          <!-- Writing Tone & Active Provider Selection -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Default Writing Tone</label>
              <Select v-model="settingsData.ai_tone">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select tone" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="professional">Professional Business</SelectItem>
                  <SelectItem value="friendly">Friendly & Warm</SelectItem>
                  <SelectItem value="concise">Concise & Direct</SelectItem>
                  <SelectItem value="executive">Formal Executive</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Preferred AI Engine & Model</label>
              <Select v-model="settingsData.ai_provider">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select AI provider" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="prov in globalAiState.active_providers"
                    :key="prov.id"
                    :value="prov.id"
                  >
                    {{ prov.name }} ({{ prov.model }}) {{ prov.is_default ? '• Default' : '' }}
                  </SelectItem>
                  <template v-if="globalAiState.active_providers.length === 0">
                    <SelectItem value="gemini">Google Gemini (gemini-2.0-flash)</SelectItem>
                    <SelectItem value="openai">OpenAI GPT (gpt-4o-mini)</SelectItem>
                    <SelectItem value="claude">Anthropic Claude (claude-3-5-sonnet)</SelectItem>
                    <SelectItem value="deepseek">DeepSeek (deepseek-chat)</SelectItem>
                    <SelectItem value="grok">xAI Grok (grok-2-latest)</SelectItem>
                  </template>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Permitted Contexts (Konteks yang Diizinkan) -->
          <div class="space-y-2 pt-1">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              Permitted AI Capabilities (Konteks yang Diizinkan)
            </h4>
            <div class="space-y-2 rounded-xl border border-border/40 p-3 bg-muted/20">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-semibold text-foreground">AI Email Drafting & Polish</p>
                  <p class="text-[10px] text-muted-foreground">Generate drafts and polish business tone in composer.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_drafting" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground">Thread Summarization</p>
                  <p class="text-[10px] text-muted-foreground">Summarize long email threads into actionable key points.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_summarize" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground">Contextual Smart Replies</p>
                  <p class="text-[10px] text-muted-foreground">Suggest intelligent 1-click quick replies for incoming mail.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_smart_reply" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground">Urgency & Sentiment Analysis</p>
                  <p class="text-[10px] text-muted-foreground">Detect urgency and classify priority status of incoming messages.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_sentiment" />
              </div>
            </div>
          </div>

          <!-- Safety Boundaries & Guardrails (Batasan Keamanan) -->
          <div class="space-y-2 pt-1">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              Safety Guardrails & Boundaries (Batasan Keamanan)
            </h4>
            <div class="space-y-2 rounded-xl border border-border/40 p-3 bg-muted/20">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Human-in-the-Loop Required</span>
                  </p>
                  <p class="text-[10px] text-muted-foreground">AI is strictly prohibited from autonomous dispatch; manual click required.</p>
                </div>
                <Switch v-model="settingsData.ai_guardrail_human_review" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Sensitive Data & PII Sanitization</span>
                  </p>
                  <p class="text-[10px] text-muted-foreground">Automatically redact passwords, tokens, and credit cards before AI prompt.</p>
                </div>
                <Switch v-model="settingsData.ai_guardrail_pii_masking" />
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 5: Out-of-Office / Auto-Reply -->
        <div v-if="activeTab === 'vacation'" class="space-y-4">
          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground">Enable Vacation Auto-Responder</p>
              <p class="text-[11px] text-muted-foreground">Automatically sends an instant reply message to incoming emails.</p>
            </div>
            <Switch v-model="settingsData.vacation_enabled" />
          </div>

          <div v-if="settingsData.vacation_enabled" class="space-y-3">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-Reply Subject</label>
              <Input
                v-model="settingsData.vacation_subject"
                type="text"
                placeholder="Out of Office Auto-Reply"
                class="h-8 text-xs"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-Reply Message</label>
              <Textarea
                v-model="settingsData.vacation_body"
                :rows="4"
                placeholder="Thank you for reaching out. I am currently out of office with limited email access..."
                class="text-xs rounded-xl resize-none leading-relaxed"
              />
            </div>
          </div>
        </div>

        <!-- Tab 6: Server & Transport -->
        <div v-if="activeTab === 'server'" class="space-y-4">
          <div class="p-4 rounded-xl bg-muted/30 border border-border/40 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Server class="w-4 h-4 text-primary" />
                <span class="text-xs font-bold text-foreground">Active Outbound SMTP Server</span>
              </div>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                Connected
              </span>
            </div>
            <p class="text-[11px] text-muted-foreground">
              Outbound mail routing is configured globally in system settings.
            </p>
            <Button
              variant="outline"
              size="sm"
              class="h-7 text-xs gap-1.5"
              @click="goToEmailSettings"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Open Global Email SMTP Settings</span>
            </Button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="h-14 px-5 bg-muted/30 border-t border-border/40 flex items-center justify-between shrink-0">
        <span class="text-[11px] text-muted-foreground">Preferences saved to system profile.</span>

        <div class="flex items-center gap-2">
          <Button
            variant="ghost"
            size="sm"
            class="h-8 text-xs"
            @click="$emit('close')"
          >
            Close
          </Button>
          <Button
            size="sm"
            class="h-8 gap-1.5 text-xs font-semibold px-4 shadow-xs"
            :disabled="saving"
            @click="saveSettings"
          >
            <Loader2 v-if="saving" class="w-3.5 h-3.5 animate-spin" />
            <Save v-else class="w-3.5 h-3.5" />
            <span>Save Preferences</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import {
  Settings,
  X,
  Sliders,
  PenTool,
  Calendar,
  Server,
  Save,
  Loader2,
  ExternalLink,
  ShieldCheck,
  ImageIcon,
  Sparkles,
  Upload,
  AlertTriangle,
  Bookmark,
  Plus,
  Trash2,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
  Textarea,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Switch,
} from '@/shared/components/ui';
import MediaPicker from '@/shared/components/ui/MediaPicker.vue';
import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import type { MailTemplate } from '@/modules/Core/System/composables/useMailClient';

const props = withDefaults(
    defineProps<{
        isOpen: boolean;
        initialTab?: 'general' | 'signature' | 'templates' | 'ai' | 'vacation' | 'server';
    }>(),
    {
        initialTab: 'general',
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const toast = useToast();
const router = useRouter();
const activeTab = ref<'general' | 'signature' | 'templates' | 'ai' | 'vacation' | 'server'>('general');
const saving = ref(false);

const tabs = [
    { id: 'general' as const, label: 'Preferences', icon: Sliders },
    { id: 'signature' as const, label: 'Signature & Logo', icon: PenTool },
    { id: 'templates' as const, label: 'Canned Templates', icon: Bookmark },
    { id: 'ai' as const, label: 'AI Copilot & Scope', icon: Sparkles },
    { id: 'vacation' as const, label: 'Auto-Reply', icon: Calendar },
    { id: 'server' as const, label: 'Server & Transport', icon: Server },
];

const globalAiState = ref<{
    enabled: boolean;
    default_provider: string;
    active_providers: Array<{ id: string; name: string; model: string; has_key: boolean; is_default: boolean }>;
}>({
    enabled: true,
    default_provider: 'gemini',
    active_providers: [],
});

const settingsData = ref({
    per_page: 25,
    storage_quota_gb: 15,
    trash_retention_days: 30,
    auto_check_interval: 5,
    auto_read_delay: 0,
    sound_notifications: true,
    block_remote_images: true,
    signature_company: '',
    signature_logo: '',
    signature: '',
    reply_to: '',
    vacation_enabled: false,
    vacation_subject: 'Out of Office Auto-Reply',
    vacation_body: 'Thank you for your message. I am currently away from my desk.',
    // AI Governance
    ai_enabled: true,
    ai_provider: 'gemini',
    ai_tone: 'professional',
    ai_scope_drafting: true,
    ai_scope_summarize: true,
    ai_scope_smart_reply: true,
    ai_scope_sentiment: true,
    ai_guardrail_human_review: true,
    ai_guardrail_pii_masking: true,
});

// Templates CRUD State
const templateList = ref<MailTemplate[]>([]);
const isEditingTemplate = ref(false);
const currentTemplate = ref<MailTemplate>({
    id: '',
    title: '',
    snippet: '',
    body: '',
});

const createNewTemplate = () => {
    currentTemplate.value = {
        id: `tpl_${Date.now()}`,
        title: '',
        snippet: '',
        body: '',
    };
    isEditingTemplate.value = true;
};

const editTemplate = (tpl: MailTemplate) => {
    currentTemplate.value = { ...tpl };
    isEditingTemplate.value = true;
};

const saveCurrentTemplate = async () => {
    if (!currentTemplate.value.title.trim()) {
        toast.error.action('Please enter a template title');
        return;
    }
    if (!currentTemplate.value.body.trim()) {
        toast.error.action('Please enter email body content');
        return;
    }

    const idx = templateList.value.findIndex(t => t.id === currentTemplate.value.id);
    if (idx >= 0) {
        templateList.value[idx] = { ...currentTemplate.value };
    } else {
        templateList.value.unshift({ ...currentTemplate.value });
    }

    try {
        await api.post('/manage/mail/templates', { templates: templateList.value });
        toast.success.action('Template saved successfully');
        isEditingTemplate.value = false;
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const deleteTemplate = async (id: string) => {
    templateList.value = templateList.value.filter(t => t.id !== id);
    try {
        await api.post('/manage/mail/templates', { templates: templateList.value });
        toast.success.action('Template removed');
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const fetchTemplates = async () => {
    try {
        const res = await api.get('/manage/mail/templates');
        const data = res.data?.data || res.data;
        if (Array.isArray(data)) {
            templateList.value = data;
        }
    } catch {
        // Fallback
    }
};

const handleMediaSelect = (media: any) => {
    if (media?.url) {
        settingsData.value.signature_logo = media.url;
    } else if (media?.path) {
        settingsData.value.signature_logo = `/storage/${media.path.replace(/^\//, '')}`;
    }
};

const loadSettings = async () => {
    try {
        const response = await api.get('/manage/mail/settings');
        const data = response.data?.data || response.data;
        if (data) {
            settingsData.value = {
                per_page: data.per_page ?? 25,
                storage_quota_gb: data.storage_quota_gb ?? 15,
                trash_retention_days: data.trash_retention_days ?? 30,
                auto_check_interval: data.auto_check_interval ?? 5,
                auto_read_delay: data.auto_read_delay ?? 0,
                sound_notifications: Boolean(data.sound_notifications),
                block_remote_images: Boolean(data.block_remote_images),
                signature_company: data.signature_company ?? '',
                signature_logo: data.signature_logo ?? '',
                signature: data.signature ?? '',
                reply_to: data.reply_to ?? '',
                vacation_enabled: Boolean(data.vacation_enabled),
                vacation_subject: data.vacation_subject ?? 'Out of Office Auto-Reply',
                vacation_body: data.vacation_body ?? 'Thank you for your message. I am currently away from my desk.',
                ai_enabled: Boolean(data.ai_enabled),
                ai_provider: data.ai_provider || 'gemini',
                ai_tone: data.ai_tone || 'professional',
                ai_scope_drafting: Boolean(data.ai_scope_drafting),
                ai_scope_summarize: Boolean(data.ai_scope_summarize),
                ai_scope_smart_reply: Boolean(data.ai_scope_smart_reply),
                ai_scope_sentiment: Boolean(data.ai_scope_sentiment),
                ai_guardrail_human_review: Boolean(data.ai_guardrail_human_review),
                ai_guardrail_pii_masking: Boolean(data.ai_guardrail_pii_masking),
            };
            if (data.global_ai) {
                globalAiState.value = data.global_ai;
            }
        }
    } catch {
        // Fallback to local state
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        const res = await api.post('/manage/mail/settings', settingsData.value);
        const data = res.data?.data || res.data;
        if (data) {
            settingsData.value = {
                per_page: data.per_page ?? settingsData.value.per_page,
                storage_quota_gb: data.storage_quota_gb ?? settingsData.value.storage_quota_gb,
                trash_retention_days: data.trash_retention_days ?? settingsData.value.trash_retention_days,
                auto_check_interval: data.auto_check_interval ?? settingsData.value.auto_check_interval,
                auto_read_delay: data.auto_read_delay ?? settingsData.value.auto_read_delay,
                sound_notifications: Boolean(data.sound_notifications),
                block_remote_images: Boolean(data.block_remote_images),
                signature_company: data.signature_company ?? settingsData.value.signature_company,
                signature_logo: data.signature_logo ?? settingsData.value.signature_logo,
                signature: data.signature ?? settingsData.value.signature,
                reply_to: data.reply_to ?? settingsData.value.reply_to,
                vacation_enabled: Boolean(data.vacation_enabled),
                vacation_subject: data.vacation_subject ?? settingsData.value.vacation_subject,
                vacation_body: data.vacation_body ?? settingsData.value.vacation_body,
                ai_enabled: Boolean(data.ai_enabled),
                ai_provider: data.ai_provider || settingsData.value.ai_provider,
                ai_tone: data.ai_tone || settingsData.value.ai_tone,
                ai_scope_drafting: Boolean(data.ai_scope_drafting),
                ai_scope_summarize: Boolean(data.ai_scope_summarize),
                ai_scope_smart_reply: Boolean(data.ai_scope_smart_reply),
                ai_scope_sentiment: Boolean(data.ai_scope_sentiment),
                ai_guardrail_human_review: Boolean(data.ai_guardrail_human_review),
                ai_guardrail_pii_masking: Boolean(data.ai_guardrail_pii_masking),
            };
            if (data.global_ai) {
                globalAiState.value = data.global_ai;
            }
        }
        toast.success.action('Mail preferences saved successfully');
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

const goToEmailSettings = () => {
    emit('close');
    router.push({ name: 'settings', query: { tab: 'email' } });
};

const goToAiSettings = () => {
    emit('close');
    router.push({ name: 'settings', query: { tab: 'ai' } });
};

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            if (props.initialTab) {
                activeTab.value = props.initialTab;
            }
            loadSettings();
            fetchTemplates();
        }
    }
);

onMounted(() => {
    loadSettings();
    fetchTemplates();
});
</script>
