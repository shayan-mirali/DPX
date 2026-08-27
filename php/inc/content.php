<?php
/**
 * All site copy and data — the PHP counterpart of src/lib/content.ts.
 *
 * This is the one file to edit for text, prices, hours or contact
 * details. Everything else reads from here, so a change lands in the
 * page, the structured data and the emails at once.
 *
 * Values are deliberately plain arrays rather than classes: whoever
 * maintains this next should be able to change a price without knowing
 * any PHP beyond "don't delete the quote marks".
 */

declare(strict_types=1);

const SITE = [
    'name'       => 'DPX Golf',
    'tagline'    => 'Swing Better. Play More. Experience Golf Differently.',
    'descriptor' => 'Premium Indoor Golf',
    'town'       => 'Burton upon Trent',

    'address' => [
        'line1'    => 'Oakwood House',
        'line2'    => 'Bretby Business Park',
        'line3'    => 'Ashby Road East',
        'town'     => 'Burton upon Trent',
        'postcode' => 'DE15 0PS',
        'country'  => 'GB',
    ],

    /* NOTE: these two arrived on different domains — dpxgolf.co.uk and
     * dpx.co.uk. Entered exactly as supplied; worth confirming the second
     * isn't a typo before launch. */
    'emails' => ['markpaxton@dpxgolf.co.uk', 'heatherfisher@dpx.co.uk'],

    'phone' => '+44 7368 805031',

    /* Seven days a week, 10:00–22:00. Both the visible label and the
     * structured data derive from this, so changing which days the venue
     * opens means editing `days` and nothing else. */
    'hours' => [
        'opens' => '10:00',
        'closes' => '22:00',
        'days' => [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday',
            'Friday', 'Saturday', 'Sunday',
        ],
    ],

    /* Companies House details, required on the site by the Companies Act.
     *
     * NOTE: the registered office is NOT the venue. Customers visit
     * Oakwood House, Bretby Business Park; this address is a legal
     * formality and is labelled as such in the footer so nobody drives
     * to the wrong door. */
    'legal' => [
        'company' => 'DPX Golf Ltd',
        'companyNumber' => '17054770',
        'office' => [
            'line1' => 'Chartwell House',
            'line2' => "4 St Paul's Square",
            'town' => 'Burton upon Trent',
            'country' => 'England',
            'postcode' => 'DE14 2EF',
        ],
    ],

    /* The venue is going with TrackMan's own booking system; the link
     * lands once the contract is signed. Put the URL here and every
     * "Book a Bay" control on the site switches from scrolling to the
     * enquiry form to linking straight out at it — no other edits. */
    'bookingUrl' => null,

    /* Used for canonical URLs and Open Graph. Change this when the real
     * domain goes live. */
    'origin' => 'https://dpxgolf.co.uk',
];

/**
 * Attributes for every "Book a Bay" control. While bookingUrl is null
 * these scroll to the enquiry form; the moment it's set they open the
 * booking system in a new tab instead.
 *
 * @return array<string,string>
 */
function booking_link_attrs(): array
{
    if (!empty(SITE['bookingUrl'])) {
        return [
            'href'   => SITE['bookingUrl'],
            'target' => '_blank',
            'rel'    => 'noopener noreferrer',
        ];
    }

    return ['href' => '#book'];
}

const WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

/**
 * "Mon – Fri" rather than "Mon, Tue, Wed, Thu, Fri". Collapses any
 * unbroken run of days into a range, so this still reads properly if the
 * opening days change later.
 */
function days_label(): string
{
    $days = SITE['hours']['days'];
    if (count($days) === 7) {
        return 'Every day';
    }

    $idx = [];
    foreach ($days as $d) {
        $pos = array_search($d, WEEK, true);
        if ($pos !== false) {
            $idx[] = $pos;
        }
    }
    sort($idx);

    if (count($idx) === 0) {
        return '';
    }

    $contiguous = true;
    for ($i = 1, $n = count($idx); $i < $n; $i++) {
        if ($idx[$i] !== $idx[$i - 1] + 1) {
            $contiguous = false;
            break;
        }
    }

    $short = static fn(int $n): string => substr(WEEK[$n], 0, 3);

    if ($contiguous && count($idx) > 1) {
        return $short($idx[0]) . ' – ' . $short($idx[count($idx) - 1]);
    }

    return implode(', ', array_map($short, $idx));
}

/** Human-readable hours, e.g. "Every day · 10:00 – 22:00". */
function hours_label(): string
{
    return days_label() . ' · ' . SITE['hours']['opens'] . ' – ' . SITE['hours']['closes'];
}

/** `tel:` needs the number stripped of spaces; the display form keeps them. */
function tel_href(): ?string
{
    if (empty(SITE['phone'])) {
        return null;
    }
    return 'tel:' . preg_replace('/\s+/', '', SITE['phone']);
}

