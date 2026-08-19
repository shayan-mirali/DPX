"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import { NAV, SITE, bookingLinkProps } from "@/lib/content";
import { Magnetic } from "@/components/ui/Magnetic";

export function Nav() {
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  // The mobile sheet takes over the viewport, so lock the page behind it.
  useEffect(() => {
    document.documentElement.style.overflow = open ? "hidden" : "";
    return () => {
      document.documentElement.style.overflow = "";
    };
  }, [open]);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && setOpen(false);
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  return (
    <>
      <header
        className={`fixed inset-x-0 top-0 z-50 transition-all duration-500 ${
          scrolled
            ? "border-b border-bone/10 bg-ink/72 backdrop-blur-xl"
            : "border-b border-transparent bg-transparent"
        }`}
        style={{ transitionTimingFunction: "var(--ease-out-expo)" }}
      >
        <nav className="mx-auto flex h-[var(--nav-h)] max-w-[1440px] items-center justify-between px-5 sm:px-8">
          <a href="#top" aria-label={`${SITE.name} — home`} className="relative z-10 flex items-center">
            <Image
              src="/img/dpx-bone.png"
              alt={SITE.name}
              width={695}
              height={443}
              priority
              className="h-8 w-auto sm:h-9"
            />
          </a>

          <ul className="hidden items-center gap-9 lg:flex">
            {NAV.map((item) => (
              <li key={item.href}>
                <a
                  href={item.href}
                  data-reticle="Go"
                  className="group relative text-[0.9rem] text-bone/70 transition-colors duration-300 hover:text-bone"
                >
                  {item.label}
                  <span className="absolute -bottom-1.5 left-0 h-px w-full origin-right scale-x-0 bg-lime transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:origin-left group-hover:scale-x-100" />
                </a>
              </li>
            ))}
          </ul>

          <div className="flex items-center gap-3">
            {/* The wrapper owns the responsive display. Passing `hidden`
                into Magnetic would collide with its own `inline-block`
                and lose the specificity tie. */}
            <div className="hidden sm:block">
            <Magnetic>
              <a
                {...bookingLinkProps()}
                data-reticle="Book"
                className="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-lime px-5 py-2.5 text-[0.85rem] font-semibold text-ink transition-colors duration-300"
              >
                <span className="relative z-10">Book a Bay</span>
                <span className="relative z-10 transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1">
                  →
                </span>
                <span className="absolute inset-0 translate-y-full bg-bone transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-y-0" />
              </a>
            </Magnetic>
            </div>

            <button
              onClick={() => setOpen((v) => !v)}
              aria-expanded={open}
              aria-label={open ? "Close menu" : "Open menu"}
              className="relative z-10 flex h-10 w-10 items-center justify-center rounded-full hairline lg:hidden"
            >
              <span className="relative block h-3 w-4">
                <span
                  className="absolute left-0 block h-px w-full bg-bone transition-all duration-400"
                  style={{ top: open ? 6 : 1, transform: open ? "rotate(45deg)" : "none" }}
                />
                <span
                  className="absolute left-0 block h-px w-full bg-bone transition-all duration-400"
                  style={{ top: open ? 6 : 10, transform: open ? "rotate(-45deg)" : "none" }}
                />
              </span>
            </button>
          </div>
        </nav>
      </header>

      {/* Mobile sheet */}
      <div
        className="fixed inset-0 z-40 bg-ink-2 transition-[clip-path] duration-[850ms] lg:hidden"
        style={{
          transitionTimingFunction: "var(--ease-in-out-quint)",
          clipPath: open ? "inset(0 0 0% 0)" : "inset(0 0 100% 0)",
          pointerEvents: open ? "auto" : "none",
        }}
        aria-hidden={!open}
      >
        <div className="flex h-full flex-col justify-between px-6 pb-10 pt-[calc(var(--nav-h)+3rem)]">
          <ul className="flex flex-col gap-2">
            {NAV.map((item, i) => (
              <li key={item.href} className="overflow-hidden">
                <a
                  href={item.href}
                  onClick={() => setOpen(false)}
                  className="display block py-2 text-[2.6rem] leading-tight text-bone transition-transform duration-700"
                  style={{
                    transitionTimingFunction: "var(--ease-out-expo)",
                    transitionDelay: `${open ? 180 + i * 70 : 0}ms`,
                    transform: open ? "translateY(0)" : "translateY(110%)",
                  }}
                >
                  {item.label}
                </a>
              </li>
            ))}
          </ul>

          <a
            {...bookingLinkProps()}
            onClick={() => setOpen(false)}
            className="flex items-center justify-between rounded-full bg-lime px-7 py-4 text-base font-semibold text-ink"
          >
            Book a Bay <span>→</span>
          </a>
        </div>
      </div>
    </>
  );
}
