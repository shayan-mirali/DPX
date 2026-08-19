"use client";

import { ROADMAP } from "@/lib/content";
import { Reveal, RevealLines } from "@/components/ui/Reveal";

/**
 * Membership, Coaching and Competitions are all pre-launch. Three
 * "coming soon" blocks would normally be dead weight, so they're framed
 * as a development track with live status — the honest version is also
 * the more interesting one, and every card converts into a signup rather
 * than an apology.
 */
export function Roadmap() {
  const register = (id: string) => {
    window.dispatchEvent(new CustomEvent("dpx:interest", { detail: id }));
    document.querySelector("#book")?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section id="coming" className="relative overflow-hidden py-24 sm:py-36">
      {/* Sweep, like a radar pass over the track */}
      <div
        aria-hidden
        className="pointer-events-none absolute inset-x-0 top-1/2 h-px"
        style={{
          background:
            "linear-gradient(90deg, transparent, rgba(198,242,78,.35) 50%, transparent)",
        }}
      />

      <div className="relative mx-auto max-w-[1440px] px-5 sm:px-8">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <Reveal>
              <p className="eyebrow flex items-center gap-3 text-amber">
                <span className="h-px w-8 bg-amber/50" />
                In Development
              </p>
            </Reveal>
            <RevealLines
              className="display t-h2 mt-7"
              lines={[
                <span key="a">What&apos;s</span>,
                <span key="b">coming <span className="text-gradient-lime">next.</span></span>,
              ]}
            />
          </div>

          <Reveal delay={180}>
            <p className="max-w-[26rem] text-[0.95rem] leading-relaxed text-bone/55">
              Three things are being built right now. Register your interest and
              you&apos;ll be first through the door when each one opens.
            </p>
          </Reveal>
        </div>

        <div className="mt-14 grid gap-4 lg:grid-cols-3">
          {ROADMAP.map((r, i) => (
            <Reveal
              key={r.id}
              delay={i * 110}
              className="group relative flex flex-col overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/60 p-7 backdrop-blur-md transition-colors duration-500 hover:border-lime/25 sm:p-8"
            >
              {/* Status */}
              <div className="flex items-center justify-between">
                <span className="flex items-center gap-2.5">
                  <span className="relative flex h-2 w-2">
                    <span
                      className="absolute inline-flex h-full w-full rounded-full bg-amber/60 [animation:pulse-ring_2.6s_ease-out_infinite]"
                      style={{ animationDelay: `${i * 0.5}s` }}
                    />
                    <span className="relative inline-flex h-2 w-2 rounded-full bg-amber" />
                  </span>
                  <span className="data text-[10px] uppercase tracking-[0.2em] text-amber">
                    Coming soon
                  </span>
                </span>
                <span className="data text-[11px] tracking-[0.2em] text-bone/25">
                  {String(i + 1).padStart(2, "0")}
                </span>
              </div>

              <h3 className="display mt-8 text-[1.9rem] leading-none sm:text-[2.2rem]">
                {r.title}
              </h3>
              <p className="mt-3.5 text-[0.95rem] leading-relaxed text-bone/55">{r.lede}</p>

              <ul className="mt-7 flex flex-1 flex-col gap-2.5">
                {r.points.map((p) => (
                  <li key={p} className="flex items-start gap-3">
                    <span className="mt-[7px] h-1 w-1 shrink-0 rotate-45 bg-lime/70" />
                    <span className="text-[0.88rem] leading-snug text-bone/60">{p}</span>
                  </li>
                ))}
              </ul>

              <button
                type="button"
                onClick={() => register(r.id)}
                data-reticle="Register"
                className="mt-9 flex items-center justify-between rounded-full border border-bone/15 px-6 py-3.5 text-[0.9rem] font-medium text-bone transition-colors duration-400 hover:border-lime hover:bg-lime hover:text-ink"
              >
                {r.cta}
                <span className="transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1">
                  →
                </span>
              </button>

              <span
                aria-hidden
                className="pointer-events-none absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                style={{
                  background:
                    "radial-gradient(100% 70% at 50% 0%, rgba(198,242,78,.07), transparent 60%)",
                }}
              />
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
