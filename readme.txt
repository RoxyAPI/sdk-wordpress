=== RoxyAPI: Astrology, Vedic, Tarot, Numerology ===
Contributors: roxyapi
Tags: astrology, horoscope, tarot, numerology, vedic
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.5.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

20+ astrology, horoscope, tarot, and numerology readings in one plugin. Free WordPress blocks and shortcodes, no account to start.

== Description ==

Launch a complete astrology, horoscope, or tarot experience on WordPress this weekend, not this quarter. Drop a block or shortcode on any page and it renders a real reading, server-side, the moment you activate the plugin. No account, no setup, no calculation code to write. Start free, then add an API key when you go live.

**One API key. 12+ domains. 160+ endpoints.** Most astrology plugins do one thing. RoxyAPI covers the whole stack, so you build the entire experience without stitching services together:

* **Western astrology:** natal and birth charts, daily, weekly, and monthly horoscopes, synastry, compatibility, transits, and moon phases
* **Vedic astrology:** kundli, KP charts, panchang, Vimshottari dasha, divisional charts, nakshatras, and yogas
* **Numerology:** Life Path, Expression, Soul Urge, and Personality numbers
* **Tarot:** single card, three card, Celtic Cross, and custom spreads
* **Human design:** full bodygraph with type, authority, profile, centers, channels, and gates
* **Forecasts:** cross-domain timelines, transit forecasts, solar returns, and significant dates
* **Plus:** biorhythm, I Ching, crystals, dream interpretation, and angel numbers

Every chart is calculated by Roxy Ephemeris and verified against NASA JPL Horizons, with readings in 8 languages. One key, one plan, no per-domain fees.

**Free to start. Add a key when you go live.**

Every reading works the moment you activate the plugin, with no account. A free daily allowance, shared across your site, covers casual traffic so you can build and preview complete pages at no cost. When you are ready for production, add a RoxyAPI API key and the daily limit is removed. There is no separate paid plugin and no locked reading types: the same blocks and shortcodes cover every domain whether or not a key is set. A key simply lifts the daily cap, covers production traffic, and keeps all 12+ domains live under one key. Pick a plan at https://roxyapi.com/pricing.

**Also built in:**

* Gutenberg blocks and shortcodes for every reading, plus a visitor form mode for interactive inputs like birth details
* Pythagorean numerology, full 78 card tarot, and a 2,000+ entry dream symbol dictionary
* Interactive SVG charts and cards that follow your light or dark theme automatically
* Readings in 8 languages (English, German, Hindi, Spanish, Turkish, Portuguese, French, Russian) via one setting
* Parent Astrology Section wrapper block that shares the zodiac sign across all child blocks via block context
* Server side caching with per endpoint TTL to keep your API quota low
* API key stays server side. Never exposed to the browser.

**About the service this plugin connects to**

This plugin is a thin WordPress interface to RoxyAPI, a third-party paid service operated at https://roxyapi.com. The astrology, tarot, numerology, and other calculations all run on RoxyAPI servers. **A small free daily allowance lets the plugin display readings without an account; a RoxyAPI API key is required for production use and removes the daily limit.** Pricing and plan tiers are listed at https://roxyapi.com/pricing. Terms of Service: https://roxyapi.com/policy/terms. Privacy Policy: https://roxyapi.com/policy/privacy.

The plugin itself is GPLv2 or later and the source is available at https://github.com/RoxyAPI/sdk-wordpress.

== Shortcode examples ==

Every reading is a shortcode. Pass attributes for a fixed reading the site owner controls, or drop the shortcode with no attributes to render an accessible visitor form. Heroes that take two charts or nested birth details are form mode only.

**Hero shortcodes**

`[roxy_horoscope sign="aries"]`
`[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060" tz="America/New_York"]`
`[roxy_kundli birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_panchang date="2026-04-28" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_mangal_dosha birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_kp_chart birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_moon_phase]`
`[roxy_tarot_card spread="three" question="What should I focus on this week"]`
`[roxy_tarot_yes_no question="Should I take the new job"]`
`[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]`
`[roxy_life_path birth_date="1990-05-15"]`
`[roxy_biorhythm birth_date="1990-05-15" target_date="today"]`
`[roxy_angel_number number="1111"]`
`[roxy_crystals_by_zodiac sign="aries"]`

