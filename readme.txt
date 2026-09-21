=== RoxyAPI: Astrology, Vedic, Forecast, Human Design, Numerology ===
Contributors: roxyapi
Tags: astrology, horoscope, tarot, vedic, human design
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.16.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Birth charts, horoscopes, kundli, Human Design, BaZi and Feng Shui as blocks and shortcodes. 18+ domains on one key. Free to start.

== Description ==

RoxyAPI adds Astrology, Vedic, Forecast, Human Design, Chinese Astrology and Feng Shui readings to any WordPress post, page or widget, as Gutenberg blocks and shortcodes. Drop one on a page and it renders a real reading, server side, the moment you activate the plugin. No account, no setup, no calculation code to write. Start free, then add an API key when you go live.

**One API key. 18+ domains. 258+ endpoints.** Most astrology plugins cover one system. RoxyAPI covers the whole stack, so you build the entire experience without stitching services together:

* **Western astrology:** natal and birth charts, daily, weekly, monthly, and yearly horoscopes, synastry with a house overlay, compatibility, transits, and moon phases
* **Vedic astrology:** kundli, KP charts, panchang, Vimshottari dasha, divisional charts, nakshatras, yogas, gochara transits, and the Bhava Bala and Bhav Chalit house readings
* **Forecast:** cross-domain timelines, transit forecasts, solar returns, and significant dates
* **Human Design:** full bodygraph with type, authority, profile, centers, channels, gates, variables, and penta
* **Chinese astrology:** the BaZi Four Pillars chart, Day Master strength, ten year luck pillars, the Chinese zodiac sign with daily readings and compatibility, the 24 solar terms, and the Chinese almanac day (Tong Shu)
* **Feng shui:** the Kua number with Eight Mansions directions, the nine palace Flying Star chart, bagua sectors, and annual afflictions
* **Mesoamerican astrology:** the Tzolkin day sign, the Mayan chart with Haab and Long Count, a Long Count converter, daily and monthly readings, nawal compatibility, and the Aztec tonalpohualli
* **Vastu:** entrance pada, the Vastu Purusha Mandala, plot analysis, Ayadi, room compliance, and griha pravesh dates
* **Numerology:** Life Path, Expression, Soul Urge, and Personality numbers
* **Kabbalah:** gematria with every spelling shown, name and birth profiles, the 72 names, the Tree of Life, and the Hebrew letters
* **Tarot:** single card, three card, Celtic Cross, and custom spreads
* **Plus:** biorhythm, Ayurveda, I Ching, crystals, dream interpretation, and angel numbers

Every chart is calculated by Roxy Ephemeris and verified against NASA JPL Horizons, with readings in your site language. One key, one plan, no per-domain fees.

**Frequently asked, answered up front**

* **Do I need an account to try it?** No. Blocks and shortcodes render readings straight after activation on a free daily allowance.
* **Do I need to know astrology to use it?** No. Every reading arrives calculated and formatted, so you place a block and pick your options.
* **Is the birth chart accurate?** Charts come from Roxy Ephemeris and are verified against NASA JPL Horizons.
* **Can visitors enter their own birth details?** Yes. Leave the attributes off a hero shortcode and it renders a form; the submission is processed server side.
* **Does it work with my theme?** Yes. Charts are SVG and follow your light or dark theme through CSS custom properties.
* **Where does my API key live?** On your server only. It is never sent to the browser.

**Free to start. Add a key when you go live.**

Every reading works the moment you activate the plugin, with no account. A free daily allowance, shared across your site, covers casual traffic so you can build and preview complete pages at no cost. When you are ready for production, add a RoxyAPI API key and the daily limit is removed. There is no separate paid plugin and no locked reading types: the same blocks and shortcodes cover every domain whether or not a key is set. A key simply lifts the daily cap, covers production traffic, and keeps all 18+ domains live under one key. Pick a plan at https://roxyapi.com/pricing.

**Also built in:**

