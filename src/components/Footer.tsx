import Image from "next/image";
import { NAV, SITE } from "@/lib/content";

export function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="relative overflow-hidden border-t border-bone/10 pt-16">
      <div className="mx-auto max-w-[1440px] px-5 sm:px-8">
        <div className="grid gap-10 pb-16 sm:grid-cols-2 lg:grid-cols-4">
          <div className="lg:col-span-2">
            <Image
              src="/img/dpx-bone.png"
              alt={SITE.name}
              width={695}
              height={443}
              className="h-10 w-auto"
            />
            <p className="mt-6 max-w-[24rem] text-[0.95rem] leading-relaxed text-bone/50">
              {SITE.descriptor} in {SITE.town}. {SITE.tagline}
            </p>
          </div>

          <div>
            <h4 className="data text-[10px] uppercase tracking-[0.2em] text-bone-dim">
              Explore
            </h4>
            <ul className="mt-5 flex flex-col gap-3">
              {NAV.map((n) => (
                <li key={n.href}>
                  <a
                    href={n.href}
                    className="text-[0.95rem] text-bone/60 transition-colors duration-300 hover:text-lime"
                  >
                    {n.label}
                  </a>
                </li>
              ))}
              <li>
                <a
                  href="#book"
                  className="text-[0.95rem] text-bone/60 transition-colors duration-300 hover:text-lime"
                >
                  Book a Bay
                </a>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="data text-[10px] uppercase tracking-[0.2em] text-bone-dim">
              Visit
            </h4>
            <ul className="mt-5 flex flex-col gap-3 text-[0.95rem] text-bone/60">
              <li>{SITE.address ?? `${SITE.town}, Staffordshire`}</li>
              {SITE.phone && (
                <li>
                  <a href={`tel:${SITE.phone.replace(/\s/g, "")}`} className="hover:text-lime">
                    {SITE.phone}
                  </a>
                </li>
              )}
              {SITE.email && (
                <li>
                  <a href={`mailto:${SITE.email}`} className="hover:text-lime">
                    {SITE.email}
                  </a>
                </li>
              )}
              {!SITE.phone && !SITE.email && (
                <li className="text-bone/35">Contact details coming shortly</li>
              )}
            </ul>
          </div>
        </div>

        {/* Oversized wordmark, cropped by the viewport edge */}
        <div aria-hidden className="relative select-none">
          <p
            className="display whitespace-nowrap text-center leading-[0.8] text-bone/[0.055]"
            style={{ fontSize: "clamp(4rem, 19vw, 17rem)" }}
          >
            DPX GOLF
          </p>
        </div>

        <div className="flex flex-col gap-3 border-t border-bone/10 py-7 sm:flex-row sm:items-center sm:justify-between">
          <p className="data text-[10px] uppercase tracking-[0.18em] text-bone/35">
            © {year} {SITE.name}
          </p>
          <p className="data text-[10px] uppercase tracking-[0.18em] text-bone/35">
            TrackMan is a trademark of its respective owner
          </p>
        </div>
      </div>
    </footer>
  );
}
