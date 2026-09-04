<template>
  <header
    data-ja-customizer-target="header"
    :class="[
      'relative z-[100]',
      'w-full border-b border-border/80 transition-colors shadow-sm overflow-visible',
      headerStyleClasses,
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-visible">
      <div class="flex items-center justify-between h-16 sm:h-20 overflow-visible">
        <!-- Branding -->
        <router-link
          to="/"
          class="flex items-center gap-2.5 sm:gap-3.5 group focus:outline-none shrink-0"
        >
          <div
            v-if="siteLogo && brandingDisplay !== 'text_only'"
            class="h-9 sm:h-11 w-auto flex items-center shrink-0"
          >
            <img
              :src="siteLogo"
              :alt="brandingDisplay === 'logo_only' ? displaySchoolName : ''"
              class="h-9 sm:h-11 w-auto object-contain transition-transform group-hover:scale-105"
            >
          </div>
          <div
            v-else-if="brandingDisplay !== 'text_only'"
            class="w-9 h-9 sm:w-11 sm:h-11 rounded-2xl bg-[#0f172a] text-amber-400 flex items-center justify-center font-black text-lg sm:text-xl shadow-md border border-slate-700/60 group-hover:scale-105 transition-transform shrink-0"
            aria-hidden="true"
          >
            {{ displaySchoolName.charAt(0).toUpperCase() }}
          </div>
          <div
            v-if="brandingDisplay !== 'logo_only'"
            class="flex flex-col min-w-0"
          >
            <span class="text-base sm:text-lg lg:text-xl font-extrabold tracking-tight text-foreground font-heading leading-tight group-hover:text-primary transition-colors truncate max-w-[190px] xs:max-w-[260px] sm:max-w-none">
              {{ displaySchoolName }}
            </span>
            <span class="hidden sm:flex text-[11px] text-muted-foreground font-semibold items-center gap-1.5 mt-0.5 leading-none">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 shrink-0" />
              <span class="truncate">{{ displayAccreditation }} · {{ displayNpsn }}</span>
            </span>
          </div>
        </router-link>

        <!-- Desktop Navigation -->
        <nav
          v-if="isDesktop"
          data-ja-customizer-target="nav"
          class="hidden lg:flex items-center gap-0.5 xl:gap-1 overflow-visible relative z-[105]"
          :aria-label="tt('header.navAria', 'Navigasi utama')"
        >
          <template
            v-for="(item, idx) in navItems"
            :key="String(item.id || item.title || item.url)"
          >
            <div
              v-if="item.children && item.children.length > 0"
              class="relative overflow-visible group/nav"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="px-2.5 xl:px-3 py-1.5 xl:py-2 rounded-xl text-xs xl:text-sm font-semibold transition-colors inline-flex items-center gap-1 xl:gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 transition-transform duration-200 group-hover/nav:rotate-180 group-hover/nav:text-primary" />
              </a>
              <router-link
                v-else-if="item.url"
                :to="getInternalUrl(item.url)"
                class="px-2.5 xl:px-3 py-1.5 xl:py-2 rounded-xl text-xs xl:text-sm font-semibold transition-colors inline-flex items-center gap-1 xl:gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 transition-transform duration-200 group-hover/nav:rotate-180 group-hover/nav:text-primary" />
              </router-link>
              <button
                v-else
                type="button"
                class="px-2.5 xl:px-3 py-1.5 xl:py-2 rounded-xl text-xs xl:text-sm font-semibold transition-colors inline-flex items-center gap-1 xl:gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 transition-transform duration-200 group-hover/nav:rotate-180 group-hover/nav:text-primary" />
              </button>

              <div
                class="absolute top-full pt-2 z-[120] opacity-0 invisible -translate-y-1 pointer-events-none group-hover/nav:opacity-100 group-hover/nav:visible group-hover/nav:translate-y-0 group-hover/nav:pointer-events-auto group-focus-within/nav:opacity-100 group-focus-within/nav:visible group-focus-within/nav:translate-y-0 group-focus-within/nav:pointer-events-auto transition-all duration-200"
                :class="idx >= 3 ? 'right-0 origin-top-right' : 'left-0 origin-top-left'"
              >
                <div class="absolute -top-3 inset-x-0 h-4 bg-transparent" />
                <div class="sarangenge-panel p-2 min-w-[240px] shadow-2xl border border-border/80 bg-card rounded-2xl space-y-1">
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
                      :to="getInternalUrl(child.url)"
                      active-class=""
                      exact-active-class=""
                      class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-muted/70 transition-colors group/item focus:outline-none"
                      :class="isDropdownChildActive(child, item.children || [], route) ? '!bg-primary/10' : ''"
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
                    </router-link>
                  </template>
                </div>
              </div>
            </div>

            <a
              v-else-if="isExternalLink(item.url)"
              :href="item.url || '#'"
              target="_blank"
              rel="noopener noreferrer"
              class="px-2.5 xl:px-3 py-1.5 xl:py-2 rounded-xl text-xs xl:text-sm font-semibold text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors inline-flex items-center gap-1 xl:gap-1.5 whitespace-nowrap shrink-0"
            >
              <span>{{ item.title }}</span>
            </a>
            <router-link
              v-else
              :to="getInternalUrl(item.url)"
              class="px-2.5 xl:px-3 py-1.5 xl:py-2 rounded-xl text-xs xl:text-sm font-semibold text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors inline-flex items-center gap-1 xl:gap-1.5 whitespace-nowrap shrink-0"
              :class="isNavItemActive(item, route) ? '!text-primary !bg-primary/10 !font-bold' : ''"
            >
              <span>{{ item.title }}</span>
            </router-link>
          </template>
        </nav>

        <!-- Right utilities -->
        <div class="flex items-center gap-2 sm:gap-2.5 overflow-visible relative z-[105]">
          <DropdownMenu v-if="isDesktop">
            <DropdownMenuTrigger
              class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-border/80 text-muted-foreground hover:text-foreground hover:bg-muted transition-colors inline-flex items-center gap-1.5 focus:outline-none"
              :aria-label="tt('header.selectLanguage', 'Bahasa')"
            >
              <Globe class="w-3.5 h-3.5 text-primary" />
              <span class="uppercase font-mono">{{ currentLanguageCode }}</span>
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

          <!-- Portal Login / Account Button -->
          <Button
            v-if="memberEnabled && memberStore.isAuthenticated"
            as="router-link"
            to="/member/profile"
            variant="outline"
            size="sm"
            class="hidden md:inline-flex items-center gap-2 font-semibold rounded-xl"
          >
            <User class="w-3.5 h-3.5 text-primary" />
            <span>{{ memberStore.member?.name || tt('header.account', 'Akun Siswa') }}</span>
          </Button>
          <Button
            v-else-if="memberEnabled"
            as="a"
            :href="loginUrl"
            variant="outline"
            size="sm"
            class="hidden md:inline-flex items-center gap-2 font-semibold rounded-xl border-border/80 hover:border-primary/50 hover:bg-primary/5 hover:text-primary transition-all shadow-sm"
          >
            <LogIn class="w-3.5 h-3.5 text-primary" />
            <span>{{ loginLabel }}</span>
          </Button>

          <!-- Mobile hamburger toggle -->
          <button
            v-if="!isDesktop"
            ref="mobileMenuButtonRef"
            type="button"
            class="lg:hidden p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition-colors focus:outline-none"
            :aria-expanded="mobileMenuOpen"
            aria-controls="sarangenge-mobile-drawer"
            :aria-label="mobileMenuOpen ? tt('header.closeMenuAria', 'Tutup menu') : tt('header.openMenuAria', 'Buka menu')"
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

    <!-- Mobile drawer (teleported — escapes sticky overflow/stacking) -->
    <teleport
      to="body"
      :disabled="isBuilder"
    >
      <div
        v-if="mobileMenuOpen && !isDesktop"
        class="fixed inset-0 z-[9999] flex justify-end"
      >
        <!-- Frosted Dark Backdrop (click to close) -->
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
            class="fixed inset-0 bg-slate-950/75 dark:bg-slate-950/80 backdrop-blur-sm"
            aria-hidden="true"
            @click="closeMobileMenu"
          />
        </transition>

        <!-- Slide-out Drawer Panel -->
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
            id="sarangenge-mobile-drawer"
            ref="mobileDrawerRef"
            role="dialog"
            aria-modal="true"
            aria-labelledby="sarangenge-mobile-drawer-title"
            class="relative z-10 w-full max-w-sm sm:max-w-md bg-card text-card-foreground h-full flex flex-col shadow-2xl border-l border-border/80 overflow-hidden"
            tabindex="-1"
            @keydown="onMobileDrawerKeydown"
          >
            <!-- Ambient Background Glows -->
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-primary/10 rounded-full blur-3xl pointer-events-none" />
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none" />

            <!-- 1. Drawer Header -->
            <div class="flex items-center justify-between h-16 px-5 border-b border-border/80 bg-card/90 backdrop-blur-md relative z-10 shrink-0">
              <div class="flex items-center gap-3 min-w-0">
                <img
                  v-if="siteLogo"
                  :src="siteLogo"
                  :alt="displaySchoolName"
                  class="h-9 w-auto object-contain shrink-0"
                >
                <div class="flex flex-col min-w-0">
                  <span
                    id="sarangenge-mobile-drawer-title"
                    class="text-sm font-bold font-heading tracking-tight text-foreground truncate leading-none"
                  >
                    {{ displaySchoolName }}
                  </span>
                  <span class="text-[11px] text-muted-foreground font-mono flex items-center gap-1.5 mt-1 leading-none">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0" />
                    <span class="truncate">{{ displayAccreditation }}</span>
                  </span>
                </div>
              </div>

              <button
                type="button"
                class="p-2 rounded-xl text-muted-foreground hover:text-foreground bg-muted/60 border border-border/60 hover:bg-muted transition-colors focus:outline-none shrink-0"
                :aria-label="tt('header.closeMenuAria', 'Tutup menu')"
                @click="closeMobileMenu"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- 2. Feature Shortcut: PPDB Portal -->
            <div class="px-5 pt-4 shrink-0 relative z-10">
              <a
                v-if="isExternalLink(ppdbPortalUrl)"
                :href="ppdbPortalUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="group block p-3 rounded-2xl bg-gradient-to-r from-primary/15 via-amber-500/10 to-primary/5 border border-primary/30 hover:border-primary/50 transition-all shadow-sm"
                @click="closeMobileMenu"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-primary/20 text-primary flex items-center justify-center border border-primary/40 shrink-0">
                      <GraduationCap class="w-4 h-4" />
                    </div>
                    <div>
                      <span class="text-xs font-bold text-foreground group-hover:text-primary transition-colors block font-heading">
                        {{ tt('header.ppdbShortcutTitle', 'Portal PPDB Online') }}
                      </span>
                      <span class="text-[10px] text-muted-foreground block">
                        {{ tt('header.ppdbShortcutDesc', 'Informasi seleksi masuk & pendaftaran') }}
                      </span>
                    </div>
                  </div>
                  <ChevronRight class="w-4 h-4 text-primary group-hover:translate-x-0.5 transition-transform shrink-0" />
                </div>
              </a>
              <router-link
                v-else
                :to="getInternalUrl(ppdbPortalUrl)"
                class="group block p-3 rounded-2xl bg-gradient-to-r from-primary/15 via-amber-500/10 to-primary/5 border border-primary/30 hover:border-primary/50 transition-all shadow-sm"
                @click="closeMobileMenu"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-primary/20 text-primary flex items-center justify-center border border-primary/40 shrink-0">
                      <GraduationCap class="w-4 h-4" />
                    </div>
                    <div>
                      <span class="text-xs font-bold text-foreground group-hover:text-primary transition-colors block font-heading">
                        {{ tt('header.ppdbShortcutTitle', 'Portal PPDB Online') }}
                      </span>
                      <span class="text-[10px] text-muted-foreground block">
                        {{ tt('header.ppdbShortcutDesc', 'Informasi seleksi masuk & pendaftaran') }}
                      </span>
                    </div>
                  </div>
                  <ChevronRight class="w-4 h-4 text-primary group-hover:translate-x-0.5 transition-transform shrink-0" />
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
                  <!-- Item with Submenu -->
                  <div
                    v-if="item.children && item.children.length > 0"
                    class="rounded-xl overflow-hidden transition-colors"
                    :class="isMobileSubmenuOpen(item) ? 'bg-muted/60 border border-border/80 shadow-xs' : ''"
                  >
                    <button
                      type="button"
                      class="w-full px-3.5 py-2.5 text-left text-sm font-semibold flex items-center justify-between rounded-xl transition-all"
                      :class="isMobileSubmenuOpen(item)
                        ? 'text-primary font-bold'
                        : 'text-foreground/90 hover:text-foreground hover:bg-muted/50'"
                      :aria-expanded="isMobileSubmenuOpen(item)"
                      @click="toggleMobileSubmenu(item)"
                    >
                      <div class="flex items-center gap-3">
                        <component
                          :is="getMenuItemIcon(item)"
                          class="w-4 h-4 text-muted-foreground shrink-0"
                          :class="{ '!text-primary': isMobileSubmenuOpen(item) }"
                        />
                        <span>{{ item.title }}</span>
                      </div>
                      <ChevronDown
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200 shrink-0"
                        :class="{ 'rotate-180 !text-primary': isMobileSubmenuOpen(item) }"
                      />
                    </button>

                    <!-- Submenu Children List -->
                    <div
                      v-if="isMobileSubmenuOpen(item)"
                      class="px-2 pb-2 pt-1 space-y-1 border-t border-border/60"
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
                          class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-foreground/80 hover:text-foreground hover:bg-muted/70 transition-colors"
                          @click="closeMobileMenu"
                        >
                          <span>{{ child.title }}</span>
                          <ExternalLink class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                        </a>
                        <router-link
                          v-else
                          :to="getInternalUrl(child.url)"
                          class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors"
                          :class="isDropdownChildActive(child, item.children || [], route)
                            ? 'bg-primary/15 text-primary font-bold border border-primary/20'
                            : 'text-foreground/80 hover:text-foreground hover:bg-muted/70'"
                          @click="closeMobileMenu"
                        >
                          <div>
                            <span class="block">{{ child.title }}</span>
                            <span
                              v-if="child.description"
                              class="text-[10px] text-muted-foreground line-clamp-1 mt-0.5"
                            >
                              {{ child.description }}
                            </span>
                          </div>
                          <ChevronRight class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
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
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold text-foreground/90 hover:text-foreground hover:bg-muted/70 transition-all border border-transparent hover:border-border/60"
                    @click="closeMobileMenu"
                  >
                    <div class="flex items-center gap-3">
                      <component
                        :is="getMenuItemIcon(item)"
                        class="w-4 h-4 text-muted-foreground shrink-0"
                      />
                      <span>{{ item.title }}</span>
                    </div>
                    <ExternalLink class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                  </a>
                  <router-link
                    v-else
                    :to="getInternalUrl(item.url)"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all"
                    :class="isNavItemActive(item, route)
                      ? 'bg-primary/10 text-primary font-bold border border-primary/25 shadow-xs'
                      : 'text-foreground/90 hover:text-foreground hover:bg-muted/70 border border-transparent hover:border-border/60'"
                    @click="closeMobileMenu"
                  >
                    <div class="flex items-center gap-3">
                      <component
                        :is="getMenuItemIcon(item)"
                        class="w-4 h-4 text-muted-foreground shrink-0"
                        :class="{ '!text-primary': isNavItemActive(item, route) }"
                      />
                      <span>{{ item.title }}</span>
                    </div>
                    <ChevronRight class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                  </router-link>
                </template>
              </nav>
            </div>

            <!-- 4. Drawer Footer / Actions Panel -->
            <div class="p-5 border-t border-border/80 bg-card/95 backdrop-blur-md relative z-10 shrink-0 space-y-3.5">
              <!-- Language Selection (Segmented Pill) -->
              <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase text-muted-foreground tracking-wider flex items-center gap-1.5">
                  <Globe class="w-3.5 h-3.5 text-primary" />
                  <span>{{ tt('header.selectLanguage', 'Bahasa') }}</span>
                </span>
                <div class="inline-flex p-1 rounded-xl bg-muted border border-border font-mono text-xs">
                  <button
                    v-for="lang in languages"
                    :key="lang.code"
                    type="button"
                    class="px-2.5 py-1 rounded-lg font-bold uppercase transition-all"
                    :class="currentLanguageCode === lang.code
                      ? 'bg-primary text-primary-foreground shadow-xs'
                      : 'text-muted-foreground hover:text-foreground'"
                    @click="handleSelectLanguage(lang.code)"
                  >
                    {{ lang.code }}
                  </button>
                </div>
              </div>

              <!-- Portal Login / Member Button -->
              <div class="pt-1">
                <Button
                  v-if="memberEnabled && memberStore.isAuthenticated"
                  as="router-link"
                  to="/member/profile"
                  variant="outline"
                  size="md"
                  class="w-full font-semibold border-border bg-muted/60 text-foreground hover:bg-muted justify-center gap-2 rounded-xl"
                  @click="closeMobileMenu"
                >
                  <User class="w-4 h-4 text-primary" />
                  <span>{{ memberStore.member?.name || tt('header.account', 'Akun Siswa') }}</span>
                </Button>
                <Button
                  v-else-if="memberEnabled"
                  as="a"
                  :href="loginUrl"
                  variant="primary"
                  size="md"
                  class="w-full font-bold justify-center gap-2 rounded-xl shadow-md"
                  @click="closeMobileMenu"
                >
                  <LogIn class="w-4 h-4" />
                  <span>{{ loginLabel }}</span>
                </Button>
              </div>

              <!-- School Info & Hotline Note -->
              <div class="pt-2 border-t border-border/60 flex items-center justify-between text-[11px] text-muted-foreground font-mono">
                <span class="flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block shrink-0" />
                  <span>{{ displayNpsn }}</span>
                </span>
                <a
                  v-if="whatsAppUrl"
                  :href="whatsAppUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-primary hover:underline flex items-center gap-1 font-semibold"
                >
                  <MessageCircle class="w-3.5 h-3.5" />
                  <span>Hotline PPDB</span>
                </a>
                <a
                  v-else
                  :href="phoneDialHref"
                  class="text-primary hover:underline flex items-center gap-1 font-semibold"
                >
                  <PhoneCall class="w-3.5 h-3.5" />
                  <span>{{ displayPhone }}</span>
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
  User, LogIn, ExternalLink, GraduationCap, Building,
  Newspaper, PhoneCall, Users, BookOpen, Home,
  Briefcase, Award, MessageCircle,
} from 'lucide-vue-next';
import {
  Button,
  ThemeToggle,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLanguage } from '@/shared/composables/useLanguage';
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { MenuItem } from '@/modules/Layout/types/menu';
import {
  isExternalLink,
  getInternalUrl,
  isDropdownChildActive,
  isMenuItemActive as isNavItemActive,
} from '@/modules/Layout/utils/menuUrl';

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);

