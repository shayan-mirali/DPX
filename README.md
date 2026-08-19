# DPX Golf

Marketing site for DPX Golf — TrackMan-powered indoor golf in Burton upon Trent.

Next.js 15 (App Router) · React 19 · Tailwind v4 · TypeScript.

---

## Running it

```bash
npm install
npm run dev        # http://localhost:3000
npm run build && npm start
npm run assets     # regenerate images in public/img from _assets/
```

---

## Before this goes live

These are the things that need a real answer. They're marked `TODO` in
`src/lib/content.ts` and render as visible "coming shortly" placeholders
rather than invented details.

| What | Where |
| --- | --- |
| Street address, phone, email, opening hours | `src/lib/content.ts` → `SITE` |
| Booking system URL, if one exists | `SITE.bookingUrl` |
| Enquiry delivery target | `ENQUIRY_WEBHOOK_URL` env var (see below) |
| Real domain | `metadataBase` in `src/app/layout.tsx` |

### The enquiry form

`POST /api/enquiry` forwards submissions to whatever `ENQUIRY_WEBHOOK_URL`
points at — Zapier, Make, Formspree, a Slack incoming webhook, a CRM.

```bash
ENQUIRY_WEBHOOK_URL=https://hooks.zapier.com/...
```

**With that variable unset the endpoint returns 503, not 200,** and the form
tells the visitor it isn't connected. That's deliberate: a form that reports
success while binning real customer enquiries is worse than one that admits
it isn't wired up. Set the variable before launch.

---

## Assets

Source files live in `_assets/`; `npm run assets` processes them into
`public/img/`. The script (`scripts/process-assets.mjs`) does three things:

1. **Crops the venue photo.** The original is a phone screenshot with black
   letterbox bars — only a 735×552 band is real image. The script detects
   the band by row brightness and crops to it, then upscales and grades.
2. **Knocks out the logos.** Both were flattened JPEGs. Alpha is derived
   from luminance, so the marks come out on transparent backgrounds in
   bone / ink / lime.
3. **Builds the favicon** from the reticle in the DPX mark.

### Asset quality is the main constraint on this site

There is currently **one** venue photograph, and it's a low-resolution
screenshot. Everything is designed to work around that — tight crops, heavy
grade, grain, type-led sections instead of image-led ones. Replacing it lifts
the ceiling on the whole design more than any code change will:

- The **original** of that photo, off the camera roll, un-letterboxed.
- 6–10 more: a bay head-on, the screen mid-shot with data visible, seating,
  a detail of clubs or a ball on the turf, the entrance. Evening, lights on.
- The **DPX logo as vector** (`.svg`/`.ai`/`.eps`). Currently traced from a JPEG.
- **TrackMan's official brand pack** — the current logo is keyed out of a
  WhatsApp screenshot of their orange tile.

Drop replacements into `_assets/` and re-run `npm run assets`.

---

## How it's built

### Design language

The DPX mark is a crosshair locking onto a ball, so the whole site behaves
like a tracking system: a reticle cursor, shot-tracer arcs, telemetry
readouts. The palette comes from the venue — near-black petrol (`#060a09`),
warm bone (`#ede8dc`), an acid lime pulled from the turf (`#c6f24e`) and a
tungsten amber matching the ceiling downlights (`#f0a05a`). Deliberately not
dark-plus-orange, which is both the old demo and TrackMan's own palette.

Type: Bricolage Grotesque (display), Instrument Sans (body), JetBrains Mono
(all telemetry).

### The shot tracer

`src/components/ShotTracer.tsx` integrates real ball flight — drag plus a
Magnus lift term — then normalises each trajectory to a target carry. The
shape matters more than the accuracy: a fast climb, a late apex and a
noticeably steeper descent. A symmetric parabola reads as fake instantly to
anyone who plays.

A perspective camera behind the tee projects the arcs so they converge on the
horizon. Two focal lengths, not one: the lateral fan scales with viewport
width while the arc height scales with the larger dimension, otherwise the
whole fan collapses into a vertical squiggle on a phone.

Azimuths are fanned much wider than real drives off a single tee would be.
Accurate azimuths project to a bundle of near-parallel vertical lines —
correct, and unreadable.

### Motion

- Reveals are driven by `useInView` (IntersectionObserver), one-shot.
- **The wipe reveal clips a child, never the observed element.** `clip-path`
  shrinks an element's intersection rectangle to zero, so an element that
  clips itself can never be seen by the observer meant to un-clip it — it
  stays invisible forever. This bit once; see the note in `globals.css`.
- The observer also treats "already scrolled past" as revealed, so a restored
  scroll position can't leave a section blank.
- Lenis handles scroll easing; anchor links are routed through it.
- `prefers-reduced-motion` is honoured everywhere. The preloader is skipped,
  the tracer renders one static frame, and every reveal resolves to its
  final state. Verified: zero elements left invisible.

### Accessibility

Skip link, focus-visible rings, `aria-expanded` on the audience accordion
(hover is a convenience, not the mechanism — it's operable by keyboard and
click), `aria-live` on form status, honeypot rather than a CAPTCHA, and the
reticle cursor is `aria-hidden`, mouse-only, and never blocks a click.
