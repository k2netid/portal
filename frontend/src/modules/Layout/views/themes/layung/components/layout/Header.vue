<template>
  <header
    ref="headerRef"
    data-ja-customizer-target="header"
    :class="[
      'relative z-[100]',
      'w-full border-b border-border/80 transition-colors shadow-sm overflow-visible',
      headerStyleClasses,
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-visible">
      <div class="flex items-center justify-between h-14 overflow-visible">
        <router-link
          to="/"
          class="flex items-center gap-2.5 group focus:outline-none shrink-0"
        >
          <BrandMark
            v-if="siteLogo && brandingDisplay !== 'text_only'"
            :src="siteLogo"
            :alt="displayCompanyName"
          />
          <div
            v-if="brandingDisplay !== 'logo_only'"
            class="flex flex-col"
          >
            <span class="text-[13px] font-medium tracking-tight text-foreground font-heading leading-none group-hover:text-primary transition-colors">
              {{ displayCompanyName }}
            </span>
            <span class="text-[10px] text-muted-foreground font-medium flex items-center gap-1.5 mt-px leading-none font-mono">
              <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" />
              <span class="truncate max-w-[220px] sm:max-w-xs">{{ displayTagline }}</span>
            </span>
          </div>
        </router-link>

        <nav
          v-if="isDesktop"
          data-ja-customizer-target="nav"
          class="hidden lg:flex items-center gap-1 overflow-visible relative z-[105]"
          :aria-label="tt('header.navAria', 'Navigasi utama')"
        >
          <template
            v-for="(item, idx) in navItems"
            :key="String(item.id || item.title || item.url)"
          >
            <div
              v-if="item.children && item.children.length > 0"
              class="relative overflow-visible"
              @mouseenter="setDropdown(String(item.id || item.title || idx))"
              @mouseleave="setDropdown(null)"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="layung-nav-link px-2.5 py-1 rounded-lg text-[13px] font-medium transition-colors inline-flex items-center gap-1.5 focus:outline-none whitespace-nowrap shrink-0 cursor-pointer"
                :class="isNavItemActive(item, route) || activeDesktopDropdown === String(item.id || item.title || idx) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown
                  class="w-3.5 h-3.5 opacity-60 transition-transform duration-200"
                  :class="activeDesktopDropdown === String(item.id || item.title || idx) ? 'rotate-180 text-primary' : ''"
                />
              </a>
              <router-link
                v-else-if="item.url"
                :to="resolvePublicMenuTo(item.url)"
                class="layung-nav-link px-2.5 py-1 rounded-lg text-[13px] font-medium transition-colors inline-flex items-center gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) || activeDesktopDropdown === String(item.id || item.title || idx) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
                @click="setDropdown(null)"
              >
                <span>{{ item.title }}</span>
                <ChevronDown
                  class="w-3.5 h-3.5 opacity-60 transition-transform duration-200"
                  :class="activeDesktopDropdown === String(item.id || item.title || idx) ? 'rotate-180 text-primary' : ''"
                />
              </router-link>
              <button
                v-else
                type="button"
                class="layung-nav-link px-2.5 py-1 rounded-lg text-[13px] font-medium transition-colors inline-flex items-center gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) || activeDesktopDropdown === String(item.id || item.title || idx) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown
                  class="w-3.5 h-3.5 opacity-60 transition-transform duration-200"
                  :class="activeDesktopDropdown === String(item.id || item.title || idx) ? 'rotate-180 text-primary' : ''"
                />
              </button>

              <transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-1 scale-95"
              >
                <div
                  v-if="activeDesktopDropdown === String(item.id || item.title || idx)"
                  class="absolute top-full pt-2 z-[150]"
                  :class="idx >= 3 ? 'right-0 origin-top-right' : 'left-0 origin-top-left'"
                >
                  <div class="absolute -top-3 inset-x-0 h-4 bg-transparent" />
                  <div class="layung-panel p-2 min-w-[280px] shadow-2xl border border-border/90 bg-card rounded-2xl space-y-1">
                    <template
                      v-for="child in item.children"
                      :key="String(child.id || child.title || child.url)"
                    >
                      <a
                        v-if="isExternalLink(child.url)"
                        :href="child.url || '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-muted/70 transition-colors group/item"
                        @click="setDropdown(null)"
                      >
                        <div>
                          <div class="text-xs font-bold text-foreground group-hover/item:text-primary transition-colors">
                            {{ child.title }}
                          </div>
                          <div
                            v-if="child.description"
                            class="text-[11px] text-muted-foreground line-clamp-1"
                          >
                            {{ child.description }}
                          </div>
                        </div>
                      </a>
                      <router-link
                        v-else
                        :to="resolvePublicMenuTo(child.url)"
                        class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-muted/70 transition-colors group/item focus:outline-none"
                        @click="setDropdown(null)"
                      >
                        <div>
                          <div
                            class="text-xs font-bold text-foreground group-hover/item:text-primary transition-colors"
                            :class="isDropdownChildActive(child, item.children || [], route) ? '!text-primary font-black' : ''"
                          >
                            {{ child.title }}
                          </div>
                          <div
                            v-if="child.description"
                            class="text-[11px] text-muted-foreground line-clamp-1"
                          >
                            {{ child.description }}
                          </div>
                        </div>
                      </router-link>
                    </template>
                  </div>
                </div>
              </transition>
            </div>

            <a
              v-else-if="isExternalLink(item.url)"
              :href="item.url || '#'"
              target="_blank"
              rel="noopener noreferrer"
              class="layung-nav-link px-2.5 py-1 rounded-lg text-[13px] font-medium text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors inline-flex items-center gap-1.5 whitespace-nowrap shrink-0"
            >
              <span>{{ item.title }}</span>
            </a>
            <router-link
              v-else
              :to="resolvePublicMenuTo(item.url)"
              class="layung-nav-link px-2.5 py-1 rounded-lg text-[13px] font-medium text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors inline-flex items-center gap-1.5 whitespace-nowrap shrink-0"
              :class="isNavItemActive(item, route) ? '!text-primary !bg-primary/10 !font-bold' : ''"
            >
              <span>{{ item.title }}</span>
            </router-link>
          </template>
        </nav>

        <div class="flex items-center gap-1.5 sm:gap-2 overflow-visible relative z-[105]">
          <DropdownMenu v-if="isDesktop">
            <DropdownMenuTrigger
              class="px-2 py-1 rounded-lg text-xs font-medium border border-border/80 text-muted-foreground hover:text-foreground hover:bg-muted transition-colors inline-flex items-center gap-1.5 focus:outline-none font-mono"
              :aria-label="tt('header.selectLanguage', 'Bahasa')"
            >
              <Globe class="w-3.5 h-3.5 text-primary" />
              <span class="uppercase">{{ currentLanguageCode }}</span>
              <ChevronDown class="w-3 h-3 opacity-50" />
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              :side-offset="10"
              class="w-52"
            >
              <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                class="flex items-center gap-3 cursor-pointer"
                :class="{ 'bg-primary/5 text-primary font-semibold': currentLanguageCode === lang.code }"
                @click.stop="handleSelectLanguage(lang.code)"
              >
                <span class="text-base leading-none">{{ getLanguageFlag(lang) }}</span>
                <span class="flex-1 text-sm">{{ lang.native_name || lang.name }}</span>
                <Check
                  v-if="currentLanguageCode === lang.code"
                  class="w-4 h-4 text-primary shrink-0"
                />
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <ThemeToggle />

          <template v-if="headerCtaText">
            <Button
              v-if="isExternalLink(headerCtaUrl)"
              as="a"
              :href="headerCtaUrl"
              target="_blank"
              rel="noopener noreferrer"
              variant="primary"
              size="sm"
              class="hidden sm:inline-flex font-semibold shadow-sm hover:shadow-md transition-all"
            >
              {{ headerCtaText }}
            </Button>
            <Button
              v-else
              as="router-link"
              :to="resolvePublicMenuTo(headerCtaUrl)"
              variant="primary"
              size="sm"
              class="hidden sm:inline-flex font-semibold shadow-sm hover:shadow-md transition-all"
            >
              {{ headerCtaText }}
            </Button>
          </template>

          <Button
            v-if="memberEnabled && memberStore.isAuthenticated"
            as="router-link"
            to="/member/profile"
            variant="outline"
            size="sm"
            class="hidden md:inline-flex"
          >
            {{ memberStore.member?.name || tt('header.account', 'Akun Klien') }}
          </Button>
          <Button
            v-else-if="memberEnabled"
            as="a"
            :href="loginUrl"
            variant="outline"
            size="sm"
            class="hidden md:inline-flex font-semibold"
          >
            {{ loginLabel }}
          </Button>

          <button
            v-if="!isDesktop"
            ref="mobileMenuButtonRef"
            type="button"
            class="lg:hidden p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors focus:outline-none"
            :aria-expanded="mobileMenuOpen"
            aria-controls="layung-mobile-drawer"
            :aria-label="mobileMenuOpen
              ? tt('header.closeMenuAria', 'Tutup menu')
              : tt('header.openMenuAria', 'Buka menu')"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <Menu
              v-if="!mobileMenuOpen"
              class="w-6 h-6"
            />
            <X
              v-else
              class="w-6 h-6"
            />
          </button>
        </div>
      </div>
    </div>

    <teleport
      to="body"
      :disabled="isBuilder"
    >
      <div
        v-if="mobileMenuOpen && !isDesktop"
        class="fixed inset-0 z-[9999] flex justify-end"
      >
        <!-- Frosted Dark Backdrop -->
        <transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
          appear
        >
          <div
            class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"
            aria-hidden="true"
            @click="closeMobileMenu"
          />
        </transition>

        <!-- Slide-out Cyberpunk Telecom Drawer -->
        <transition
          enter-active-class="transition duration-300 ease-out transform"
          enter-from-class="translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transition duration-200 ease-in transform"
          leave-from-class="translate-x-0"
          leave-to-class="translate-x-full"
          appear
        >
          <div
            id="layung-mobile-drawer"
            ref="mobileDrawerRef"
            role="dialog"
            aria-modal="true"
            aria-labelledby="layung-mobile-drawer-title"
            class="relative z-10 w-full max-w-sm sm:max-w-md bg-slate-950 text-slate-100 h-full flex flex-col shadow-2xl border-l border-slate-800/90 overflow-hidden"
            tabindex="-1"
            @keydown="onMobileDrawerKeydown"
          >
            <!-- Ambient Background Glows -->
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-sky-500/10 rounded-full blur-3xl pointer-events-none" />

            <!-- 1. Drawer Header -->
            <div class="flex items-center justify-between h-16 px-5 border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-md relative z-10 shrink-0">
              <div class="flex items-center gap-3">
                <BrandMark
                  v-if="siteLogo && brandingDisplay !== 'text_only'"
                  :src="siteLogo"
                  :alt="displayCompanyName"
                  class="h-8 w-auto shrink-0"
                />
                <div class="flex flex-col">
                  <span
                    id="layung-mobile-drawer-title"
                    class="text-sm font-black font-heading tracking-tight text-white leading-none"
                  >
                    {{ displayCompanyName }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5 mt-1 leading-none">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0" />
                    <span>AS153992 Peering</span>
                  </span>
                </div>
              </div>

              <button
                type="button"
                class="p-2 rounded-xl text-slate-400 hover:text-white bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors"
                :aria-label="tt('header.closeMenuAria', 'Tutup menu')"
                @click="mobileMenuOpen = false"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- 2. Interactive Feature Shortcut: Bandwidth Simulator -->
            <div class="px-5 pt-4 shrink-0 relative z-10">
              <router-link
                to="/pricing/isp#calculator"
                class="group block p-3 rounded-2xl bg-gradient-to-r from-cyan-500/15 via-sky-500/10 to-slate-900/80 border border-cyan-500/30 hover:border-cyan-400 transition-all shadow-sm"
                @click="mobileMenuOpen = false"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/40 shrink-0">
                      <Sparkles class="w-4 h-4" />
                    </div>
                    <div>
                      <span class="text-xs font-bold text-white group-hover:text-cyan-300 transition-colors block font-heading">
                        Simulator Bandwidth Kami
                      </span>
                      <span class="text-[10px] text-slate-400 font-mono block">
                        Hitung estimasi Mbps & paket ideal
                      </span>
                    </div>
                  </div>
                  <ChevronRight class="w-4 h-4 text-cyan-400 group-hover:translate-x-0.5 transition-transform shrink-0" />
                </div>
              </router-link>
            </div>

            <!-- 3. Navigation Links List -->
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-1.5 relative z-10">
              <nav class="space-y-1.5 font-sans">
                <template
                  v-for="item in navItems"
                  :key="String(item.id || item.title || item.url)"
                >
                  <!-- Item with Submenu (e.g. Layanan, Paket & Harga) -->
                  <div
                    v-if="item.children && item.children.length > 0"
                    class="rounded-xl overflow-hidden transition-colors"
                    :class="isMobileSubmenuOpen(item) ? 'bg-slate-900/70 border border-slate-800/80' : ''"
                  >
                    <button
                      type="button"
                      class="w-full px-3.5 py-2.5 text-left text-sm font-semibold flex items-center justify-between rounded-xl transition-all"
                      :class="isMobileSubmenuOpen(item)
                        ? 'text-cyan-300 font-bold'
                        : 'text-slate-300 hover:text-white hover:bg-slate-900/50'"
                      :aria-expanded="isMobileSubmenuOpen(item)"
                      @click="toggleMobileSubmenu(item)"
                    >
                      <div class="flex items-center gap-3">
                        <component
                          :is="getMenuItemIcon(item)"
                          class="w-4 h-4 text-slate-400 shrink-0"
                          :class="{ '!text-cyan-400': isMobileSubmenuOpen(item) }"
                        />
                        <span>{{ item.title }}</span>
                      </div>
                      <ChevronDown
                        class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0"
                        :class="{ 'rotate-180 text-cyan-400': isMobileSubmenuOpen(item) }"
                      />
                    </button>

                    <!-- Submenu Children list -->
                    <div
                      v-if="isMobileSubmenuOpen(item)"
                      class="px-2 pb-2 pt-1 space-y-1 border-t border-slate-800/60"
                    >
                      <template
                        v-for="child in item.children"
                        :key="String(child.id || child.title || child.url)"
                      >
                        <a
                          v-if="isExternalLink(child.url)"
                          :href="child.url || '#'"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800/70 transition-colors"
                          @click="mobileMenuOpen = false"
                        >
                          <span>{{ child.title }}</span>
                          <ExternalLink class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                        </a>
                        <router-link
                          v-else
                          :to="resolvePublicMenuTo(child.url)"
                          class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors"
                          :class="isDropdownChildActive(child, item.children || [], route)
                            ? 'bg-cyan-500/20 text-cyan-300 font-bold border border-cyan-500/30'
                            : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                          @click="mobileMenuOpen = false"
                        >
                          <div>
                            <span class="block">{{ child.title }}</span>
                            <span
                              v-if="child.description"
                              class="text-[10px] text-slate-400 line-clamp-1 mt-0.5"
                            >
                              {{ child.description }}
                            </span>
                          </div>
                          <ChevronRight class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                        </router-link>
                      </template>
                    </div>
                  </div>

                  <!-- Single Item without Submenu -->
                  <a
                    v-else-if="isExternalLink(item.url)"
                    :href="item.url || '#'"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900/70 transition-all border border-transparent hover:border-slate-800/60"
                    @click="mobileMenuOpen = false"
                  >
                    <div class="flex items-center gap-3">
                      <component
                        :is="getMenuItemIcon(item)"
                        class="w-4 h-4 text-slate-400 shrink-0"
                      />
                      <span>{{ item.title }}</span>
                    </div>
                    <ExternalLink class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                  </a>
                  <router-link
                    v-else
                    :to="resolvePublicMenuTo(item.url)"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all"
                    :class="isNavItemActive(item, route)
                      ? 'bg-cyan-500/15 text-cyan-300 font-bold border border-cyan-500/30 shadow-sm shadow-cyan-500/10'
                      : 'text-slate-300 hover:text-white hover:bg-slate-900/70 border border-transparent hover:border-slate-800/60'"
                    @click="mobileMenuOpen = false"
                  >
                    <div class="flex items-center gap-3">
                      <component
                        :is="getMenuItemIcon(item)"
                        class="w-4 h-4 text-slate-400 shrink-0"
                        :class="{ '!text-cyan-400': isNavItemActive(item, route) }"
                      />
                      <span>{{ item.title }}</span>
                    </div>
                    <ChevronRight class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                  </router-link>
                </template>
              </nav>
            </div>

            <!-- 4. Drawer Footer / Actions Panel -->
            <div class="p-5 border-t border-slate-800/80 bg-slate-950/95 backdrop-blur-md relative z-10 shrink-0 space-y-3.5">
              <!-- Language Selection (Segmented Pill) -->
              <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase text-slate-400 tracking-wider flex items-center gap-1.5">
                  <Globe class="w-3.5 h-3.5 text-cyan-400" />
                  <span>{{ tt('header.selectLanguage', 'Bahasa') }}</span>
                </span>
                <div class="inline-flex p-1 rounded-xl bg-slate-900 border border-slate-800 font-mono text-xs">
                  <button
                    v-for="lang in languages"
                    :key="lang.code"
                    type="button"
                    class="px-2.5 py-1 rounded-lg font-bold uppercase transition-all"
                    :class="currentLanguageCode === lang.code
                      ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/30'
                      : 'text-slate-400 hover:text-white'"
                    @click="handleSelectLanguage(lang.code)"
                  >
                    {{ lang.code }}
                  </button>
                </div>
              </div>

              <!-- Buttons CTA & Login -->
              <div class="space-y-2 pt-1">
                <!-- Primary CTA (Hubungi Sales / Konsultasi) -->
                <template v-if="headerCtaText">
                  <Button
                    v-if="isExternalLink(headerCtaUrl)"
                    as="a"
                    :href="headerCtaUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    variant="primary"
                    size="md"
                    class="w-full font-bold shadow-lg shadow-cyan-500/20 justify-center gap-2"
                    @click="mobileMenuOpen = false"
                  >
                    <span>{{ headerCtaText }}</span>
                    <ArrowRight class="w-4 h-4" />
                  </Button>
                  <Button
                    v-else
                    as="router-link"
                    :to="resolvePublicMenuTo(headerCtaUrl)"
                    variant="primary"
                    size="md"
                    class="w-full font-bold shadow-lg shadow-cyan-500/20 justify-center gap-2"
                    @click="mobileMenuOpen = false"
                  >
                    <span>{{ headerCtaText }}</span>
                    <ArrowRight class="w-4 h-4" />
                  </Button>
                </template>

                <!-- Member / Login Button -->
                <Button
                  v-if="memberEnabled && memberStore.isAuthenticated"
                  as="router-link"
                  to="/member/profile"
                  variant="outline"
                  size="md"
                  class="w-full font-semibold border-slate-800 bg-slate-900/80 text-slate-200 hover:bg-slate-800 justify-center gap-2"
                  @click="mobileMenuOpen = false"
                >
                  <User class="w-4 h-4 text-cyan-400" />
                  <span>{{ memberStore.member?.name || tt('header.account', 'Akun Klien') }}</span>
                </Button>
                <Button
                  v-else-if="memberEnabled"
                  as="a"
                  :href="loginUrl"
                  variant="outline"
                  size="md"
                  class="w-full font-semibold border-slate-800 bg-slate-900/80 text-slate-200 hover:bg-slate-800 hover:text-white justify-center gap-2"
                  @click="mobileMenuOpen = false"
                >
                  <LogIn class="w-4 h-4 text-cyan-400" />
                  <span>{{ loginLabel }}</span>
                </Button>
              </div>

              <!-- 24/7 Hotline Note -->
              <div class="pt-2 border-t border-slate-900 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                <span class="flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block" />
                  <span>NOC 24/7 Monitoring</span>
                </span>
                <a
                  href="https://wa.me/6285136290851"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-cyan-400 hover:underline flex items-center gap-1 font-semibold"
                >
                  <MessageCircle class="w-3.5 h-3.5" />
                  <span>0851-3629-0851</span>
                </a>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </teleport>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, watch, inject, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
  Globe, Menu, X, ChevronDown, ChevronRight, Check,
  Sparkles, User, LogIn, ExternalLink, ArrowRight,
  MessageCircle, Home, Layers, Package, Newspaper, PhoneCall, Info,
} from 'lucide-vue-next';
import BrandMark from '@/modules/Layout/views/themes/layung/components/layout/BrandMark.vue';
import {
  Button,
  ThemeToggle,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/modules/Layout/views/themes/layung/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLanguage } from '@/shared/composables/useLanguage';
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import type { MenuItem } from '@/modules/Layout/types/menu';
import {
  isExternalLink,
  resolvePublicMenuTo,
  isDropdownChildActive,
  isMenuItemActive as isNavItemActive,
} from '@/modules/Layout/utils/menuUrl';

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);