* Gutenberg blocks and shortcodes for every reading, plus a visitor form mode for interactive inputs like birth details
* Pythagorean numerology, full 78 card tarot, and a 2,000+ entry dream symbol dictionary
* Interactive SVG charts and cards that follow your light or dark theme automatically
* Readings in English, German, Hindi, Spanish, Turkish, Portuguese, French, and Russian, following the site language or one setting
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
`[roxy_horoscope sign="aries" period="yearly"]`
`[roxy_horoscope sign="aries" period="monthly" date="2026-10-01"]`
`[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060" tz="America/New_York"]`
`[roxy_kundli birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_panchang date="2026-04-28" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_mangal_dosha birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_kp_chart birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]`
`[roxy_moon_phase]`
`[roxy_bodygraph birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060" tz="America/New_York"]`
`[roxy_tarot_card spread="three" question="What should I focus on this week"]`
`[roxy_tarot_yes_no question="Should I take the new job"]`
`[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]`
`[roxy_life_path birth_date="1990-05-15"]`
`[roxy_biorhythm birth_date="1990-05-15" target_date="today"]`
`[roxy_angel_number number="1111"]`
`[roxy_crystals_by_zodiac sign="aries"]`

Four heroes take no attributes: the three two-person compatibility readings render a visitor form for both people, and the forecast timeline renders a visitor form for the birth details and an optional date window:

`[roxy_synastry]`
`[roxy_gun_milan]`
`[roxy_compatibility]`
`[roxy_forecast]`

Leave the attributes off any hero shortcode to render a form instead. For example `[roxy_horoscope]` shows a zodiac sign picker and `[roxy_natal_chart]` shows a birth date, time, and city picker.

**Long-tail shortcodes**

A matching shortcode exists for every endpoint in the spec. A sample across the domains:

* Western astrology: `[roxy_calculate_aspects date="1990-07-15" time="14:30:00" timezone="UTC"]`, `[roxy_get_weekly_horoscope sign="aries"]` and `[roxy_get_yearly_horoscope sign="aries"]`
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

One key covers 18 domains. Western astrology: natal chart, daily / weekly / monthly / yearly horoscopes, synastry with a two way house overlay, compatibility, transits, aspect patterns, and moon phases. Vedic astrology: kundli, KP chart, panchang, Vimshottari dasha, divisional charts, nakshatras, doshas (Manglik, Kaal Sarpa, Sade Sati), and classical yogas. Numerology: Life Path, Expression, Soul Urge, Personality, personal year, and compatibility. Tarot: single card, three card, Celtic Cross, and the full 78 card catalog. Human Design: bodygraph, type, authority, profile, centers, channels, and gates. Forecasts: timelines, transit forecasts, solar returns, and significant dates. Chinese astrology: the BaZi Four Pillars chart, Day Master strength, ten year luck pillars, the Chinese zodiac sign with daily readings and compatibility, the 24 solar terms, and the Chinese almanac day (Tong Shu) with its day officer. Feng shui: the Kua number and Eight Mansions directions, the nine palace Flying Star chart for a building or a year, bagua sectors, and annual afflictions. Mesoamerican astrology: the Tzolkin day sign, the Mayan chart, a Long Count converter, daily and monthly readings, nawal compatibility, and the Aztec tonalpohualli. Vastu: entrance pada, the Vastu Purusha Mandala, plot analysis, Ayadi, room compliance, and griha pravesh dates. Kabbalah: gematria, name and birth profiles, the 72 names, the Tree of Life, and the Hebrew letters. Ayurveda: constitution from the birth chart, dinacharya, ritucharya, a daily reading, and the dosha, rasa, and guna catalogues. Plus biorhythm, I Ching hexagrams, crystal reference data, dream symbols, and angel numbers. Every reading is available as a shortcode. More than 180 of them also ship a matching Gutenberg block, each with sidebar controls for its inputs (a date picker for dates, a dropdown for fixed choices, text and number fields for the rest) and a live preview in the editor. The headline readings (horoscope, natal chart, tarot, numerology, biorhythm, angel number) are built the same way, and the horoscope block adds a variation picker. Interactive multi input readings such as two chart compatibility and nested birth forms stay shortcode and visitor form mode, because the block editor cannot collect their nested input. Browse the full list inside WordPress under RoxyAPI, Shortcodes.

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

