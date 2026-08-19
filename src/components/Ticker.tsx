import { TICKER } from "@/lib/content";

/**
 * The eight tracked parameters, running as a band between the hero and
 * the venue. Duplicated once so the -50% keyframe loops seamlessly.
 */
export function Ticker() {
  const row = [...TICKER, ...TICKER];

  return (
    <div className="marquee relative overflow-hidden border-y border-bone/10 bg-ink-2/60 py-4">
      <div className="marquee-track" style={{ ["--dur" as string]: "44s" }}>
        {row.map((label, i) => (
          <span key={i} className="flex shrink-0 items-center gap-8 px-8">
            <span className="data text-[0.68rem] uppercase tracking-[0.26em] text-bone/55">
              {label}
            </span>
            <span className="h-1 w-1 rotate-45 bg-lime/60" />
          </span>
        ))}
      </div>

      {/* Feather the ends so items enter and leave rather than pop. */}
      <div className="pointer-events-none absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-ink to-transparent" />
      <div className="pointer-events-none absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-ink to-transparent" />
    </div>
  );
}
