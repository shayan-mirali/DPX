"use client";

import { useEffect, useState } from "react";
import { usePrefersReducedMotion } from "@/lib/useInView";

/**
 * A short boot sequence framed as the launch monitor acquiring. It is
 * capped at ~1.7s, runs once per session, and is skipped entirely for
 * reduced motion — a preloader that holds content hostage is a bug, not
 * a flourish.
 */
export function Preloader() {
  const reduced = usePrefersReducedMotion();
  const [pct, setPct] = useState(0);
  const [done, setDone] = useState(false);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);

    const seen = sessionStorage.getItem("dpx:booted");
    if (seen || reduced) {
      setDone(true);
      return;
    }

    document.documentElement.style.overflow = "hidden";
    const start = performance.now();
    const DUR = 1500;
    let raf = 0;

    const tick = (now: number) => {
      const p = Math.min(1, (now - start) / DUR);
      const eased = 1 - Math.pow(1 - p, 3);
      setPct(Math.round(eased * 100));
      if (p < 1) {
        raf = requestAnimationFrame(tick);
      } else {
        sessionStorage.setItem("dpx:booted", "1");
        setTimeout(() => {
          setDone(true);
          document.documentElement.style.overflow = "";
        }, 220);
      }
    };

    raf = requestAnimationFrame(tick);

    return () => {
      cancelAnimationFrame(raf);
      document.documentElement.style.overflow = "";
    };
  }, [reduced]);

  // Render nothing server-side so the markup below never flashes for
  // people who have already booted this session.
  if (!mounted || done) return null;

  return (
    <div
      aria-hidden
      className="fixed inset-0 z-[90] flex items-center justify-center bg-ink transition-[clip-path] duration-[900ms]"
      style={{
        transitionTimingFunction: "var(--ease-in-out-quint)",
        clipPath: pct >= 100 ? "inset(0 0 100% 0)" : "inset(0 0 0 0)",
      }}
    >
      <div className="flex w-[min(78vw,340px)] flex-col items-center gap-6">
        {/* Reticle acquiring */}
        <div className="relative h-16 w-16">
          <div
            className="absolute inset-0 rounded-full border border-lime/40"
            style={{ transform: `scale(${0.6 + pct / 250})`, transition: "transform .2s linear" }}
          />
          <div className="absolute inset-[22px] rounded-full bg-bone" />
          <span className="absolute left-1/2 top-0 h-3 w-px -translate-x-1/2 bg-lime" />
          <span className="absolute bottom-0 left-1/2 h-3 w-px -translate-x-1/2 bg-lime" />
          <span className="absolute left-0 top-1/2 h-px w-3 -translate-y-1/2 bg-lime" />
          <span className="absolute right-0 top-1/2 h-px w-3 -translate-y-1/2 bg-lime" />
        </div>

        <div className="w-full">
          <div className="h-px w-full bg-bone/12">
            <div
              className="h-px bg-lime"
              style={{ width: `${pct}%`, transition: "width .12s linear" }}
            />
          </div>
          <div className="data mt-3 flex justify-between text-[10px] uppercase tracking-[0.24em] text-bone-dim">
            <span>Acquiring</span>
            <span className="text-lime">{String(pct).padStart(3, "0")}</span>
          </div>
        </div>
      </div>
    </div>
  );
}