The three two chart compatibility heroes render a visitor form for both people and take no attributes:

`[roxy_synastry]`
`[roxy_gun_milan]`
`[roxy_compatibility]`

Leave the attributes off any hero shortcode to render a form instead. For example `[roxy_horoscope]` shows a zodiac sign picker and `[roxy_natal_chart]` shows a birth date, time, and city picker.

**Long-tail shortcodes**

A matching shortcode exists for every endpoint in the spec. A sample across the domains:

* Western astrology: `[roxy_calculate_aspects date="1990-07-15" time="14:30:00" timezone="UTC"]` and `[roxy_get_weekly_horoscope sign="aries"]`
* Vedic astrology: `[roxy_get_hora date="2026-02-03" latitude="17.385044" longitude="78.486671" timezone="UTC"]` and `[roxy_calculate_drishti date="2026-02-03" time="12:00:00" latitude="17.385044" longitude="78.486671" timezone="UTC"]`
* Tarot: `[roxy_cast_celtic_cross question="What should I know about this path"]`
* Numerology: `[roxy_calculate_expression full_name="Ada Lovelace"]`
* Human design: `[roxy_generate_bodygraph date="1990-07-15" time="13:00:00" timezone="UTC" latitude="40.7128" longitude="-74.0060"]`, `[roxy_calculate_variables date="1990-07-15" time="13:00:00" timezone="UTC" latitude="40.7128" longitude="-74.0060"]`, plus the two chart `[roxy_calculate_connection]` and `[roxy_calculate_penta]` which render visitor forms and take no attributes
* Forecast: `[roxy_generate_digest]` (renders a visitor form, no attributes)
* I Ching: `[roxy_get_daily_hexagram]`
* Crystals: `[roxy_get_crystal id="amethyst"]`
* Dreams: `[roxy_search_dream_symbols q="water"]`
* Location: `[roxy_search_cities q="berlin"]`

Add lang to any shortcode to override the response language, for example `[roxy_get_crystal id="amethyst" lang="es"]`.

== Installation ==

1. In your WordPress admin, go to Plugins, Add New, search for "RoxyAPI", and click Install Now.
2. Activate the plugin.
3. Sign up for a RoxyAPI account at https://roxyapi.com and get your API key.
4. Open the RoxyAPI menu in the WordPress admin sidebar and paste your key.
5. Click Save Changes. Use the Test Connection button to verify.
6. Add a block from the inserter or use the matching shortcode.

**Configure the API key via wp-config.php (recommended for production):**

Add this line to your wp-config.php above the "stop editing" comment:

`define('ROXYAPI_KEY', 'your_roxyapi_key_here');`

When the constant is set, the settings field is disabled and the constant takes priority.

For production hosts that inject secrets via environment variables (Pantheon, WP Engine, Kinsta, Bedrock), also define the encryption key and salt:

`define( 'ROXYAPI_ENCRYPTION_KEY', getenv( 'ROXYAPI_ENCRYPTION_KEY' ) );`
`define( 'ROXYAPI_ENCRYPTION_SALT', getenv( 'ROXYAPI_ENCRYPTION_SALT' ) );`

Without these, the plugin falls back to your WordPress LOGGED_IN_KEY and LOGGED_IN_SALT, which is acceptable for most installs.

== Frequently Asked Questions ==

= Do I need a RoxyAPI account? =

Not to get started. A limited number of free readings per day are allowed right after install, with no account, so you can try the plugin. The allowance is counted per site and resets each day. For production use, add an API key from a RoxyAPI plan: one key covers every reading and removes the daily limit. Pick a plan at https://roxyapi.com/pricing.

= What readings can I add to my site? =

