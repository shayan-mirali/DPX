/* ------------------------------------------------------------------ *
 *  Confirmation email — sent to the person who enquired.
 *
 *  Netlify triggers this automatically: an event function must be named
 *  after its event, so the filename `submission-created` IS the wiring.
 *  There is nothing to configure in the UI for it to fire. It runs only
 *  on submissions Netlify has already verified, so spam never reaches it.
 *
 *  The venue's own copy of each enquiry is a separate thing — that's the
 *  email notification configured under Notifications in the Netlify UI.
 *  This function only handles the autoresponder back to the customer.
 *
 *  Delivery goes through Resend, which needs RESEND_API_KEY set in the
 *  Netlify environment. With no key it logs and exits quietly: a
 *  confirmation that cannot be sent must never take the enquiry down
 *  with it. The submission is already stored by the time this runs.
 * ------------------------------------------------------------------ */

/* Duplicated from src/lib/content.ts rather than imported. A Netlify
 * function is bundled separately from the Next.js app and does not share
 * its path aliases, and a bundling failure here would break the deploy.
 * Four values, kept deliberately obvious — if the venue's phone or hours
 * change in content.ts, change them here too. */
const VENUE = {
  name: "DPX Golf",
  tagline: "Swing Better. Play More. Experience Golf Differently.",
  phone: "+44 7368 805031",
  address:
    "Oakwood House, Bretby Business Park, Ashby Road East, Burton upon Trent, DE15 0PS",
  hours: "Every day · 10:00 – 22:00",
  site: "https://dpxgolf.co.uk",
};

/* Matches the option labels in the enquiry form, so the confirmation
 * reads back what the customer actually picked rather than the raw
 * value. An unknown key falls through to the value itself. */
const INTEREST_LABELS = {
  bay: "Booking a bay",
  membership: "Membership",
  coaching: "Coaching",
  competitions: "Competitions & leagues",
  corporate: "Corporate or group event",
  other: "Something else",
};

const esc = (s) =>
  String(s ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");

export default async function handler(req) {
  try {
    const body = await req.json();

    /* Netlify wraps the submission: { payload: { data: {...} } }. Accept
     * the unwrapped shape too, so this is testable with a plain POST. */
    const data = body?.payload?.data ?? body?.data ?? body ?? {};

    const to = String(data.email ?? "").trim();
    const name = String(data.name ?? "").trim();

    if (!to || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(to)) {
      console.warn("[confirmation] no usable email on submission — skipping");
      return new Response("no recipient", { status: 200 });
    }

    // Belt and braces. Netlify filters spam before this fires, but a
    // submission carrying the honeypot should never earn a reply.
    if (String(data.company ?? "").trim()) {
      console.warn("[confirmation] honeypot set — skipping");
      return new Response("skipped", { status: 200 });
    }

    const apiKey = process.env.RESEND_API_KEY;
    if (!apiKey) {
      console.warn(
        "[confirmation] RESEND_API_KEY not set — no confirmation sent to",
        to,
        "(the enquiry itself is stored and unaffected)"
      );
      return new Response("not configured", { status: 200 });
    }

    /* Resend will only send from a domain verified in their dashboard.
     * The shared onboarding sender below is a sandbox: it can ONLY send
     * to the address the Resend account was registered with, and returns
     * 403 for anyone else. So until dpxgolf.co.uk is verified, this
     * reaches you and nobody else — it is not a soft fallback, real
     * customers get no confirmation at all. */
    const from = process.env.CONFIRMATION_FROM ?? "DPX Golf <onboarding@resend.dev>";

    const interest = INTEREST_LABELS[data.interest] ?? data.interest ?? "General enquiry";
    const message = String(data.message ?? "").trim();
    const phone = String(data.phone ?? "").trim();
    const firstName = name.split(/\s+/)[0] || "there";

    const text = [
      `Thanks, ${firstName} — that's with us.`,
      "",
      `Someone from ${VENUE.name} will come back to you shortly, usually within one working day.`,
      "",
      "What you sent us:",
      `  Interested in: ${interest}`,
      phone ? `  Phone: ${phone}` : null,
      message ? `  Message: ${message}` : null,
      "",
      "In the meantime:",
      `  Call: ${VENUE.phone}`,
      `  Where: ${VENUE.address}`,
      `  Open: ${VENUE.hours}`,
      "",
      VENUE.tagline,
      VENUE.name,
    ]
      .filter((line) => line !== null)
      .join("\n");

    /* Deliberately a light, plain email. A dark-themed HTML mail gets
     * mangled by half the clients out there, and a confirmation needs to
     * be legible far more than it needs to be on-brand. */
    const html = `
<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;max-width:520px;margin:0 auto;padding:32px 24px;color:#1a1a1a;line-height:1.6">
  <p style="font-size:18px;margin:0 0 20px"><strong>Thanks, ${esc(firstName)} — that's with us.</strong></p>
  <p style="margin:0 0 24px">Someone from ${esc(VENUE.name)} will come back to you shortly, usually within one working day.</p>

  <table style="width:100%;border-collapse:collapse;background:#f6f6f4;border-radius:10px;margin:0 0 24px">
    <tr><td style="padding:18px 20px">
      <p style="margin:0 0 10px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#777">What you sent us</p>
      <p style="margin:0 0 6px"><strong>Interested in:</strong> ${esc(interest)}</p>
      ${phone ? `<p style="margin:0 0 6px"><strong>Phone:</strong> ${esc(phone)}</p>` : ""}
      ${message ? `<p style="margin:0"><strong>Message:</strong> ${esc(message)}</p>` : ""}
    </td></tr>
  </table>

  <p style="margin:0 0 6px"><strong>Call</strong> <a href="tel:${esc(VENUE.phone.replace(/\s+/g, ""))}" style="color:#4a7c00">${esc(VENUE.phone)}</a></p>
  <p style="margin:0 0 6px"><strong>Where</strong> ${esc(VENUE.address)}</p>
  <p style="margin:0 0 24px"><strong>Open</strong> ${esc(VENUE.hours)}</p>

  <p style="margin:0;padding-top:20px;border-top:1px solid #e2e2de;font-size:13px;color:#777">
    ${esc(VENUE.tagline)}<br>${esc(VENUE.name)}
  </p>
</div>`.trim();

    const res = await fetch("https://api.resend.com/emails", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${apiKey}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        from,
        to: [to],
        subject: `We've got your enquiry — ${VENUE.name}`,
        text,
        html,
        reply_to: process.env.CONFIRMATION_REPLY_TO ?? undefined,
      }),
    });

    if (!res.ok) {
      console.error("[confirmation] Resend rejected:", res.status, await res.text().catch(() => ""));
      return new Response("send failed", { status: 200 });
    }

    console.log("[confirmation] sent to", to);
    return new Response("sent", { status: 200 });
  } catch (err) {
    /* Never surface an error. Netlify retries failed event functions, and
     * a retry loop here would mean the same customer emailed repeatedly. */
    console.error("[confirmation] threw:", err);
    return new Response("error handled", { status: 200 });
  }
}
