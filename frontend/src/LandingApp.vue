<template>
  <div class="landing-root min-h-screen flex flex-col text-slate-900">
    <div
      class="pointer-events-none fixed inset-0 -z-10"
      aria-hidden="true"
      style="background:
        radial-gradient(ellipse 80% 50% at 20% -10%, rgba(15, 118, 110, 0.18), transparent 55%),
        radial-gradient(ellipse 60% 40% at 90% 10%, rgba(232, 163, 23, 0.16), transparent 50%),
        linear-gradient(180deg, #f4faf8 0%, #eef2f1 100%);"
    />

    <header class="px-6 sm:px-10 pt-8 flex items-center justify-between gap-4">
      <p class="text-xl sm:text-2xl font-semibold tracking-tight">
        Jejakawan
      </p>
      <a
        href="/auth/console-sign-in"
        class="text-sm font-medium text-teal-800/80 hover:text-teal-900 underline-offset-4 hover:underline"
      >
        Console
      </a>
    </header>

    <main class="flex-1 flex flex-col justify-center px-6 sm:px-10 pb-20 pt-16 max-w-3xl">
      <p class="text-sm font-semibold tracking-wide text-teal-800/70 uppercase mb-4">
        Core Engine
      </p>
      <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-[1.1] text-slate-900">
        Siap dijalankan.
      </h1>
      <p class="mt-5 text-lg text-slate-600 leading-relaxed max-w-xl">
        Situs publik belum diaktifkan. Saat pack <strong class="font-semibold text-slate-800">Site</strong>
        (tema CMS) aktif, halaman ini diganti oleh theme publik. Console operator tetap di jalur terpisah.
      </p>

      <div class="mt-10 flex flex-col sm:flex-row gap-3">
        <a
          href="/auth/console-sign-in"
          class="inline-flex items-center justify-center rounded-xl bg-teal-800 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-900 transition-colors"
        >
          Buka console
        </a>
        <a
          href="/dash"
          class="inline-flex items-center justify-center rounded-xl border border-slate-300/80 bg-white/70 px-5 py-3 text-sm font-semibold text-slate-800 hover:bg-white transition-colors"
        >
          Dashboard
        </a>
      </div>
    </main>

    <footer class="px-6 sm:px-10 py-6 text-xs text-slate-500">
      Login console tidak dipasang di apex <code class="text-slate-700">/</code> — gunakan
      <code class="text-slate-700">/auth/console-sign-in</code>.
    </footer>
  </div>
</template>

<script setup lang="ts">
document.title = 'Jejakawan';
</script>
