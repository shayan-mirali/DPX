"use client";

import { useEffect, useRef, useState } from "react";
import { useInView, usePrefersReducedMotion } from "@/lib/useInView";

const GLYPHS = "0123456789";

/**
 * Counts a number into place the way a launch monitor resolves a reading:
 * a brief scramble while it "acquires", then a settle onto the true value.
 */
export function Counter({
  value,
  decimals = 0,
  duration = 1400,
  className = "",
}: {
  value: number;
  decimals?: number;
  duration?: number;
  className?: string;
}) {
  const { ref, inView } = useInView<HTMLSpanElement>({ threshold: 0.5 });
  const reduced = usePrefersReducedMotion();
  const [display, setDisplay] = useState(() => (reduced ? value.toFixed(decimals) : null));
  const raf = useRef<number | null>(null);

  useEffect(() => {
    if (!inView) return;

    if (reduced) {
      setDisplay(value.toFixed(decimals));
      return;
    }

    const start = performance.now();
    const scrambleFor = duration * 0.35;

    const tick = (now: number) => {
      const t = now - start;

      if (t < scrambleFor) {
        // Acquisition phase: right shape, wrong digits.
        const template = value.toFixed(decimals);
        const scrambled = template
          .split("")
          .map((ch) => (/\d/.test(ch) ? GLYPHS[(Math.random() * 10) | 0] : ch))
          .join("");
        setDisplay(scrambled);
      } else {
        const p = Math.min(1, (t - scrambleFor) / (duration - scrambleFor));
        // easeOutExpo
        const eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
        setDisplay((value * eased).toFixed(decimals));
        if (p >= 1) {
          setDisplay(value.toFixed(decimals));
          return;
        }
      }
      raf.current = requestAnimationFrame(tick);
    };

    raf.current = requestAnimationFrame(tick);
    return () => {
      if (raf.current) cancelAnimationFrame(raf.current);
    };
  }, [inView, value, decimals, duration, reduced]);

  return (
    <span ref={ref} className={className}>
      {display ?? (0).toFixed(decimals)}
    </span>
  );
}
