=== RoxyAPI: Astrology, Vedic, Forecast, Human Design, Numerology ===
Contributors: roxyapi
Tags: astrology, horoscope, tarot, vedic, human design
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.13.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Birth charts, horoscopes, kundli, Human Design, BaZi and Feng Shui as blocks and shortcodes. 14+ domains on one key. Free to start.

== Description ==

RoxyAPI adds Astrology, Vedic, Forecast, Human Design, Chinese Astrology and Feng Shui readings to any WordPress post, page or widget, as Gutenberg blocks and shortcodes. Drop one on a page and it renders a real reading, server side, the moment you activate the plugin. No account, no setup, no calculation code to write. Start free, then add an API key when you go live.

**One API key. 14+ domains. 209+ endpoints.** Most astrology plugins cover one system. RoxyAPI covers the whole stack, so you build the entire experience without stitching services together:

* **Western astrology:** natal and birth charts, daily, weekly, and monthly horoscopes, synastry, compatibility, transits, and moon phases
* **Vedic astrology:** kundli, KP charts, panchang, Vimshottari dasha, divisional charts, nakshatras, yogas, gochara transits, and the Bhava Bala and Bhav Chalit house readings
* **Forecast:** cross-domain timelines, transit forecasts, solar returns, and significant dates
* **Human Design:** full bodygraph with type, authority, profile, centers, channels, gates, variables, and penta
* **Chinese astrology:** BaZi four pillars, Day Master strength, luck pillars, zodiac signs and compatibility, the 24 solar terms, and the lunisolar almanac
* **Feng shui:** Kua numbers, Eight Mansions directions, flying star charts, and annual afflictions
* **Numerology:** Life Path, Expression, Soul Urge, and Personality numbers
* **Tarot:** single card, three card, Celtic Cross, and custom spreads
* **Plus:** biorhythm, I Ching, crystals, dream interpretation, and angel numbers

Every chart is calculated by Roxy Ephemeris and verified against NASA JPL Horizons, with readings in 8 languages. One key, one plan, no per-domain fees.

**Frequently asked, answered up front**

* **Do I need an account to try it?** No. Blocks and shortcodes render readings straight after activation on a free daily allowance.
* **Do I need to know astrology to use it?** No. Every reading arrives calculated and formatted, so you place a block and pick your options.
* **Is the birth chart accurate?** Charts come from Roxy Ephemeris and are verified against NASA JPL Horizons.
* **Can visitors enter their own birth details?** Yes. Leave the attributes off a hero shortcode and it renders a form; the submission is processed server side.
* **Does it work with my theme?** Yes. Charts are SVG and follow your light or dark theme through CSS custom properties.
* **Where does my API key live?** On your server only. It is never sent to the browser.

**Free to start. Add a key when you go live.**

Every reading works the moment you activate the plugin, with no account. A free daily allowance, shared across your site, covers casual traffic so you can build and preview complete pages at no cost. When you are ready for production, add a RoxyAPI API key and the daily limit is removed. There is no separate paid plugin and no locked reading types: the same blocks and shortcodes cover every domain whether or not a key is set. A key simply lifts the daily cap, covers production traffic, and keeps all 14+ domains live under one key. Pick a plan at https://roxyapi.com/pricing.

**Also built in:**

* Gutenberg blocks and shortcodes for every reading, plus a visitor form mode for interactive inputs like birth details
* Pythagorean numerology, full 78 card tarot, and a 2,000+ entry dream symbol dictionary
* Interactive SVG charts and cards that follow your light or dark theme automatically
* Readings in 8 languages (English, German, Hindi, Spanish, Turkish, Portuguese, French, Russian) via one setting
* Parent Astrology Section wrapper block that sets the zodiac sign for the Horoscope blocks placed inside it
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
* Forecast: `[roxy_generate_digest]` (renders a visitor form, no attributes)
* Human Design: `[roxy_generate_bodygraph date="1990-07-15" time="13:00:00" timezone="UTC" latitude="40.7128" longitude="-74.0060"]`, `[roxy_calculate_variables date="1990-07-15" time="13:00:00" timezone="UTC" latitude="40.7128" longitude="-74.0060"]`, plus the two chart `[roxy_calculate_connection]` and `[roxy_calculate_penta]` which render visitor forms and take no attributes
* Chinese astrology: `[roxy_generate_bazi_chart date="1990-06-15" time="14:30:00" timezone="Asia/Shanghai"]`, `[roxy_get_zodiac_animal id="dragon"]` and `[roxy_get_almanac_day date="2026-02-17"]`
* Feng shui: `[roxy_calculate_kua_number date="1985-07-15" gender="male"]` and `[roxy_get_annual_flying_stars year="2026"]`
* Numerology: `[roxy_calculate_expression full_name="Ada Lovelace"]`
* Tarot: `[roxy_cast_celtic_cross question="What should I know about this path"]`
* I Ching: `[roxy_get_daily_hexagram]`
* Crystals: `[roxy_get_crystal id="amethyst"]`
* Dreams: `[roxy_search_dream_symbols q="water"]`
* Location: `[roxy_search_cities q="berlin"]`

