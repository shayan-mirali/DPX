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
| Enquiry delivery | Netlify Forms (see below) | wired; **add the email notification in the Netlify UI** |
| Real domain | `metadataBase` in `src/app/layout.tsx` | set to `dpxgolf.co.uk`; confirm before launch |

### The enquiry form

Submissions go to **Netlify Forms**. No API key, no third-party account, no
monthly bill — the site is already hosted on Netlify, and Netlify stores every
submission and can email them on arrival.

**One manual step, in the Netlify UI, done once:**

> Site configuration → Forms → **Form notifications** → *Add notification* →
> *Email notification* → form `enquiry` → the address that should receive them.

Without that, submissions still arrive and are stored — they just sit in
**Forms** in the Netlify dashboard rather than landing in an inbox. Nothing is
lost either way.

**How it's wired.** Netlify detects forms by scanning deployed HTML at build
time, and a React-rendered page has none for it to find. So the form is
declared in a static file, `public/__forms.html`, and the real form in
`src/components/Book.tsx` POSTs to `/__forms.html` as url-encoded data.

**If you add a field to the form, add it to `public/__forms.html` too.** That
is the one place the two can drift: an undeclared field is dropped silently by
Netlify, with no error anywhere.

Spam is handled by a honeypot field (`company`) declared via
`netlify-honeypot`, not a CAPTCHA — nothing for a real visitor to solve.

#### It does not deliver on `npm run dev`

Netlify's form endpoint only exists on a real deploy. Locally, `next dev`
serves `public/` as static files and answers a POST with 405, so the form shows
*"Our enquiry form isn't connected yet — please call or email us…"* and the
contact details beside it. **That is the correct local result, not a bug.**
Test it on a Netlify deploy preview.

That fail-closed behaviour is deliberate throughout: a form that reports
success while binning real customer enquiries is worse than one that admits it
isn't connected.

#### Confirmation email to the customer

When an enquiry comes in, the person who sent it gets an automatic
confirmation — a thank-you, a copy of what they submitted, and the venue's
phone, address and hours.

`netlify/functions/submission-created.mjs` does this. Netlify triggers event
functions by filename, so the name *is* the wiring — there is nothing to
configure in the UI for it to fire, and it only runs on submissions Netlify
has already verified, so spam never gets a reply.

**It needs an email provider.** Delivery goes through
[Resend](https://resend.com) (free tier: 3,000/month):

1. Create a Resend account and an API key
2. In Netlify: **Configuration → Environment variables** → add
   `RESEND_API_KEY`
3. Redeploy

**Until then nothing breaks.** With no key the function logs and exits — the
enquiry is still stored and the venue still gets its notification. Only the
customer's confirmation is skipped.

**Before launch, verify the domain in Resend.** Without a verified domain the
function falls back to Resend's shared `onboarding@resend.dev` sender, which
works for testing but will land in spam for real customers. Once
`dpxgolf.co.uk` is verified, set `CONFIRMATION_FROM`:

```bash
CONFIRMATION_FROM="DPX Golf <hello@dpxgolf.co.uk>"
CONFIRMATION_REPLY_TO="markpaxton@dpxgolf.co.uk"   # optional
```

The function never throws. Netlify retries failed event functions, and a retry
loop would mean emailing the same customer over and over — so every failure
path returns 200 and logs instead.

**The venue's contact details are duplicated inside that function**, not
imported from `content.ts`. A Netlify function is bundled separately from the
Next.js app and doesn't share its path aliases, and a bundling failure there
would break the deploy. Four values, at the top of the file — if the phone
number or hours change in `content.ts`, change them there too.

#### Sending somewhere else instead

`POST /api/enquiry` is still in the repo, unused, for the day enquiries should
go to a CRM, Slack or Zapier rather than Netlify. It validates, strips the
honeypot and forwards to whatever `ENQUIRY_WEBHOOK_URL` names:

```bash
ENQUIRY_WEBHOOK_URL=https://hooks.zapier.com/...
```

To switch over, set that variable and change `FORM_ENDPOINT` in `Book.tsx` to
`/api/enquiry`, posting JSON instead of url-encoded data.

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
