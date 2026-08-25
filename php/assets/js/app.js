/* ==================================================================== *
 *  DPX Golf — client behaviour
 *
 *  The vanilla-JS counterpart of the React client components. Written as
 *  independent init functions so a failure in one cannot take the rest
 *  of the page down with it: each is called inside its own try/catch at
 *  the bottom of this file.
 *
 *  Everything here is an enhancement. With scripting off the page still
 *  reads, the accordion still shows its first row, the price table still
 *  shows off-peak, and the enquiry form still posts.
 * ==================================================================== */
(function () {
  "use strict";

  var reduced = false;
  try {
    reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  } catch (e) { /* ancient browser — assume motion is fine */ }

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  };

  /* ------------------------------------------------------------------ *
   *  Reveals
   *
   *  Sets data-inview once an element crosses the viewport. One-shot —
   *  reveals shouldn't re-fire and make the page feel twitchy on the way
   *  back up.
   * ------------------------------------------------------------------ */
  function initReveals() {
    var els = $$(".reveal-up, .reveal-wipe, .reveal-lines");

    // No IntersectionObserver: show everything rather than hide it.
    if (typeof IntersectionObserver === "undefined") {
      els.forEach(function (el) { el.setAttribute("data-inview", "true"); });
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          /* A jump — an anchor link, a restored scroll position, a flick
           * on a trackpad — can carry an element from below the fold to
           * above it between two observations, so it never reports as
           * intersecting. Treat "already scrolled past" as revealed;
           * leaving it hidden would blank a whole section. */
          var scrolledPast =
            !!entry.rootBounds &&
            entry.boundingClientRect.bottom < entry.rootBounds.top;

          if (entry.isIntersecting || scrolledPast) {
            entry.target.setAttribute("data-inview", "true");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.18, rootMargin: "0px 0px -8% 0px" }
    );

    els.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------------ *
   *  Counters
   *
   *  Counts a number into place the way a launch monitor resolves a
   *  reading: a brief scramble while it "acquires", then a settle onto
   *  the true value.
   * ------------------------------------------------------------------ */
  var GLYPHS = "0123456789";

  function runCounter(el) {
    var value = parseFloat(el.getAttribute("data-value"));
    var decimals = parseInt(el.getAttribute("data-decimals"), 10) || 0;
    var duration = parseInt(el.getAttribute("data-duration"), 10) || 1400;

    if (isNaN(value)) return;

    if (reduced) {
      el.textContent = value.toFixed(decimals);
      return;
    }

    var start = performance.now();
    var scrambleFor = duration * 0.35;

    function tick(now) {
      var t = now - start;

      if (t < scrambleFor) {
        // Acquisition phase: right shape, wrong digits.
        el.textContent = value
          .toFixed(decimals)
          .split("")
          .map(function (ch) {
            return /\d/.test(ch) ? GLYPHS[(Math.random() * 10) | 0] : ch;
          })
          .join("");
      } else {
        var p = Math.min(1, (t - scrambleFor) / (duration - scrambleFor));
        var eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
        el.textContent = (value * eased).toFixed(decimals);
        if (p >= 1) {
          el.textContent = value.toFixed(decimals);
          return;
        }
      }
      requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
  }

  function initCounters() {
    var els = $$(".counter");
    if (!els.length) return;

    if (typeof IntersectionObserver === "undefined") {
      els.forEach(runCounter);
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            runCounter(entry.target);
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );

    els.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------------ *
   *  Magnetic controls
   *
   *  Nudges a control toward the cursor when it's nearby. Coarse pointers
   *  get nothing, which is correct — there's no cursor to attract to.
   * ------------------------------------------------------------------ */
  function initMagnetic() {
    if (reduced) return;

    $$(".magnetic").forEach(function (el) {
      var strength = parseFloat(el.getAttribute("data-strength")) || 0.32;

      el.addEventListener("pointermove", function (e) {
        if (e.pointerType !== "mouse") return;
        var r = el.getBoundingClientRect();
        var dx = e.clientX - (r.left + r.width / 2);
        var dy = e.clientY - (r.top + r.height / 2);
        el.style.transform =
          "translate3d(" + dx * strength + "px," + dy * strength + "px,0)";
      });

      el.addEventListener("pointerleave", function () {
        el.style.transform = "translate3d(0,0,0)";
      });
    });
  }

  /* ------------------------------------------------------------------ *
   *  Smooth scroll
   *
   *  Lenis handles the easing. Deliberately restrained — heavy damping
   *  makes a site feel like it is fighting you, especially on a trackpad.
   * ------------------------------------------------------------------ */
  var lenis = null;

  function initSmoothScroll() {
    if (reduced || typeof window.Lenis === "undefined") return;

    lenis = new window.Lenis({
      duration: 1.05,
      easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); },
      wheelMultiplier: 1,
      touchMultiplier: 1.6,
      // Native scrolling on touch feels better than any emulation.
      syncTouch: false
    });

    function loop(time) {
      lenis.raf(time);
      requestAnimationFrame(loop);
    }
    requestAnimationFrame(loop);
  }

  /* Anchor links need handing to Lenis or they jump. Bound whether or not
     Lenis loaded, so the offset for the fixed nav is applied either way. */
  function initAnchors() {
    document.addEventListener("click", function (e) {
      var a = e.target && e.target.closest ? e.target.closest("a[href^='#']") : null;
      if (!a) return;

      var id = a.getAttribute("href");
      if (!id || id === "#") return;

      var el = document.querySelector(id);
      if (!el) return;

      e.preventDefault();
      if (lenis) {
        lenis.scrollTo(el, { offset: -90 });
      } else {
        var top = el.getBoundingClientRect().top + window.scrollY - 90;
        window.scrollTo({ top: top, behavior: reduced ? "auto" : "smooth" });
      }
    });
  }

  /* ------------------------------------------------------------------ *
   *  Scroll tracer — the hairline down the right edge
   * ------------------------------------------------------------------ */
  function initScrollTracer() {
    var fill = $("#tracer-fill");
    var pct = $("#tracer-pct");
    if (!fill || !pct) return;

    var ticking = false;

    function apply() {
      var max = document.documentElement.scrollHeight - window.innerHeight;
      var p = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
      fill.style.transform = "scaleY(" + p + ")";
      pct.textContent = String(Math.round(p * 100)).padStart(3, "0");
      ticking = false;
    }

    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(apply);
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
    apply();
  }

  /* ------------------------------------------------------------------ *
   *  Hero parallax
   * ------------------------------------------------------------------ */
  function initHeroParallax() {
    var plate = $("#hero-plate");
    if (!plate || reduced) return;

    var ticking = false;

    function apply() {
      var y = window.scrollY;
      if (y < window.innerHeight * 1.2) {
        plate.style.transform =
          "translate3d(0," + y * 0.22 + "px,0) scale(" + (1 + y * 0.00012) + ")";
      }
      ticking = false;
    }

    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(apply);
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    apply();
  }

  /* ------------------------------------------------------------------ *
   *  Interlude drift — scroll-linked, so it reads as a response to the
   *  reader rather than ambient noise.
   * ------------------------------------------------------------------ */
  function initInterlude() {
    var section = $("#interlude");
    var track = $("#interlude-track");
    if (!section || !track || reduced) return;

    var ticking = false;

    function apply() {
      var r = section.getBoundingClientRect();
      // -1 .. 1 as the section crosses the viewport.
      var p = (window.innerHeight / 2 - (r.top + r.height / 2)) / window.innerHeight;
      track.style.transform = "translate3d(" + -p * 70 + "px,0,0)";
      ticking = false;
    }

    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(apply);
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    apply();
  }

  /* ------------------------------------------------------------------ *
   *  Nav — scrolled state and the mobile sheet
   * ------------------------------------------------------------------ */
  function initNav() {
    var header = $("#nav");
    var toggle = $("#nav-toggle");
    var sheet = $("#nav-sheet");

    if (header) {
      var onScroll = function () {
        var scrolled = window.scrollY > 24;
        header.classList.toggle("border-bone/10", scrolled);
        header.classList.toggle("bg-ink/72", scrolled);
        header.classList.toggle("backdrop-blur-xl", scrolled);
        header.classList.toggle("border-transparent", !scrolled);
        header.classList.toggle("bg-transparent", !scrolled);
      };
      window.addEventListener("scroll", onScroll, { passive: true });
      onScroll();
    }

    if (!toggle || !sheet) return;

    // The hamburger bars live in the toggle button, not the sheet.
    var top = toggle.querySelector('[data-bar="top"]');
    var bottom = toggle.querySelector('[data-bar="bottom"]');

    function setOpen(open) {
      sheet.setAttribute("data-open", open ? "true" : "false");
      sheet.setAttribute("aria-hidden", open ? "false" : "true");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");

      // The sheet takes over the viewport, so lock the page behind it.
      document.documentElement.style.overflow = open ? "hidden" : "";

      if (top && bottom) {
        top.style.top = open ? "6px" : "1px";
        top.style.transform = open ? "rotate(45deg)" : "none";
        bottom.style.top = open ? "6px" : "10px";
        bottom.style.transform = open ? "rotate(-45deg)" : "none";
      }
    }

    toggle.addEventListener("click", function () {
      setOpen(sheet.getAttribute("data-open") !== "true");
    });

    $$("[data-sheet-link]", sheet).forEach(function (a) {
      a.addEventListener("click", function () { setOpen(false); });
    });

    window.addEventListener("keydown", function (e) {
      if (e.key === "Escape") setOpen(false);
    });

    setOpen(false);
  }

  /* ------------------------------------------------------------------ *
   *  Audience register
   *
   *  Pointer users get it on hover, everyone gets it on click or
   *  keyboard — the open row is real state, not a CSS-only hover trick a
   *  keyboard user can never reach.
   * ------------------------------------------------------------------ */
  function initAudience() {
    var rows = $$("[data-audience-row]");
    if (!rows.length) return;

    function open(idx) {
      rows.forEach(function (row, i) {
        var on = i === idx;
        row.setAttribute("data-open", on ? "true" : "false");
        var btn = row.querySelector("button");
        if (btn) btn.setAttribute("aria-expanded", on ? "true" : "false");
      });
    }

    rows.forEach(function (row, i) {
      var btn = row.querySelector("button");
      if (!btn) return;
      btn.addEventListener("mouseenter", function () { open(i); });
      btn.addEventListener("focus", function () { open(i); });
      btn.addEventListener("click", function () { open(i); });
    });
  }

  /* ------------------------------------------------------------------ *
   *  Pricing switches
   *
   *  Both periods are already in the DOM; these only change which is
   *  shown, so the table is complete with scripting off.
   * ------------------------------------------------------------------ */
  var ON_CLASSES = ["bg-lime", "text-ink"];
  var OFF_PERIOD = ["text-bone/60", "hover:text-bone"];
  var OFF_HOURS = ["text-bone/55"];

  function initPricing() {
    var periodBtns = $$("[data-period-btn]");

    periodBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-period-btn");

        periodBtns.forEach(function (b) {
          var on = b === btn;
          b.setAttribute("aria-pressed", on ? "true" : "false");
          ON_CLASSES.forEach(function (c) { b.classList.toggle(c, on); });
          OFF_PERIOD.forEach(function (c) { b.classList.toggle(c, !on); });
        });

        $$("[data-period-panel]").forEach(function (p) {
          var show = p.getAttribute("data-period-panel") === id;
          p.hidden = !show;

          /* Reveals inside a hidden panel were never observed — a display:none
           * element never intersects — so they are still sitting at opacity 0.
           * Reveal them on the way in, or the table appears blank. */
          if (show) {
            $$(".reveal-up, .reveal-wipe, .reveal-lines", p).forEach(function (r) {
              r.setAttribute("data-inview", "true");
            });
          }
        });
        $$("[data-period-when]").forEach(function (p) {
          p.hidden = p.getAttribute("data-period-when") !== id;
        });
      });
    });

    /* The hours picker is per-period, and both periods are in the DOM, so
       switching hours has to apply to every panel at once — otherwise the
       hidden panel keeps a stale selection and shows the wrong price the
       moment someone switches period. */
    $$("[data-hours-btn]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var idx = btn.getAttribute("data-hours-btn");

        $$("[data-hours-btn]").forEach(function (b) {
          var on = b.getAttribute("data-hours-btn") === idx;
          b.setAttribute("aria-pressed", on ? "true" : "false");
          ON_CLASSES.forEach(function (c) { b.classList.toggle(c, on); });
          OFF_HOURS.forEach(function (c) { b.classList.toggle(c, !on); });
        });

        $$("[data-hours-cell]").forEach(function (cell) {
          cell.hidden = cell.getAttribute("data-hours-cell") !== idx;
        });
      });
    });
  }

  /* ------------------------------------------------------------------ *
   *  Roadmap "register interest" — deep-links into the form with the
   *  topic preselected.
   * ------------------------------------------------------------------ */
  function initInterestLinks() {
    $$("[data-interest]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-interest");
        var select = document.querySelector('#enquiry-form select[name="interest"]');
        if (select) {
          var match = Array.prototype.some.call(select.options, function (o) {
            return o.value === id;
          });
          if (match) select.value = id;
        }
        var book = $("#book");
        if (!book) return;
        if (lenis) {
          lenis.scrollTo(book, { offset: -90 });
        } else {
          book.scrollIntoView({ behavior: reduced ? "auto" : "smooth" });
        }
      });
    });
  }

  /* ------------------------------------------------------------------ *
   *  Preloader
   *
   *  The inline script in index.php already decided whether to show it;
   *  this only runs the progress and gets out of the way.
   * ------------------------------------------------------------------ */
  function initPreloader() {
    var el = $("#preloader");
    if (!el || !window.__dpxBoot) return;

    var bar = $("#pre-bar");
    var ring = $("#pre-ring");
    var pctEl = $("#pre-pct");
    var start = performance.now();
    var DUR = 1500;

    function finish() {
      try { sessionStorage.setItem("dpx:booted", "1"); } catch (e) { /* blocked storage */ }
      setTimeout(function () {
        el.style.clipPath = "inset(0 0 100% 0)";
        document.documentElement.style.overflow = "";
        setTimeout(function () { el.hidden = true; }, 950);
      }, 220);
    }

    function tick(now) {
      var p = Math.min(1, (now - start) / DUR);
      var eased = 1 - Math.pow(1 - p, 3);
      var pct = Math.round(eased * 100);

      if (bar) bar.style.width = pct + "%";
      if (ring) ring.style.transform = "scale(" + (0.6 + pct / 250) + ")";
      if (pctEl) pctEl.textContent = String(pct).padStart(3, "0");

      if (p < 1) {
        requestAnimationFrame(tick);
      } else {
        finish();
      }
    }

    requestAnimationFrame(tick);

    /* Belt and braces: if a rAF stall ever stranded the boot sequence it
       would lock the page behind an invisible overlay. Force it open. */
    setTimeout(function () {
      if (!el.hidden) {
        el.hidden = true;
        document.documentElement.style.overflow = "";
      }
    }, 4000);
  }

  /* ------------------------------------------------------------------ *
   *  Reticle cursor
   * ------------------------------------------------------------------ */
  function initReticle() {
    var wrap = $("#reticle");
    if (!wrap || reduced) return;
    if (!window.matchMedia("(pointer: fine)").matches) return;

    var body = $("#reticle-body");
    var ring = $("#reticle-ring");
    var dot = $("#reticle-dot");
    var labelEl = $("#reticle-label");

    var target = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    var pos = { x: target.x, y: target.y };
    var visible = false;

    function onMove(e) {
      target.x = e.clientX;
      target.y = e.clientY;

      if (!visible) {
        // First sighting: drop the reticle straight onto the pointer
        // instead of gliding in from wherever it was parked.
        pos.x = e.clientX;
        pos.y = e.clientY;
        visible = true;
        wrap.style.opacity = "1";
      }

      var el = e.target && e.target.closest
        ? e.target.closest("a, button, input, textarea, select, summary, [data-reticle]")
        : null;

      var active = !!el;
      wrap.setAttribute("data-active", active ? "true" : "false");

      if (body) {
        body.style.width = active ? "62px" : "30px";
        body.style.height = active ? "62px" : "30px";
      }
      if (ring) {
        ring.style.borderColor = active ? "rgba(198,242,78,.9)" : "rgba(237,232,220,.45)";
      }
      if (dot) {
        dot.style.width = active ? "5px" : "3px";
        dot.style.height = active ? "5px" : "3px";
        dot.style.background = active ? "rgb(198,242,78)" : "rgb(237,232,220)";
      }
      if (labelEl) {
        labelEl.textContent = (el && el.dataset && el.dataset.reticle) || "";
      }
    }

    function loop() {
      // Critically-damped-ish follow: quick enough to feel attached, slow
      // enough to feel like it has mass.
      pos.x += (target.x - pos.x) * 0.18;
      pos.y += (target.y - pos.y) * 0.18;
      wrap.style.transform = "translate3d(" + pos.x + "px," + pos.y + "px,0)";
      requestAnimationFrame(loop);
    }

    window.addEventListener("pointermove", onMove, { passive: true });
    document.addEventListener("pointerleave", function () {
      visible = false;
      wrap.style.opacity = "0";
    });
    document.addEventListener("pointerenter", function () {
      wrap.style.opacity = "1";
    });

    requestAnimationFrame(loop);
    document.documentElement.style.cursor = "none";
  }

  /* ------------------------------------------------------------------ *
   *  Shot tracer
   *
   *  Integrated in yards/seconds with drag and a Magnus lift term, then
   *  normalised so the shot carries the distance we asked for. The point
   *  isn't simulation accuracy — it's that the *shape* is real: a fast
   *  climb, a late apex, and a markedly steeper descent than ascent. A
   *  symmetric parabola reads as fake immediately to anyone who plays.
   * ------------------------------------------------------------------ */
  var G = 10.725; // yd/s^2
  var K_DRAG = 0.0031;
  var K_LIFT = 0.0021;

  function simulate(ballSpeedMph, launchDeg, azimuthDeg, spinAxisDeg, targetCarry) {
    var v0 = ballSpeedMph * 0.4889; // mph -> yd/s
    var la = (launchDeg * Math.PI) / 180;
    var az = (azimuthDeg * Math.PI) / 180;
    var axis = (spinAxisDeg * Math.PI) / 180;

    var p = { x: 0, y: 0, z: 0 };
    var v = {
      x: v0 * Math.cos(la) * Math.cos(az),
      y: v0 * Math.sin(la),
      z: v0 * Math.cos(la) * Math.sin(az)
    };

    var pts = [{ x: 0, y: 0, z: 0 }];
    var dt = 1 / 120;

    for (var i = 0; i < 2400; i++) {
      var speed = Math.hypot(v.x, v.y, v.z) || 1e-6;

      // Drag opposes velocity.
      var drag = K_DRAG * speed;
      // Lift acts perpendicular to travel; tilting the spin axis pushes
      // some of it sideways, which is what bends a draw or a fade.
      var lift = K_LIFT * speed * speed;

      var ax = -drag * v.x;
      var ay = -drag * v.y + lift * Math.cos(axis) - G;
      var azc = -drag * v.z + lift * Math.sin(axis);

      v = { x: v.x + ax * dt, y: v.y + ay * dt, z: v.z + azc * dt };
      p = { x: p.x + v.x * dt, y: p.y + v.y * dt, z: p.z + v.z * dt };

      pts.push({ x: p.x, y: p.y, z: p.z });
      if (p.y <= 0 && i > 4) break;
    }

    // Scale downrange so the carry matches the number we advertise.
    var carried = pts[pts.length - 1].x || 1;
    var s = targetCarry / carried;
    return pts.map(function (q) {
      return { x: q.x * s, y: q.y * s, z: q.z * s };
    });
  }

  /* Azimuths are spread wide on purpose. Real drives off one tee sit
   * within a few degrees of each other, which projects to a bundle of
   * near-vertical lines — accurate, and unreadable. Fanning them reads as
   * what it actually is: a range with every bay hitting at once. */
  var SHOT_SPECS = [
    { speed: 167, launch: 12.8, az: -21, axis: 7, carry: 289, hue: "lime" },
    { speed: 149, launch: 16.4, az: -9, axis: -11, carry: 232, hue: "amber" },
    { speed: 158, launch: 10.2, az: 2, axis: 4, carry: 262, hue: "lime" },
    { speed: 138, launch: 21.5, az: 13, axis: 9, carry: 196, hue: "lime" },
    { speed: 172, launch: 14.1, az: 23, axis: -6, carry: 301, hue: "amber" }
  ];

  function initShotTracer() {
    var canvas = $("#shot-tracer");
    if (!canvas) return;

    var ctx = canvas.getContext("2d", { alpha: true });
    if (!ctx) return;

    var W = 0;
    var H = 0;
    var running = true;

    var shots = SHOT_SPECS.map(function (s, i) {
      return {
        pts: simulate(s.speed, s.launch, s.az, s.axis, s.carry),
        t: 0,
        dur: 2.6 + i * 0.18,
        delay: i * 1.45,
        fade: 0,
        hue: s.hue
      };
    });

    function resize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var r = canvas.getBoundingClientRect();
      W = r.width;
      H = r.height;
      canvas.width = Math.round(W * dpr);
      canvas.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    resize();

    if (typeof ResizeObserver !== "undefined") {
      new ResizeObserver(resize).observe(canvas);
    } else {
      window.addEventListener("resize", resize);
    }

    /* Camera sits just behind and above the tee, looking downrange. The
     * result is that every tracer converges on the horizon — the thing
     * that makes it read as distance rather than as a 2D squiggle. */
    var CAM = { back: 12, height: 2.5 };

    /* Two focal lengths, deliberately. A single one ties the lateral fan
     * to viewport height, which collapses the whole spread into a vertical
     * squiggle on a narrow phone. Splitting them lets the fan breathe with
     * width while the arc keeps its height. */
    function focal() {
      return { fx: W * 0.78, fy: Math.max(W, H) * 0.55 };
    }

    function project(p) {
      var f = focal();
      var dx = p.x + CAM.back;
      return {
        x: W * 0.5 + (f.fx * p.z) / dx,
        y: H * 0.42 - (f.fy * (p.y - CAM.height)) / dx,
        // Tapers with distance, but never below ~1px — a hairline that
        // thin simply disappears against the photograph.
        w: Math.max(1.15, Math.min(4.5, 60 / dx))
      };
    }

    function drawGround() {
      var horizon = H * 0.42;
      var f = focal().fx;
      var bands = [50, 100, 150, 200, 250, 300];

      ctx.save();
      // Distance bands, converging toward the horizon.
      for (var i = 0; i < bands.length; i++) {
        var yd = bands[i];
        var p = project({ x: yd, y: 0, z: 0 });
        var spread = (f * 60) / (yd + CAM.back);
        ctx.beginPath();
        ctx.moveTo(W * 0.5 - spread, p.y);
        ctx.lineTo(W * 0.5 + spread, p.y);
        ctx.strokeStyle = "rgba(198,242,78," + Math.max(0, 0.05 - yd * 0.00012) + ")";
        ctx.lineWidth = 1;
        ctx.stroke();
      }
      // Horizon haze.
      var g = ctx.createLinearGradient(0, horizon - 60, 0, horizon + 90);
      g.addColorStop(0, "rgba(198,242,78,0)");
      g.addColorStop(0.5, "rgba(198,242,78,0.045)");
      g.addColorStop(1, "rgba(198,242,78,0)");
      ctx.fillStyle = g;
      ctx.fillRect(0, horizon - 60, W, 150);
      ctx.restore();
    }

    function drawShot(s) {
      var n = s.pts.length;
      var head = Math.floor(s.t * (n - 1));
      if (head < 1) return;

      var rgb = s.hue === "lime" ? "198,242,78" : "240,160,90";
      var alpha = 1 - s.fade;

      ctx.save();
      ctx.lineCap = "round";
      ctx.lineJoin = "round";

      // Trail drawn segment-wise so it can taper in both width and
      // opacity — a single stroked path cannot do that.
      for (var i = 1; i <= head; i++) {
        var a = project(s.pts[i - 1]);
        var b = project(s.pts[i]);
        var age = i / head;
        var tail = Math.pow(age, 0.55);

        ctx.beginPath();
        ctx.moveTo(a.x, a.y);
        ctx.lineTo(b.x, b.y);
        ctx.strokeStyle = "rgba(" + rgb + "," + 0.9 * tail * alpha + ")";
        ctx.lineWidth = b.w * (0.65 + 0.35 * tail);
        ctx.stroke();
      }

      // Glow pass over the live end of the trail.
      var from = Math.max(1, head - 26);
      ctx.beginPath();
      var p0 = project(s.pts[from]);
      ctx.moveTo(p0.x, p0.y);
      for (var j = from + 1; j <= head; j++) {
        var pj = project(s.pts[j]);
        ctx.lineTo(pj.x, pj.y);
      }
      ctx.strokeStyle = "rgba(" + rgb + "," + 0.55 * alpha + ")";
      ctx.lineWidth = 9;
      ctx.filter = "blur(8px)";
      ctx.stroke();
      ctx.filter = "none";

      // The ball.
      var h = project(s.pts[head]);
      var rad = Math.max(2, h.w * 1.5);
      var grad = ctx.createRadialGradient(h.x, h.y, 0, h.x, h.y, rad * 5);
      grad.addColorStop(0, "rgba(255,255,255," + 0.95 * alpha + ")");
      grad.addColorStop(0.25, "rgba(" + rgb + "," + 0.75 * alpha + ")");
      grad.addColorStop(1, "rgba(" + rgb + ",0)");
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.arc(h.x, h.y, rad * 5, 0, Math.PI * 2);
      ctx.fill();

      ctx.fillStyle = "rgba(255,255,255," + alpha + ")";
      ctx.beginPath();
      ctx.arc(h.x, h.y, rad * 0.8, 0, Math.PI * 2);
      ctx.fill();

      ctx.restore();
    }

    var last = performance.now();
    var clock = 0;
    var CYCLE = 9;

    function frame(now) {
      if (!running) return;
      var dt = Math.min(0.05, (now - last) / 1000);
      last = now;
      clock += dt;

      ctx.clearRect(0, 0, W, H);
      drawGround();

      for (var i = 0; i < shots.length; i++) {
        var s = shots[i];
        var local = (clock - s.delay + CYCLE * 10) % CYCLE;

        if (local > s.dur + 2.2) {
          s.t = 0;
          s.fade = 1;
          continue;
        }
        if (local <= s.dur) {
          s.t = local / s.dur;
          s.fade = 0;
        } else {
          s.t = 1;
          s.fade = Math.min(1, (local - s.dur) / 2.2);
        }
        drawShot(s);
      }

      requestAnimationFrame(frame);
    }

    // A static frame is the right answer for reduced motion — the
    // composition still reads, nothing moves.
    if (reduced) {
      ctx.clearRect(0, 0, W, H);
      drawGround();
      shots.slice(0, 3).forEach(function (s) {
        s.t = 1;
        s.fade = 0.35;
        drawShot(s);
      });
    } else {
      requestAnimationFrame(frame);
    }

    document.addEventListener("visibilitychange", function () {
      if (document.hidden) {
        running = false;
      } else if (!reduced) {
        running = true;
        last = performance.now();
        requestAnimationFrame(frame);
      }
    });
  }

  /* ------------------------------------------------------------------ *
   *  Enquiry form
   *
   *  The form is a real POST target, so it already works without this.
   *  Here we upgrade it to submit in place and report inline.
   * ------------------------------------------------------------------ */
  function initForm() {
    var form = $("#enquiry-form");
    if (!form) return;

    var statusEl = $("#form-status");
    var button = form.querySelector('button[type="submit"]');
    var label = form.querySelector("[data-submit-label]");

    function say(html) {
      if (statusEl) statusEl.innerHTML = html;
    }

    function escapeHtml(s) {
      return String(s == null ? "" : s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var fd = new FormData(form);

      /* Honeypot. The server drops these too; this is the belt to that
       * pair of braces. Report success rather than an error — a bot that
       * learns it was caught is a bot that comes back differently. */
      if (String(fd.get("company") || "").trim()) {
        say('<span class="text-lime">Thanks — that’s with us. We’ll be in touch shortly.</span>');
        form.reset();
        return;
      }

      if (button) button.disabled = true;
      if (label) label.textContent = "Sending…";
      say("");

      var params = new URLSearchParams();
      fd.forEach(function (value, key) {
        if (typeof value === "string") params.append(key, value);
      });
      // Tells enquiry.php to answer with JSON instead of a redirect.
      params.append("ajax", "1");

      fetch(form.getAttribute("action"), {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          Accept: "application/json"
        },
        body: params.toString()
      })
        .then(function (res) {
          return res.json().then(
            function (data) { return { ok: res.ok, status: res.status, data: data }; },
            // A non-JSON body means PHP fell over and printed something.
            function () { return { ok: false, status: res.status, data: {} }; }
          );
        })
        .then(function (r) {
          if (button) button.disabled = false;
          if (label) label.textContent = "Send enquiry";

          if (r.ok && r.data && r.data.ok) {
            say('<span class="text-lime">Thanks — that’s with us. We’ll be in touch shortly.</span>');
            form.reset();
            return;
          }

          if (r.status === 400 && r.data && r.data.message) {
            say('<span class="text-amber">' + escapeHtml(r.data.message) + "</span>");
            return;
          }

          /* Delivery failed. Compose the same enquiry as a mail draft so a
           * failed submit costs a click rather than the whole form — and
           * do not reset, so nothing typed is lost. */
          var mailto = buildMailto(fd);
          say(
            '<span class="text-amber">We couldn’t send that automatically — but nothing you typed is lost. ' +
            '<a href="' + escapeHtml(mailto) + '" class="font-medium text-lime underline underline-offset-4 hover:text-bone">Send it by email instead</a>' +
            ", or call us on the number above.</span>"
          );
        })
        .catch(function () {
          if (button) button.disabled = false;
          if (label) label.textContent = "Send enquiry";

          var mailto = buildMailto(fd);
          say(
            '<span class="text-amber">Couldn’t reach the server. ' +
            '<a href="' + escapeHtml(mailto) + '" class="font-medium text-lime underline underline-offset-4 hover:text-bone">Send it by email instead</a>' +
            ", or call us on the number above.</span>"
          );
        });
    });

    function buildMailto(fd) {
      var select = form.querySelector('select[name="interest"]');
      var interestLabel = "General";
      if (select && select.selectedIndex >= 0) {
        interestLabel = select.options[select.selectedIndex].text;
      }

      var lines = [
        "Name: " + (fd.get("name") || ""),
        "Email: " + (fd.get("email") || "")
      ];
      if (String(fd.get("phone") || "").trim()) {
        lines.push("Phone: " + fd.get("phone"));
      }
      lines.push("Interested in: " + interestLabel);
      lines.push("");
      lines.push(String(fd.get("message") || "").trim() || "(no message)");

      var to = form.getAttribute("data-mailto") || "";
      return (
        "mailto:" + to +
        "?subject=" + encodeURIComponent("DPX Golf enquiry — " + interestLabel) +
        "&body=" + encodeURIComponent(lines.join("\n"))
      );
    }
  }

  /* ------------------------------------------------------------------ *
   *  Boot
   *
   *  Each init is isolated: one throwing must not stop the others, or a
   *  single unsupported API takes the whole page's behaviour with it.
   * ------------------------------------------------------------------ */
  function boot() {
    [
      initPreloader,
      initReveals,
      initCounters,
      initMagnetic,
      initSmoothScroll,
      initAnchors,
      initScrollTracer,
      initHeroParallax,
      initInterlude,
      initNav,
      initAudience,
      initPricing,
      initInterestLinks,
      initReticle,
      initShotTracer,
      initForm
    ].forEach(function (fn) {
      try {
        fn();
      } catch (err) {
        if (window.console && console.error) {
          console.error("[dpx] " + (fn.name || "init") + " failed:", err);
        }
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