const route = useRoute();
const { locale } = useI18n({ useScope: 'global' });
const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { motion } = useThemeMotion();
const { menus, fetchMenuByIdentifier } = useMenu();
const { setLanguage, initializeLanguage, currentLanguageCode, languages, getLanguageFlag } = useLanguage();
const device = useResponsiveDevice();
const memberStore = useMemberStore();
const authStore = useAuthStore();
const {
  displayCompanyName,
  displayBrandLogo,
  displayTagline,
} = useLayungIdentity();

const isDesktop = computed(() => device.value === 'desktop');
const mobileMenuOpen = ref(false);
const mobileOpenSubmenus = ref<Set<string>>(new Set());
const mobileMenuButtonRef = ref<HTMLButtonElement | null>(null);
const mobileDrawerRef = ref<HTMLElement | null>(null);

const MOBILE_FOCUSABLE =
  'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
};

const focusableInDrawer = (): HTMLElement[] => {
  const root = mobileDrawerRef.value;
  if (!root) return [];
  return Array.from(root.querySelectorAll<HTMLElement>(MOBILE_FOCUSABLE)).filter(
    (el) => !el.hasAttribute('disabled') && el.getAttribute('aria-hidden') !== 'true',
  );
};

const onMobileDrawerKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    event.preventDefault();
    closeMobileMenu();
    return;
  }
  if (event.key !== 'Tab') return;
  const nodes = focusableInDrawer();
  if (nodes.length === 0) return;
  const first = nodes[0]!;
  const last = nodes[nodes.length - 1]!;
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault();
    last.focus();
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first.focus();
  }
};
const activeDesktopDropdown = ref<string | null>(null);