/** The registered office on one line, for the footer's legal notice. */
function registered_office_one_line(): string
{
    return implode(', ', [
        SITE['legal']['office']['line1'],
        SITE['legal']['office']['line2'],
        SITE['legal']['office']['town'],
        SITE['legal']['office']['country'],
        SITE['legal']['office']['postcode'],
    ]);
}

/** Formatted one-line address, for inline use and map links. */
function address_one_line(): string
{
    return implode(', ', [
        SITE['address']['line1'],
        SITE['address']['line2'],
        SITE['address']['line3'],
        SITE['address']['town'],
        SITE['address']['postcode'],
    ]);
}

const NAV = [
    ['label' => 'The Venue',     'href' => '#venue'],
    ['label' => 'Technology',    'href' => '#tech'],
    ['label' => "Who It's For",  'href' => '#who'],
    ['label' => 'Pricing',       'href' => '#pricing'],
    ['label' => "What's Coming", 'href' => '#coming'],
];

/* ------------------------------------------------------------------ *
 *  Pricing
 *
 *  Straight from DPX_Golf_Full_Pricing_1_to_4_Hours.pdf. Only the bay
 *  TOTAL is stored — the "each" figure underneath it is total ÷ players,
 *  computed at render. Every price on the card divides exactly, so there
 *  is nothing to round and, more usefully, no second set of numbers to
 *  keep in step when a rate changes.
 *
 *  `totals` runs 1 hour → 4 hours, matching PRICING['durations'].
 * ------------------------------------------------------------------ */
const PRICING = [
    'durations' => [1, 2, 3, 4],

    'periods' => [
        [
            'id'    => 'offpeak',
            'label' => 'Weekday Off-Peak',
            'when'  => 'Monday – Friday · 10am – 4pm',
            'rows'  => [
                ['players' => 1, 'totals' => [15, 28, 40, 52]],
                ['players' => 2, 'totals' => [24, 44, 64, 84]],
                ['players' => 3, 'totals' => [30, 57, 84, 111]],
                ['players' => 4, 'totals' => [36, 68, 100, 132]],
            ],
        ],
        [
            'id'    => 'peak',
            'label' => 'Peak & Weekends',
            'when'  => 'Monday – Friday 4pm – 10pm · Saturday & Sunday all day',
            'rows'  => [
                ['players' => 1, 'totals' => [25, 48, 69, 88]],
                ['players' => 2, 'totals' => [36, 68, 96, 124]],
                ['players' => 3, 'totals' => [45, 84, 120, 156]],
                ['players' => 4, 'totals' => [56, 104, 148, 192]],
            ],
        ],
    ],

    /* Shown immediately under the table, ahead of the notes. Consumer
     * pricing has to state this, so it gets its own line rather than
     * being buried in a bullet list. */
    'vatNote' => 'All prices include VAT.',

    /* Shown under the table. Anything that is a condition of the price
     * rather than the price itself belongs here.
     *
     * TODO: the second line is unverified — nobody has confirmed that
     * clubs are available to borrow. Confirm with the venue or remove. */
    'notes' => [
        'Prices are per bay, not per person — split it however you like.',
        "Up to 4 players per bay. Clubs are available if you don't have your own.",
    ],
];

/**
 * "£15", or "£12.50" if it ever needs to be. Every price on the current
 * rate card is whole pounds and every bay total divides exactly by its
 * player count — but the per-person figure is derived, so a future edit
 * to a total could land on a half.
 */
function gbp(float $n): string
{
    return '£' . (floor($n) === $n ? (string) (int) $n : number_format($n, 2));
}

/** Per-player share of a bay total. */
function per_player(float $total, int $players): float
{
    return $total / $players;
}

/** "£15 – £192". Google shows this on the business panel. */
function price_range(): string
{
    $all = [];
    foreach (PRICING['periods'] as $p) {
        foreach ($p['rows'] as $row) {
            foreach ($row['totals'] as $t) {
                $all[] = $t;
            }
        }
    }
    return gbp((float) min($all)) . ' – ' . gbp((float) max($all));
}

/* The eight TrackMan parameters called out in the brief. Values are
 * illustrative of a typical drive and are labelled as a sample readout
 * in the UI — they are not claims about any individual golfer. */
