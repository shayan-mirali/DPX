/* All copy is taken from the DPX Golf brief. Anything the brief did not
 * supply is marked with a TODO rather than invented. */

export const SITE = {
  name: "DPX Golf",
  tagline: "Swing Better. Play More. Experience Golf Differently.",
  descriptor: "Premium Indoor Golf",
  town: "Burton upon Trent",

  address: {
    line1: "Oakwood House",
    line2: "Bretby Business Park",
    line3: "Ashby Road East",
    town: "Burton upon Trent",
    postcode: "DE15 0PS",
    country: "GB",
  },

  /* NOTE: these two arrived on different domains — dpxgolf.co.uk and
   * dpx.co.uk. Entered exactly as supplied; worth confirming the second
   * isn't a typo before launch. */
  emails: ["markpaxton@dpxgolf.co.uk", "heatherfisher@dpx.co.uk"],

  phone: "+44 7368 805031",

  /* Seven days a week, 10:00–22:00. Both the visible label and the
   * structured data derive from this, so changing which days the venue
   * opens means editing `days` and nothing else. */
  hours: {
    opens: "10:00",
    closes: "22:00",
    days: [
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
      "Sunday",
    ],
  },

  /* Companies House details, required on the site by the Companies Act.
   *
   * NOTE: the registered office is NOT the venue. Customers visit
   * Oakwood House, Bretby Business Park; this address is a legal
   * formality and is labelled as such in the footer so nobody drives to
   * the wrong door. */
  legal: {
    company: "DPX Golf Ltd",
    companyNumber: "17054770",
    office: {
      line1: "Chartwell House",
      line2: "4 St Paul's Square",
      town: "Burton upon Trent",
      country: "England",
      postcode: "DE14 2EF",
    },
  },

  /* The venue is going with TrackMan's own booking system; the link
   * lands once the contract is signed. Drop the URL in here and every
   * "Book a Bay" control on the site switches from scrolling to the
   * enquiry form to linking straight out at it — no other edits needed. */
  bookingUrl: null as string | null,
};

/**
 * Props for every "Book a Bay" control. While `bookingUrl` is null these
 * scroll to the enquiry form; the moment it's set they open TrackMan's
 * booking system in a new tab instead. Spread this rather than
 * hard-coding an href, so the switch is one line in one file.
 */
export const bookingLinkProps = () =>
  SITE.bookingUrl
    ? { href: SITE.bookingUrl, target: "_blank", rel: "noopener noreferrer" }
    : { href: "#book" };

const WEEK = [
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday",
] as const;

/**
 * "Mon – Fri" rather than "Mon, Tue, Wed, Thu, Fri". Collapses any
 * unbroken run of days into a range, so this still reads properly if
 * weekend hours get added later.
 */
export const daysLabel = (() => {
  const days = SITE.hours.days;
  if (days.length === 7) return "Every day";

  const idx = days.map((d) => WEEK.indexOf(d as (typeof WEEK)[number])).sort((a, b) => a - b);
  const contiguous = idx.every((n, i) => i === 0 || n === idx[i - 1] + 1);
  const short = (n: number) => WEEK[n].slice(0, 3);

  if (contiguous && idx.length > 1) return `${short(idx[0])} – ${short(idx[idx.length - 1])}`;
  return idx.map(short).join(", ");
})();

/** Human-readable hours, e.g. "Mon – Fri · 10:00 – 22:00". */
export const hoursLabel = `${daysLabel} · ${SITE.hours.opens} – ${SITE.hours.closes}`;

/** `tel:` needs the number stripped of spaces; the display form keeps them. */
export const telHref = SITE.phone ? `tel:${SITE.phone.replace(/\s+/g, "")}` : null;

/** The registered office on one line, for the footer's legal notice. */
export const registeredOfficeOneLine = [
  SITE.legal.office.line1,
  SITE.legal.office.line2,
  SITE.legal.office.town,
  SITE.legal.office.country,
  SITE.legal.office.postcode,
].join(", ");

/** Formatted one-line address, for inline use. */
export const addressOneLine = [
  SITE.address.line1,
  SITE.address.line2,
  SITE.address.line3,
  SITE.address.town,
  SITE.address.postcode,
].join(", ");

export const NAV = [
  { label: "The Venue", href: "#venue" },
  { label: "Technology", href: "#tech" },
  { label: "Who It's For", href: "#who" },
  { label: "Pricing", href: "#pricing" },
  { label: "What's Coming", href: "#coming" },
] as const;

/* ------------------------------------------------------------------ *
 *  Pricing
 *
 *  Straight from DPX_Golf_Full_Pricing_1_to_4_Hours.pdf. Only the bay
 *  TOTAL is stored — the "each" figure underneath it is total ÷ players,
 *  computed at render. Every price in the PDF divides exactly, so there
 *  is nothing to round and, more usefully, no second set of numbers to
 *  keep in step when a rate changes. To edit a price, change one number
 *  in the row below and the per-person figure follows.
 *
 *  `totals` runs 1 hour → 4 hours, matching PRICING.durations.
 * ------------------------------------------------------------------ */