const setDropdown = (key: string | null) => {
  activeDesktopDropdown.value = key;
};

// Auto close dropdown on route change
watch(
  () => route.path,
  () => {
    activeDesktopDropdown.value = null;
    mobileMenuOpen.value = false;
  },
);

const headerRef = ref<HTMLElement>();
const headerStyle = computed(() => String(getSetting('header_style', 'glass') || 'glass'));
const brandingDisplay = computed(() => String(getSetting('branding_display', 'logo_only') || 'logo_only'));

const headerCtaText = computed(() => {
  const raw = getSetting('header_cta_text', '');
  return typeof raw === 'string' ? raw.trim() : '';
});

const headerCtaUrl = computed(() => {
  const raw = getSetting('header_cta_url', '/contact');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/contact';
});

const headerStyleClasses = computed(() => {
  switch (headerStyle.value) {
    case 'solid':
      return 'bg-card border-border shadow-sm';
    case 'transparent':
      return 'bg-transparent border-transparent shadow-none';
    default:
      return 'bg-card/90 backdrop-blur-md border-border/80 shadow-sm';
  }
});

const siteLogo = displayBrandLogo;

const memberEnabled = computed(() => Boolean(getSetting('enable_members', true)));
const contactPageEnabled = computed(() => getSetting('enable_contact', true) !== false);