Readings follow your WordPress site language automatically. To pick a language yourself, open the RoxyAPI menu, Branding tab, and set the reading language. Some of the long tail shortcodes above also take a lang attribute, for example `[roxy_get_crystal id="amethyst" lang="es"]`, but the headline readings take the site or Branding setting instead.

Want the chart on its own, without the written report? Open the RoxyAPI menu, Display tab, and turn on Written readings. Every reading on the site then shows its chart, tables, and values with the written text left out. To set it for one placement instead of the whole site, add `hide_readings="1"` to that shortcode, for example `[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060" tz="America/New_York" hide_readings="1"]`. Passing `hide_readings="0"` keeps the written text on that placement even when the site setting is on.

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

Not to get started. A limited number of free readings per day are allowed right after install, with no account, so you can try the plugin. The allowance is shared across your site and resets each day. For production use, add an API key from a RoxyAPI plan: one key covers every reading and removes the daily limit. Pick a plan at https://roxyapi.com/pricing.

= What readings can I add to my site? =

One key covers 14 domains. Western astrology: natal chart, daily / weekly / monthly horoscopes, synastry, compatibility, transits, aspect patterns, and moon phases. Vedic astrology: kundli, KP chart, panchang, Vimshottari dasha, divisional charts, nakshatras, doshas (Manglik, Kaal Sarpa, Sade Sati), and classical yogas. Numerology: Life Path, Expression, Soul Urge, Personality, personal year, and compatibility. Tarot: single card, three card, Celtic Cross, and the full 78 card catalog. Human Design: bodygraph, type, authority, profile, centers, channels, and gates. Forecasts: timelines, transit forecasts, solar returns, and significant dates. Chinese astrology: BaZi four pillars, Day Master strength, luck pillars, zodiac signs and compatibility, the 24 solar terms, and the lunisolar almanac. Feng shui: Kua numbers, Eight Mansions directions, flying star charts, and annual afflictions. Plus biorhythm, I Ching hexagrams, crystal reference data, dream symbols, and angel numbers. Every reading is available as a shortcode. More than 150 of them also ship a matching Gutenberg block, each with sidebar controls for its inputs (a date picker for dates, a dropdown for fixed choices, text and number fields for the rest) and a live preview in the editor. The headline readings (horoscope, natal chart, tarot, numerology, biorhythm, angel number) are built the same way, and the horoscope block adds a variation picker. Interactive multi input readings such as two chart compatibility and nested birth forms stay shortcode and visitor form mode, because the block editor cannot collect their nested input. Browse the full list inside WordPress under RoxyAPI, Shortcodes.

= Can I show readings in another language? =

Yes. Readings follow your WordPress site language on their own, so a Spanish site returns Spanish readings with nothing to configure. To run your site in one language and your readings in another, open the RoxyAPI menu, Branding tab, and pick a reading language: English, German, Hindi, Spanish, Turkish, Portuguese, French, or Russian. Readings switch to that language, though a handful of specialized terms stay in English.

= When does the plugin contact the RoxyAPI service? =

The plugin contacts roxyapi.com only when you take a clear action that requires it:

1. You click the Test Connection button on the settings page.
2. A page on your site that contains a RoxyAPI block or shortcode is rendered (cached for one hour by default to keep your API quota low). Readings render with or without an API key, so these requests also happen before you connect one.

The plugin never contacts RoxyAPI on plugin activation, on plugin update, on any admin page that does not display a reading, or in the background. Placing a RoxyAPI block or shortcode on a page is the explicit action that authorizes the render-time calls described above.

= What data is sent to RoxyAPI? =

When the plugin contacts roxyapi.com, the request includes:

* The reading parameters you supply via the block or shortcode (zodiac sign, birth date, name, location coordinates, question text).
* Your site URL, so RoxyAPI can attribute requests to your site for support.
* The plugin name and version, so RoxyAPI can spot compatibility problems with a particular release.
* Your server outbound IP address (incidentally captured by the receiving server, like any HTTP request).

