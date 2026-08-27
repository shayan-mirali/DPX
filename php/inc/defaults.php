<?php
/**
 * Shipped content — the factory defaults.
 *
 * Anything the admin dashboard changes is written to
 * storage/content.json and merged over the top of this at runtime, so the
 * two never mix. To undo every edit ever made, delete that one JSON file
 * and the site returns to exactly what is below.
 *
 * Edit this file only to change what "reset to default" produces. For
 * ordinary copy changes, use the dashboard.
 *
 * Generated from the original inc/content.php, so the values are exactly
 * what shipped. A few notes worth keeping are repeated here:
 *
 *  - SITE.emails arrived on two different domains (dpxgolf.co.uk and
 *    dpx.co.uk). Entered as supplied; the second is worth confirming.
 *  - SITE.legal.office is the REGISTERED office, not the venue. Customers
 *    visit SITE.address. The footer says so explicitly.
 *  - SITE.bookingUrl is null, so every "Book a Bay" control scrolls to the
 *    enquiry form. Set it and they all open the booking system instead.
 *  - PRICING stores only the bay total; the per-person figure and the
 *    structured-data price range are derived from it.
 *  - PRICING.notes[1] claims clubs are available to borrow. That was never
 *    confirmed by the venue — verify or remove it.
 */

declare(strict_types=1);