const isContactMenuPath = (url?: string | null): boolean => {
  if (!url) return false;
  const target = resolvePublicMenuTo(url);
  const withoutQuery = target.split('?')[0] ?? '';
  const path = withoutQuery.split('#')[0]?.replace(/\/+$/, '') || '/';
  return path === '/contact';
};

const loginUrl = computed(() => {
  const raw = getSetting('header_login_url', '/member/login');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/member/login';
});

const loginLabel = computed(() => {
  const raw = getSetting('header_login_label', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('header.signIn', 'Portal Klien');
});

const handleSelectLanguage = async (code: string) => {
  await setLanguage(code);
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('language-changed', { detail: { code: locale.value } }));
  }
};

const normalizeMenuSetting = (value: unknown, fallback: string): string => {
  if (value === null || value === undefined || value === '' || value === 'none') return fallback;
  return String(value);
};

const currentMenuLocation = computed(() => {
  const routeMenu = route.meta?.menu_location as string | undefined;
  if (routeMenu) return routeMenu;
  return normalizeMenuSetting(getSetting('menu_location_header', 'header'), 'header');
});

const filterMenuItems = (items: MenuItem[]): MenuItem[] => {
  if (!Array.isArray(items)) return [];
  return items
    .filter((item) => {
      const meta = item.metadata as Record<string, unknown> | undefined;
      if (meta) {
        if (meta.guest_only && authStore.isAuthenticated) return false;
        if (meta.requires_auth && !authStore.isAuthenticated) return false;
        if (
          meta.required_permission
          && (!authStore.isAuthenticated || !authStore.hasPermission(String(meta.required_permission)))
        ) {
          return false;
        }
      }
      if (!contactPageEnabled.value && isContactMenuPath(item.url)) return false;
      return true;
    })
    .map((item) => {
      let title = item.title;
      const meta = item.metadata as Record<string, unknown> | undefined;
      if (meta) {
        const currentLang = locale.value;
        if (currentLang === 'en' && meta.title_en) title = String(meta.title_en);
        else if (currentLang === 'id' && meta.title_id) title = String(meta.title_id);
      }
      const mappedItem: MenuItem = { ...item, title };
      if (item.children && item.children.length > 0) {
        mappedItem.children = filterMenuItems(item.children);
        if (mappedItem.children.length === 0) {
          delete mappedItem.children;
        }
      }
      return mappedItem;
    });
};

