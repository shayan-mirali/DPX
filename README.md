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

## Editing the site

**Almost all copy lives in one file: `src/lib/content.ts`.** Editing it needs
no React — just don't delete the quote marks or commas. Everything below is
read from there and nowhere else:

| What | Where in `content.ts` |
| --- | --- |
| Address, phone, both email addresses | `SITE.address`, `SITE.phone`, `SITE.emails` |
| Opening hours and days | `SITE.hours` |
| Booking link | `SITE.bookingUrl` |
| Prices | `PRICING` |
| Nav labels | `NAV` |
| The eight TrackMan readouts | `METRICS`, `TICKER` |
| Venue feature cards | `FEATURES` |
| "Who it's for" cards | `AUDIENCES` |
| Membership / coaching / competitions | `ROADMAP` |

Two of those are worth calling out:

**`SITE.hours`** drives both the visible "Every day · 10:00 – 22:00" label and
the Google structured data. Change which days the venue opens and the label
re-derives itself — `daysLabel` collapses runs of days into "Mon – Fri" and
prints "Every day" at seven, so it stays correct without being edited.

**`SITE.bookingUrl`** is `null` today, so every "Book a Bay" control scrolls to
the enquiry form. Put TrackMan's booking URL in and all of them switch to
opening it in a new tab instead. One line, one file, no other edits.

### Prices

`PRICING` holds **only the bay total** for each player-count × duration cell.
The "£33 each" line underneath is `total ÷ players`, computed at render, and
`priceRange` in the structured data derives from the same numbers. So a price
change is one number in one place, and the per-person figure, the Google
price range and the table can't drift apart.

Every price on the current card divides exactly by its player count. If a
future edit doesn't, `gbp()` prints "£12.50" rather than "£12.5".

### Headlines and images

Section headlines ("Golf, indoors / done properly.") sit in the component
files rather than `content.ts`, because each line is animated separately.
Still editable — it's just code rather than a content file.

Images: drop replacements into `public/img/` under the same filenames, or into
`_assets/` and re-run `npm run assets`. See **Assets** below.

**There is no CMS.** Every edit is a file change plus a deploy. If the venue
needs to change prices or swap photos without touching the repo, that's a
separate piece of work — Decap CMS is the cheapest fit given the Netlify host.

---

## Before this goes live

| What | Where | Status |
| --- | --- | --- |
| Street address, phone, email, opening hours | `src/lib/content.ts` → `SITE` | done |
| Pricing | `src/lib/content.ts` → `PRICING` | done |
| Booking system URL, if one exists | `SITE.bookingUrl` | **outstanding** |
| Enquiry delivery target | `ENQUIRY_WEBHOOK_URL` env var (see below) | **outstanding** |
| Real domain | `metadataBase` in `src/app/layout.tsx` | set to `dpxgolf.co.uk`; confirm before launch |

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
