import { NextResponse } from "next/server";

export const runtime = "nodejs";

/* ------------------------------------------------------------------ *
 *  Enquiry intake — the OPTIONAL webhook route.
 *
 *  NOT what the site uses by default. The enquiry form posts straight to
 *  Netlify Forms (see `public/__forms.html` and the submit handler in
 *  `src/components/Book.tsx`), because that delivers with no third-party
 *  account, no API key and nothing to keep paying for.
 *
 *  This route is kept for the day the venue wants enquiries in a CRM,
 *  a Slack channel or a Zapier flow instead. Point ENQUIRY_WEBHOOK_URL
 *  at it and change FORM_ENDPOINT in Book.tsx to "/api/enquiry", posting
 *  JSON rather than url-encoded data — the handler below already
 *  validates, strips the honeypot and normalises the payload.
 *
 *  With the variable unset it returns 503 rather than 200. That is
 *  deliberate: a form that reports success while quietly binning real
 *  customer enquiries is far worse than one that admits it isn't wired
 *  up yet.
 * ------------------------------------------------------------------ */

type Payload = {
  name?: string;
  email?: string;
  phone?: string;
  interest?: string;
  message?: string;
  company?: string; // honeypot
};

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export async function POST(req: Request) {
  let body: Payload;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ ok: false, message: "Malformed request." }, { status: 400 });
  }

  // Honeypot: accept silently so bots don't learn anything, but drop it.
  if (body.company) {
    return NextResponse.json({ ok: true });
  }

  const name = (body.name ?? "").trim();
  const email = (body.email ?? "").trim();

  if (name.length < 2) {
    return NextResponse.json({ ok: false, message: "Please give us a name." }, { status: 400 });
  }
  if (!EMAIL_RE.test(email)) {
    return NextResponse.json(
      { ok: false, message: "That email doesn't look right." },
      { status: 400 }
    );
  }

  const enquiry = {
    name,
    email,
    phone: (body.phone ?? "").trim() || null,
    interest: (body.interest ?? "bay").trim(),
    message: (body.message ?? "").trim() || null,
    receivedAt: new Date().toISOString(),
    source: "dpxgolf.co.uk",
  };

  const target = process.env.ENQUIRY_WEBHOOK_URL;

  if (!target) {
    console.warn(
      "[enquiry] ENQUIRY_WEBHOOK_URL is not set — enquiry NOT delivered:",
      JSON.stringify(enquiry)
    );
    return NextResponse.json(
      {
        ok: false,
        configured: false,
        message:
          "Our enquiry form isn't connected yet — please call or email the venue directly and we'll get you booked in.",
      },
      { status: 503 }
    );
  }

  try {
    const res = await fetch(target, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(enquiry),
    });

    if (!res.ok) {
      console.error("[enquiry] webhook rejected:", res.status, await res.text().catch(() => ""));
      return NextResponse.json(
        { ok: false, message: "We couldn't send that just now. Please try again shortly." },
        { status: 502 }
      );
    }

    return NextResponse.json({ ok: true });
  } catch (err) {
    console.error("[enquiry] webhook threw:", err);
    return NextResponse.json(
      { ok: false, message: "We couldn't send that just now. Please try again shortly." },
      { status: 502 }
    );
  }
}