export const PRICING = {
  durations: [1, 2, 3, 4],

  periods: [
    {
      id: "offpeak",
      label: "Weekday Off-Peak",
      when: "Monday – Friday · 10am – 4pm",
      rows: [
        { players: 1, totals: [15, 28, 40, 52] },
        { players: 2, totals: [24, 44, 64, 84] },
        { players: 3, totals: [30, 57, 84, 111] },
        { players: 4, totals: [36, 68, 100, 132] },
      ],
    },
    {
      id: "peak",
      label: "Peak & Weekends",
      when: "Monday – Friday 4pm – 10pm · Saturday & Sunday all day",
      rows: [
        { players: 1, totals: [25, 48, 69, 88] },
        { players: 2, totals: [36, 68, 96, 124] },
        { players: 3, totals: [45, 84, 120, 156] },
        { players: 4, totals: [56, 104, 148, 192] },
      ],
    },
  ],

  /* Shown immediately under the table, ahead of the notes. Consumer
   * pricing has to state this, so it gets its own line rather than
   * being buried in a bullet list. */
  vatNote: "All prices include VAT.",

  /* Shown under the table. Anything that is a condition of the price
   * rather than the price itself belongs here. */
  notes: [
    "Prices are per bay, not per person — split it however you like.",
    "Up to 4 players per bay. Clubs are available if you don't have your own.",
  ],
} as const;

/**
 * "£15", or "£12.50" if it ever needs to be. Every price on the current
 * rate card is whole pounds and every bay total divides exactly by its
 * player count — but the per-person figure is derived, so a future edit
 * to a total could land on a half. Formatting for that here means one
 * price change can never render "£12.5" at anybody.
 */
export const gbp = (n: number) => `£${Number.isInteger(n) ? n : n.toFixed(2)}`;

/** Per-player share of a bay total. */
export const perPlayer = (total: number, players: number) => total / players;

/** Every bay total on the card, cheapest first — feeds `priceRange`. */
const ALL_TOTALS = PRICING.periods.flatMap((p) => p.rows.flatMap((r) => [...r.totals]));

/** "£15 – £192". Google shows this on the business panel. */
export const priceRange = `${gbp(Math.min(...ALL_TOTALS))} – ${gbp(Math.max(...ALL_TOTALS))}`;

/* The eight TrackMan parameters called out in the brief. Values are
 * illustrative of a typical drive and are labelled as a sample readout
 * in the UI — they are not claims about any individual golfer. */
export const METRICS = [
  { key: "Club Speed",     value: 113.2, unit: "mph", decimals: 1 },
  { key: "Ball Speed",     value: 167.4, unit: "mph", decimals: 1 },
  { key: "Carry Distance", value: 289,   unit: "yds", decimals: 0 },
  { key: "Launch Angle",   value: 12.8,  unit: "deg", decimals: 1 },
  { key: "Spin Rate",      value: 2540,  unit: "rpm", decimals: 0 },
  { key: "Shot Shape",     value: 4.1,   unit: "yds", decimals: 1 },
  { key: "Club Path",      value: 1.8,   unit: "deg", decimals: 1 },
  { key: "Attack Angle",   value: -1.4,  unit: "deg", decimals: 1 },
] as const;

export const FEATURES = [
  {
    n: "01",
    title: "Premium TrackMan Technology",
    body: "One of the world's most advanced golf simulator systems — the same platform trusted by professionals, coaches and golfers worldwide.",
    span: "wide",
  },
  {
    n: "02",
    title: "Premium Golf Bays",
    body: "Spacious simulator bays built for comfort, whether you're grooving a swing, playing a full round or settling in for a social game.",
    span: "tall",
  },
  {
    n: "03",
    title: "Play Iconic Courses",
    body: "Some of the greatest courses on earth, without leaving Burton upon Trent.",
    span: "normal",
  },
  {
    n: "04",
    title: "Practice Smarter",
    body: "Instant, honest feedback on every parameter that actually moves your game — club speed, ball speed, carry, launch, spin, shape, path and attack angle.",
    span: "wide",
  },
  {
    n: "05",
    title: "Relax & Socialise",
    body: "Comfortable seating, refreshments and a welcoming room to unwind in.",
    span: "normal",
  },
] as const;

export const AUDIENCES = [
  {
    id: "golfers",
    title: "Golfers",
    body: "Practise all year round with accurate data and realistic course conditions built to move your performance forward.",
    note: "All year. Any weather.",
  },
  {
    id: "beginners",
    title: "Beginners",
    body: "New to golf? No problem. The simulators are a relaxed, genuinely fun way to learn without the pressure of a first tee.",
    note: "No experience needed.",
  },
  {
    id: "families",
    title: "Families",
    body: "Interactive golf games and challenges that work for every age in the group, not just the one with a handicap.",
    note: "All ages welcome.",
  },
  {
    id: "corporate",
    title: "Corporate",
    body: "A venue that isn't another meeting room — team building, client entertainment, networking and company events.",
    note: "Private bays available.",
  },
  {
    id: "groups",
    title: "Groups & Celebrations",
    body: "Make the next occasion a different one: great golf, real competition and a night people actually remember.",
    note: "Book the room.",
  },
] as const;

export const ROADMAP = [
  {
    id: "membership",
    title: "Membership",
    lede: "Exclusive packages for regular golfers who want more from DPX.",
    points: ["Exclusive member offers", "Priority booking", "Members-only events", "And more to come"],
    cta: "Register interest",
  },
  {
    id: "coaching",
    title: "Coaching",
    lede: "Professional coaching built on TrackMan data, not guesswork.",
    points: ["Lower your handicap", "Add distance", "Build consistency", "Understand your swing"],
    cta: "Register interest",
  },
  {
    id: "competitions",
    title: "Competitions & Leagues",
    lede: "Competitive golf indoors, for every ability.",
    points: [
      "Indoor golf leagues",
      "Nearest the pin",
      "Longest drive",
      "Seasonal events",
      "Major championship competitions",
    ],
    cta: "Register interest",
  },
] as const;

export const TICKER = [
  "Club Speed", "Ball Speed", "Carry Distance", "Launch Angle",
  "Spin Rate", "Shot Shape", "Club Path", "Attack Angle",
] as const;
