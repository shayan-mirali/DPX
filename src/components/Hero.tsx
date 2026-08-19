"use client";

import Image from "next/image";
import { useEffect, useRef } from "react";
import { SITE, bookingLinkProps } from "@/lib/content";
import { ShotTracer } from "@/components/ShotTracer";
import { Magnetic } from "@/components/ui/Magnetic";
import { usePrefersReducedMotion } from "@/lib/useInView";

const LQIP =
  "data:image/webp;base64,UklGRmYAAABXRUJQVlA4IFoAAADQBACdASoYABIAPuVepk2pJSOiN/VYASAciWUAw3AL1TNrnCvDQSEouI6+Uuy3IAD+7L2CbscT3jzhx/h67ji1vQJPZXjgROx6vPhDQHd8YcTy40Bp0BQAAAA=";

const READOUT = [
  { k: "Ball Speed", v: "167.4", u: "mph" },
  { k: "Carry", v: "289", u: "yds" },
  { k: "Launch", v: "12.8", u: "deg" },
  { k: "Spin", v: "2540", u: "rpm" },
];

export function Hero() {
  const plateRef = useRef<HTMLDivElement>(null);
  const reduced = usePrefersReducedMotion();

  // Parallax on the plate. A plain rAF-throttled scroll handler is both
  // cheaper and easier to reason about than a scroll-linked library here.
  useEffect(() => {
    if (reduced) return;
    let raf = 0;
    let ticking = false;

    const apply = () => {
      const y = window.scrollY;
      const el = plateRef.current;
      if (el && y < window.innerHeight * 1.2) {
        el.style.transform = `translate3d(0, ${y * 0.22}px, 0) scale(${1 + y * 0.00012})`;
      }
      ticking = false;
    };

    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      raf = requestAnimationFrame(apply);
    };

    window.addEventListener("scroll", onScroll, { passive: true });
    apply();
    return () => {
      window.removeEventListener("scroll", onScroll);
      cancelAnimationFrame(raf);
    };
  }, [reduced]);

  return (
    <section id="top" className="relative isolate min-h-[100svh] overflow-hidden">
      {/* Venue plate */}
      <div ref={plateRef} className="absolute inset-0 -z-20 will-change-transform">
        <Image
          src="/img/venue-wide.webp"
          alt="The simulator bays at DPX Golf, Burton upon Trent"
          fill
          priority
          placeholder="blur"
          blurDataURL={LQIP}
          sizes="100vw"
          className="scale-105 object-cover object-[30%_center] opacity-90"
        />
      </div>

      {/* Grade. A single flat scrim would kill the room, so this is three
          targeted passes: a soft top so the nav clears, a left-weighted
          scrim carrying the headline, and a floor that hands off to the
          page background. The bright corridor on the right is left alone —
          it's the only real depth the photograph has. */}
      <div
        className="absolute inset-0 -z-10"
        style={{
          background:
            "linear-gradient(180deg, rgba(6,10,9,.85) 0%, rgba(6,10,9,.25) 26%, rgba(6,10,9,.45) 58%, rgba(6,10,9,.94) 88%, var(--color-ink) 100%)",
        }}
      />
      <div
        className="absolute inset-0 -z-10"
        style={{
          background:
            "linear-gradient(96deg, rgba(6,10,9,.93) 0%, rgba(6,10,9,.72) 34%, rgba(6,10,9,.18) 62%, transparent 85%)",
        }}
      />
      <div
        className="absolute inset-0 -z-10"
        style={{
          background:
            "radial-gradient(135% 95% at 42% 45%, transparent 42%, rgba(6,10,9,.7) 100%)",
        }}
      />

      {/* Tracers */}
      {/* Held back on phones, where the arcs cross the body copy rather
          than sitting in clear space beside it as they do on desktop. */}
      <ShotTracer className="absolute inset-0 -z-[5] h-full w-full opacity-45 [mix-blend-mode:screen] sm:opacity-100" />

      {/* Content */}
      <div className="relative mx-auto flex min-h-[100svh] max-w-[1440px] flex-col justify-end px-5 pb-10 pt-[var(--nav-h)] sm:px-8 sm:pb-14">
        <div className="max-w-[62rem]">
          <p
            className="eyebrow mb-7 flex flex-wrap items-center gap-x-3 gap-y-1 text-lime"
            style={{ animation: "hero-fade .9s var(--ease-out-expo) .05s both" }}
          >
            <span className="inline-block h-1.5 w-1.5 rounded-full bg-lime" />
            {SITE.descriptor}
            <span className="text-bone/30">/</span>
            {SITE.town}
          </p>

          <h1 className="display t-hero">
            {["Your next round", "is always on."].map((line, i) => (
              <span key={line} className="block overflow-hidden">
                <span
                  className="block"
                  style={{
                    animation: `hero-line 1.15s var(--ease-out-expo) ${0.15 + i * 0.12}s both`,
                  }}
                >
                  {i === 1 ? (
                    <>
                      is <span className="text-gradient-lime">always on.</span>
                    </>
                  ) : (
                    line
                  )}
                </span>
              </span>
            ))}
          </h1>

          <p
            className="t-lead mt-7 max-w-[46rem] text-bone/65"
            style={{ animation: "hero-fade 1s var(--ease-out-expo) .55s both" }}
          >
            Rain or shine, summer or winter. TrackMan-powered bays in the heart of{" "}
            {SITE.town} — play the world&apos;s great courses, practise against
            tour-level data, or just pull up a chair with friends.
          </p>

          <div
            className="mt-10 flex flex-wrap items-center gap-4"
            style={{ animation: "hero-fade 1s var(--ease-out-expo) .7s both" }}
          >
            <Magnetic strength={0.24}>
              <a
                {...bookingLinkProps()}
                data-reticle="Book a bay"
                className="group relative inline-flex items-center gap-3 overflow-hidden rounded-full bg-lime px-8 py-4 text-base font-semibold text-ink"
              >
                <span className="relative z-10">Book a Bay</span>
                <span className="relative z-10 transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1.5">
                  →
                </span>
                <span className="absolute inset-0 translate-y-full bg-bone transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)] group-hover:translate-y-0" />
              </a>
            </Magnetic>

            <a
              href="#venue"
              data-reticle="Look inside"
              className="group inline-flex items-center gap-3 rounded-full px-7 py-4 text-base text-bone/80 hairline transition-colors duration-300 hover:border-bone/30 hover:text-bone"
            >
              See the venue
              <span className="inline-block h-1.5 w-1.5 rounded-full bg-lime transition-transform duration-500 group-hover:scale-150" />
            </a>
          </div>
        </div>

        {/* Live readout strip */}
        <div
          className="mt-14 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-bone/10 bg-bone/[0.06] sm:grid-cols-4"
          style={{ animation: "hero-fade 1s var(--ease-out-expo) .9s both" }}
        >
          {READOUT.map((r) => (
            <div key={r.k} className="bg-ink/70 px-5 py-4 backdrop-blur-md">
              <div className="data text-[10px] uppercase tracking-[0.2em] text-bone-dim">
                {r.k}
              </div>
              <div className="mt-1.5 flex items-baseline gap-1.5">
                <span className="data text-2xl text-bone sm:text-[1.75rem]">{r.v}</span>
                <span className="data text-[11px] text-lime">{r.u}</span>
              </div>
            </div>
          ))}
        </div>
        <p className="data mt-3 text-[10px] uppercase tracking-[0.18em] text-bone/25">
          Sample TrackMan readout
        </p>
      </div>
    </section>
  );
}