const route = useRoute();
const { locale } = useI18n({ useScope: 'global' });
const { t: tt } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { menus, fetchMenuByIdentifier } = useMenu();
const { setLanguage, initializeLanguage, currentLanguageCode, languages, getLanguageFlag } = useLanguage();
const device = useResponsiveDevice();
const memberStore = useMemberStore();
const authStore = useAuthStore();
const {
  displaySchoolName,
  displayAccreditation,
  displayNpsn,
  displayPhone,
  phoneDialHref,
  whatsAppUrl,
  ppdbPortalUrl,
  siteLogo,
} = useSarangengeIdentity();

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

const headerStyle = computed(() => String(getSetting('header_style', 'glass') || 'glass'));
const brandingDisplay = computed(() => String(getSetting('branding_display', 'both') || 'both'));

const headerStyleClasses = computed(() => {
  switch (headerStyle.value) {
    case 'solid':
      return 'bg-card border-border';
    case 'transparent':
      return 'bg-transparent border-transparent shadow-none';
    default:
      return 'bg-card/95 backdrop-blur-md';
  }
});

const memberEnabled = computed(() => Boolean(getSetting('enable_members', true)));

const loginUrl = computed(() => {
  const raw = getSetting('header_login_url', '/member/login');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/member/login';
});