return array (
  'SITE' => 
  array (
    'name' => 'DPX Golf',
    'tagline' => 'Swing Better. Play More. Experience Golf Differently.',
    'descriptor' => 'Premium Indoor Golf',
    'town' => 'Burton upon Trent',
    'address' => 
    array (
      'line1' => 'Oakwood House',
      'line2' => 'Bretby Business Park',
      'line3' => 'Ashby Road East',
      'town' => 'Burton upon Trent',
      'postcode' => 'DE15 0PS',
      'country' => 'GB',
    ),
    'emails' => 
    array (
      0 => 'markpaxton@dpxgolf.co.uk',
      1 => 'heatherfisher@dpx.co.uk',
    ),
    'phone' => '+44 7368 805031',
    'hours' => 
    array (
      'opens' => '10:00',
      'closes' => '22:00',
      'days' => 
      array (
        0 => 'Monday',
        1 => 'Tuesday',
        2 => 'Wednesday',
        3 => 'Thursday',
        4 => 'Friday',
        5 => 'Saturday',
        6 => 'Sunday',
      ),
    ),
    'legal' => 
    array (
      'company' => 'DPX Golf Ltd',
      'companyNumber' => '17054770',
      'office' => 
      array (
        'line1' => 'Chartwell House',
        'line2' => '4 St Paul\'s Square',
        'town' => 'Burton upon Trent',
        'country' => 'England',
        'postcode' => 'DE14 2EF',
      ),
    ),
    'bookingUrl' => NULL,
    'origin' => 'https://dpxgolf.co.uk',
  ),
  'NAV' => 
  array (
    0 => 
    array (
      'label' => 'The Venue',
      'href' => '#venue',
    ),
    1 => 
    array (
      'label' => 'Technology',
      'href' => '#tech',
    ),
    2 => 
    array (
      'label' => 'Who It\'s For',
      'href' => '#who',
    ),
    3 => 
    array (
      'label' => 'Pricing',
      'href' => '#pricing',
    ),
    4 => 
    array (
      'label' => 'What\'s Coming',
      'href' => '#coming',
    ),
  ),
  'PRICING' => 
  array (
    'durations' => 
    array (
      0 => 1,
      1 => 2,
      2 => 3,
      3 => 4,
    ),
    'periods' => 
    array (
      0 => 
      array (
        'id' => 'offpeak',
        'label' => 'Weekday Off-Peak',
        'when' => 'Monday – Friday · 10am – 4pm',
        'rows' => 
        array (
          0 => 
          array (
            'players' => 1,
            'totals' => 
            array (
              0 => 15,
              1 => 28,
              2 => 40,
              3 => 52,
            ),
          ),
          1 => 
          array (
            'players' => 2,
            'totals' => 
            array (
              0 => 24,
              1 => 44,
              2 => 64,
              3 => 84,
            ),
          ),
          2 => 
          array (
            'players' => 3,
            'totals' => 
            array (
              0 => 30,
              1 => 57,
              2 => 84,
              3 => 111,
            ),
          ),
          3 => 
          array (
            'players' => 4,
            'totals' => 
            array (
              0 => 36,
              1 => 68,
              2 => 100,
              3 => 132,
            ),
          ),
        ),
      ),
      1 => 
      array (
        'id' => 'peak',
        'label' => 'Peak & Weekends',
        'when' => 'Monday – Friday 4pm – 10pm · Saturday & Sunday all day',
        'rows' => 
        array (
          0 => 
          array (
            'players' => 1,
            'totals' => 
            array (
              0 => 25,
              1 => 48,
              2 => 69,
              3 => 88,
            ),
          ),
          1 => 
          array (
            'players' => 2,
            'totals' => 
            array (
              0 => 36,
              1 => 68,
              2 => 96,
              3 => 124,
            ),
          ),
          2 => 
          array (
            'players' => 3,
            'totals' => 
            array (
              0 => 45,
              1 => 84,
              2 => 120,
              3 => 156,
            ),
          ),
          3 => 
          array (
            'players' => 4,
            'totals' => 
            array (
              0 => 56,
              1 => 104,
              2 => 148,
              3 => 192,
            ),
          ),
        ),
      ),
    ),
    'vatNote' => 'All prices include VAT.',
    'notes' => 
    array (
      0 => 'Prices are per bay, not per person — split it however you like.',
      1 => 'Up to 4 players per bay. Clubs are available if you don\'t have your own.',
    ),
  ),
  'METRICS' => 
  array (
    0 => 
    array (
      'key' => 'Club Speed',
      'value' => 113.2,
      'unit' => 'mph',
      'decimals' => 1,
    ),
    1 => 
    array (
      'key' => 'Ball Speed',
      'value' => 167.4,
      'unit' => 'mph',
      'decimals' => 1,
    ),
    2 => 
    array (
      'key' => 'Carry Distance',
      'value' => 289,
      'unit' => 'yds',
      'decimals' => 0,
    ),
    3 => 
    array (
      'key' => 'Launch Angle',
      'value' => 12.8,
      'unit' => 'deg',
      'decimals' => 1,
    ),
    4 => 
    array (
      'key' => 'Spin Rate',
      'value' => 2540,
      'unit' => 'rpm',
      'decimals' => 0,
    ),
    5 => 
    array (
      'key' => 'Shot Shape',
      'value' => 4.1,
      'unit' => 'yds',
      'decimals' => 1,
    ),
    6 => 
    array (
      'key' => 'Club Path',
      'value' => 1.8,
      'unit' => 'deg',
      'decimals' => 1,
    ),
    7 => 
    array (
      'key' => 'Attack Angle',
      'value' => -1.4,
      'unit' => 'deg',
      'decimals' => 1,
    ),
  ),
  'FEATURES' => 
  array (
    0 => 
    array (
      'n' => '01',
      'title' => 'Premium TrackMan Technology',
      'body' => 'One of the world\'s most advanced golf simulator systems — the same platform trusted by professionals, coaches and golfers worldwide.',
      'span' => 'wide',
    ),
    1 => 
    array (
      'n' => '02',
      'title' => 'Premium Golf Bays',
      'body' => 'Spacious simulator bays built for comfort, whether you\'re grooving a swing, playing a full round or settling in for a social game.',
      'span' => 'tall',
    ),
    2 => 
    array (
      'n' => '03',
      'title' => 'Play Iconic Courses',
      'body' => 'Some of the greatest courses on earth, without leaving Burton upon Trent.',
      'span' => 'normal',
    ),
    3 => 
    array (
      'n' => '04',
      'title' => 'Practice Smarter',
      'body' => 'Instant, honest feedback on every parameter that actually moves your game — club speed, ball speed, carry, launch, spin, shape, path and attack angle.',
      'span' => 'wide',
    ),
    4 => 
    array (
      'n' => '05',
      'title' => 'Relax & Socialise',
      'body' => 'Comfortable seating, refreshments and a welcoming room to unwind in.',
      'span' => 'normal',
    ),
  ),
  'AUDIENCES' => 
  array (
    0 => 
    array (
      'id' => 'golfers',
      'title' => 'Golfers',
      'body' => 'Practise all year round with accurate data and realistic course conditions built to move your performance forward.',
      'note' => 'All year. Any weather.',
    ),
    1 => 
    array (
      'id' => 'beginners',
      'title' => 'Beginners',
      'body' => 'New to golf? No problem. The simulators are a relaxed, genuinely fun way to learn without the pressure of a first tee.',
      'note' => 'No experience needed.',
    ),
    2 => 
    array (
      'id' => 'families',
      'title' => 'Families',
      'body' => 'Interactive golf games and challenges that work for every age in the group, not just the one with a handicap.',
      'note' => 'All ages welcome.',
    ),
    3 => 
    array (
      'id' => 'corporate',
      'title' => 'Corporate',
      'body' => 'A venue that isn\'t another meeting room — team building, client entertainment, networking and company events.',
      'note' => 'Private bays available.',
    ),
    4 => 
    array (
      'id' => 'groups',
      'title' => 'Groups & Celebrations',
      'body' => 'Make the next occasion a different one: great golf, real competition and a night people actually remember.',
      'note' => 'Book the room.',
    ),
  ),
  'ROADMAP' => 
  array (
    0 => 
    array (
      'id' => 'membership',
      'title' => 'Membership',
      'lede' => 'Exclusive packages for regular golfers who want more from DPX.',
      'points' => 
      array (
        0 => 'Exclusive member offers',
        1 => 'Priority booking',
        2 => 'Members-only events',
        3 => 'And more to come',
      ),
      'cta' => 'Register interest',
    ),
    1 => 
    array (
      'id' => 'coaching',
      'title' => 'Coaching',
      'lede' => 'Professional coaching built on TrackMan data, not guesswork.',
      'points' => 
      array (
        0 => 'Lower your handicap',
        1 => 'Add distance',
        2 => 'Build consistency',
        3 => 'Understand your swing',
      ),
      'cta' => 'Register interest',
    ),
    2 => 
    array (
      'id' => 'competitions',
      'title' => 'Competitions & Leagues',
      'lede' => 'Competitive golf indoors, for every ability.',
      'points' => 
      array (
        0 => 'Indoor golf leagues',
        1 => 'Nearest the pin',
        2 => 'Longest drive',
        3 => 'Seasonal events',
        4 => 'Major championship competitions',
      ),
      'cta' => 'Register interest',
    ),
  ),
  'TICKER' => 
  array (
    0 => 'Club Speed',
    1 => 'Ball Speed',
    2 => 'Carry Distance',
    3 => 'Launch Angle',
    4 => 'Spin Rate',
    5 => 'Shot Shape',
    6 => 'Club Path',
    7 => 'Attack Angle',
  ),
  'INTERESTS' => 
  array (
    0 => 
    array (
      'value' => 'bay',
      'label' => 'Book a bay',
    ),
    1 => 
    array (
      'value' => 'membership',
      'label' => 'Membership',
    ),
    2 => 
    array (
      'value' => 'coaching',
      'label' => 'Coaching',
    ),
    3 => 
    array (
      'value' => 'competitions',
      'label' => 'Competitions & leagues',
    ),
    4 => 
    array (
      'value' => 'corporate',
      'label' => 'Corporate or group event',
    ),
    5 => 
    array (
      'value' => 'other',
      'label' => 'Something else',
    ),
  ),
);
