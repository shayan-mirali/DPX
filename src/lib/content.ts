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

  /* TODO — still outstanding. */
  phone: null as string | null,
  hours: null as string | null,

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
  { label: "What's Coming", href: "#coming" },
] as const;

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