= 1.17.0 =
* New: [roxy_forecast], a featured forecast timeline. Visitors enter their birth details and an optional date window, and the reading merges transits, sign ingresses, retrograde stations, eclipses, moon phases, Vimshottari dasha changes and biorhythm critical days into one timeline of up to 90 days.
* New: [roxy_bodygraph], a featured Human Design reading. Pass birth_date, birth_time, lat, lon and tz for a fixed bodygraph, or leave them off for a visitor form with city search. Type, strategy, authority, profile, centers, channels and gates on one chart, with the Design moment printed under the title.
* New: nine more featured readings as Gutenberg blocks: Vedic Kundli, Panchang, Mangal Dosha, KP Chart, Moon Phase, Bodygraph, Tarot Yes or No, Life Path and Crystals by Zodiac, each with sidebar inputs and a live preview in the editor.
* New: two Vedic matching readings. [roxy_calculate_dashakoot] scores the ten porutham South Indian match and [roxy_calculate_papasamyam] compares malefic affliction between two charts. Both render a two person visitor form.
* Improved: every wheel, map and compass spaces its labels by their measured width, so long sign and planet names no longer overlap on a phone screen.
* Improved: the bodygraph captions its side centers outside the triangles, tables and charts share one degree notation, and the biorhythm chart draws its bars above and below the axis.
* Improved: the KP planets table prints the pada beside each node, and a reading the dedicated card cannot draw falls back to the generic table instead of an empty card.
* Improved: three chart labels corrected on German, French and Portuguese sites.
* Improved: a sample copied from the Shortcodes library no longer carries a language attribute, so a pasted reading follows the site language. Featured readings in the library keep their catalogue order instead of sorting by name, and the Connect tab fits a phone screen.

= 1.16.0 =
* New: a KP daily finance reading, [roxy_get_kp_daily_finance], with a Daily Finance Score block. Four Krishnamurti Paddhati layers are scored against the money houses and weighed into one number for the day, every row printed, and the gain houses, loss houses and layer weights can be overridden. It draws as a card of its own: the band and score, the gain and loss significators with the evidence by house, the four layers as tables with their weights, and the best and worst Moon windows of the day, with every heading in your site language.
* New: the gematria, name profile and name compatibility readings take a second letter map. Add transliteration="letter-map-modern" for the modern Israeli transcription, which writes every Latin letter, or leave it off for the Hermetic map.
* New: the finance section of the Vedic daily reading carries a combined score. One word for the day, the score beside it, and the three readings behind it with a score each, so a client can see what the verdict rests on.
* New: the current dasha reading takes a datetime, so you can prepare a reading for a day ahead or read the running periods at a past moment. Add datetime="2026-10-01T09:00:00" to the shortcode, or fill the field in the block.
* Fixed: the date attribute on [roxy_horoscope] now reaches the weekly, monthly and yearly periods, so period="monthly" date="2026-10-01" publishes the October reading. It was silently ignored on every period but daily. The current week and month also turn over on your site clock rather than at midnight UTC, and one reading is fetched per week or month however many days a page is viewed on.
* Fixed: the sign picker under [roxy_horoscope period="monthly"] answers with the monthly reading. It answered with the daily whatever period was placed.
* Fixed: a list attribute, such as planets, aspect_types, ciphers, gain_houses or loss_houses, is read as a comma list, and an object attribute such as weights as JSON. Each was posted as typed and refused by the API.
* Improved: the I Ching daily cast now draws as a hexagram card, with any changing lines named, instead of a plain list of values.
* Improved: the choghadiya grid and the hora table head their daylight column Daytime instead of Day, which reads correctly beside the night periods. Translated sites get that heading and the combined score in the site language.
* Improved: a long reading keeps its paragraphs in the version search engines and readers without JavaScript receive. A monthly horoscope column ran as one block there.

Older entries are in changelog.txt: https://github.com/RoxyAPI/sdk-wordpress/blob/main/changelog.txt

== Upgrade Notice ==

= 1.17.0 =
Worth updating if you build pages in the block editor: nine more featured readings become blocks with sidebar inputs, a forecast timeline and a Human Design bodygraph join the library with visitor forms, and two Vedic matching readings arrive.

= 1.16.0 =
Worth updating if you publish horoscope pages by date, Vedic finance readings or Kabbalah names: the horoscope date attribute now works on every period, a KP daily finance reading joins the library, and gematria takes the modern letter map.

