<?php
/**
 * Copy this file to config.php and fill it in. config.php is git-ignored
 * — it holds an API key and must never be committed.
 *
 * Nothing here is required for the site to run. With no config the
 * enquiry form still works and still stores every submission to
 * storage/enquiries.jsonl; only the emails are skipped.
 */

declare(strict_types=1);

return [
    /* Resend API key — https://resend.com/api-keys
     * Sending access is enough; this never needs full access. */
    'resend_api_key' => '',

    /* Sender. Resend only delivers from a domain verified in its
     * dashboard. Until dpxgolf.co.uk is verified, the shared sandbox
     * sender below reaches ONLY the address the Resend account was
     * registered with and returns 403 for anyone else — so verify the
     * domain before launch, then change this. */
    'from' => 'DPX Golf <onboarding@resend.dev>',

    /* Where enquiry notifications go. Defaults to SITE['emails'] from
     * inc/content.php if omitted. */
    'notify' => ['markpaxton@dpxgolf.co.uk'],

    /* Reply-to on the customer's confirmation, so a reply reaches a human. */
    'reply_to' => 'markpaxton@dpxgolf.co.uk',
];
