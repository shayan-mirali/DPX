"use client";

import { useEffect, useRef } from "react";

/**
 * A hairline down the right edge that fills as you read — the page's own
 * shot tracer. Purely decorative, so it is hidden from assistive tech and
 * from small screens where it would only crowd the content.
 */
export function ScrollTracer() {
  const fillRef = useRef<HTMLSpanElement>(null);
  const pctRef = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    let raf = 0;
    let ticking = false;

    const apply = () => {
      const max = document.documentElement.scrollHeight - window.innerHeight;
      const p = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
      if (fillRef.current) fillRef.current.style.transform = `scaleY(${p})`;
      if (pctRef.current) pctRef.current.textContent = String(Math.round(p * 100)).padStart(3, "0");
      ticking = false;
    };

    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      raf = requestAnimationFrame(apply);
    };

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
    apply();

    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onScroll);
      cancelAnimationFrame(raf);
    };
  }, []);

  return (
    <div
      aria-hidden
      className="fixed right-5 top-1/2 z-40 hidden -translate-y-1/2 flex-col items-center gap-3 min-[1620px]:flex"
    >
      <span className="data text-[9px] tracking-[0.2em] text-bone/30">000</span>
      <span className="relative block h-40 w-px bg-bone/12">
        <span
          ref={fillRef}
          className="absolute inset-0 block origin-top bg-gradient-to-b from-lime to-amber"
          style={{ transform: "scaleY(0)" }}
        />
      </span>
      <span ref={pctRef} className="data text-[9px] tracking-[0.2em] text-lime">
        000
      </span>
    </div>
  );
}
