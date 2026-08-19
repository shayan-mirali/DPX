"use client";

import { useEffect, useRef } from "react";
import { SITE } from "@/lib/content";
import { Reveal } from "@/components/ui/Reveal";
import { usePrefersReducedMotion } from "@/lib/useInView";

/**
 * A breath between the roadmap and the booking form: the tagline drifting
 * on scroll, then the food & drink note. The drift is scroll-linked
 * rather than time-linked, so it reads as a response to the reader
 * instead of ambient noise.
 */
export function Interlude() {
  const trackRef = useRef<HTMLDivElement>(null);
  const sectionRef = useRef<HTMLElement>(null);
  const reduced = usePrefersReducedMotion();

  useEffect(() => {
    if (reduced) return;
    let raf = 0;
    let ticking = false;

    const apply = () => {
      const sec = sectionRef.current;
      const track = trackRef.current;
      if (sec && track) {
        const r = sec.getBoundingClientRect();
        // -1 .. 1 as the section crosses the viewport.
        const p = (window.innerHeight / 2 - (r.top + r.height / 2)) / window.innerHeight;
        track.style.transform = `translate3d(${-p * 70}px, 0, 0)`;
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

  const words = SITE.tagline.split(" ");
  // "Better", "More" and "Differently" are the promise; accent those
  // rather than whatever an every-nth-word rule happens to land on.
  const HIGHLIGHT = new Set(
    words.map((w, i) => (/^(Better|More|Differently)/i.test(w) ? i : -1)).filter((i) => i >= 0)
  );

  return (
    <section ref={sectionRef} className="relative overflow-hidden border-y border-bone/10 py-20 sm:py-28">
      <div ref={trackRef} className="mx-auto max-w-[1180px] px-5 will-change-transform">
        <p className="display text-center text-[clamp(2rem,6.4vw,5.4rem)] leading-[0.95]">
          {words.map((w, i) => (
            <span
              key={i}
              className={`inline-block ${HIGHLIGHT.has(i) ? "text-gradient-lime" : "text-bone"}`}
            >
              {w}
              {i < words.length - 1 ? " " : ""}
            </span>
          ))}
        </p>
      </div>

      <div className="mx-auto mt-16 max-w-[1440px] px-5 sm:px-8">
        <div className="grid gap-8 sm:grid-cols-12 sm:items-start">
          <Reveal className="sm:col-span-4">
            <p className="eyebrow flex items-center gap-3 text-lime">
              <span className="h-px w-8 bg-lime/50" />
              Food &amp; Drink
            </p>
          </Reveal>
          <Reveal delay={120} className="sm:col-span-8">
            <p className="t-lead max-w-[44rem] text-bone/60">
              A selection of refreshments while you play. Competitive round,
              social evening or just catching up with friends — the room is built
              to sit in as much as it is to swing in.
            </p>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
