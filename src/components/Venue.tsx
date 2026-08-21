import Image from "next/image";
import { FEATURES, SITE } from "@/lib/content";
import { Reveal, RevealLines } from "@/components/ui/Reveal";

/**
 * Statement, then the room, then the five reasons. The bento is
 * deliberately uneven — a flat 3x2 grid of identical cards is the single
 * most template-looking thing on the web.
 */
export function Venue() {
  return (
    <section id="venue" className="relative py-24 sm:py-36">
      <div className="mx-auto max-w-[1440px] px-5 sm:px-8">
        {/* Statement */}
        <div className="grid gap-10 lg:grid-cols-12">
          <div className="lg:col-span-5">
            <Reveal>
              <p className="eyebrow flex items-center gap-3 text-lime">
                <span className="h-px w-8 bg-lime/50" />
                The Venue
              </p>
            </Reveal>
          </div>

          <div className="lg:col-span-7">
            <RevealLines
              className="display t-h2"
              lines={[
                <span key="a">Golf, indoors</span>,
                <span key="b">
                  done <span className="text-gradient-lime">properly.</span>
                </span>,
              ]}
            />
            <Reveal delay={220}>
              <p className="t-lead mt-8 max-w-[42rem] text-bone/60">
                Whether you&apos;re an experienced golfer chasing a number, a
                beginner picking up a club for the first time, or a group after a
                genuinely different night out — {SITE.name} was built for all of it.
              </p>
            </Reveal>
          </div>
        </div>

        {/* The room */}
        <Reveal variant="wipe" delay={120} className="relative mt-16 sm:mt-24">
          <div className="relative aspect-[16/10] overflow-hidden rounded-3xl sm:aspect-[21/9]">
            <Image
              src="/img/venue-wide.webp"
              alt="Simulator bays, turf and lounge seating inside DPX Golf"
              fill
              sizes="(max-width: 1440px) 100vw, 1440px"
              className="object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-ink/75 via-transparent to-ink/25" />

            {/* Annotation, in the language of the launch monitor */}
            <div className="absolute bottom-5 left-5 flex items-center gap-3 sm:bottom-8 sm:left-8">
              <span className="relative flex h-2.5 w-2.5">
                <span className="absolute inline-flex h-full w-full rounded-full bg-lime/60 [animation:pulse-ring_2.4s_ease-out_infinite]" />
                <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-lime" />
              </span>
              <span className="data text-[10px] uppercase tracking-[0.22em] text-bone/80">
                {SITE.town} · Bays live
              </span>
            </div>
          </div>
        </Reveal>

        {/* Reasons */}
        <div className="mt-6 grid auto-rows-[minmax(190px,auto)] grid-cols-1 gap-4 sm:mt-8 sm:grid-cols-2 lg:grid-cols-4">
          {FEATURES.map((f, i) => {
            const span =
              f.span === "wide"
                ? "sm:col-span-2"
                : f.span === "tall"
                  ? "lg:row-span-2"
                  : "";
            return (
              <Reveal
                key={f.n}
                delay={i * 70}
                className={`panel group relative flex flex-col justify-between overflow-hidden rounded-2xl p-6 sm:p-7 ${span}`}
              >
                {/* Lime bloom that follows the card on hover */}
                <span className="pointer-events-none absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                  style={{
                    background:
                      "radial-gradient(120% 100% at 50% 0%, rgba(198,242,78,.09), transparent 62%)",
                  }}
                />
                <span className="data relative text-[11px] tracking-[0.2em] text-lime/70">
                  {f.n}
                </span>
                <div className="relative mt-10">
                  <h3 className="display t-h3 leading-[1.05]">{f.title}</h3>
                  <p className="mt-3 max-w-[34rem] text-[0.95rem] leading-relaxed text-bone/55">
                    {f.body}
                  </p>
                </div>
                <span className="absolute bottom-0 left-0 h-px w-full origin-left scale-x-0 bg-gradient-to-r from-lime to-transparent transition-transform duration-[900ms] [transition-timing-function:var(--ease-out-expo)] group-hover:scale-x-100" />
              </Reveal>
            );
          })}
        </div>
      </div>
    </section>
  );
}
