"use client";

import { useEffect, useRef, useState } from "react";
import { usePrefersReducedMotion } from "@/lib/useInView";

/**
 * The DPX mark is a crosshair locking onto a ball, so the cursor becomes
 * that crosshair. It trails the pointer on a spring, opens up over
 * anything interactive, and reads out a label when one is offered via
 * `data-reticle`.
 *
 * Mouse only. Touch devices never see it, and it never blocks a click.
 */
export function Reticle() {
  const wrapRef = useRef<HTMLDivElement>(null);
  const [active, setActive] = useState(false);
  const [label, setLabel] = useState<string | null>(null);
  const [visible, setVisible] = useState(false);
  // Read through a ref inside the loop. Putting `visible` in the effect
  // deps would tear the whole thing down on the first pointer move and
  // re-seed the spring at screen centre, so the reticle would visibly
  // snap to the middle before catching up.
  const visibleRef = useRef(false);
  const reduced = usePrefersReducedMotion();

  useEffect(() => {
    // Coarse pointers get nothing at all.
    if (reduced || !window.matchMedia("(pointer: fine)").matches) return;

    const target = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    const pos = { ...target };
    let raf = 0;

    const onMove = (e: PointerEvent) => {
      target.x = e.clientX;
      target.y = e.clientY;
      if (!visibleRef.current) {
        // First sighting: drop the reticle straight onto the pointer
        // instead of gliding in from wherever it was parked.
        pos.x = e.clientX;
        pos.y = e.clientY;
        visibleRef.current = true;
        setVisible(true);
      }

      const el = (e.target as HTMLElement)?.closest?.(
        "a, button, input, textarea, select, summary, [data-reticle]"
      ) as HTMLElement | null;

      setActive(!!el);
      setLabel(el?.dataset?.reticle ?? null);
    };

    const loop = () => {
      // Critically-damped-ish follow: quick enough to feel attached,
      // slow enough to feel like it has mass.
      pos.x += (target.x - pos.x) * 0.18;
      pos.y += (target.y - pos.y) * 0.18;
      const el = wrapRef.current;
      if (el) el.style.transform = `translate3d(${pos.x}px, ${pos.y}px, 0)`;
      raf = requestAnimationFrame(loop);
    };

    const onLeave = () => {
      visibleRef.current = false;
      setVisible(false);
    };
    const onEnter = () => setVisible(true);

    window.addEventListener("pointermove", onMove, { passive: true });
    document.addEventListener("pointerleave", onLeave);
    document.addEventListener("pointerenter", onEnter);
    raf = requestAnimationFrame(loop);

    document.documentElement.style.cursor = "none";

    return () => {
      window.removeEventListener("pointermove", onMove);
      document.removeEventListener("pointerleave", onLeave);
      document.removeEventListener("pointerenter", onEnter);
      cancelAnimationFrame(raf);
      document.documentElement.style.cursor = "";
    };
  }, [reduced]);

  if (reduced) return null;

  return (
    <div
      ref={wrapRef}
      aria-hidden
      className="pointer-events-none fixed left-0 top-0 z-[70] hidden [@media(pointer:fine)]:block"
      style={{ opacity: visible ? 1 : 0, transition: "opacity .25s ease" }}
    >
      <div
        className="relative -translate-x-1/2 -translate-y-1/2 transition-[width,height] duration-500"
        style={{
          width: active ? 62 : 30,
          height: active ? 62 : 30,
          transitionTimingFunction: "var(--ease-out-expo)",
        }}
      >
        {/* Ring */}
        <div
          className="absolute inset-0 rounded-full border transition-colors duration-300"
          style={{
            borderColor: active ? "rgba(198,242,78,.9)" : "rgba(237,232,220,.45)",
          }}
        />
        {/* Crosshair ticks, matching the mark */}
        {[
          "left-1/2 top-0 h-[7px] w-px -translate-x-1/2",
          "left-1/2 bottom-0 h-[7px] w-px -translate-x-1/2",
          "top-1/2 left-0 w-[7px] h-px -translate-y-1/2",
          "top-1/2 right-0 w-[7px] h-px -translate-y-1/2",
        ].map((cls, i) => (
          <span
            key={i}
            className={`absolute ${cls} transition-colors duration-300`}
            style={{ background: active ? "rgb(198,242,78)" : "rgba(237,232,220,.6)" }}
          />
        ))}
        {/* Centre dot — the ball */}
        <div
          className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full transition-all duration-500"
          style={{
            width: active ? 5 : 3,
            height: active ? 5 : 3,
            background: active ? "rgb(198,242,78)" : "rgb(237,232,220)",
          }}
        />
      </div>

      {label && (
        <span className="data absolute left-9 top-4 whitespace-nowrap text-[10px] uppercase tracking-[0.2em] text-lime">
          {label}
        </span>
      )}
    </div>
  );
}
