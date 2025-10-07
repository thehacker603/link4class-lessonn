<?php
// Link4Class — PHP Landing (Liquid Glass Futuristic)
// Enhanced visuals + extra sections + controls (no framework)
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Link4Class — Learn together. Build skills faster.</title>
  <!-- Tailwind CDN (for demo; for prod use a proper build) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: {} }, corePlugins: { preflight: true } };
  </script>
  <style>
    html{scroll-behavior:smooth}
    :root{
      --lg-blur:26px;      /* liquid glass blur */
      --lg-caustic:0.8;    /* intensity */
      --lg-ripple:0.22;    /* hover ripple opacity */
      --lg-grid:0.10;      /* neon grid opacity */
      overflow-x: hidden;
    }
    @keyframes marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}
    @keyframes upReveal{0%{transform:translateY(40px) rotateX(-30deg);opacity:0}100%{transform:translateY(0) rotateX(0);opacity:1}}
    @keyframes bounceDot{0%,100%{transform:translateY(0)}50%{transform:translateY(6px)}}
    @keyframes sweep{0%{transform:translateX(-20%)}100%{transform:translateX(120%)}}
    .reveal [data-reveal-item]{opacity:0;transform:translateY(18px);transition:opacity .6s cubic-bezier(.22,1,.36,1),transform .6s cubic-bezier(.22,1,.36,1)}
    .reveal.revealed [data-reveal-item]{opacity:1;transform:translateY(0)}
    .tilt-inner{transition:transform .2s ease-out;transform-style:preserve-3d;will-change:transform}
    .btn-ripple::before{content:"";position:absolute;inset:0;opacity:0;transition:opacity .3s;pointer-events:none;background:radial-gradient(240px 240px at var(--mx,50%) var(--my,50%), rgba(255,255,255,var(--lg-ripple)), transparent 60%);mix-blend-mode:screen}
    .btn-ripple:hover::before{opacity:1}
    .scroll-shell{display:inline-block;height:24px;width:12px;border:1px solid rgb(255 255 255 / 0.2);border-radius:8px;padding:2px}
    .scroll-dot{display:block;height:8px;width:4px;border-radius:4px;background:rgb(255 255 255 /.5);animation:bounceDot 1.6s ease-in-out infinite}
    .neon-sweep{position:absolute;top:33%;height:1px;width:50%;filter:blur(20px);background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);animation:sweep 14s linear infinite}
    .glass-ring{box-shadow:inset 0 0 0 1px rgba(255,255,255,.08)}
    .no-scrollbar::-webkit-scrollbar{display:none}
    /* Sheen & glints */
    .btn-ripple::after{content:"";position:absolute;inset:-120% -40%;transform:rotate(35deg) translateX(-20%);background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);opacity:0;transition:transform .6s ease, opacity .3s}
    .btn-ripple:hover::after{transform:rotate(35deg) translateX(60%);opacity:.6}
    .card-sheen{position:absolute;inset:-20%;background:linear-gradient(120deg,transparent,rgba(255,255,255,.12),transparent);filter:blur(12px);opacity:0;transform:translateX(-30%);transition:opacity .5s ease, transform .8s cubic-bezier(.22,1,.36,1);pointer-events:none}
    .js-tilt:hover .card-sheen{opacity:.9;transform:translateX(30%)}
    @media (prefers-reduced-motion: reduce){
      .neon-sweep{animation:none}
      .scroll-dot{animation:none}
      [style*="animation:marquee"]{animation:none !important}
      .btn-ripple::after{transition:none}
      .card-sheen{transition:none}
    }
      /* Nav underline indicator */
    .nav-link{position:relative;color:rgba(255,255,255,.7);transition:color .2s ease}
    .nav-link:hover,.nav-link[data-active="true"]{color:#fff}
    .nav-link::after{content:"";position:absolute;left:0;bottom:-6px;height:2px;width:0;background:linear-gradient(90deg,rgba(99,102,241,.8),rgba(16,185,129,.8));border-radius:2px;transition:width .35s cubic-bezier(.22,1,.36,1)}
    .nav-link:hover::after,.nav-link[data-active="true"]::after{width:100%}
    /* Gradient ring (soft premium) */
    .ring-gradient{position:absolute;inset:-1px;border-radius:inherit;padding:1px;background:linear-gradient(135deg,rgba(99,102,241,.55),rgba(16,185,129,.55));-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:.65}
    /* Focus visible outline */
    .js-magnetic:focus-visible{outline:none;box-shadow:0 0 0 2px rgba(255,255,255,.65)}
      /* ===== Card polish (equal height, clamp, accent) ===== */
    [data-test="category-card"]{position:relative;display:grid;grid-template-rows:auto 1fr auto;min-height:280px}
    [data-test="category-card"] p{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    [data-test="category-card"]::after{content:"";position:absolute;left:0;right:0;bottom:0;height:1px;background:linear-gradient(90deg,rgba(99,102,241,.5),rgba(255,255,255,.12),rgba(16,185,129,.5));opacity:.6}
    /* Subtle lift on all tilt cards */
    .js-tilt{transition:transform .35s cubic-bezier(.22,1,.36,1), box-shadow .3s ease}
    .js-tilt:hover{transform:translateY(-4px)}
  </style>
  <!-- Lucide icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#0a0b10] text-white min-h-screen relative">
  <!-- Background noise -->
  <div aria-hidden class="pointer-events-none fixed inset-0 -z-20 opacity-[0.035] [background-image:radial-gradient(rgba(255,255,255,0.6)_1px,transparent_1px)] [background-size:3px_3px]"></div>

  <!-- Edge Vignette (liquid glass frame) -->
  <div data-test="edge-vignette" aria-hidden class="pointer-events-none fixed inset-0 -z-10 [box-shadow:inset_0_0_0_1px_rgba(255,255,255,0.06),inset_0_60px_160px_-60px_rgba(99,102,241,0.15),inset_0_-60px_160px_-60px_rgba(16,185,129,0.12)]"></div>

  <!-- Neon Grid & Sweep -->
  <div data-test="neon-grid" aria-hidden class="pointer-events-none fixed inset-0 -z-10">
    <div class="absolute inset-0 opacity-[var(--lg-grid)] [background:repeating-linear-gradient(0deg,transparent,transparent_29px,rgba(255,255,255,0.08)_30px),repeating-linear-gradient(90deg,transparent,transparent_29px,rgba(255,255,255,0.08)_30px)] [mask-image:radial-gradient(ellipse_80%_60%_at_50%_40%,black,transparent)]"></div>
    <div class="neon-sweep"></div>
  </div>

  <!-- Corner Glints (global subtle highlights) -->
  <div data-test="corner-glints" aria-hidden class="pointer-events-none fixed inset-0 -z-[6]">
    <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full opacity-30 [background:radial-gradient(circle,rgba(255,255,255,0.2),transparent_60%)]"></div>
    <div class="absolute -right-8 top-1/4 h-28 w-28 rounded-full opacity-25 [background:radial-gradient(circle,rgba(255,255,255,0.18),transparent_60%)]"></div>
    <div class="absolute left-1/3 bottom-10 h-24 w-24 rounded-full opacity-20 [background:radial-gradient(circle,rgba(255,255,255,0.15),transparent_60%)]"></div>
  </div>

  <!-- Cursor Blob -->
  <div id="cursor-blob" aria-hidden class="pointer-events-none fixed z-30 hidden h-56 w-56 -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl md:block" style="background:radial-gradient(circle at center, rgba(99,102,241,0.20), rgba(16,185,129,0.16), transparent 60%)"></div>

  <!-- Navbar -->
  <header class="sticky top-0 z-50 backdrop-blur-md">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 relative flex items-center justify-between py-4">
      <a href="#" class="group inline-flex items-center gap-2">
        <span class="grid h-9 w-9 place-content-center rounded-xl bg-gradient-to-br from-indigo-500 to-emerald-400 text-white shadow-lg shadow-indigo-500/25">LC</span>
        <span class="text-lg font-bold tracking-tight text-white/90 group-hover:text-white">Link4Class</span>
      </a>
      <nav class="hidden items-center gap-6 md:flex">
        <a href="#groups" class="nav-link text-sm text-white/70 transition">Groups</a>
        <a href="#lessons" class="nav-link text-sm text-white/70 transition">Lessons</a>
        <a href="#bookswap" class="nav-link text-sm text-white/70 transition">Book Swap</a>
        <a href="#features" class="nav-link text-sm text-white/70 transition">Features</a>
        <a href="#cta" class="nav-link text-sm text-white/70 transition">Start</a>
      </nav>
      <div class="flex items-center gap-3">
      </div>
    </div>
    <div id="progress-bar" data-test="progress-bar" class="pointer-events-none fixed left-0 top-0 z-[60] h-0.5 w-screen origin-left bg-gradient-to-r from-indigo-400 via-white to-emerald-400" style="transform:scaleX(0)"></div>
    <div class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
  </header>

  <!-- HERO -->
  <section id="hero" class="relative overflow-hidden">
    <!-- Glow -->
    <div aria-hidden class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
      <div class="absolute -top-40 left-1/2 h-[50rem] w-[50rem] -translate-x-1/2 rounded-full bg-[radial-gradient(circle_at_center,rgba(99,102,241,0.25),transparent_60%)] blur-3xl"></div>
      <div class="absolute -bottom-40 left-1/3 h-[40rem] w-[40rem] rounded-full bg-[radial-gradient(circle_at_center,rgba(16,185,129,0.18),transparent_60%)] blur-3xl"></div>
      <div class="absolute inset-0 opacity-[0.08] [background:repeating-linear-gradient(0deg,transparent,transparent_31px,rgba(255,255,255,0.07)_32px),repeating-linear-gradient(90deg,transparent,transparent_31px,rgba(255,255,255,0.07)_32px)] [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,transparent_60%,rgba(0,0,0,0.55))]"></div>
    </div>
    <!-- Aurora blobs (parallax) -->
    <div id="aurora" aria-hidden class="pointer-events-none absolute inset-0 -z-10 opacity-60 [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]">
      <div id="auroraA" class="absolute left-[-20%] top-[-10%] h-[40rem] w-[40rem] rounded-full blur-3xl [background:conic-gradient(from_180deg_at_50%_50%,rgba(99,102,241,0.3),rgba(16,185,129,0.25),rgba(255,255,255,0),rgba(99,102,241,0.3))]"></div>
      <div id="auroraB" class="absolute right-[-10%] bottom-[-20%] h-[36rem] w-[36rem] rounded-full blur-3xl [background:conic-gradient(from_0deg_at_50%_50%,rgba(16,185,129,0.25),rgba(99,102,241,0.3),rgba(255,255,255,0),rgba(16,185,129,0.25))]"></div>
    </div>

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 relative grid min-h-[86vh] place-items-center py-20">
      <div data-test="hero-scrim" class="md:hidden absolute inset-0 z-0 [background:radial-gradient(ellipse_120%_60%_at_50%_40%,rgba(0,0,0,.45),transparent_60%)]"></div>
      <div aria-hidden class="absolute left-1/2 top-1/2 z-[1] -translate-x-1/2 -translate-y-1/2 w-[min(92%,64rem)] h-[28rem] rounded-[2.5rem] bg-white/[0.07] backdrop-blur-2xl border border-white/10 shadow-[0_0_120px_rgba(99,102,241,0.15)_inset,0_0_120px_rgba(16,185,129,0.12)_inset] [mask-image:radial-gradient(80%_60%_at_50%_40%,black,transparent)]"></div>
      <div id="heroContent" class="relative z-[2] text-center">

        <h1 class="mx-auto max-w-5xl text-6xl font-black leading-[1.05] text-white drop-shadow-[0_4px_24px_rgba(0,0,0,0.75)] sm:text-7xl md:text-8xl">
          <span class="sr-only">Learn together. Build skills faster.</span>
          <span aria-hidden class="kinetic">
            <span class="inline-block mr-2 animate-[upReveal_.7s_cubic-bezier(.22,1,.36,1)_0s_both]">Learn</span>
            <span class="inline-block mr-2 animate-[upReveal_.7s_cubic-bezier(.22,1,.36,1)_.05s_both]">together.</span>
            <span class="inline-block mr-2 animate-[upReveal_.7s_cubic-bezier(.22,1,.36,1)_.10s_both]">Build</span>
            <span class="inline-block mr-2 animate-[upReveal_.7s_cubic-bezier(.22,1,.36,1)_.15s_both]">skills</span>
            <span class="inline-block mr-2 animate-[upReveal_.7s_cubic-bezier(.22,1,.36,1)_.20s_both]">faster.</span>
          </span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-balance text-base text-white/70 md:text-lg">Link4Class unisce collaboration in tempo reale, chat private cifrate e lezioni strutturate in un'unica esperienza elegante.</p>
        <div class="reveal mt-8 flex flex-wrap items-center justify-center gap-3">
          <button class="js-magnetic btn-ripple group relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-500 to-emerald-400 px-6 py-3 text-white shadow-xl" data-reveal-item>
            <a href="link4class/register.php">inizia gratis</a> <i data-lucide="arrow-right" class="h-4 w-4 inline-block align-[-2px] transition-transform group-hover:translate-x-0.5"></i>
          </button>
          <button class="js-magnetic btn-ripple relative overflow-hidden rounded-2xl bg-white/5 px-6 py-3 text-white/90 ring-1 ring-inset ring-white/10" data-reveal-item>
            <i data-lucide="play" class="mr-1 h-4 w-4 inline-block"></i> Guarda la demo
          </button>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-6 left-1/2 z-10 -translate-x-1/2">
      <div class="flex items-center gap-2 text-[11px] uppercase tracking-widest text-white/60">
        <span>Scroll</span>
        <span class="scroll-shell"><span class="scroll-dot"></span></span>
      </div>
    </div>
  </section>

  <!-- Divider -->
  <div data-test="divider" aria-hidden class="relative"><div class="mx-auto my-12 h-px w-full max-w-7xl bg-gradient-to-r from-transparent via-white/10 to-transparent"></div></div>



  <!-- Ribbon (marquee) -->
  <section>
    <div class="relative">
      <div class="absolute inset-0 -z-10 bg-gradient-to-b from-white/5 to-transparent"></div>
      <div class="w-full overflow-hidden py-8 [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
        <div class="flex gap-6 whitespace-nowrap text-white/60 will-change-transform" style="animation:marquee 32s linear infinite">
          <?php $words=["Realtime","Encrypted","Accessible","Performant","Beautiful","Composable","Reliable"]; $doubled=array_merge($words,$words); foreach($doubled as $w): ?>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><span class="h-1.5 w-1.5 rounded-full bg-white/40"></span> <?= htmlspecialchars($w) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Split Showcase -->
  <section id="groups" class="relative py-24">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-10 flex items-end justify-between">
        <h2 class="text-3xl font-bold text-white sm:text-4xl">Tre aree, un'unica esperienza</h2>
        <p class="max-w-xl text-sm text-white/70"><strong>Groups</strong>, <strong>Lessons</strong> e <strong>Book Swap</strong> hanno pesi e stili bilanciati: collaborazione live, studio strutturato e scambio libri.</p>
      </div>
      <div class="reveal grid gap-6 md:grid-cols-3">
        <!-- Groups -->
        <div class="js-tilt relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-0.5 backdrop-blur-md transition-shadow duration-300 hover:shadow-2xl hover:shadow-black/20" data-reveal-item>
          <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-indigo-500/25 via-white/10 to-emerald-400/25 opacity-30"></div>
          <span aria-hidden class="ring-gradient"></span>
          <div class="tilt-inner relative rounded-[calc(theme(borderRadius.3xl)-2px)] bg-gradient-to-b from-white/10 to-white/[0.04]">
            <div class="relative overflow-hidden rounded-[inherit] p-6" data-test="category-card" data-label="Groups">
              <span aria-hidden class="card-sheen"></span>
              <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-indigo-500/15 text-indigo-200 ring-1 ring-inset ring-indigo-400/30 px-3 py-1 text-xs"><i data-lucide="users" class="h-4 w-4"></i><span>Groups</span></div>
              <h3 class="text-2xl font-semibold text-white">Groups</h3>
              <p class="mt-2 max-w-md text-sm text-white/80">Stanze live, whiteboard, task veloci e chat E2E. Ideale per team, progetti e studio in gruppo.</p>
              <div class="mt-8"><button class="js-magnetic btn-ripple group relative overflow-hidden rounded-xl bg-white/10 px-4 py-2 text-white ring-1 ring-white/15"><a href="link4class/dashboard.php">Entra in Groups</a> <i data-lucide="arrow-right" class="h-4 w-4 inline-block"></i></button></div>
              <span aria-hidden class="pointer-events-none absolute -inset-6 block opacity-[var(--lg-caustic)] [mask-image:radial-gradient(60%_50%_at_50%_30%,black,transparent)]" style="background:radial-gradient(80rem 40rem at 20% -10%, rgba(255,255,255,0.15), transparent 60%), radial-gradient(50rem 30rem at 110% 120%, rgba(255,255,255,0.10), transparent 65%);mix-blend-mode:screen;filter:blur(var(--lg-blur))"></span>
            </div>
          </div>
        </div>
        <!-- Lessons -->
        <div class="js-tilt relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-0.5 backdrop-blur-md transition-shadow duration-300 hover:shadow-2xl hover:shadow-black/20" data-reveal-item>
          <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-indigo-500/25 via-white/10 to-emerald-400/25 opacity-30"></div>
          <span aria-hidden class="ring-gradient"></span>
          <div class="tilt-inner relative rounded-[calc(theme(borderRadius.3xl)-2px)] bg-gradient-to-b from-white/10 to-white/[0.04]">
            <div class="relative overflow-hidden rounded-[inherit] p-6" data-test="category-card" data-label="Lessons" id="lessons">
              <span aria-hidden class="card-sheen"></span>
              <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-400/15 text-emerald-100 ring-1 ring-inset ring-emerald-300/30 px-3 py-1 text-xs"><i data-lucide="graduation-cap" class="h-4 w-4"></i><span>Lessons</span></div>
              <h3 class="text-2xl font-semibold text-white">Lessons</h3>
              <p class="mt-2 max-w-md text-sm text-white/80">Percorsi modulari, quiz con feedback e certificazioni. Costruisci competenze con metriche chiare.</p>
              <div class="mt-8"><button class="js-magnetic btn-ripple group relative overflow-hidden rounded-xl bg-white/10 px-4 py-2 text-white ring-1 ring-white/15"><a href="http://localhost/link4schooll-main44/dashboard.php">Esplora le lesson</a> <i data-lucide="arrow-right" class="h-4 w-4 inline-block"></i></button></div>
              <span aria-hidden class="pointer-events-none absolute -inset-6 block opacity-[var(--lg-caustic)] [mask-image:radial-gradient(60%_50%_at_50%_30%,black,transparent)]" style="background:radial-gradient(80rem 40rem at 20% -10%, rgba(255,255,255,0.15), transparent 60%), radial-gradient(50rem 30rem at 110% 120%, rgba(255,255,255,0.10), transparent 65%);mix-blend-mode:screen;filter:blur(var(--lg-blur))"></span>
            </div>
          </div>
        </div>
        <!-- Book Swap -->
        <div id="bookswap" class="js-tilt relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-0.5 backdrop-blur-md transition-shadow duration-300 hover:shadow-2xl hover:shadow-black/20" data-reveal-item>
          <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-indigo-500/25 via-white/10 to-emerald-400/25 opacity-30"></div>
          <span aria-hidden class="ring-gradient"></span>
          <div class="tilt-inner relative rounded-[calc(theme(borderRadius.3xl)-2px)] bg-gradient-to-b from-white/10 to-white/[0.04]">
            <div class="relative overflow-hidden rounded-[inherit] p-6" data-test="category-card" data-label="Book Swap">
              <span aria-hidden class="card-sheen"></span>
              <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 text-white/90 ring-1 ring-inset ring-white/20 px-3 py-1 text-xs"><i data-lucide="book-open" class="h-4 w-4"></i><span>Book Swap</span></div>
              <h3 class="text-2xl font-semibold text-white">Book Swap</h3>
              <p class="mt-2 max-w-md text-sm text-white/80">Presentazione dello scambio libri tra studenti: ricerca, filtri per campus/città e contatto privato. Leggero, integrato.</p>
              <div class="mt-8"><button class="js-magnetic btn-ripple group relative overflow-hidden rounded-xl bg-white/10 px-4 py-2 text-white ring-1 ring-white/15"><a href="http://localhost/BookSwap/BookSwap/Sitoplus/home">Scopri Book Swap</a> <i data-lucide="arrow-right" class="h-4 w-4 inline-block"></i></button></div>
              <span aria-hidden class="pointer-events-none absolute -inset-6 block opacity-[var(--lg-caustic)] [mask-image:radial-gradient(60%_50%_at_50%_30%,black,transparent)]" style="background:radial-gradient(80rem 40rem at 20% -10%, rgba(255,255,255,0.15), transparent 60%), radial-gradient(50rem 30rem at 110% 120%, rgba(255,255,255,0.10), transparent 65%);mix-blend-mode:screen;filter:blur(var(--lg-blur))"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="reveal mt-10 grid grid-cols-2 gap-3 text-sm">
        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-2 text-white/80 ring-1 ring-inset ring-white/10" data-reveal-item><i data-lucide="message-square" class="h-4 w-4"></i><span class="text-xs font-medium">Chat cifrata</span></div>
        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-2 text-white/80 ring-1 ring-inset ring-white/10" data-reveal-item><i data-lucide="shield" class="h-4 w-4"></i><span class="text-xs font-medium">Ruoli & permessi</span></div>
        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-2 text-white/80 ring-1 ring-inset ring-white/10" data-reveal-item><i data-lucide="sparkles" class="h-4 w-4"></i><span class="text-xs font-medium">AI copilots</span></div>
        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-2 text-white/80 ring-1 ring-inset ring-white/10" data-reveal-item><i data-lucide="play" class="h-4 w-4"></i><span class="text-xs font-medium">Video & interattivi</span></div>
      </div>
    </div>
  </section>

  <!-- Divider -->
  <div data-test="divider" aria-hidden class="relative"><div class="mx-auto my-12 h-px w-full max-w-7xl bg-gradient-to-r from-transparent via-white/10 to-transparent"></div></div>

  <!-- Features Grid -->
  <section id="features" class="relative py-24">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-12 max-w-3xl">
        <h2 class="text-3xl font-bold text-white sm:text-4xl">Un toolkit premium per l'apprendimento</h2>
        <p class="mt-3 text-white/70">Prestazioni real‑time, privacy by design e interfaccia curata al pixel. Tutto ciò che ti aspetti da un progetto da Link4Class, ma usabile ogni giorno.</p>
      </div>
      <div class="reveal grid gap-6 md:grid-cols-3">
        <?php $features=[["Chat private E2E","Cifratura end‑to‑end nelle chat 1:1 e di gruppo.","shield"],["Whiteboard live","Schemi, idee e flowchart con cursori multipli.","users"],["Lesson builder","Editor modulare con quiz e certificazioni.","graduation-cap"],["Spaces & ruoli","Gestione ruoli, gruppi e permessi granulari.","users"],["Book Swap","Presentazione dello scambio libri tra studenti.","book-open"]]; foreach($features as $f): ?>
          <div class="js-tilt relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-0.5 backdrop-blur-md transition-shadow duration-300 hover:shadow-2xl hover:shadow-black/20" data-reveal-item>
            <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-indigo-500/25 via-white/10 to-emerald-400/25 opacity-30"></div>
            <span aria-hidden class="ring-gradient"></span>
            <div class="tilt-inner relative rounded-[calc(theme(borderRadius.3xl)-2px)] bg-gradient-to-b from-white/10 to-white/[0.04]">
              <div class="relative overflow-hidden rounded-[inherit] p-6">
                <span aria-hidden class="card-sheen"></span>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs text-white/80 ring-1 ring-white/15"><i data-lucide="<?= htmlspecialchars($f[2]) ?>" class="h-5 w-5"></i><span>Feature</span></div>
                <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($f[0]) ?></h3>
                <p class="mt-1 text-sm text-white/70"><?= htmlspecialchars($f[1]) ?></p>
                <span aria-hidden class="pointer-events-none absolute -inset-8 opacity-[var(--lg-caustic)] [mask-image:radial-gradient(50%_40%_at_30%_0%,black,transparent)]" style="background:radial-gradient(50rem 30rem at 0% -10%, rgba(255,255,255,0.12), transparent 55%), radial-gradient(40rem 30rem at 120% 120%, rgba(255,255,255,0.08), transparent 65%);mix-blend-mode:screen;filter:blur(var(--lg-blur))"></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FAQ (liquid glass accordions) -->
  <section id="faq" class="relative py-16">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-6"><h3 class="text-2xl font-bold">Domande frequenti</h3></div>
      <div class="grid gap-4 md:grid-cols-2">
        <?php $faq=[
          ["Cos'è Link4Class?","Una piattaforma di social learning con gruppi, lezioni e Book Swap."],
          ["Book Swap come funziona?","È uno spazio di presentazione per scambiare libri tra studenti — ricerca e contatto privato."],
          ["Serve la carta di credito?","No, puoi iniziare gratis e invitare il team."],
          ["Le chat sono sicure?","Sì, sono cifrate end‑to‑end nelle conversazioni private."],
        ]; foreach($faq as $f): ?>
        <details data-test="faq-item" class="group rounded-2xl bg-white/5 p-5 ring-1 ring-white/10 backdrop-blur">
          <summary class="flex cursor-pointer list-none items-center justify-between text-white/80">
            <span class="font-medium mr-4"><?= htmlspecialchars($f[0]) ?></span>
            <i data-lucide="plus" class="h-5 w-5 transition group-open:rotate-45"></i>
          </summary>
          <p class="mt-3 text-sm text-white/70"><?= htmlspecialchars($f[1]) ?></p>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>



  <!-- Back to top -->
  <button id="backTop" data-test="back-top" class="fixed bottom-6 left-6 z-50 hidden rounded-full bg-white/10 p-3 ring-1 ring-white/15 backdrop-blur hover:bg-white/20"><i data-lucide="chevrons-up" class="h-5 w-5"></i></button>

  <!-- Footer -->
  <footer class="relative border-t border-white/10 py-10 text-sm text-white/60">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-between gap-6 sm:flex-row">
      <div class="flex items-center gap-2">
        <span class="grid h-8 w-8 place-content-center rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 text-white">LC</span>
        <span>© <?= $year ?> Link4Class</span>
      </div>

    </div>
  </footer>

  <script>
    // Icons
    window.addEventListener('DOMContentLoaded', () => { if (window.lucide) window.lucide.createIcons(); });

    // Cursor blob
    (function(){
      const blob = document.getElementById('cursor-blob'); if(!blob) return;
      window.addEventListener('pointermove', (e)=>{ blob.style.left = e.clientX + 'px'; blob.style.top = e.clientY + 'px'; }, { passive: true });
    })();

    // Magnetic buttons + ripple gradient position
    (function(){
      const mags = Array.from(document.querySelectorAll('.js-magnetic'));
      mags.forEach(btn => {
        btn.addEventListener('mousemove', (e)=>{
          const r = btn.getBoundingClientRect(); const cx = e.clientX - (r.left + r.width/2); const cy = e.clientY - (r.top + r.height/2);
          btn.style.transform = `translate3d(${cx*0.2}px, ${cy*0.2}px, 0)`;
          btn.style.setProperty('--mx', (e.clientX - r.left) + 'px'); btn.style.setProperty('--my', (e.clientY - r.top) + 'px');
        });
        btn.addEventListener('mouseleave', ()=>{ btn.style.transform = 'translate3d(0,0,0)'; });
      });
    })();

    // Tilt cards
    (function(){
      const cards = Array.from(document.querySelectorAll('.js-tilt'));
      cards.forEach(card => {
        const inner = card.querySelector('.tilt-inner'); if(!inner) return;
        card.addEventListener('mousemove', (e)=>{
          const r = card.getBoundingClientRect(); const px = (e.clientX - r.left) / r.width; const py = (e.clientY - r.top) / r.height;
          const rotX = (0.5 - py) * 10; const rotY = (px - 0.5) * 10;
          inner.style.transform = `perspective(800px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(6px)`;
        });
        card.addEventListener('mouseleave', ()=>{ inner.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) translateZ(0)'; });
      });
    })();

    // Reveal on scroll
    (function(){
      const containers = Array.from(document.querySelectorAll('.reveal'));
      const io = new IntersectionObserver((entries)=>{ entries.forEach(en => { if (en.isIntersecting) en.target.classList.add('revealed'); }); }, { rootMargin: '-10% 0px' });
      containers.forEach(c => io.observe(c));
    })();

    // Parallax Aurora + hero content fade/translate
    (function(){
      const aurA = document.getElementById('auroraA'); const aurB = document.getElementById('auroraB'); const heroContent = document.getElementById('heroContent');
      const onScroll = () => {
        const y = window.scrollY || 0; const yA = Math.max(-40, -y * 0.07); const yB = Math.max(-70, -y * 0.12);
        if (aurA) aurA.style.transform = `translateY(${yA}px)`; if (aurB) aurB.style.transform = `translateY(${yB}px)`;
        if (heroContent) { const op = Math.max(0.5, 1 - y / 600); const ty = Math.min(80, y * 0.2); heroContent.style.transform = `translateY(-${ty}px)`; heroContent.style.opacity = op.toString(); }
      };
      onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
    })();

    // Top progress bar
    (function(){
      const bar = document.getElementById('progress-bar'); if(!bar) return;
      const onScroll = () => { const h = document.documentElement; const max = h.scrollHeight - h.clientHeight; const p = max > 0 ? (h.scrollTop || document.body.scrollTop) / max : 0; bar.style.transform = `scaleX(${p})`; };
      onScroll(); document.addEventListener('scroll', onScroll, { passive: true });
    })();

    
    // Nav active highlight (underline + color)
    (function(){
      const links = Array.from(document.querySelectorAll('.nav-link'));
      if(!links.length) return;
      const targets = links.map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);
      const setActive = () => {
        let idx = 0; let min = Infinity;
        targets.forEach((sec,i)=>{ if(!sec) return; const r = sec.getBoundingClientRect(); const d = Math.abs(r.top - 80); if (r.top < window.innerHeight*0.6 && d < min){ min = d; idx = i; } });
        links.forEach((l,i)=>{ if(i===idx) l.setAttribute('data-active','true'); else l.removeAttribute('data-active'); });
      };
      setActive();
      document.addEventListener('scroll', setActive, { passive: true });
      window.addEventListener('resize', setActive);
    })();

    // Testimonials arrows
    (function(){
      const rail = document.getElementById('tsRail'); if(!rail) return; const prev=document.getElementById('tsPrev'); const next=document.getElementById('tsNext');
      prev && prev.addEventListener('click', ()=> rail.scrollBy({left:-360,behavior:'smooth'}));
      next && next.addEventListener('click', ()=> rail.scrollBy({left: 360,behavior:'smooth'}));
    })();

    // Back to top visibility + action
    (function(){
      const btn = document.getElementById('backTop'); if(!btn) return;
      const onScroll = ()=>{ const y = window.scrollY || 0; btn.style.display = y>600 ? 'inline-flex' : 'none'; };
      onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
      btn.addEventListener('click', ()=> window.scrollTo({top:0, behavior:'smooth'}));
    })();

    // Runtime smoke tests (console)
    (function(){
      try {
        // 1: Many ribbon items
        const ribbonCount = document.querySelectorAll('section span.inline-flex.border').length; console.assert(ribbonCount >= 10, 'Ribbon should render many items');
        // 2: Navbar anchors include groups/features/cta/bookswap
        const hrefs = Array.from(document.querySelectorAll('a[href^="#"]')).map(a=>a.getAttribute('href')); console.assert(hrefs.includes('#groups') && hrefs.includes('#features') && hrefs.includes('#cta') && hrefs.includes('#bookswap'), 'Navbar anchors should include #groups, #features, #bookswap, #cta');
        // 3: 3 category cards
        console.assert(document.querySelectorAll('[data-test="category-card"]').length === 3, 'SplitShowcase must render exactly 3 category cards');
        // 4: Progress bar exists
        console.assert(!!document.querySelector('[data-test="progress-bar"]'), 'Progress bar element is missing');
        // 5: Hero exists
        console.assert(!!document.querySelector('section#hero'), 'Hero section should exist with id #hero');
        // 6: CTA buttons >= 2
        console.assert(document.querySelectorAll('#cta button').length >= 2, 'CTA should render at least two buttons');
        // 7: Headline spans = 5
        console.assert(document.querySelectorAll('h1 .kinetic span').length === 5, 'KineticHeadline should render 5 animated spans');
        // 8: Features h3 = 6
        console.assert(document.querySelectorAll('#features h3').length === 6, 'FeaturesGrid should render 6 feature headings');
        // 9: Body theme class
        console.assert(document.body.classList.contains('bg-[#0a0b10]'), 'Body background theme class should be applied');
        // 10: Get started in navbar
        const hasGetStarted = Array.from(document.querySelectorAll('header button')).some(b => (b.textContent||'').toLowerCase().includes('get started')); console.assert(hasGetStarted, "Navbar should include a 'Get started' button");
        // 11: EdgeVignette present
        console.assert(!!document.querySelector('[data-test="edge-vignette"]'), 'EdgeVignette overlay should exist');
        // 12: Two dividers
        console.assert(document.querySelectorAll('[data-test="divider"]').length >= 2, 'Should render at least two section dividers');
        // 13: Neon grid present
        console.assert(!!document.querySelector('[data-test="neon-grid"]'), 'Neon grid overlay should exist');
        // 14: CSS var set
        console.assert(getComputedStyle(document.documentElement).getPropertyValue('--lg-blur').trim() !== '', 'CSS var --lg-blur should be set');
        // 15: Settings panel removed
        console.assert(!document.querySelector('[data-test="settings"]'), 'Settings panel should be removed');
        // 16: Stats >=3
        console.assert(document.querySelectorAll('[data-test="stats-chip"]').length >= 3, 'Should render at least 3 stats chips');
        // 17: Testimonials >=3
        console.assert(document.querySelectorAll('[data-test="testimonial-card"]').length >= 3, 'Should render at least 3 testimonial cards');
        // 18: FAQ >=4
        console.assert(document.querySelectorAll('[data-test="faq-item"]').length >= 4, 'Should render at least 4 FAQ items');
        // 19: Partners >=5
        console.assert(document.querySelectorAll('[data-test="partner"]').length >= 5, 'Should render at least 5 partners');
        // 20: Back-to-top exists
        console.assert(!!document.querySelector('[data-test="back-top"]'), 'Back-to-top button should exist');
        // 21: Corner glints exist
        console.assert(!!document.querySelector('[data-test="corner-glints"]'), 'Corner glints overlay should exist');
        // 22: Card sheens exist
        console.assert(document.querySelectorAll('.card-sheen').length >= 3, 'Should render card sheen overlays');
        // 23: nav-link count should be 5
        console.assert(document.querySelectorAll('.nav-link').length === 5, 'Navbar should have 5 nav-link anchors');
        // 24: ring-gradient overlays should exist
        console.assert(document.querySelectorAll('.ring-gradient').length >= 4, 'Should render gradient ring overlays');
        // 25: hero scrim exists
        console.assert(!!document.querySelector('[data-test="hero-scrim"]'), 'Hero mobile scrim should exist');
        console.log('[Link4Class] PHP build — Smoke tests passed ✅');
      } catch (e) { console.error('Smoke tests error', e); }
    })();
  </script>
</body>
</html>
