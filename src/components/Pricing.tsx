"use client";

import { useState } from "react";
import { PRICING, gbp, perPlayer, bookingLinkProps } from "@/lib/content";
import { Reveal, RevealLines } from "@/components/ui/Reveal";

/**
 * The rate card is a 4 × 4 matrix per period — players down, hours
 * across. It's shown whole on desktop, because comparing across it is
 * the entire point of a rate card. On phones the same grid becomes an
 * hours picker over four player rows, since sixteen price cells at 360px
 * is either unreadable or a sideways scroll.
 *
 * Both views read the same rows from content.ts and derive the
 * per-person figure, so changing a price is one number in one place.
 *
 * The two switches are toggle buttons with `aria-pressed`, not a
 * tablist: the same panel is rendered twice (desktop table, mobile list)
 * and a tablist would need one unique panel id per tab to point at.
 */
export function Pricing() {
  const [periodId, setPeriodId] = useState<string>(PRICING.periods[0].id);
  const [hours, setHours] = useState(1);

  const period = PRICING.periods.find((p) => p.id === periodId) ?? PRICING.periods[0];
  const hourIdx = Math.max(0, PRICING.durations.findIndex((d) => d === hours));

  const players = (n: number) => `${n} ${n === 1 ? "Player" : "Players"}`;

  return (
    <section id="pricing" className="relative overflow-hidden py-24 sm:py-36">
      {/* Faint measurement grid, as on the shot report — a rate card is
          another readout. */}
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0 opacity-[0.04]"
        style={{
          backgroundImage:
            "linear-gradient(var(--color-bone) 1px, transparent 1px), linear-gradient(90deg, var(--color-bone) 1px, transparent 1px)",
          backgroundSize: "72px 72px",
          maskImage: "radial-gradient(75% 60% at 50% 45%, black, transparent 80%)",
        }}
      />

      <div className="relative mx-auto max-w-[1440px] px-5 sm:px-8">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <Reveal>
              <p className="eyebrow flex items-center gap-3 text-lime">
                <span className="h-px w-8 bg-lime/50" />
                Pricing
              </p>
            </Reveal>
            <RevealLines
              className="display t-h2 mt-7"
              lines={[
                <span key="a">By the hour,</span>,
                <span key="b">
                  by the <span className="text-gradient-lime">bay.</span>
                </span>,
              ]}
            />
          </div>

          <Reveal delay={180}>
            <p className="max-w-[26rem] text-[0.95rem] leading-relaxed text-bone/55">
              One price for the bay, up to four players in it. Split four ways,
              an hour costs less each than a round of drinks.
            </p>
          </Reveal>
        </div>

        {/* Period switch */}
        <Reveal delay={120} className="mt-12">
          <div
            role="group"
            aria-label="Pricing period"
            className="inline-flex rounded-full border border-bone/12 bg-ink-2/60 p-1 backdrop-blur-md"
          >
            {PRICING.periods.map((p) => {
              const on = p.id === period.id;
              return (
                <button
                  key={p.id}
                  type="button"
                  aria-pressed={on}
                  onClick={() => setPeriodId(p.id)}
                  data-reticle="Switch"
                  className={`rounded-full px-5 py-2.5 text-[0.85rem] font-medium transition-colors duration-400 sm:px-7 ${
                    on ? "bg-lime text-ink" : "text-bone/60 hover:text-bone"
                  }`}
                >
                  {p.label}
                </button>
              );
            })}
          </div>

          <p className="data mt-4 text-[11px] uppercase tracking-[0.16em] text-bone/40">
            {period.when}
          </p>
        </Reveal>

        {/* ---------------- Desktop: the whole matrix ---------------- */}
        <Reveal
          delay={200}
          className="mt-8 hidden overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/70 backdrop-blur-md md:block"
        >
          <table className="w-full border-collapse text-left">
            <caption className="sr-only">
              {period.label} — {period.when}. Bay prices by number of players and
              session length.
            </caption>
            <thead>
              <tr className="border-b border-bone/10">
                <th
                  scope="col"
                  className="data px-6 py-4 text-[10px] font-medium uppercase tracking-[0.2em] text-bone/50"
                >
                  {period.label}
                </th>
                {PRICING.durations.map((h) => (
                  <th
                    key={h}
                    scope="col"
                    className="data px-6 py-4 text-[10px] font-medium uppercase tracking-[0.2em] text-bone/50"
                  >
                    {h} {h === 1 ? "Hour" : "Hours"}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {period.rows.map((row) => (
                <tr
                  key={row.players}
                  className="group border-b border-bone/[0.07] transition-colors duration-500 last:border-b-0 hover:bg-ink-3/60"
                >
                  <th scope="row" className="px-6 py-6 text-[0.95rem] font-medium text-bone/75">
                    {players(row.players)}
                  </th>
                  {row.totals.map((total, i) => (
                    <td key={i} className="px-6 py-6">
                      <span className="data block text-[1.45rem] leading-none text-bone transition-colors duration-500 group-hover:text-lime">
                        {gbp(total)}
                      </span>
                      {row.players > 1 && (
                        <span className="data mt-1.5 block text-[11px] text-bone/40">
                          {gbp(perPlayer(total, row.players))} each
                        </span>
                      )}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </Reveal>

        {/* ---------------- Mobile: pick the hours, read the rows -------- */}
        <div className="mt-8 md:hidden">
          <div
            role="group"
            aria-label="Session length"
            className="grid grid-cols-4 gap-1.5 rounded-2xl border border-bone/12 bg-ink-2/60 p-1.5"
          >
            {PRICING.durations.map((h) => {
              const on = h === hours;
              return (
                <button
                  key={h}
                  type="button"
                  aria-pressed={on}
                  onClick={() => setHours(h)}
                  className={`data rounded-xl py-2.5 text-[11px] uppercase tracking-[0.12em] transition-colors duration-400 ${
                    on ? "bg-lime text-ink" : "text-bone/55"
                  }`}
                >
                  {h} hr{h === 1 ? "" : "s"}
                </button>
              );
            })}
          </div>

          <ul className="mt-4 overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/70">
            {period.rows.map((row) => (
              <li
                key={row.players}
                className="flex items-baseline justify-between gap-4 border-b border-bone/[0.07] px-5 py-5 last:border-b-0"
              >
                <span className="text-[0.95rem] text-bone/75">{players(row.players)}</span>
                <span className="text-right">
                  <span className="data block text-[1.35rem] leading-none text-bone">
                    {gbp(row.totals[hourIdx])}
                  </span>
                  {row.players > 1 && (
                    <span className="data mt-1.5 block text-[11px] text-bone/40">
                      {gbp(perPlayer(row.totals[hourIdx], row.players))} each
                    </span>
                  )}
                </span>
              </li>
            ))}
          </ul>
        </div>

        {/* VAT, stated on its own line rather than buried in the notes —
            consumer pricing has to be unambiguous about it. */}
        <Reveal delay={100}>
          <p className="mt-6 text-[0.9rem] font-medium text-bone/70">
            {PRICING.vatNote}
          </p>
        </Reveal>

        {/* Conditions, then the ask */}
        <div className="mt-8 flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
          <Reveal delay={120}>
            <ul className="flex flex-col gap-2.5">
              {PRICING.notes.map((n) => (
                <li key={n} className="flex items-start gap-3">
                  <span className="mt-[7px] h-1 w-1 shrink-0 rotate-45 bg-lime/70" />
                  <span className="max-w-[36rem] text-[0.88rem] leading-snug text-bone/55">
                    {n}
                  </span>
                </li>
              ))}
            </ul>
          </Reveal>

          <Reveal delay={200}>
            <a
              {...bookingLinkProps()}
              data-reticle="Book"
              className="group inline-flex items-center gap-2.5 rounded-full border border-bone/15 px-7 py-3.5 text-[0.9rem] font-medium text-bone transition-colors duration-400 hover:border-lime hover:bg-lime hover:text-ink"
            >
              Book a Bay
              <span className="transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1">
                →
              </span>
            </a>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
