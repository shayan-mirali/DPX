import type { Metadata, Viewport } from "next";
import { Bricolage_Grotesque, Instrument_Sans, JetBrains_Mono } from "next/font/google";
import "./globals.css";

import { SITE } from "@/lib/content";
import { Nav } from "@/components/Nav";
import { Footer } from "@/components/Footer";
import { Preloader } from "@/components/Preloader";
import { Reticle } from "@/components/Reticle";
import { SmoothScroll } from "@/components/SmoothScroll";
import { ScrollTracer } from "@/components/ScrollTracer";

/* Bricolage carries the display voice — it has enough character to feel
 * drawn rather than picked. Instrument Sans keeps body copy quiet, and
 * JetBrains Mono does all the telemetry. */
const bricolage = Bricolage_Grotesque({
  subsets: ["latin"],
  display: "swap",
  variable: "--font-bricolage",
});

const instrument = Instrument_Sans({
  subsets: ["latin"],
  display: "swap",
  variable: "--font-instrument",
});

const jetbrains = JetBrains_Mono({
  subsets: ["latin"],
  display: "swap",
  weight: ["400", "500"],
  variable: "--font-jetbrains",
});

export const metadata: Metadata = {
  metadataBase: new URL("https://dpxgolf.co.uk"),
  title: {
    default: `${SITE.name} — ${SITE.descriptor}, ${SITE.town}`,
    template: `%s · ${SITE.name}`,
  },
  description:
    "TrackMan-powered indoor golf in Burton upon Trent. Play iconic courses, practise against tour-level data, or bring the group for a night out. Rain or shine, your next round is always on.",
  keywords: [
    "indoor golf Burton upon Trent",
    "golf simulator Burton",
    "TrackMan golf Staffordshire",
    "DPX Golf",
    "golf lessons Burton upon Trent",
  ],
  openGraph: {
    type: "website",
    locale: "en_GB",
    siteName: SITE.name,
    title: `${SITE.name} — ${SITE.descriptor}, ${SITE.town}`,
    description:
      "TrackMan-powered indoor golf bays in Burton upon Trent. Play, practise and compete — whatever the weather.",
    images: [{ url: "/img/venue-wide.webp", width: 2200, height: 1652, alt: "Inside DPX Golf" }],
  },
  twitter: {
    card: "summary_large_image",
    title: `${SITE.name} — ${SITE.descriptor}`,
    description: "TrackMan-powered indoor golf in Burton upon Trent.",
    images: ["/img/venue-wide.webp"],
  },
  robots: { index: true, follow: true },
};

export const viewport: Viewport = {
  themeColor: "#060a09",
  colorScheme: "dark",
};

/* Local business structured data — this is what puts the venue on the
 * map panel in Google results, so the postal address matters. Fields we
 * don't have yet (phone, opening hours) are omitted rather than filled
 * with plausible-looking inventions. */
const JSON_LD = {
  "@context": "https://schema.org",
  "@type": "SportsActivityLocation",
  name: SITE.name,
  description: `${SITE.descriptor} in ${SITE.town}, powered by TrackMan.`,
  address: {
    "@type": "PostalAddress",
    streetAddress: [SITE.address.line1, SITE.address.line2, SITE.address.line3].join(", "),
    addressLocality: SITE.address.town,
    addressRegion: "Staffordshire",
    postalCode: SITE.address.postcode,
    addressCountry: SITE.address.country,
  },
  email: SITE.emails[0],
  telephone: SITE.phone,
  sport: "Golf",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html
      lang="en-GB"
      className={`${bricolage.variable} ${instrument.variable} ${jetbrains.variable}`}
    >
      <body className="grain antialiased">
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(JSON_LD) }}
        />

        <a
          href="#main"
          className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-lime focus:px-5 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-ink"
        >
          Skip to content
        </a>

        <Preloader />
        <SmoothScroll />
        <Reticle />
        <ScrollTracer />

        <Nav />
        <main id="main">{children}</main>
        <Footer />
      </body>
    </html>
  );
}