const defaultNavItems = computed((): MenuItem[] => [
  { id: 'ly-nav-home', title: tt('header.home', 'Beranda'), url: '/', type: 'custom', sort_order: 0 },
  { id: 'ly-nav-about', title: tt('header.about', 'Tentang Kami'), url: '/about', type: 'custom', sort_order: 1 },
  {
    id: 'ly-nav-layanan',
    title: tt('header.layanan', 'Layanan'),
    url: '/services',
    type: 'custom',
    sort_order: 2,
    children: [
      { id: 'ly-nav-internet', title: tt('header.services', 'Internet'), url: '/pricing/isp', type: 'custom' },
      { id: 'ly-nav-solusi', title: tt('header.solusi', 'Managed Services'), url: '/solusi', type: 'custom' },
      { id: 'ly-nav-achievement', title: tt('header.achievement', 'SLA & Jaringan'), url: '/achievement', type: 'custom' },
    ],
  },
  {
    id: 'ly-nav-pricing',
    title: tt('header.pricing', 'Paket & Harga'),
    url: '/pricing',
    type: 'custom',
    sort_order: 3,
    children: [
      { id: 'ly-nav-pricing-isp', title: tt('header.pricingIsp', 'Paket Internet'), url: '/pricing/isp', type: 'custom' },
      { id: 'ly-nav-pricing-msp', title: tt('header.pricingMsp', 'Paket MSP'), url: '/pricing/msp', type: 'custom' },
    ],
  },
  { id: 'ly-nav-blog', title: tt('header.blog', 'Berita'), url: '/blog', type: 'custom', sort_order: 4 },
  { id: 'ly-nav-contact', title: tt('header.contact', 'Kontak'), url: '/contact', type: 'custom', sort_order: 5 },
]);