No site visitor data is collected by the plugin when a visitor only views a page; their IP, user agent, and any browser-side data are not sent to RoxyAPI in the passive case. When a visitor submits a form-mode shortcode (their birth date, name, or question), the plugin sends only the fields they typed, after they tick the consent checkbox. See https://roxyapi.com/policy/privacy for what RoxyAPI does with the data once received.

= Can visitors fill in their own birth details? =

Yes. Drop a hero shortcode with no attributes (for example [roxy_natal_chart]) and the plugin renders an accessible form with a city search that fills in coordinates automatically. On submit the plugin validates the input, applies a per-IP rate limit and a consent checkbox, calls the API server side, and renders the result on the same page. The API key never reaches the browser.

= I am behind Cloudflare or a reverse proxy. Does the visitor rate limit still count each visitor? =

Not until you tell the plugin which forwarded header to believe. The only address a WordPress site can verify is the one the connection arrives from, which behind a proxy is the proxy itself, so by default every visitor shares one bucket. Forwarded headers are ignored because anyone can send them, and a limit that resets whenever a header changes is no limit at all.

If your proxy overwrites a header on every request, opt that header in from your theme functions file or a small site plugin:

`add_filter( 'roxyapi_trusted_proxy_headers', function () { return array( 'HTTP_CF_CONNECTING_IP' ); } );`

Register it with `10, 2` instead and the callback also receives the connecting address, so you can trust the header only while the request really is arriving from your own edge. On a custom setup, return the visitor address yourself with the `roxyapi_client_ip` filter instead.

= Can I sell readings or charts to my clients? =

Yes, and you do not need a separate integration. Place any RoxyAPI shortcode or block on a page, then gate that page with a membership or paywall plugin such as Paid Memberships Pro, Restrict Content, or WP-Members, or sell access as a WooCommerce product through a memberships add-on. Members and buyers who reach the page see the reading; everyone else sees your paywall. Because the chart renders exactly where you place the shortcode, you decide what stays free, like a teaser daily horoscope, and what is paid, like a full natal chart, a compatibility score, or a Vedic kundli. RoxyAPI keeps no birth data, so your client list and their details stay entirely yours.

= Is the API key safe? =

Yes. The plugin makes API calls server side in PHP. The key is never sent to the browser. You can also store the key in wp-config.php with define('ROXYAPI_KEY', '...') if you do not want it in the database. Stored keys are encrypted at rest via AES-256-CTR.

= Does this work with caching plugins? =

Yes. The plugin uses WordPress transients, which automatically use Redis or Memcached if you have a persistent object cache.

= Can I customize the styling? =

Yes. Readings pick up your theme font, and the RoxyAPI menu under Branding gives you four ready-made palettes plus seven colors you can set yourself: accent, page background, card background, text, secondary text, borders and warnings. Each takes a light value and a dark value, so a dark brand color stays readable for a visitor in dark mode. Charts follow your light or dark mode automatically and read the --roxy-* CSS custom properties, so you can also override any token in your theme stylesheet. Every output element has a .roxyapi-* class you can target.

= Can I hide part of a reading, such as the chart patterns? =

Yes, at two levels. The RoxyAPI menu under Display has Written readings, which removes the interpretation from every reading and leaves the charts, tables and values in place. Below it, Hide sections takes a comma-separated list of section names and removes those blocks outright, so entering `patterns` removes the chart patterns block wherever it appears on your site.

Click the Hide sections field to see every name your version supports, and separate several with commas. The names are internal and stay the same whatever language your site runs in, so the title printed above a block is not one of them. If you enter a name that matches nothing, saving tells you which one, rather than leaving you with a setting that appears to have saved and quietly does nothing.

Those two are separate on purpose. A chart pattern such as a T-square reports the figure, its element and modality, how tight it is and which planets form it. Those are measurements rather than prose, so turning off written readings leaves them in place.

Both can be overridden on a single placement, so one page does not have to follow the whole site. Add `hide_sections="patterns"` to a shortcode and only that reading loses the block, or `hide_sections="none"` to keep a block on one page that the site setting hides everywhere else. `hide_readings="1"` and `hide_readings="0"` work the same way. A block placed in the editor follows the site setting.

You can also target any section yourself from Appearance, Customize, Additional CSS, or from your child theme stylesheet. Every block of a reading is exposed as a CSS part:

`roxy-natal-chart::part(patterns) { display: none }`

`roxy-ephemeris-table::part(changes) { display: none }`

Common section names are readings, patterns, aspects, changes, chart, legend, table and details. A name means the same block everywhere it appears, so one rule covers every reading that has that block: `::part(aspects)` reaches the aspect grid on a natal chart and the aspect list on an aspects table alike. Open your browser inspector and read the part attribute off any block to find the name for it. The same selector restyles a section instead of hiding it, so you can give one block its own background or spacing without touching the rest.

