"use client";

import { useEffect, useRef, useState } from "react";
import { SITE } from "@/lib/content";
import { Reveal, RevealLines } from "@/components/ui/Reveal";

const INTERESTS = [
  { value: "bay", label: "Book a bay" },
  { value: "membership", label: "Membership" },
  { value: "coaching", label: "Coaching" },
  { value: "competitions", label: "Competitions & leagues" },
  { value: "corporate", label: "Corporate or group event" },
  { value: "other", label: "Something else" },
];

type Status = "idle" | "sending" | "sent" | "unconfigured" | "error";

export function Book() {
  const [interest, setInterest] = useState("bay");
  const [status, setStatus] = useState<Status>("idle");
  const [message, setMessage] = useState("");
  const formRef = useRef<HTMLFormElement>(null);

  // The roadmap cards deep-link into this form with a topic preselected.
  useEffect(() => {
    const onInterest = (e: Event) => {
      const id = (e as CustomEvent<string>).detail;
      if (INTERESTS.some((i) => i.value === id)) setInterest(id);
    };
    window.addEventListener("dpx:interest", onInterest);
    return () => window.removeEventListener("dpx:interest", onInterest);
  }, []);

  const submit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setStatus("sending");
    setMessage("");

    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());

    try {
      const res = await fetch("/api/enquiry", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(() => ({}));

      if (res.ok) {
        setStatus("sent");
        formRef.current?.reset();
        setInterest("bay");
      } else if (res.status === 503) {
        // No delivery target configured yet. Say so plainly rather than
        // showing a success state over a dropped enquiry.
        setStatus("unconfigured");
        setMessage(data.message ?? "");
      } else {
        setStatus("error");
        setMessage(data.message ?? "Something went wrong. Please try again.");
      }
    } catch {
      setStatus("error");
      setMessage("Couldn't reach the server. Please check your connection.");
    }
  };

  const field =
    "w-full rounded-xl border border-bone/12 bg-bone/[0.04] px-4 py-3.5 text-[0.95rem] text-bone placeholder:text-bone/30 transition-colors duration-300 focus:border-lime/60 focus:bg-bone/[0.06] focus:outline-none";

  return (
    <section id="book" className="relative overflow-hidden py-24 sm:py-36">
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0"
        style={{
          background:
            "radial-gradient(70% 55% at 50% 100%, rgba(198,242,78,.07), transparent 70%)",
        }}
      />

      <div className="relative mx-auto max-w-[1440px] px-5 sm:px-8">
        <div className="grid gap-14 lg:grid-cols-12 lg:gap-10">
          {/* Pitch */}
          <div className="lg:col-span-5">
            <Reveal>
              <p className="eyebrow flex items-center gap-3 text-lime">
                <span className="h-px w-8 bg-lime/50" />
                Book Your Bay
              </p>
            </Reveal>

            <RevealLines
              className="display t-h2 mt-7"
              lines={[
                <span key="a">Bring your clubs.</span>,
                <span key="b">We&apos;ll do <span className="text-gradient-lime">the rest.</span></span>,
              ]}
            />

            <Reveal delay={220}>
              <p className="t-lead mt-8 max-w-[34rem] text-bone/60">
                Eighteen holes, an hour on the range or a first ever swing —
                tell us what you&apos;re after and we&apos;ll get you booked in.
              </p>
            </Reveal>

            <Reveal delay={300}>
              <dl className="mt-12 flex flex-col gap-5 border-t border-bone/12 pt-8">
                {[
                  ["Where", SITE.address ?? `${SITE.town}, Staffordshire`],
                  ["Opening hours", SITE.hours ?? "Details coming shortly"],
                  ["Call", SITE.phone ?? "Details coming shortly"],
                ].map(([k, v]) => (
                  <div key={k} className="flex items-baseline justify-between gap-6">
                    <dt className="data text-[10px] uppercase tracking-[0.2em] text-bone-dim">
                      {k}
                    </dt>
                    <dd className="text-right text-[0.95rem] text-bone/75">{v}</dd>
                  </div>
                ))}
              </dl>
            </Reveal>
          </div>

          {/* Form */}
          <div className="lg:col-span-7">
            <Reveal delay={120} className="rounded-3xl border border-bone/10 bg-ink-2/60 p-6 backdrop-blur-md sm:p-9">
              <form ref={formRef} onSubmit={submit} className="flex flex-col gap-5">
                <div className="grid gap-5 sm:grid-cols-2">
                  <label className="flex flex-col gap-2">
                    <span className="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                      Name
                    </span>
                    <input name="name" required autoComplete="name" placeholder="Your name" className={field} />
                  </label>

                  <label className="flex flex-col gap-2">
                    <span className="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                      Email
                    </span>
                    <input
                      name="email"
                      type="email"
                      required
                      autoComplete="email"
                      placeholder="you@example.com"
                      className={field}
                    />
                  </label>
                </div>

                <div className="grid gap-5 sm:grid-cols-2">
                  <label className="flex flex-col gap-2">
                    <span className="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                      Phone <span className="text-bone/25">(optional)</span>
                    </span>
                    <input name="phone" type="tel" autoComplete="tel" placeholder="07…" className={field} />
                  </label>

                  <label className="flex flex-col gap-2">
                    <span className="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                      I&apos;m interested in
                    </span>
                    <select
                      name="interest"
                      value={interest}
                      onChange={(e) => setInterest(e.target.value)}
                      className={`${field} appearance-none`}
                    >
                      {INTERESTS.map((i) => (
                        <option key={i.value} value={i.value} className="bg-ink-2">
                          {i.label}
                        </option>
                      ))}
                    </select>
                  </label>
                </div>

                <label className="flex flex-col gap-2">
                  <span className="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                    Anything else? <span className="text-bone/25">(optional)</span>
                  </span>
                  <textarea
                    name="message"
                    rows={4}
                    placeholder="Group size, preferred day, whether you've played before…"
                    className={`${field} resize-none`}
                  />
                </label>

                {/* Bot trap — real people never fill this in. */}
                <input
                  type="text"
                  name="company"
                  tabIndex={-1}
                  autoComplete="off"
                  aria-hidden
                  className="absolute left-[-9999px] h-0 w-0 opacity-0"
                />

                <button
                  type="submit"
                  disabled={status === "sending"}
                  data-reticle="Send"
                  className="group relative mt-2 inline-flex w-full items-center justify-center gap-3 overflow-hidden rounded-full bg-lime px-8 py-4 text-base font-semibold text-ink transition-opacity disabled:opacity-60 sm:w-auto sm:self-start"
                >
                  <span className="relative z-10">
                    {status === "sending" ? "Sending…" : "Send enquiry"}
                  </span>
                  <span className="relative z-10 transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1.5">
                    →
                  </span>
                  <span className="absolute inset-0 translate-y-full bg-bone transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)] group-hover:translate-y-0" />
                </button>

                <p aria-live="polite" className="min-h-[1.4rem] text-[0.875rem]">
                  {status === "sent" && (
                    <span className="text-lime">
                      Thanks — that&apos;s with us. We&apos;ll be in touch shortly.
                    </span>
                  )}
                  {status === "unconfigured" && (
                    <span className="text-amber">
                      {message ||
                        "The enquiry system isn't connected yet — please contact the venue directly."}
                    </span>
                  )}
                  {status === "error" && <span className="text-amber">{message}</span>}
                </p>
              </form>
            </Reveal>
          </div>
        </div>
      </div>
    </section>
  );
}
