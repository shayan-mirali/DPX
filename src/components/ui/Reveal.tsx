"use client";

import { ElementType, ReactNode } from "react";
import { useInView } from "@/lib/useInView";

type Props = {
  children: ReactNode;
  /** `mask` slides text up from behind a clipped edge, `wipe` sweeps it
   *  across like a tracer, `up` is the plain lift. */
  variant?: "mask" | "up" | "wipe";
  delay?: number;
  as?: ElementType;
  className?: string;
};

export function Reveal({
  children,
  variant = "up",
  delay = 0,
  as: Tag = "div",
  className = "",
}: Props) {
  const { ref, inView } = useInView<HTMLDivElement>();

  if (variant === "mask") {
    return (
      <div ref={ref} data-inview={inView} className={`reveal-mask overflow-hidden ${className}`}>
        <Tag style={{ transitionDelay: `${delay}ms` }}>{children}</Tag>
      </div>
    );
  }

  // The wipe needs an inner element to carry the clip — see the note in
  // globals.css for why the observed node must stay unclipped.
  if (variant === "wipe") {
    return (
      <Tag ref={ref} data-inview={inView} className={`reveal-wipe ${className}`}>
        <div style={{ ["--d" as string]: `${delay}ms` }}>{children}</div>
      </Tag>
    );
  }

  return (
    <Tag
      ref={ref}
      data-inview={inView}
      style={{ ["--d" as string]: `${delay}ms` }}
      className={`reveal-up ${className}`}
    >
      {children}
    </Tag>
  );
}

/** Splits a heading into lines that each mask upward on a stagger. */
export function RevealLines({
  lines,
  className = "",
  lineClassName = "",
  delay = 0,
  step = 90,
}: {
  lines: ReactNode[];
  className?: string;
  lineClassName?: string;
  delay?: number;
  step?: number;
}) {
  const { ref, inView } = useInView<HTMLDivElement>();

  return (
    <div ref={ref} data-inview={inView} className={className}>
      {lines.map((line, i) => (
        <span key={i} className="block overflow-hidden">
          <span
            className={`block transition-transform duration-[1100ms] ${lineClassName}`}
            style={{
              transitionTimingFunction: "var(--ease-out-expo)",
              transitionDelay: `${delay + i * step}ms`,
              transform: inView ? "translateY(0)" : "translateY(102%)",
            }}
          >
            {line}
          </span>
        </span>
      ))}
    </div>
  );
}