= Which calculation engine powers RoxyAPI? =

RoxyAPI cross-checks its astronomy calculations against the NASA JPL Horizons ephemeris (a public NASA dataset; no affiliation with NASA or JPL). See https://roxyapi.com/methodology for the full breakdown.

= How do I share the zodiac sign across several horoscopes on one page? =

Add the Astrology Section wrapper block, set the sign in its Inspector, then drop Horoscope blocks inside. Each one picks the sign up from the wrapper, so you set it once. Other readings take their inputs from their own sidebar controls.

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

= 1.13.0 =
* New: two whole domains, Chinese astrology and feng shui, as 27 readings with matching Gutenberg blocks. BaZi four pillars, Day Master strength, luck pillars, BaZi compatibility and the annual forecast, the twelve zodiac animals with daily readings and compatibility, lunar date conversion, the Tong Shu almanac, the 24 solar terms and date selection for weddings and openings. On the feng shui side, Kua numbers, Eight Mansions directions, flying star natal, annual and monthly charts, the bagua sectors, the nine periods and the annual afflictions.
* New: readings that turn on a school rule report which one they used, so a chart can be reconciled against the one a practitioner already draws. The day boundary, the year boundary and the hour clock come back on every BaZi response.
* Improved: tested against WordPress 7.1.
* Improved: the plugin listing leads with the domains people ask for most, and names the two new ones.

= 1.12.0 =
* New: the daily Vedic reading renders as a full reading instead of a plain list of values. Panchang, the grahas of the day with their state, tara and chandrabala, the finance read and the running dasha, each in its own block.
* Improved: chart labels read in your site language across the whole reading library. Headings, column names and vocabulary were still drawing in English on a long list of readings whatever the reading language, among them panchang, dasha, biorhythm, numerology, crystal, dream, hora, kundli, dosha, angel number, yoga, synastry, transit and the strength tables.
* Improved: kundli and Western position tables mark a combust graha and a planetary war, and carry the nakshatra lord and essential dignity.
* Improved: aspect lines on every chart are weighted by how exact the contact is, so a close aspect no longer reads the same as a wide one.
* Fixed: Gochara reads from the natal Moon, and a chart with no ascendant falls back to a sign-fixed layout instead of coming out blank.
* Fixed: synastry names each sign sector, shows each planet house in its own chart, and translates the score label.

= 1.11.4 =
* New: four Western timing readings, each as a shortcode and a block. [roxy_get_monthly_tropical_aspects] for a month of aspects, [roxy_get_monthly_tropical_transits] for a month of sign ingresses, [roxy_get_monthly_declination_parallels] for a month of declination contacts, and [roxy_get_planetary_node_passages] for a year of ecliptic crossings.
* Fixed: a reading stays readable inside a section that has a background of its own. The month controls under the ephemeris, the reading card, the visitor form and the error and notice lines now carry their own background and text colour together, instead of taking one from the page and the other from your theme.
* Fixed: reading titles, badges, links and the submit button now meet the WCAG AA contrast minimum, in both light and dark.
* Fixed: the sign picker on [roxy_horoscope] now looks like every other RoxyAPI form. It was the one form carrying its own set of class names, so none of the shared field, button and spacing styles ever reached it.

= 1.11.3 =
* New: the RoxyAPI settings screens now read in your site language. Every tab, field label, help text, button and notice on them is translated, in all eight languages, as is the plugin description on your Plugins page. Until now the whole admin stayed English however your site was set, so a Spanish site was configured through an English screen. The domain names in the Shortcodes library, such as Human Design, stay as they are, the same way shortcode names do.
* Fixed: the notice a visitor reads when a reading cannot be loaded, and the status messages under the city box on a birth details form, now read in your site language too. Fourteen more lines were coming out English on fully translated sites.

Older entries are in changelog.txt.

== Upgrade Notice ==

= 1.13.0 =
Worth updating if you publish Chinese astrology or feng shui. Adds both domains, 27 readings with matching blocks, from BaZi four pillars and the Tong Shu almanac to Kua numbers and flying star charts.

= 1.12.0 =
Worth updating on any translated site. Chart labels and column headings now read in your site language across the whole reading library, and the daily Vedic reading renders as a full reading rather than a list of values.

= 1.11.4 =
Worth updating if any reading sits on a coloured or dark section. The reading card and the ephemeris month controls now bring their own background, so their text cannot end up dark on dark.

= 1.11.3 =
Worth updating on any site not run in English. The whole RoxyAPI settings area now reads in your site language, along with the notices a visitor sees when a reading cannot load and the messages under the city box.
