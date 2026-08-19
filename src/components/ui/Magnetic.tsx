"use client";

import { ReactNode, useRef } from "react";
import { usePrefersReducedMotion } from "@/lib/useInView";

/**
 * Nudges a control toward the cursor when it's nearby. Pointer-events on
 * coarse inputs get nothing, which is correct — there's no cursor to
 * attract to.
 */
export function Magnetic({
  children,
  strength = 0.32,
  className = "",
}: {
  children: ReactNode;
  strength?: number;
  className?: string;
}) {
  const ref = useRef<HTMLSpanElement>(null);
  const reduced = usePrefersReducedMotion();

  const move = (e: React.PointerEvent) => {
    if (reduced || e.pointerType !== "mouse") return;
    const el = ref.current;
    if (!el) return;
    const r = el.getBoundingClientRect();
    const dx = e.clientX - (r.left + r.width / 2);
    const dy = e.clientY - (r.top + r.height / 2);
    el.style.transform = `translate3d(${dx * strength}px, ${dy * strength}px, 0)`;
  };

  const reset = () => {
    const el = ref.current;
    if (el) el.style.transform = "translate3d(0,0,0)";
  };

  return (
    <span
      ref={ref}
      onPointerMove={move}
      onPointerLeave={reset}
      className={`inline-block will-change-transform transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)] ${className}`}
    >
      {children}
    </span>
  );
}
