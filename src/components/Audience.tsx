"use client";

import { useState } from "react";
import { AUDIENCES } from "@/lib/content";
import { Reveal, RevealLines } from "@/components/ui/Reveal";

/**
 * Five audiences as an expanding register. Pointer users get it on hover,
 * everyone gets it on click or keyboard — the open row is real state, not
 * a CSS-only hover trick that keyboard users can never reach.
 *
 * Rows animate with grid-template-rows 0fr -> 1fr, which is the only way
 * to transition to auto height without measuring anything.
 */
export function Audience() {
  const [open, setOpen] = useState(0);

  return (
    <section id="who" className="relative py-24 sm:py-36">
      <div className="mx-auto max-w-[1440px] px-5 sm:px-8">
        <div className="grid gap-10 lg:grid-cols-12">
          <div className="lg:col-span-4">
            <Reveal>
              <p className="eyebrow flex items-center gap-3 text-lime">
                <span className="h-px w-8 bg-lime/50" />
                Who It&apos;s For
              </p>
            </Reveal>
            <RevealLines
              className="display t-h2 mt-7"
              lines={[
                <span key="a">Everyone</span>,
                <span key="b">who <span className="text-gradient-lime">swings.</span></span>,
              ]}
            />
            <Reveal delay={220}>
              <p className="mt-7 max-w-[26rem] text-[0.95rem] leading-relaxed text-bone/55">
                Low handicapper or complete beginner, a team away-day or a
                birthday — the bay adapts to whoever is standing in it.
              </p>
            </Reveal>
          </div>

          <div className="lg:col-span-8">
            <ul className="border-t border-bone/12">
              {AUDIENCES.map((a, i) => {
                const isOpen = open === i;
                return (
                  <li key={a.id} className="border-b border-bone/12">
                    <button
                      type="button"
                      onMouseEnter={() => setOpen(i)}
                      onFocus={() => setOpen(i)}
                      onClick={() => setOpen(i)}
                      aria-expanded={isOpen}
                      data-reticle={a.title}
                      className="group flex w-full items-center gap-5 py-6 text-left sm:py-7"
                    >
                      <span
                        className="data shrink-0 text-[11px] tracking-[0.2em] transition-colors duration-500"
                        style={{ color: isOpen ? "var(--color-lime)" : "rgba(237,232,220,.3)" }}
                      >
                        {String(i + 1).padStart(2, "0")}
                      </span>

                      <span
                        className="display flex-1 text-[1.7rem] leading-none transition-all duration-[700ms] sm:text-[2.4rem]"
                        style={{
                          transitionTimingFunction: "var(--ease-out-expo)",
                          color: isOpen ? "var(--color-bone)" : "rgba(237,232,220,.42)",
                          transform: isOpen ? "translateX(10px)" : "translateX(0)",
                        }}
                      >
                        {a.title}
                      </span>

                      <span
                        className="shrink-0 transition-all duration-[700ms]"
                        style={{
                          transitionTimingFunction: "var(--ease-out-expo)",
                          opacity: isOpen ? 1 : 0.25,
                          transform: isOpen ? "rotate(0deg)" : "rotate(-45deg)",
                        }}
                      >
                        <span className="block h-2 w-2 rounded-full bg-lime" />
                      </span>
                    </button>

                    <div
                      className="grid transition-[grid-template-rows] duration-[700ms]"
                      style={{
                        transitionTimingFunction: "var(--ease-out-expo)",
                        gridTemplateRows: isOpen ? "1fr" : "0fr",
                      }}
                    >
                      <div className="overflow-hidden">
                        <div
                          className="flex flex-col gap-3 pb-7 pl-11 pr-4 transition-opacity duration-500 sm:flex-row sm:items-end sm:justify-between sm:gap-10"
                          style={{ opacity: isOpen ? 1 : 0 }}
                        >
                          <p className="max-w-[38rem] text-[0.98rem] leading-relaxed text-bone/60">
                            {a.body}
                          </p>
                          <span className="data shrink-0 text-[10px] uppercase tracking-[0.18em] text-lime/70">
                            {a.note}
                          </span>
                        </div>
                      </div>
                    </div>
                  </li>
                );
              })}
            </ul>
          </div>
        </div>
      </div>
    </section>
  );
}
