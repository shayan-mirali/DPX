"use client";

import { useEffect, useRef, useState } from "react";

/**
 * Sets `data-inview` on the returned ref once the element crosses the
 * viewport. One-shot by default — reveals shouldn't re-fire and make the
 * page feel twitchy on the way back up.
 */
export function useInView<T extends HTMLElement = HTMLDivElement>(
  { threshold = 0.18, rootMargin = "0px 0px -8% 0px", once = true } = {}
) {
  const ref = useRef<T | null>(null);
  const [inView, setInView] = useState(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    // Anything already on screen at mount (or a browser without IO)
    // should just be visible.
    if (typeof IntersectionObserver === "undefined") {
      setInView(true);
      return;
    }

    const io = new IntersectionObserver(
      ([entry]) => {
        // A jump — an anchor link, a restored scroll position, a flick on
        // a trackpad — can carry an element from below the fold to above
        // it between two observations, so it never reports as
        // intersecting. Treat "already scrolled past" as revealed;
        // leaving it hidden would blank a whole section.
        const scrolledPast =
          !!entry.rootBounds && entry.boundingClientRect.bottom < entry.rootBounds.top;

        if (entry.isIntersecting || scrolledPast) {
          setInView(true);
          if (once) io.disconnect();
        } else if (!once) {
          setInView(false);
        }
      },
      { threshold, rootMargin }
    );

    io.observe(el);
    return () => io.disconnect();
  }, [threshold, rootMargin, once]);

  return { ref, inView };
}

/** True when the user has asked the OS for reduced motion. */
export function usePrefersReducedMotion() {
  const [reduced, setReduced] = useState(false);

  useEffect(() => {
    const mq = window.matchMedia("(prefers-reduced-motion: reduce)");
    setReduced(mq.matches);
    const on = (e: MediaQueryListEvent) => setReduced(e.matches);
    mq.addEventListener("change", on);
    return () => mq.removeEventListener("change", on);
  }, []);

  return reduced;
}
