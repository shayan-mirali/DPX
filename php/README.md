# DPX Golf — PHP build

The same site as the Next.js build in the parent directory, rewritten to run
on ordinary PHP hosting (IONOS). No framework, no Composer, no build step on
the server.

Requires **PHP 8.0+** with **cURL** (for sending mail). Both are standard on
IONOS.

---

## Deploying to IONOS

Upload the **contents** of this `php/` directory to the web root
(`/` or `htdocs/`), so that `index.php` sits at the top level:

```
htdocs/
  index.php
  enquiry.php
  config.php          ← you create this, see below
  .htaccess
  inc/
  assets/
  storage/            ← must be writable
```

Two things to get right:

1. **`storage/` must be writable by PHP** (755 is usually enough; 775 if not).
   Every enquiry is written there before any email is attempted, so if it
   isn't writable you lose the safety net.
2. **Upload `storage/.htaccess` too.** It blocks HTTP access to the enquiry
   log, which contains customer names, emails and phone numbers. Some FTP
   clients hide dotfiles by default — check it arrived.

---

## Configuration

Copy `config.sample.php` to `config.php` and fill in the Resend API key:

```php
'resend_api_key' => 're_...',
'from'           => 'DPX Golf <hello@dpxgolf.co.uk>',
'notify'         => ['markpaxton@dpxgolf.co.uk'],
'reply_to'       => 'markpaxton@dpxgolf.co.uk',
```

`config.php` is git-ignored and blocked by `.htaccess` — it holds a secret and
must never be committed or served.

**With no config the site still works.** The form still accepts submissions and
still writes them to `storage/enquiries.jsonl`; only the emails are skipped.
Nothing is lost, but somebody has to read the file.

**Resend sandbox:** until `dpxgolf.co.uk` is verified in Resend, the shared
`onboarding@resend.dev` sender only delivers to the address the Resend account
was registered with and returns 403 for anyone else. Verify the domain before
launch, then set `from` to an address on it.

---

## Editing the site

**All copy, prices, hours and contact details live in `inc/content.php`.** It
is the direct counterpart of `src/lib/content.ts` in the Next.js build and
needs no PHP knowledge beyond not deleting the quote marks.

Prices store only the bay **total**; the "£33 each" line and the `priceRange`
in the structured data are both derived, so a rate change is one number in one
place.

Section markup lives in `inc/partials/`, one file per section, included by
`index.php` in reading order.

### Rebuilding the CSS

The stylesheet is compiled with Tailwind and **committed**, so the server needs
no Node. If you change classes in any `.php` file or in `app.js`, rebuild it
from the repository root:

```bash
npm run css:php
```

That regenerates `assets/css/styles.css` from `assets/css/src.css`. Upload the
result. If you edit markup and *don't* rebuild, new classes simply won't have
any styles — it fails silently, so it's worth remembering.

`index.php` appends `?v=<file mtime>` to the CSS and JS URLs, so a rebuilt file
is a new URL and the week-long cache in `.htaccess` never serves a stale copy.

---

## How the enquiry form works

`enquiry.php` handles the POST and does three things, in this order:

1. **Writes the enquiry to `storage/enquiries.jsonl`** — one JSON object per
   line. This is the replacement for the Netlify Forms dashboard and it runs
   before any email, so a mail outage cannot lose a customer.
2. **Emails the venue**, with the customer's address as `reply-to`, so hitting
   reply goes straight back to them.
3. **Emails the customer** a confirmation with a copy of what they sent.

If the venue email fails but the write succeeded, the submission still reports
success — because it *was* received, and it's sitting in the log. If both fail,
it reports failure rather than showing a success message over an enquiry that
went nowhere.

The form is a **real `<form method="post">`**, so it works with JavaScript
disabled — `enquiry.php` redirects back to `index.php?sent=1#book` and the page
renders the result server-side. With JS on, it submits in place and, if
delivery fails, offers a pre-filled `mailto:` so nothing typed is lost.

**Spam:** a honeypot field plus a per-IP rate limit of 5 enquiries an hour. The
rate limiter fails *open* — if its file can't be read or written the enquiry
still goes through, because losing a real customer is worse than letting a
spammer past. Netlify's spam filtering has no equivalent here.

---

## What differs from the Next.js build

Nothing visual or textual. Structurally:

| | Next.js / Netlify | This build |
| --- | --- | --- |
| Rendering | React SSR | PHP templates |
| Interactivity | 16 React client components | `assets/js/app.js`, vanilla |
| Form storage | Netlify Forms dashboard | `storage/enquiries.jsonl` |
| Confirmation email | Netlify event function | `enquiry.php` |
| Spam filtering | Netlify + honeypot | honeypot + rate limit |
| Images | `next/image` (auto AVIF/WebP, resizing) | plain `<img>`, pre-built WebP |
| CSS | built at deploy | committed, rebuilt with `npm run css:php` |

The `next/image` change is the one with a real cost: there is no automatic
resizing or format negotiation, so every visitor gets the full-size WebP. It's
fine at the current image sizes, but if more photography is added it's worth
generating explicit `srcset` sizes.

`app.js` initialises each behaviour in its own `try/catch`, so one failure
can't take the rest of the page down with it. Failures log to the console
prefixed `[dpx]`.