const navItems = computed<MenuItem[]>(() => {
  const menu = menus.value.header || menus.value[currentMenuLocation.value];
  const filtered = filterMenuItems((menu?.items || []) as MenuItem[]);
  return filtered.length > 0 ? filtered : filterMenuItems(defaultNavItems.value);
});

const getMobileMenuKey = (item: MenuItem): string => String(item.id || item.title || item.url || '');
const isMobileSubmenuOpen = (item: MenuItem) => mobileOpenSubmenus.value.has(getMobileMenuKey(item));
const toggleMobileSubmenu = (item: MenuItem) => {
  const key = getMobileMenuKey(item);
  const next = new Set(mobileOpenSubmenus.value);
  if (next.has(key)) next.delete(key);
  else next.add(key);
  mobileOpenSubmenus.value = next;
};

const getMenuItemIcon = (item: MenuItem) => {
  const url = String(item.url || '').toLowerCase();
  const title = String(item.title || '').toLowerCase();

  if (url === '/' || title.includes('beranda') || title.includes('home')) return Home;
  if (url.includes('about') || title.includes('tentang')) return Info;
  if (url.includes('service') || title.includes('layanan')) return Layers;
  if (url.includes('pricing') || title.includes('paket') || title.includes('harga')) return Package;
  if (url.includes('blog') || title.includes('berita') || title.includes('warta')) return Newspaper;
  if (url.includes('contact') || title.includes('kontak')) return PhoneCall;
  return Globe;
};

