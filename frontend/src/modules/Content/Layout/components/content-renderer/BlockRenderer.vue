<template>
  <div class="block-renderer w-full space-y-8">
    <template v-for="(block, index) in internalBlocks" :key="block.id || index">
      <!-- 1. SECTION BLOCK -->
      <section
        v-if="block.type === 'section' || block.type === 'fullwidth_section'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-section w-full transition-all"
        :class="[
          getSettingStr(block, 'css_class'),
          getSettingBool(block, 'fullwidth') ? 'w-full' : 'container mx-auto px-4'
        ]"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </section>

      <!-- 2. ROW BLOCK (GRID SYSTEM) -->
      <div
        v-else-if="block.type === 'row'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-row grid gap-6 items-start"
        :class="[
          getRowGridClass(block),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </div>

      <!-- 3. COLUMN BLOCK -->
      <div
        v-else-if="block.type === 'column'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-column flex flex-col space-y-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </div>

      <!-- 4. HERO / BANNER BLOCK -->
      <div
        v-else-if="block.type === 'hero' || block.type === 'fullwidth_header'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-hero-block relative overflow-hidden rounded-3xl p-8 md:p-16 lg:p-20 text-center flex flex-col items-center justify-center border border-border shadow-lg"
        :class="getSettingStr(block, 'css_class')"
        :style="[
          resolveBlockStyles(block),
          getSettingStr(block, 'image') ? { backgroundImage: `url(${getSettingStr(block, 'image')})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}
        ]"
      >
        <div v-if="getSettingStr(block, 'image')" class="absolute inset-0 bg-background/85 backdrop-blur-[2px] z-0" />
        
        <div class="relative z-10 max-w-3xl mx-auto space-y-6">
          <div v-if="getSettingStr(block, 'badge')" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary/10 text-primary border border-primary/20 text-xs font-bold uppercase tracking-wider">
            <Sparkles class="w-3.5 h-3.5" />
            <span>{{ getSettingStr(block, 'badge') }}</span>
          </div>

          <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-foreground leading-[1.15]">
            {{ getSettingStr(block, 'title', 'Judul Hero Landing Page') }}
          </h1>

          <p class="text-base sm:text-lg md:text-xl text-muted-foreground leading-relaxed max-w-2xl mx-auto">
            {{ getSettingStr(block, 'subtitle') || getSettingStr(block, 'description', 'Deskripsi ringkas yang menarik perhatian pengunjung dan mendorong konversi.') }}
          </p>

          <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
            <a
              v-if="getSettingStr(block, 'button_text', 'Mulai Sekarang')"
              :href="getSettingStr(block, 'button_url', '#')"
              class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-primary text-primary-foreground font-bold text-sm shadow-md hover:shadow-lg hover:scale-105 transition-all"
            >
              <span>{{ getSettingStr(block, 'button_text', 'Mulai Sekarang') }}</span>
              <ArrowRight class="w-4 h-4" />
            </a>

            <a
              v-if="getSettingStr(block, 'secondary_button_text')"
              :href="getSettingStr(block, 'secondary_button_url', '#')"
              class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl border-2 border-border bg-background/80 hover:bg-muted font-bold text-sm text-foreground transition-all"
            >
              <span>{{ getSettingStr(block, 'secondary_button_text') }}</span>
            </a>
          </div>
        </div>
      </div>

      <!-- 5. FEATURES GRID / ICON LIST BLOCK -->
      <div
        v-else-if="block.type === 'features' || block.type === 'feature_grid' || block.type === 'icon_list'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-features-block w-full py-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="text-center max-w-2xl mx-auto mb-10 space-y-2">
          <h2 class="text-2xl sm:text-3xl font-black text-foreground">{{ getSettingStr(block, 'title') }}</h2>
          <p v-if="getSettingStr(block, 'subtitle')" class="text-sm text-muted-foreground">{{ getSettingStr(block, 'subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <template v-for="(feat, fIdx) in getSampleFeatures(getSettingNum(block, 'count', 3))" :key="fIdx">
            <div class="group p-6 rounded-2xl border border-border/80 bg-card/60 hover:bg-card hover:border-primary/40 hover:shadow-lg transition-all duration-300 flex flex-col items-start">
              <div class="p-3 rounded-xl bg-primary/10 text-primary mb-4 group-hover:scale-110 group-hover:bg-primary group-hover:text-primary-foreground transition-all">
                <Sparkles class="w-6 h-6" />
              </div>
              <h3 class="text-lg font-bold text-foreground mb-2">{{ feat.title }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">{{ feat.desc }}</p>
            </div>
          </template>
        </div>
      </div>

      <!-- 6. CALL TO ACTION (CTA) BLOCK -->
      <div
        v-else-if="block.type === 'cta' || block.type === 'call_to_action'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-cta-block w-full rounded-3xl bg-gradient-to-r from-primary/15 via-primary/5 to-background border border-primary/20 p-8 sm:p-12 flex flex-col md:flex-row items-center justify-between gap-6 shadow-md"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div class="space-y-2 text-center md:text-left max-w-xl">
          <h3 class="text-2xl sm:text-3xl font-black text-foreground">
            {{ getSettingStr(block, 'title', 'Siap Meningkatkan Performa Website Anda?') }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ getSettingStr(block, 'description', 'Hubungi kami sekarang untuk konsultasi gratis dan dapatkan solusi terbaik.') }}
          </p>
        </div>
        <a
          :href="getSettingStr(block, 'button_url', '#')"
          class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-primary text-primary-foreground font-bold text-sm shadow hover:shadow-lg hover:scale-105 transition-all shrink-0"
        >
          <span>{{ getSettingStr(block, 'button_text', 'Hubungi Kami') }}</span>
          <ArrowRight class="w-4 h-4" />
        </a>
      </div>

      <!-- 7. FAQ / ACCORDION BLOCK -->
      <div
        v-else-if="block.type === 'faq' || block.type === 'accordion' || block.type === 'toggle'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-faq-block w-full max-w-3xl mx-auto py-4 space-y-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="text-center mb-8 space-y-2">
          <h2 class="text-2xl sm:text-3xl font-black text-foreground">{{ getSettingStr(block, 'title', 'Pertanyaan Umum (FAQ)') }}</h2>
          <p v-if="getSettingStr(block, 'subtitle')" class="text-sm text-muted-foreground">{{ getSettingStr(block, 'subtitle') }}</p>
        </div>

        <div class="space-y-3">
          <template v-for="(item, qIdx) in getSampleFaqs(getSettingNum(block, 'count', 4))" :key="qIdx">
            <div class="border border-border rounded-2xl bg-card overflow-hidden transition-all shadow-sm">
              <button
                type="button"
                class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-foreground hover:text-primary transition-colors"
                @click="toggleFaq(index, qIdx)"
              >
                <span class="text-sm sm:text-base">{{ item.q }}</span>
                <ChevronDown
                  class="w-4 h-4 text-muted-foreground transition-transform duration-300 shrink-0"
                  :class="{ 'rotate-180 text-primary': isFaqOpen(index, qIdx) }"
                />
              </button>
              <div v-if="isFaqOpen(index, qIdx)" class="px-5 pb-5 text-sm text-muted-foreground leading-relaxed border-t border-border/40 pt-4 animate-in fade-in slide-in-from-top-2">
                {{ item.a }}
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- 8. TESTIMONIALS BLOCK -->
      <div
        v-else-if="block.type === 'testimonial' || block.type === 'testimonials'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-testimonials-block w-full py-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="text-center max-w-2xl mx-auto mb-10 space-y-2">
          <h2 class="text-2xl sm:text-3xl font-black text-foreground">{{ getSettingStr(block, 'title', 'Apa Kata Klien Kami') }}</h2>
          <p v-if="getSettingStr(block, 'subtitle')" class="text-sm text-muted-foreground">{{ getSettingStr(block, 'subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <template v-for="(testi, tIdx) in getSampleTestimonials(getSettingNum(block, 'count', 3))" :key="tIdx">
            <div class="p-6 rounded-2xl border border-border bg-card/70 flex flex-col justify-between shadow-sm hover:shadow-md transition-all">
              <div class="space-y-3 mb-6">
                <div class="flex items-center gap-1 text-amber-500">
                  <Star v-for="s in 5" :key="s" class="w-4 h-4 fill-current" />
                </div>
                <p class="text-sm text-foreground/90 italic leading-relaxed">"{{ testi.quote }}"</p>
              </div>
              <div class="flex items-center gap-3 pt-4 border-t border-border/50">
                <div class="w-10 h-10 rounded-full bg-primary/20 text-primary font-bold flex items-center justify-center shrink-0">
                  {{ testi.author.charAt(0) }}
                </div>
                <div>
                  <h4 class="text-sm font-bold text-foreground">{{ testi.author }}</h4>
                  <p class="text-xs text-muted-foreground">{{ testi.role }}</p>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- 9. PRICING TABLE BLOCK -->
      <div
        v-else-if="block.type === 'pricing' || block.type === 'pricing_table'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-pricing-block w-full py-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="text-center max-w-2xl mx-auto mb-10 space-y-2">
          <h2 class="text-2xl sm:text-3xl font-black text-foreground">{{ getSettingStr(block, 'title', 'Paket Harga Fleksibel') }}</h2>
          <p v-if="getSettingStr(block, 'subtitle')" class="text-sm text-muted-foreground">{{ getSettingStr(block, 'subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
          <template v-for="(plan, plIdx) in getSamplePricing()" :key="plIdx">
            <div
              class="relative rounded-3xl p-6 sm:p-8 flex flex-col justify-between border transition-all duration-300"
              :class="plan.popular ? 'border-primary bg-card shadow-xl scale-105 z-10' : 'border-border bg-card/60 shadow-sm'"
            >
              <div v-if="plan.popular" class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-primary text-primary-foreground text-[11px] font-black uppercase tracking-wider shadow">
                Paling Populer
              </div>
              <div class="space-y-4 mb-6">
                <div>
                  <h3 class="text-xl font-bold text-foreground">{{ plan.name }}</h3>
                  <p class="text-xs text-muted-foreground mt-1">{{ plan.desc }}</p>
                </div>
                <div class="text-3xl font-black text-foreground">
                  {{ plan.price }} <span class="text-xs font-normal text-muted-foreground">/bulan</span>
                </div>
                <ul class="space-y-2.5 pt-4 border-t border-border/50 text-xs text-foreground/80">
                  <li v-for="(feature, fIdx) in plan.features" :key="fIdx" class="flex items-center gap-2">
                    <Check class="w-4 h-4 text-emerald-500 shrink-0" />
                    <span>{{ feature }}</span>
                  </li>
                </ul>
              </div>
              <a
                :href="plan.url || '#'"
                class="w-full py-3 rounded-xl font-bold text-xs text-center transition-all shadow-sm block"
                :class="plan.popular ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'border border-border bg-background hover:bg-muted text-foreground'"
              >
                {{ plan.btnText }}
              </a>
            </div>
          </template>
        </div>
      </div>

      <!-- 10. COUNTDOWN TIMER BLOCK -->
      <div
        v-else-if="block.type === 'countdown'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-countdown-block w-full text-center py-6"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <h3 v-if="getSettingStr(block, 'title')" class="text-xl font-bold text-foreground mb-4">
          {{ getSettingStr(block, 'title', 'Promo Berakhir Dalam:') }}
        </h3>
        <div class="flex items-center justify-center gap-3 sm:gap-6 font-mono">
          <div class="flex flex-col items-center p-3 sm:p-5 rounded-2xl bg-card border border-border shadow-sm min-w-[70px] sm:min-w-[90px]">
            <span class="text-2xl sm:text-4xl font-black text-primary">05</span>
            <span class="text-[10px] sm:text-xs text-muted-foreground uppercase font-sans mt-1">Hari</span>
          </div>
          <span class="text-2xl font-bold text-muted-foreground">:</span>
          <div class="flex flex-col items-center p-3 sm:p-5 rounded-2xl bg-card border border-border shadow-sm min-w-[70px] sm:min-w-[90px]">
            <span class="text-2xl sm:text-4xl font-black text-primary">14</span>
            <span class="text-[10px] sm:text-xs text-muted-foreground uppercase font-sans mt-1">Jam</span>
          </div>
          <span class="text-2xl font-bold text-muted-foreground">:</span>
          <div class="flex flex-col items-center p-3 sm:p-5 rounded-2xl bg-card border border-border shadow-sm min-w-[70px] sm:min-w-[90px]">
            <span class="text-2xl sm:text-4xl font-black text-primary">32</span>
            <span class="text-[10px] sm:text-xs text-muted-foreground uppercase font-sans mt-1">Menit</span>
          </div>
          <span class="text-2xl font-bold text-muted-foreground">:</span>
          <div class="flex flex-col items-center p-3 sm:p-5 rounded-2xl bg-card border border-border shadow-sm min-w-[70px] sm:min-w-[90px]">
            <span class="text-2xl sm:text-4xl font-black text-primary">48</span>
            <span class="text-[10px] sm:text-xs text-muted-foreground uppercase font-sans mt-1">Detik</span>
          </div>
        </div>
      </div>

      <!-- 11. VIDEO PLAYER EMBED BLOCK -->
      <div
        v-else-if="block.type === 'video' || block.type === 'video_popup'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-video-block w-full max-w-4xl mx-auto overflow-hidden rounded-3xl border border-border bg-black aspect-video relative shadow-lg"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <iframe
          v-if="getVideoEmbedUrl(getSettingStr(block, 'url'))"
          :src="getVideoEmbedUrl(getSettingStr(block, 'url'))"
          class="w-full h-full border-0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        />
        <div v-else class="w-full h-full flex flex-col items-center justify-center text-white/70 p-6 space-y-3 bg-gradient-to-br from-slate-900 to-slate-950">
          <div class="p-4 rounded-full bg-primary/20 text-primary border border-primary/40">
            <Play class="w-8 h-8 fill-current" />
          </div>
          <p class="text-sm font-semibold">Video Player Placeholder</p>
        </div>
      </div>

      <!-- 12. SOCIAL LINKS / SHARE BUTTONS -->
      <div
        v-else-if="block.type === 'social_links' || block.type === 'share_buttons'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-social-block w-full flex flex-wrap items-center gap-2.5 py-2"
        :class="[getTextAlignClass(getSettingStr(block, 'alignment')), getSettingStr(block, 'css_class')]"
        :style="resolveBlockStyles(block)"
      >
        <template v-for="(soc, sIdx) in getSocialLinks()" :key="sIdx">
          <a
            :href="soc.url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-border bg-card text-foreground/80 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-all text-xs font-semibold shadow-sm"
          >
            <Share2 class="w-3.5 h-3.5 text-primary" />
            <span>{{ soc.name }}</span>
          </a>
        </template>
      </div>

      <!-- 13. HEADING BLOCK -->
      <component
        :is="getSettingStr(block, 'tag', 'h2')"
        v-else-if="block.type === 'heading'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-heading font-black tracking-tight text-foreground"
        :class="[
          getHeadingSizeClass(getSettingStr(block, 'size') || getSettingStr(block, 'tag')),
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        {{ resolveDynamicText(getSettingStr(block, 'text') || getSettingStr(block, 'title')) }}
      </component>

      <!-- 14. TEXT / RICHTEXT BLOCK -->
      <div
        v-else-if="block.type === 'text' || block.type === 'rich_text'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-text prose prose-slate dark:prose-invert max-w-none leading-relaxed"
        :class="[
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
        v-html="resolveDynamicText(getSettingStr(block, 'content') || getSettingStr(block, 'text') || getSettingStr(block, 'body'))"
      />

      <!-- 15. IMAGE BLOCK -->
      <figure
        v-else-if="block.type === 'image'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-image overflow-hidden rounded-2xl"
        :class="[
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        <img
          :src="getSettingStr(block, 'url') || getSettingStr(block, 'src') || getSettingStr(block, 'image')"
          :alt="getSettingStr(block, 'alt') || getSettingStr(block, 'title')"
          class="w-full h-auto object-cover rounded-2xl shadow-sm transition-transform duration-300"
          loading="lazy"
        >
        <figcaption
          v-if="getSettingStr(block, 'caption')"
          class="mt-2 text-xs text-center text-muted-foreground"
        >
          {{ getSettingStr(block, 'caption') }}
        </figcaption>
      </figure>

      <!-- 16. BUTTON BLOCK -->
      <div
        v-else-if="block.type === 'button'"
        class="builder-button-wrapper"
        :class="getTextAlignClass(getSettingStr(block, 'alignment'))"
      >
        <a
          :href="getSettingStr(block, 'url') || getSettingStr(block, 'link') || '#'"
          :target="getSettingBool(block, 'open_in_new_tab') ? '_blank' : '_self'"
          :rel="getSettingBool(block, 'open_in_new_tab') ? 'noopener noreferrer' : undefined"
          class="inline-flex items-center justify-center font-bold px-6 py-3 rounded-xl transition-all shadow-sm hover:shadow hover:scale-[1.02] active:scale-[0.98]"
          :class="[
            getButtonVariantClass(getSettingStr(block, 'variant') || getSettingStr(block, 'style')),
            getSettingStr(block, 'css_class')
          ]"
          :style="resolveBlockStyles(block)"
        >
          {{ getSettingStr(block, 'text') || getSettingStr(block, 'label', 'Click Here') }}
        </a>
      </div>

      <!-- 17. NAVIGATION MENU BLOCK -->
      <nav
        v-else-if="block.type === 'menu' || block.type === 'fullwidth_menu'"
        :id="getSettingStr(block, 'html_id') || undefined"
        :aria-label="getSettingStr(block, 'aria_label', 'Navigation Menu')"
        class="builder-menu-block w-full py-2"
        :class="[
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        <div class="flex flex-wrap items-center gap-3">
          <template v-for="(item, itemIdx) in getMenuItems(getSettingStr(block, 'menuId'))" :key="itemIdx">
            <a
              :href="item.url || '#'"
              class="text-sm font-semibold text-foreground/80 hover:text-primary transition-colors py-1.5 px-3 rounded-lg hover:bg-primary/10"
              :target="item.open_in_new_tab ? '_blank' : '_self'"
            >
              {{ item.title }}
            </a>
          </template>
        </div>
      </nav>

      <!-- 18. DYNAMIC BLOG / QUERY LOOP BLOCK -->
      <div
        v-else-if="block.type === 'blog' || block.type === 'posts' || block.type === 'query_loop'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-blog-block w-full py-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="mb-6">
          <h3 class="text-2xl font-bold text-foreground">
            {{ getSettingStr(block, 'title') }}
          </h3>
        </div>

        <div
          class="grid gap-6"
          :class="getSettingNum(block, 'columns', 3) === 2 ? 'grid-cols-1 md:grid-cols-2' : (getSettingNum(block, 'columns', 3) === 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3')"
        >
          <template v-for="(post, pIdx) in getSamplePosts(getSettingNum(block, 'itemsPerPage', 3))" :key="pIdx">
            <article class="group rounded-2xl border border-border bg-card/60 p-5 shadow-sm transition-all hover:shadow-md hover:border-primary/40 flex flex-col">
              <figure v-if="getSettingBool(block, 'showImage', true)" class="overflow-hidden rounded-xl bg-muted aspect-video mb-4">
                <img :src="post.image" :alt="post.title" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
              </figure>
              <div v-if="getSettingBool(block, 'showCategory', true)" class="mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                  {{ post.category }}
                </span>
              </div>
              <h4 class="text-base font-bold text-foreground line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                <a :href="post.url">{{ post.title }}</a>
              </h4>
              <p v-if="getSettingBool(block, 'showExcerpt', true)" class="text-xs text-muted-foreground line-clamp-3 mb-4 leading-relaxed flex-1">
                {{ post.excerpt }}
              </p>
              <div v-if="getSettingBool(block, 'showDate', true) || getSettingBool(block, 'showAuthor', true)" class="pt-3 border-t border-border/50 flex items-center justify-between text-[11px] text-muted-foreground mt-auto">
                <span v-if="getSettingBool(block, 'showAuthor', true)">{{ post.author }}</span>
                <span v-if="getSettingBool(block, 'showDate', true)">{{ post.date }}</span>
              </div>
            </article>
          </template>
        </div>
      </div>

      <!-- 18B. DATA MODEL STUDIO COLLECTION REPEATER BLOCK -->
      <div
        v-else-if="block.type === 'datamodel_collection' || block.type === 'dynamic_collection'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-datamodel-collection w-full py-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="mb-6 flex items-center justify-between">
          <h3 class="text-2xl font-bold text-foreground">
            {{ getSettingStr(block, 'title') }}
          </h3>
          <span v-if="getSettingStr(block, 'modelSlug')" class="text-xs font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground">
            /dynamic/{{ getSettingStr(block, 'modelSlug') }}
          </span>
        </div>

        <div
          class="grid gap-6"
          :class="getSettingNum(block, 'columns', 3) === 1 ? 'grid-cols-1' : (getSettingNum(block, 'columns', 3) === 2 ? 'grid-cols-1 md:grid-cols-2' : (getSettingNum(block, 'columns', 3) === 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'))"
        >
          <template v-for="(item, itemIdx) in getDataModelRecords(getSettingNum(block, 'itemsPerPage', 6), getSettingStr(block, 'modelSlug'), block)" :key="itemIdx">
            <article class="group rounded-2xl border border-border bg-card/60 p-5 shadow-sm transition-all hover:shadow-md hover:border-primary/40 flex flex-col">
              <figure v-if="getSettingBool(block, 'showImage', true)" class="overflow-hidden rounded-xl bg-muted aspect-video mb-4">
                <img :src="item.image" :alt="item.title" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
              </figure>
              <div v-if="getSettingBool(block, 'showBadge', true) && item.badge" class="mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                  {{ item.badge }}
                </span>
              </div>
              <h4 class="text-base font-bold text-foreground line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                <a :href="item.url || '#'">{{ item.title }}</a>
              </h4>
              <p v-if="getSettingBool(block, 'showDescription', true)" class="text-xs text-muted-foreground line-clamp-3 mb-4 leading-relaxed flex-1">
                {{ item.description }}
              </p>
              <div v-if="getSettingBool(block, 'showLink', true)" class="pt-3 border-t border-border/50 flex items-center justify-between text-xs font-semibold text-primary mt-auto group-hover:translate-x-0.5 transition-transform">
                <span>{{ getSettingStr(block, 'buttonText', 'View Details') }}</span>
                <span aria-hidden="true">&rarr;</span>
              </div>
            </article>
          </template>
        </div>
      </div>

      <!-- 19. FORM PICKER / CONTACT FORM BLOCK -->
      <div
        v-else-if="block.type === 'form_picker' || block.type === 'contact_form'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-form-block w-full max-w-2xl mx-auto rounded-3xl border border-border bg-card p-6 md:p-8 shadow-sm"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingBool(block, 'show_title', true)" class="mb-4">
          <h3 class="text-xl font-bold text-foreground">
            {{ getSettingStr(block, 'title', 'Hubungi Kami') }}
          </h3>
          <p v-if="getSettingBool(block, 'show_description', true) && getSettingStr(block, 'description')" class="text-sm text-muted-foreground mt-1">
            {{ getSettingStr(block, 'description') }}
          </p>
        </div>

        <form class="space-y-4" @submit.prevent="handleFormSubmit">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">Nama Lengkap</label>
            <input type="text" required placeholder="Masukkan nama..." class="w-full h-10 px-3 text-sm rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20">
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">Email</label>
            <input type="email" required placeholder="name@example.com" class="w-full h-10 px-3 text-sm rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20">
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">Pesan</label>
            <textarea rows="4" required placeholder="Tuliskan pesan Anda..." class="w-full p-3 text-sm rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
          </div>
          <button type="submit" class="w-full h-10 rounded-xl bg-primary text-primary-foreground font-semibold text-sm hover:bg-primary/90 transition-colors shadow-sm">
            {{ getSettingStr(block, 'button_text', 'Kirim Pesan') }}
          </button>
        </form>
      </div>

      <!-- 20. DIVIDER / SPACER BLOCK -->
      <div
        v-else-if="block.type === 'divider' || block.type === 'spacer'"
        class="builder-divider w-full"
        :style="{ height: `${getSettingNum(block, 'height', 24)}px` }"
      >
        <hr
          v-if="block.type === 'divider'"
          class="border-border w-full my-auto"
          :style="getSettingStr(block, 'color') ? { borderColor: getSettingStr(block, 'color') } : {}"
        >
      </div>

      <!-- 21. HTML / CODE EMBED BLOCK -->
      <div
        v-else-if="block.type === 'html' || block.type === 'code' || block.type === 'embed'"
        class="builder-raw-html w-full overflow-hidden"
        v-html="getSettingStr(block, 'code') || getSettingStr(block, 'html')"
      />

      <!-- 22. GENERIC CONTAINER FALLBACK -->
      <div
        v-else
        class="builder-generic-block w-full"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue';
import api from '@/engine/api/client';
import { logger } from '@/shared/utils/logger';
import { useToast } from '@/shared/composables/useToast';
import { useMenu } from '@/modules/Content/Layout/composables/useMenu';
import { Sparkles, ArrowRight, Check, Star, Play, Share2, ChevronDown } from 'lucide-vue-next';
import { ConditionEvaluator } from '@/services/ConditionEvaluator';
import type { BlockInstance, BuilderInstance } from '@/types/builder';

const builder = inject<BuilderInstance | null>('builder', null);
const { menus: themeMenus } = useMenu();

const props = defineProps<{
  blocks?: BlockInstance[];
  block?: BlockInstance | null;
  context?: Record<string, any>;
  isPreview?: boolean;
  mode?: 'view' | 'edit';
}>();

const toast = useToast();
const openFaqs = ref<Record<string, boolean>>({});

const toggleFaq = (blockIndex: number, faqIndex: number) => {
  const key = `${blockIndex}-${faqIndex}`;
  openFaqs.value[key] = !openFaqs.value[key];
};

const isFaqOpen = (blockIndex: number, faqIndex: number) => {
  const key = `${blockIndex}-${faqIndex}`;
  return openFaqs.value[key] ?? (faqIndex === 0);
};

const internalBlocks = computed<BlockInstance[]>(() => {
  const rawList = props.block ? [props.block] : (props.blocks || []);
  if (props.mode === 'edit') {
    return rawList;
  }
  return rawList.filter(b => ConditionEvaluator.evaluate(b, props.context || {}));
});

const getSettingStr = (block: BlockInstance, key: string, fallback = ''): string => {
  const val = block.settings?.[key];
  if (typeof val === 'string') return val;
  if (typeof val === 'number') return String(val);
  return fallback;
};

const getSettingBool = (block: BlockInstance, key: string, fallback = false): boolean => {
  const val = block.settings?.[key];
  if (typeof val === 'boolean') return val;
  return fallback;
};

const getSettingNum = (block: BlockInstance, key: string, fallback = 0): number => {
  const val = block.settings?.[key];
  if (typeof val === 'number') return val;
  if (typeof val === 'string' && !isNaN(Number(val))) return Number(val);
  return fallback;
};

const resolveDynamicText = (text: string): string => {
  if (!text || typeof text !== 'string') return '';
  return text;
};

const getVideoEmbedUrl = (url?: string): string => {
  if (!url) return '';
  if (url.includes('youtube.com/watch?v=')) {
    return url.replace('watch?v=', 'embed/');
  }
  if (url.includes('youtu.be/')) {
    return url.replace('youtu.be/', 'www.youtube.com/embed/');
  }
  return url;
};

const getSocialLinks = () => [
  { name: 'WhatsApp', url: 'https://wa.me/' },
  { name: 'Instagram', url: 'https://instagram.com' },
  { name: 'Facebook', url: 'https://facebook.com' },
  { name: 'Twitter / X', url: 'https://x.com' }
];

const getSampleFeatures = (count = 3) => [
  { title: 'Performa Sangat Cepat', desc: 'Arsitektur modern yang dioptimasi untuk kecepatan loading di bawah 1 detik.' },
  { title: 'Desain Responsif', desc: 'Tampilan sempurna di layar smartphone, tablet, laptop, hingga monitor 4K.' },
  { title: 'Keamanan Tingkat Tinggi', desc: 'Perlindungan otomatis dengan SSL, enkripsi data, dan sanitasi input modern.' },
  { title: 'SEO Friendly', desc: 'Struktur semantik HTML5 dan Schema JSON-LD siap terindeks Google seketika.' }
].slice(0, Math.max(1, count));

const getSampleFaqs = (count = 4) => [
  { q: 'Bagaimana cara memulai membuat halaman dengan Visual Builder?', a: 'Cukup pilih Template Halaman, klik tombol Buka di Visual Builder, lalu drag & drop blok yang Anda inginkan.' },
  { q: 'Apakah halaman ini mendukung tampilan mobile?', a: 'Ya, seluruh blok yang dihasilkan 100% responsif dan otomatis menyesuaikan lebar layar perangkat.' },
  { q: 'Bisakah saya menyematkan formulir kontak kustom?', a: 'Tentu, gunakan modul Form Picker untuk memilih formulir yang telah dibuat di modul Reach Forms.' },
  { q: 'Apakah tema website dapat diubah sewaktu-waktu?', a: 'Bisa, seluruh token warna dan tipografi akan otomatis menyesuaikan tema aktif yang dipilih di Theme Customizer.' }
].slice(0, Math.max(1, count));

const getSampleTestimonials = (count = 3) => [
  { author: 'Ahmad Fauzi', role: 'CEO TechCorp', quote: 'Website kami mengalami peningkatan konversi hingga 180% setelah menggunakan landing page baru ini!' },
  { author: 'Siti Rahma', role: 'Marketing Director', quote: 'Proses mendesain halaman sangat cepat dan mudah tanpa perlu keahlian koding.' },
  { author: 'Budi Pratama', role: 'Founder Studio Kreatif', quote: 'Dukungan visual builder dan tema dinamis sangat memudahkan kustomisasi brand.' }
].slice(0, Math.max(1, count));

const getSamplePricing = (): Array<{ name: string; price: string; desc: string; popular: boolean; btnText: string; url?: string; features: string[] }> => [
  { name: 'Starter', price: 'Rp 99.000', desc: 'Cocok untuk personal dan blog portofolio.', popular: false, btnText: 'Pilih Starter', url: '#starter', features: ['1 Domain Website', '5 GB Cloud Storage', 'SSL Gratis', 'Dukungan Komunitas'] },
  { name: 'Profesional', price: 'Rp 249.000', desc: 'Pilihan terbaik untuk bisnis berkembang.', popular: true, btnText: 'Pilih Profesional', url: '#pro', features: ['Domain & Subdomain', '25 GB SSD Storage', 'Visual Page Builder Pro', 'Integrasi Formulir & CRM', 'Prioritas Support 24/7'] },
  { name: 'Enterprise', price: 'Rp 599.000', desc: 'Solusi lengkap skala korporasi.', popular: false, btnText: 'Hubungi Sales', url: '#enterprise', features: ['Unlimited Bandwidth', 'Dedicated Cloud Server', 'Custom API Integrations', 'SLA 99.9% Uptime', 'Account Manager Pribadi'] }
];

const getMenuItems = (menuId?: string): Array<{ title: string; url: string; open_in_new_tab?: boolean }> => {
  if (menuId) {
    const menuList = (builder?.menus?.value || []);
    const foundMenu = menuList.find((m: any) => String(m.id) === String(menuId) || m.slug === menuId)
      || (themeMenus.value ? Object.values(themeMenus.value).find((m: any) => String(m.id) === String(menuId) || m.slug === menuId || m.location === menuId) : null);
    
    if (foundMenu && Array.isArray(foundMenu.items) && foundMenu.items.length > 0) {
      return foundMenu.items.map((item: any) => ({
        title: item.title || item.name || '',
        url: item.url || '/',
        open_in_new_tab: !!item.target || !!item.open_in_new_tab
      }));
    }
  }

  return [
    { title: 'Beranda', url: '/' },
    { title: 'Tentang Kami', url: '/about' },
    { title: 'Layanan', url: '/services' },
    { title: 'Kontak', url: '/contact' }
  ];
};

const getSamplePosts = (count = 3): Array<{ title: string; excerpt: string; date: string; author: string; category: string; image: string; url: string }> => {
  const samples = [
    {
      title: 'Inovasi Teknologi Terkini dalam Pengembangan Website Modern',
      excerpt: 'Mengenal pendekatan modular dan visual builder yang mempercepat produktivitas tim pengembang.',
      date: '19 Agu 2026',
      author: 'Redaksi',
      category: 'Teknologi',
      image: '/assets/themes/janari/news-placeholder.png',
      url: '/blog'
    },
    {
      title: 'Strategi Optimasi SEO & Metadata untuk Meningkatkan Trafik',
      excerpt: 'Panduan lengkap mengatur OpenGraph, Schema JSON-LD, dan struktur konten yang ramah mesin pencari.',
      date: '18 Agu 2026',
      author: 'Admin',
      category: 'Insight',
      image: '/assets/themes/janari/hero-placeholder.png',
      url: '/blog'
    },
    {
      title: 'Penerapan Design Tokens dan Tema Dinamis pada Web Skala Besar',
      excerpt: 'Bagaimana CSS Custom Properties menyatukan Theme Customizer dengan kanvas Visual Builder.',
      date: '17 Agu 2026',
      author: 'Tim Desain',
      category: 'Design System',
      image: '/assets/themes/janari/avatar-placeholder.png',
      url: '/blog'
    }
  ];
  return samples.slice(0, Math.max(1, count));
};

const getSampleDataModelRecords = (count = 6, modelSlug = ''): Array<{ title: string; description: string; badge: string; image: string; url: string }> => {
  const modelName = modelSlug ? modelSlug.replace(/[-_]/g, ' ') : 'Item';
  const capitalized = modelName.charAt(0).toUpperCase() + modelName.slice(1);
  const samples = [
    {
      title: `${capitalized} Premium Solution 01`,
      description: 'Layanan dan solusi data model dinamis yang dirancang untuk mendukung operasional bisnis secara efisien.',
      badge: 'Featured',
      image: '/assets/themes/janari/hero-placeholder.png',
      url: '#'
    },
    {
      title: `${capitalized} Enterprise Service 02`,
      description: 'Implementasi terintegrasi dengan performa tinggi dan skalabilitas sistem yang solid.',
      badge: 'Popular',
      image: '/assets/themes/janari/news-placeholder.png',
      url: '#'
    },
    {
      title: `${capitalized} Strategic Growth 03`,
      description: 'Pendekatan berbasis data untuk mengoptimalkan alur kerja dan konversi pengguna.',
      badge: 'New',
      image: '/assets/themes/janari/avatar-placeholder.png',
      url: '#'
    },
    {
      title: `${capitalized} Advanced Core 04`,
      description: 'Fondasi arsitektur modern yang memastikan keandalan dan keamanan data tingkat tinggi.',
      badge: 'Core',
      image: '/assets/themes/janari/hero-placeholder.png',
      url: '#'
    },
    {
      title: `${capitalized} Cloud Integration 05`,
      description: 'Konektivitas menyeluruh dengan API dan ekosistem multi-platform yang fleksibel.',
      badge: 'Cloud',
      image: '/assets/themes/janari/news-placeholder.png',
      url: '#'
    },
    {
      title: `${capitalized} Smart Architecture 06`,
      description: 'Kustomisasi tanpa batas untuk memenuhi kebutuhan spesifik industri dan organisasi Anda.',
      badge: 'Pro',
      image: '/assets/themes/janari/avatar-placeholder.png',
      url: '#'
    }
  ];
  return samples.slice(0, Math.max(1, count));
};

const dataModelRecordsCache = ref<Record<string, Array<{ title: string; description: string; badge: string; image: string; url: string; raw?: Record<string, any> }>>>({});
const dataModelLoading = ref<Record<string, boolean>>({});

const loadDataModelRecords = async (
  modelSlug: string,
  count = 6,
  fieldMapping?: { titleField?: string; descriptionField?: string; imageField?: string; badgeField?: string; linkField?: string }
) => {
  if (!modelSlug) return;
  const cacheKey = `${modelSlug}_${count}`;
  if (dataModelRecordsCache.value[cacheKey] || dataModelLoading.value[cacheKey]) return;

  dataModelLoading.value[cacheKey] = true;
  try {
    const res = await api.get(`/dynamic/${modelSlug}`, { params: { per_page: count } });
    const payload = res.data?.data || res.data || [];
    const items = Array.isArray(payload) ? payload : (payload.data || []);

    const mapped = items.map((rec: Record<string, any>) => {
      const data = rec.data || rec;
      const title = String(data[fieldMapping?.titleField || 'title'] || data.name || data.subject || data.full_name || rec.title || 'Untitled');
      const description = String(data[fieldMapping?.descriptionField || 'description'] || data.content || data.body || data.excerpt || data.feedback || data.message || '');
      const badge = String(data[fieldMapping?.badgeField || 'badge'] || data.category || data.status || data.rating || data.role || '');
      const image = String(data[fieldMapping?.imageField || 'image'] || data.avatar || data.thumbnail || data.photo || '/assets/themes/janari/hero-placeholder.png');
      const url = String(data[fieldMapping?.linkField || 'link'] || data.url || '#');
      return { title, description, badge, image, url, raw: data };
    });

    if (mapped.length > 0) {
      dataModelRecordsCache.value[cacheKey] = mapped;
    }
  } catch (err) {
    logger.warning(`[BlockRenderer] Failed to load data model '${modelSlug}':`, err);
  } finally {
    dataModelLoading.value[cacheKey] = false;
  }
};

const getDataModelRecords = (
  count = 6,
  modelSlug = '',
  block?: BlockInstance
): Array<{ title: string; description: string; badge: string; image: string; url: string }> => {
  if (!modelSlug) return getSampleDataModelRecords(count, modelSlug);

  const titleField = block ? getSettingStr(block, 'titleField') : '';
  const descriptionField = block ? getSettingStr(block, 'descriptionField') : '';
  const imageField = block ? getSettingStr(block, 'imageField') : '';
  const badgeField = block ? getSettingStr(block, 'badgeField') : '';
  const linkField = block ? getSettingStr(block, 'linkField') : '';

  const cacheKey = `${modelSlug}_${count}`;
  if (!dataModelRecordsCache.value[cacheKey] && !dataModelLoading.value[cacheKey]) {
    loadDataModelRecords(modelSlug, count, { titleField, descriptionField, imageField, badgeField, linkField });
  }

  return dataModelRecordsCache.value[cacheKey] || getSampleDataModelRecords(count, modelSlug);
};

const handleFormSubmit = () => {
  toast.success.default('Formulir berhasil dikirim!');
};

const resolveBlockStyles = (block: BlockInstance): Record<string, string> => {
  const styles: Record<string, string> = {};

  const bgColor = getSettingStr(block, 'background_color');
  if (bgColor) styles.backgroundColor = bgColor;

  const txtColor = getSettingStr(block, 'text_color');
  if (txtColor) styles.color = txtColor;

  const padTop = getSettingNum(block, 'padding_top');
  if (padTop) styles.paddingTop = `${padTop}px`;

  const padBot = getSettingNum(block, 'padding_bottom');
  if (padBot) styles.paddingBottom = `${padBot}px`;

  const marTop = getSettingNum(block, 'margin_top');
  if (marTop) styles.marginTop = `${marTop}px`;

  const marBot = getSettingNum(block, 'margin_bottom');
  if (marBot) styles.marginBottom = `${marBot}px`;

  return styles;
};

const getRowGridClass = (block: BlockInstance): string => {
  const layout = getSettingStr(block, 'layout') || String(block.settings?.columns || '');
  const childCount = Array.isArray(block.children) ? block.children.length : 1;

  if (layout === '1/3_2/3' || layout === '1/3-2/3' || layout === '1-2') {
    return 'grid-cols-1 md:grid-cols-12 md:[&>*:first-child]:col-span-4 md:[&>*:last-child]:col-span-8';
  }
  if (layout === '2/3_1/3' || layout === '2/3-1/3' || layout === '2-1') {
    return 'grid-cols-1 md:grid-cols-12 md:[&>*:first-child]:col-span-8 md:[&>*:last-child]:col-span-4';
  }
  if (layout === '1/4_3/4' || layout === '1/4-3/4' || layout === '1-3') {
    return 'grid-cols-1 md:grid-cols-12 md:[&>*:first-child]:col-span-3 md:[&>*:last-child]:col-span-9';
  }
  if (layout === '3/4_1/4' || layout === '3/4-1/4' || layout === '3-1') {
    return 'grid-cols-1 md:grid-cols-12 md:[&>*:first-child]:col-span-9 md:[&>*:last-child]:col-span-3';
  }

  if (layout.includes('1/2') || layout === '2' || layout === '1-1' || childCount === 2) {
    return 'grid-cols-1 md:grid-cols-2';
  }
  if (layout.includes('1/3') || layout === '3' || layout === '1-1-1' || childCount === 3) {
    return 'grid-cols-1 md:grid-cols-3';
  }
  if (layout.includes('1/4') || layout === '4' || layout === '1-1-1-1' || childCount === 4) {
    return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';
  }
  if (layout.includes('1/5') || layout === '5' || childCount === 5) {
    return 'grid-cols-1 sm:grid-cols-2 md:grid-cols-5';
  }
  if (layout.includes('1/6') || layout === '6' || childCount === 6) {
    return 'grid-cols-1 sm:grid-cols-3 md:grid-cols-6';
  }

  return 'grid-cols-1';
};

const getHeadingSizeClass = (size?: string): string => {
  switch (size) {
    case 'h1':
    case 'xlarge':
      return 'text-4xl md:text-5xl lg:text-6xl';
    case 'h2':
    case 'large':
      return 'text-3xl md:text-4xl';
    case 'h3':
    case 'medium':
      return 'text-2xl md:text-3xl';
    case 'h4':
    case 'small':
      return 'text-xl md:text-2xl';
    case 'h5':
      return 'text-lg md:text-xl';
    default:
      return 'text-2xl md:text-3xl';
  }
};

const getTextAlignClass = (alignment?: string): string => {
  switch (alignment) {
    case 'center':
      return 'text-center justify-center';
    case 'right':
      return 'text-right justify-end';
    case 'justify':
      return 'text-justify';
    default:
      return 'text-left justify-start';
  }
};

const getButtonVariantClass = (variant?: string): string => {
  switch (variant) {
    case 'secondary':
      return 'bg-secondary text-secondary-foreground hover:bg-secondary/80';
    case 'outline':
      return 'border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground';
    case 'ghost':
      return 'bg-transparent text-primary hover:bg-primary/10';
    default:
      return 'bg-primary text-primary-foreground hover:bg-primary/90';
  }
};
</script>
