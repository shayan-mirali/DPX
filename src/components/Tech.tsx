import Image from "next/image";
import { METRICS } from "@/lib/content";
import { Reveal, RevealLines } from "@/components/ui/Reveal";
import { Counter } from "@/components/ui/Counter";

/**
 * The eight parameters from the brief, presented as an instrument panel
 * rather than a feature list. Each figure scrambles then locks, which is
 * what a launch monitor actually looks like acquiring a shot.
 */
export function Tech() {
  return (
    <section id="tech" className="relative overflow-hidden py-24 sm:py-36">
      {/* Faint measurement grid */}
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0 opacity-[0.055]"
        style={{
          backgroundImage:
            "linear-gradient(var(--color-bone) 1px, transparent 1px), linear-gradient(90deg, var(--color-bone) 1px, transparent 1px)",
          backgroundSize: "72px 72px",
          maskImage: "radial-gradient(80% 60% at 50% 40%, black, transparent 78%)",
        }}
      />

      <div className="relative mx-auto max-w-[1440px] px-5 sm:px-8">
        <div className="grid gap-12 lg:grid-cols-12 lg:gap-8">
          <div className="lg:col-span-5">
            <Reveal>
              <p className="eyebrow flex items-center gap-3 text-lime">
                <span className="h-px w-8 bg-lime/50" />
                Technology
              </p>
            </Reveal>

            <RevealLines
              className="display t-h2 mt-7"
              lines={[
                <span key="a">Measured,</span>,
                <span key="b">not <span className="text-gradient-lime">guessed.</span></span>,
              ]}
            />

            <Reveal delay={200}>
              <p className="t-lead mt-8 max-w-[34rem] text-bone/60">
                Every shot is measured the moment it leaves the club. No guesswork,
                no &ldquo;that felt about right&rdquo; — just clear, accurate data on
                exactly what happens between the club face and the ball, on screen
                before it lands.
              </p>
            </Reveal>

            <Reveal delay={300}>
              <div className="mt-10 flex items-center gap-5 rounded-2xl border border-bone/10 bg-bone/[0.03] p-5">
                <Image
                  src="/img/trackman-bone.png"
                  alt="TrackMan"
                  width={401}
                  height={56}
                  className="h-4 w-auto opacity-80"
                />
                <p className="text-[0.82rem] leading-snug text-bone/45">
                  Powered by TrackMan — trusted on tour, in coaching bays and by
                  golfers worldwide.
                </p>
              </div>
            </Reveal>
          </div>

          {/* Instrument panel */}
          <div className="lg:col-span-7">
            <div className="overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/70 backdrop-blur-md">
              <div className="flex items-center justify-between border-b border-bone/10 px-5 py-3.5 sm:px-6">
                <span className="data text-[10px] uppercase tracking-[0.24em] text-bone/50">
                  Shot Report
                </span>
                <span className="flex items-center gap-2">
                  <span className="h-1.5 w-1.5 rounded-full bg-lime" />
                  <span className="data text-[10px] uppercase tracking-[0.24em] text-lime">
                    Tracking
                  </span>
                </span>
              </div>

              <div className="grid grid-cols-2 gap-px bg-bone/[0.07] sm:grid-cols-4">
                {METRICS.map((m, i) => (
                  <Reveal
                    key={m.key}
                    delay={i * 55}
                    className="group relative bg-ink-2 px-4 py-6 transition-colors duration-500 hover:bg-ink-3 sm:px-5 sm:py-7"
                  >
                    <div className="data text-[9.5px] uppercase leading-tight tracking-[0.16em] text-bone-dim">
                      {m.key}
                    </div>
                    <div className="mt-3 flex items-baseline gap-1">
                      <Counter
                        value={m.value}
                        decimals={m.decimals}
                        duration={1300 + i * 90}
                        className="data text-[1.55rem] leading-none text-bone transition-colors duration-500 group-hover:text-lime sm:text-[1.7rem]"
                      />
                      <span className="data text-[10px] text-lime/70">{m.unit}</span>
                    </div>
                    <span className="absolute bottom-0 left-0 h-px w-full origin-left scale-x-0 bg-lime transition-transform duration-700 [transition-timing-function:var(--ease-out-expo)] group-hover:scale-x-100" />
                  </Reveal>
                ))}
              </div>

              <div className="border-t border-bone/10 px-5 py-3.5 sm:px-6">
                <p className="data text-[10px] uppercase tracking-[0.16em] text-bone/25">
                  Illustrative readout — your numbers are your own
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