watch(mobileMenuOpen, async (open) => {
  if (!isDesktop.value && !isBuilder.value) {
    document.body.style.overflow = open ? 'hidden' : '';
  } else {
    document.body.style.overflow = '';
  }
  if (open) {
    await nextTick();
    const nodes = focusableInDrawer();
    (nodes[0] ?? mobileDrawerRef.value)?.focus();
  } else {
    mobileMenuButtonRef.value?.focus();
  }
});

watch(() => route.fullPath, () => {
  mobileMenuOpen.value = false;
  mobileOpenSubmenus.value = new Set();
  activeDesktopDropdown.value = null;
  document.body.style.overflow = '';
});

const menuFetched = ref<Set<string>>(new Set());
watch(currentMenuLocation, async (newLoc) => {
  if (!newLoc || menuFetched.value.has(newLoc)) return;
  menuFetched.value.add(newLoc);
  await fetchMenuByIdentifier(newLoc, 'header');
}, { immediate: true });

onMounted(() => {
  initializeLanguage();
  if (headerRef.value) {
    motion.set(headerRef.value, { y: -28, opacity: 0 });
    motion.to(headerRef.value, { y: 0, opacity: 1, duration: 0.75, ease: 'expo.out', clearProps: 'all' });
  }
});

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<style scoped>
.layung-mobile-menu-enter-active { transition: opacity 0.25s ease; }
.layung-mobile-menu-leave-active { transition: opacity 0.2s ease; }
.layung-mobile-menu-enter-from,
.layung-mobile-menu-leave-to { opacity: 0; }
</style>