const loginLabel = computed(() => {
  const currentLang = locale.value;
  const rawLang = getSetting(`header_login_label_${currentLang}`, '');
  if (typeof rawLang === 'string' && rawLang.trim()) return rawLang.trim();

  const raw = getSetting('header_login_label', '');
  if (
    typeof raw === 'string' &&
    raw.trim() &&
    raw.trim() !== 'Masuk Portal' &&
    raw.trim() !== 'Portal Sign In' &&
    raw.trim() !== 'Lebet Portal'
  ) {
    return raw.trim();
  }
  return tt('header.signIn', 'Masuk Portal');
});

const handleSelectLanguage = async (code: string) => {
  await setLanguage(code);
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('language-changed', { detail: { code: locale.value } }));
  }
};

const normalizeMenuSetting = (value: unknown, fallback: string): string => {
  if (value === null || value === undefined || value === '' || value === 'none') {
    return fallback;
  }
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
      }
      return mappedItem;
    });
};

const defaultNavItems = computed((): MenuItem[] => [
  { id: 'sg-nav-home', title: tt('header.home', 'Beranda'), url: '/', type: 'custom', sort_order: 0 },
  {
    id: 'sg-nav-about',
    title: tt('header.about', 'Tentang Kami'),
    url: '/about',
    type: 'custom',
    sort_order: 1,
    children: [
      { id: 'sg-nav-about-history', title: 'Visi & Sejarah', url: '/about#visi', type: 'custom', description: 'Falsafah dan keunggulan sekolah' },
      { id: 'sg-nav-about-facilities', title: 'Fasilitas & Bengkel', url: '/facilities', type: 'custom', description: 'Laboratorium & bengkel kejuruan' },
      { id: 'sg-nav-about-team', title: tt('header.teachers', 'Guru & Staf'), url: '/tim', type: 'custom', description: 'Pendidik bersertifikasi industri' },
      { id: 'sg-nav-about-achieve', title: tt('header.achievements', 'Prestasi Siswa'), url: '/achievement', type: 'custom', description: 'Medali LKS & juara nasional' },
    ],
  },
  {
    id: 'sg-nav-programs',
    title: 'Program Keahlian',
    url: '/programs',
    type: 'custom',
    sort_order: 2,
    children: [
      { id: 'sg-nav-prog-dpib', title: 'DPIB (Desain Bangunan)', url: '/programs#dpib', type: 'custom', description: 'Arsitektur & Pemodelan BIM' },
      { id: 'sg-nav-prog-titl', title: 'TITL (Teknik Listrik)', url: '/programs#titl', type: 'custom', description: 'Ketenagalistrikan & Otomasi PLC' },
      { id: 'sg-nav-prog-tpm', title: 'TPM (Teknik Pemesinan)', url: '/programs#tpm', type: 'custom', description: 'CNC Milling & Bubut Presisi' },
      { id: 'sg-nav-prog-tkro', title: 'TKRO (Teknik Otomotif)', url: '/programs#tkro', type: 'custom', description: 'Perawatan Kendaraan Ringan' },
      { id: 'sg-nav-prog-tav', title: 'TAV (Audio Video)', url: '/programs#tav', type: 'custom', description: 'Elektronika & Smart IoT' },
      { id: 'sg-nav-prog-tflm', title: 'TFLM (Pengelasan)', url: '/programs#tflm', type: 'custom', description: 'Fabrikasi Logam & Las Industri' },
    ],
  },
  {
    id: 'sg-nav-bkk',
    title: 'BKK & Karir',
    url: '/career',
    type: 'custom',
    sort_order: 3,
  },
  { id: 'sg-nav-blog', title: tt('header.blog', 'Warta'), url: '/blog', type: 'custom', sort_order: 4 },
  { id: 'sg-nav-ppdb', title: 'PPDB Jabar', url: ppdbPortalUrl.value, type: 'custom', sort_order: 5 },
  { id: 'sg-nav-contact', title: tt('header.contact', 'Kontak'), url: '/contact', type: 'custom', sort_order: 6 },
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
  if (url.includes('about') || title.includes('profil') || title.includes('tentang') || title.includes('sejarah') || title.includes('visi')) return Building;
  if (url.includes('program') || title.includes('keahlian') || title.includes('jurusan') || title.includes('kurikulum')) return GraduationCap;
  if (url.includes('bkk') || url.includes('career') || title.includes('karir') || title.includes('alumni')) return Briefcase;
  if (url.includes('blog') || title.includes('berita') || title.includes('warta') || title.includes('kabar') || title.includes('artikel')) return Newspaper;
  if (url.includes('contact') || title.includes('kontak') || title.includes('hubungi')) return PhoneCall;
  if (url.includes('facil') || title.includes('fasilitas') || title.includes('bengkel') || title.includes('lab')) return BookOpen;
  if (url.includes('tim') || title.includes('guru') || title.includes('staf') || title.includes('siswa')) return Users;
  if (url.includes('achieve') || title.includes('prestasi')) return Award;
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
});

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>