One key covers 12 domains. Western astrology: natal chart, daily / weekly / monthly horoscopes, synastry, compatibility, transits, aspect patterns, and moon phases. Vedic astrology: kundli, KP chart, panchang, Vimshottari dasha, divisional charts, nakshatras, doshas (Manglik, Kaal Sarpa, Sade Sati), and classical yogas. Numerology: Life Path, Expression, Soul Urge, Personality, personal year, and compatibility. Tarot: single card, three card, Celtic Cross, and the full 78 card catalog. Human design: bodygraph, type, authority, profile, centers, channels, and gates. Forecasts: timelines, transit forecasts, solar returns, and significant dates. Plus biorhythm, I Ching hexagrams, crystal reference data, dream symbols, and angel numbers. Every reading is available as a shortcode. The chart and reading heroes plus more than 130 long-tail endpoints also ship a matching Gutenberg block. Interactive multi input readings such as two chart compatibility and nested birth forms are shortcode and visitor form mode, because the block editor cannot collect their nested input. Browse the full list inside WordPress under RoxyAPI, Shortcodes.

= Can I show readings in another language? =

Yes. Open the RoxyAPI menu, Branding tab, and pick a response language: English, German, Hindi, Spanish, Turkish, Portuguese, French, or Russian. Every reading is then returned in that language. You can also override the language per shortcode with a lang attribute, for example [roxy_horoscope sign="aries" lang="es"].

= When does the plugin contact the RoxyAPI service? =

The plugin contacts roxyapi.com only when you take a clear action that requires it:

1. You click the Test Connection button on the settings page.
2. A page on your site that contains a RoxyAPI block or shortcode is rendered (cached for one hour by default to keep your API quota low). This render-time call happens whether or not an API key is set: with a key it uses your plan, without one it uses the free daily allowance.

The plugin never contacts RoxyAPI on plugin activation, on plugin update, on any admin page that does not display a reading, or in the background. Placing a RoxyAPI block or shortcode on a page is the explicit action that authorizes the render-time calls described above.

= What data is sent to RoxyAPI? =

When the plugin contacts roxyapi.com, the request includes:

* The reading parameters you supply via the block or shortcode (zodiac sign, birth date, name, location coordinates, question text).
* Your site URL (`home_url`) in an `X-Site-URL` header so RoxyAPI can attribute requests to your site for support and rate limiting.
* A plugin identifier (`X-SDK-Client: roxy-sdk-wordpress/<version>`) so RoxyAPI can detect compatibility issues by version.
* Your server outbound IP address (incidentally captured by the receiving server, like any HTTP request).

No site visitor data is collected by the plugin when a visitor only views a page; their IP, user agent, and any browser-side data are not sent to RoxyAPI in the passive case. When a visitor submits a form-mode shortcode (their birth date, name, or question), the plugin sends only the fields they typed, after they tick the consent checkbox. See https://roxyapi.com/policy/privacy for what RoxyAPI does with the data once received.

= Can visitors fill in their own birth details? =

Yes. Drop a hero shortcode with no attributes (for example [roxy_natal_chart]) and the plugin renders an accessible form with a city search that fills in coordinates automatically. On submit the plugin validates the input, applies a per-IP rate limit and a consent checkbox, calls the API server side, and renders the result on the same page. The API key never reaches the browser.

= Can I sell readings or charts to my clients? =

Yes, and you do not need a separate integration. Place any RoxyAPI shortcode or block on a page, then gate that page with a membership or paywall plugin such as Paid Memberships Pro, Restrict Content, or WP-Members, or sell access as a WooCommerce product through a memberships add-on. Members and buyers who reach the page see the reading; everyone else sees your paywall. Because the chart renders exactly where you place the shortcode, you decide what stays free, like a teaser daily horoscope, and what is paid, like a full natal chart, a compatibility score, or a Vedic kundli. RoxyAPI keeps no birth data, so your client list and their details stay entirely yours.

= Is the API key safe? =

Yes. The plugin makes API calls server side in PHP. The key is never sent to the browser. You can also store the key in wp-config.php with define('ROXYAPI_KEY', '...') if you do not want it in the database. Stored keys are encrypted at rest via AES-256-CTR.

= Does this work with caching plugins? =

Yes. The plugin uses WordPress transients, which automatically use Redis or Memcached if you have a persistent object cache.

= Can I customize the styling? =