const METRICS = [
    ['key' => 'Club Speed',     'value' => 113.2, 'unit' => 'mph', 'decimals' => 1],
    ['key' => 'Ball Speed',     'value' => 167.4, 'unit' => 'mph', 'decimals' => 1],
    ['key' => 'Carry Distance', 'value' => 289,   'unit' => 'yds', 'decimals' => 0],
    ['key' => 'Launch Angle',   'value' => 12.8,  'unit' => 'deg', 'decimals' => 1],
    ['key' => 'Spin Rate',      'value' => 2540,  'unit' => 'rpm', 'decimals' => 0],
    ['key' => 'Shot Shape',     'value' => 4.1,   'unit' => 'yds', 'decimals' => 1],
    ['key' => 'Club Path',      'value' => 1.8,   'unit' => 'deg', 'decimals' => 1],
    ['key' => 'Attack Angle',   'value' => -1.4,  'unit' => 'deg', 'decimals' => 1],
];

const FEATURES = [
    [
        'n' => '01',
        'title' => 'Premium TrackMan Technology',
        'body' => "One of the world's most advanced golf simulator systems — the same platform trusted by professionals, coaches and golfers worldwide.",
        'span' => 'wide',
    ],
    [
        'n' => '02',
        'title' => 'Premium Golf Bays',
        'body' => "Spacious simulator bays built for comfort, whether you're grooving a swing, playing a full round or settling in for a social game.",
        'span' => 'tall',
    ],
    [
        'n' => '03',
        'title' => 'Play Iconic Courses',
        'body' => 'Some of the greatest courses on earth, without leaving Burton upon Trent.',
        'span' => 'normal',
    ],
    [
        'n' => '04',
        'title' => 'Practice Smarter',
        'body' => 'Instant, honest feedback on every parameter that actually moves your game — club speed, ball speed, carry, launch, spin, shape, path and attack angle.',
        'span' => 'wide',
    ],
    [
        'n' => '05',
        'title' => 'Relax & Socialise',
        'body' => 'Comfortable seating, refreshments and a welcoming room to unwind in.',
        'span' => 'normal',
    ],
];

const AUDIENCES = [
    [
        'id' => 'golfers',
        'title' => 'Golfers',
        'body' => 'Practise all year round with accurate data and realistic course conditions built to move your performance forward.',
        'note' => 'All year. Any weather.',
    ],
    [
        'id' => 'beginners',
        'title' => 'Beginners',
        'body' => 'New to golf? No problem. The simulators are a relaxed, genuinely fun way to learn without the pressure of a first tee.',
        'note' => 'No experience needed.',
    ],
    [
        'id' => 'families',
        'title' => 'Families',
        'body' => 'Interactive golf games and challenges that work for every age in the group, not just the one with a handicap.',
        'note' => 'All ages welcome.',
    ],
    [
        'id' => 'corporate',
        'title' => 'Corporate',
        'body' => "A venue that isn't another meeting room — team building, client entertainment, networking and company events.",
        'note' => 'Private bays available.',
    ],
    [
        'id' => 'groups',
        'title' => 'Groups & Celebrations',
        'body' => 'Make the next occasion a different one: great golf, real competition and a night people actually remember.',
        'note' => 'Book the room.',
    ],
];

const ROADMAP = [
    [
        'id' => 'membership',
        'title' => 'Membership',
        'lede' => 'Exclusive packages for regular golfers who want more from DPX.',
        'points' => ['Exclusive member offers', 'Priority booking', 'Members-only events', 'And more to come'],
        'cta' => 'Register interest',
    ],
    [
        'id' => 'coaching',
        'title' => 'Coaching',
        'lede' => 'Professional coaching built on TrackMan data, not guesswork.',
        'points' => ['Lower your handicap', 'Add distance', 'Build consistency', 'Understand your swing'],
        'cta' => 'Register interest',
    ],
    [
        'id' => 'competitions',
        'title' => 'Competitions & Leagues',
        'lede' => 'Competitive golf indoors, for every ability.',
        'points' => [
            'Indoor golf leagues',
            'Nearest the pin',
            'Longest drive',
            'Seasonal events',
            'Major championship competitions',
        ],
        'cta' => 'Register interest',
    ],
];

const TICKER = [
    'Club Speed', 'Ball Speed', 'Carry Distance', 'Launch Angle',
    'Spin Rate', 'Shot Shape', 'Club Path', 'Attack Angle',
];

/** The options in the enquiry form's "I'm interested in" select. */
const INTERESTS = [
    ['value' => 'bay',          'label' => 'Book a bay'],
    ['value' => 'membership',   'label' => 'Membership'],
    ['value' => 'coaching',     'label' => 'Coaching'],
    ['value' => 'competitions', 'label' => 'Competitions & leagues'],
    ['value' => 'corporate',    'label' => 'Corporate or group event'],
    ['value' => 'other',        'label' => 'Something else'],
];

/** Look up an interest label by its value, for emails and confirmations. */
function interest_label(string $value): string
{
    foreach (INTERESTS as $i) {
        if ($i['value'] === $value) {
            return $i['label'];
        }
    }
    return 'General enquiry';
}