Yes. Charts follow your light or dark mode automatically and read the --roxy-* CSS custom properties. Set an accent color and a light, dark, or auto theme in the RoxyAPI menu under Branding, or override any --roxy-* token in your theme stylesheet. Every output element also has a .roxyapi-* class you can target.

= Which calculation engine powers RoxyAPI? =

RoxyAPI cross-checks its astronomy calculations against the NASA JPL Horizons ephemeris (a public NASA dataset; no affiliation with NASA or JPL). See https://roxyapi.com/methodology for the full breakdown.

= How do I share the zodiac sign across multiple blocks on one page? =

Add the Astrology Section wrapper block, set the sign in its Inspector, then drop child RoxyAPI blocks inside. Every child inherits the sign via block context.

= Can I try it for free before subscribing? =

Yes. The plugin works the moment you activate it. Add a shortcode or block and it renders on a free daily allowance, no API key required. When you need more headroom, add a key from your RoxyAPI account; see https://roxyapi.com/pricing for current plans. You can also call every endpoint live in your browser at https://roxyapi.com/api-reference, no signup needed.

= Does this work with Elementor, Divi, or other page builders? =

Yes. All RoxyAPI shortcodes work inside any page builder that supports WordPress shortcodes. Use the shortcode in a text or HTML module. The Gutenberg blocks work in the default WordPress editor.

== Screenshots ==

1. Western natal chart wheel with planets, houses, aspect lines, and the chart angles. Rendered server-side, so your API key never reaches the browser.
2. The Shortcodes Library. Every reading across every domain in one searchable, copy-paste browser.
3. Vedic kundli rendered as an interactive chart in North, South, and East Indian styles.
4. Detailed panchang: tithi, nakshatra, yoga, karana, planetary hours, and the auspicious muhurtas for any date and place.
5. Human Design bodygraph with type, strategy, authority, profile, the nine centers, channels, and gates.
6. Astrocartography map. Every planetary line plotted across the world for relocation and travel planning.
7. Daily horoscope card for any zodiac sign, with love, career, health, finance, and lucky details.
8. Connect in seconds. Free to start, with copy-paste shortcodes and a guided quick start.

== Changelog ==

= 1.5.7 =
* The KP chart now shows the KP number for Rahu and Ketu. Every other body in that table carried it and those two rows sat blank, even though the reading always included the value. The KP number is the 1 to 249 position a KP reader looks at first, so a blank cell dropped the part of the row that matters most.

= 1.5.6 =
* Your brand color now themes the whole reading. Set an accent in Branding and it carries through every chart, table and card, including the highlighted labels, the chart angle markers and the focus outlines. Previously those kept the default amber no matter what you picked.
* Roomier readings. Where a reading nests a table inside a table, the inner column now gets the width it needs instead of being squeezed into a sliver, and very deep sections start folded so a long reading is scannable rather than endless.
* Cleaner cards. Long summary text is no longer promoted into a heading, and nested cards no longer stack borders inside borders.

= 1.5.5 =
* Cleaner reading cards. Readings no longer show internal working: the step by step arithmetic behind a number, the schema label for the reading type, and the page counters on list readings are all hidden now. They were only ever visible when JavaScript was on, so a reading could look different depending on the visitor. Affirmations and mantras render as a quoted line, long tables fold away behind their row count, and every data table now announces what it holds to a screen reader.
* Consistent times in the muhurta readings. Hora, choghadiya and panchang each formatted their times differently, and one of them changed format depending on the visitor's region. All three now read the same way, and a chart time can no longer shift by an hour for a visitor in a daylight saving changeover.

= 1.5.4 =
* Clearer plugin listing: the description now leads with the full breadth (12+ domains and 160+ endpoints under one API key) as a scannable list, so you can see every reading type you can add at a glance.

= 1.5.3 =
* Quieter first run: the one-time "installed but not yet connected" setup notice no longer shows. The plugin already renders readings out of the box on the free daily allowance, so there is nothing to connect before you see it working.
* Friendlier message when the free daily allowance is used up for the day. It now reassures you that the allowance resets each day and readings return on their own, and points you to add an API key to remove the daily limit.

= 1.5.2 =
* Detailed readings such as solar returns, lunar returns, and the monthly ephemeris now render as clean readable tables: rounded numbers, formatted dates, Yes and No flags, clickable links, and full width sections. Wide tables scroll sideways instead of squeezing.
* Return charts, significant dates, forecast timelines, and other date window readings are now cached, so repeat page views load faster and spend less quota.
* Fixed the Quota saver cache preset shortening the cache for long lived readings such as natal charts.
* Fixed a truncated help note under the timezone field in visitor forms.
* Refreshed the bundled component library.

= 1.5.1 =
* Added a Browse all shortcodes button on the settings screen, so you can jump straight to the full shortcode library in one click.

= 1.5.0 =
* Five more Western readings: asteroids, Black Moon Lilith, secondary progressions, solar arc directions, and annual profections.
* The new location and predictive readings now render as rich interactive charts and tables instead of plain cards: astrocartography map, local space compass, relocation wheel, position tables for asteroids, Lilith, progressions, solar arc, and the Arabic lots, fixed star conjunctions, and the annual profection card.
* Refreshed the bundled component library. The astrocartography map now plots its planetary lines over a world map of the continents for clearer relocation and travel planning.

= 1.4.5 =
* Maintenance and upstream data refresh.

= 1.4.4 =
* New readings: astrocartography, local space, relocation chart, fixed stars, and the Arabic lots.
* Upstream data refresh.

= 1.4.3 =
* Maintenance and upstream data refresh.

= 1.4.2 =
* Maintenance and upstream data refresh.

= 1.4.1 =
* Free to start: new installs open the Shortcodes library so you can drop a reading on a page right away, no key needed.
* The Settings screen now explains the free daily allowance and what an API key unlocks.
* Clearer wording on trying the plugin for free before subscribing.

= 1.4.0 =
* Ten new interactive readings: natal and transit aspects with chart-pattern detection, Vedic graha drishti aspects, planetary hours (hora), a cross-domain forecast digest, single-crystal detail, dream-symbol search, Human Design connection, penta, and variables, plus a reference card for zodiac, planet, rashi, gate, center, and number lookups.
* Refreshed the bundled component library.
* Fixed numeric inputs such as timezone in attribute-mode shortcodes so they reach the service correctly.

= 1.3.1 =
* The admin Shortcodes library now groups Human Design, Forecast, and Languages under the right headings instead of Other.
* Readings default to the light theme.

= 1.3.0 =
* Gutenberg blocks now render the full interactive charts and cards, matching the shortcodes.
* New interactive components for human design (bodygraph) and forecast timelines, plus synastry, compatibility, crystals, dream symbols, and angel numbers.
* Visitor forms now return to the same page after submission and render the interactive result in place.
* The response language setting applies to every reading, and changing it refreshes cached readings.
* Renamed the admin Connect menu item to Settings. Refreshed the bundled component library.

= 1.2.5 =
* Build and dependency maintenance.

= 1.2.4 =
* The API key field now accepts the current publishable and secret key formats (pk and sk) alongside older keys. Saving or rotating a key also clears the cached readings.

= 1.2.3 =
* Dependency maintenance.

= 1.2.2 =
* Dependency maintenance.

= 1.2.1 =
* Dependency maintenance.

= 1.2.0 =
* Build and release-tooling maintenance.

= 1.1.0 =
* Charts now render as interactive SVG: natal, kundli, KP, panchang, dasha, and more, instead of plain tables.
* Light and dark mode. Charts follow the visitor device automatically. Pick an accent color and a theme in the RoxyAPI menu under Branding.
* Try it free. A limited number of free readings per day are allowed right after install, with no account.

= 1.0.4 =
* Tidy distribution: drop maintainer-only files (CITATION.cff, eslint.config.cjs, empty patterns scaffold) that were leaking into the published zip.

= 1.0.3 =
* Sync to the latest RoxyAPI spec, now 133 endpoints across 10 spiritual domains.
* Western astrology: detect aspect patterns (Grand Trine, Kite, T-Square, Grand Cross, Yod, Mystic Rectangle, Stellium) via new [roxy_detect_aspect_patterns] shortcode + matching block.
* Vedic astrology: detect classical Vedic yogas in a birth chart via new [roxy_detect_yogas] shortcode + matching block.
* Natal chart, transits, and aspect calculations now include Black Moon Lilith alongside the lunar nodes and Chiron for full 14-body planetary coverage.
* Yoga catalog entries reworded for clearer glossary phrasing in both the list and the per-yoga detail responses.

= 1.0.2 =
* Initial public release on the WordPress Plugin Directory.
* 131 endpoints across 10 spiritual domains under one RoxyAPI key. 17 hero shortcodes with matching Gutenberg blocks (Western astrology, Vedic astrology, tarot, numerology, biorhythm, angel numbers, crystals) plus 117 auto-generated long-tail shortcodes for the full spec.
* Compatible with the declared WordPress 6.5 minimum: block registration no longer calls a WordPress 6.7 only function.
* Form mode on every hero shortcode: drop with no attributes and visitors fill the form themselves. Server-side submission, API key never reaches the browser, GDPR Article 9 consent gate, per-IP rate limit.
* Two-chart heroes (Synastry, Gun Milan, Compatibility) ship form-only with Person 1 / Person 2 fieldsets and ARIA 1.2 combobox city autocomplete via a key-protected /wp-json/roxyapi/v1/geocode proxy.
* Tabbed admin settings (Connect, Branding, Display, Privacy, Advanced) with accent color, response-language picker, disclaimer line, cache preset (fresh / balanced / quota saver), and inline Test Connection button. API key supports a ROXYAPI_KEY wp-config constant override.
* Encryption at rest via AES 256 CTR. Server-side caching with per-endpoint TTL via WordPress transients (Redis / Memcached compatible). Block Bindings API source roxyapi/daily-text for inline horoscope binding.

== Upgrade Notice ==

= 1.5.4 =
Listing and description improvements only. No functional or code changes.

= 1.5.3 =
Quieter first run and a clearer, more reassuring message when the free daily allowance is used up for the day.

= 1.5.2 =
Cleaner tables for detailed readings, caching for return charts and forecasts, and a Quota saver preset fix.

= 1.5.1 =
A Browse all shortcodes button on the settings screen for one-click access to the full library.

= 1.5.0 =
Five more Western readings and rich interactive charts for the location and predictive readings.

= 1.4.1 =
New installs open the Shortcodes library so you can start free in seconds, and Settings now explains the free daily allowance.

= 1.4.0 =
Ten new interactive readings including aspects, planetary hours, forecast digest, crystal detail, dream search, and Human Design connection, penta, and variables.

= 1.3.0 =
Blocks now render the full interactive charts and cards, human design and forecast components are added, and visitor form submissions return to the page with the result.

== Privacy ==

**This plugin connects to a third-party paid service (RoxyAPI).**

* Service: RoxyAPI (https://roxyapi.com)
* Service operator: RoxyAPI
* Terms of Service: https://roxyapi.com/policy/terms
* Privacy Policy: https://roxyapi.com/policy/privacy
* Pricing: https://roxyapi.com/pricing

**When does the plugin contact RoxyAPI?** Only after you save an API key on the settings page and either click Test Connection or render a RoxyAPI block or shortcode on a page. Saving your API key is the explicit consent for these calls. The plugin does NOT contact RoxyAPI on activation, on update, in the background, or on admin pages that do not display a reading.

**What data is sent?** The reading parameters you supply via the block or shortcode (zodiac sign, birth date, name, location coordinates, question text), your site URL (in an `X-Site-URL` header), and a plugin identifier (in an `X-SDK-Client` header). Your server outbound IP is incidentally captured by RoxyAPI, like any HTTP request. **No site visitor data is collected by the plugin itself.**

**Where is the API key stored?** Either as the `ROXYAPI_KEY` constant in your `wp-config.php` (recommended) or AES-256-CTR encrypted in the `wp_options` table under `roxyapi_settings`. The plain key is never sent to the browser at any time.

**What does RoxyAPI do with the data?** See https://roxyapi.com/policy/privacy.
