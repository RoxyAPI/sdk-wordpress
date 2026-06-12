=== RoxyAPI — Astrology, Vedic, Tarot, Numerology ===
Contributors: roxyapi
Tags: astrology, horoscope, tarot, numerology, vedic
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Astrology, daily horoscopes, tarot, numerology, and Vedic birth charts. Blocks and shortcodes for WordPress. Powered by RoxyAPI.

== Description ==

Add astrology, daily horoscopes, tarot card pulls, numerology readings, and Vedic birth charts to any WordPress site. One plugin covers ten spiritual data domains: Western and Vedic astrology, daily / weekly / monthly horoscopes, tarot, numerology, I Ching, dream interpretation, biorhythm, angel numbers, and crystals. Astronomy is cross-checked against the NASA JPL Horizons ephemeris. One API key unlocks 130+ endpoints.

**About the service this plugin connects to**

This plugin is a thin WordPress interface to RoxyAPI, a third-party paid service operated at https://roxyapi.com. The astrology, tarot, numerology, and other calculations all run on RoxyAPI servers. **A small free daily allowance lets the plugin display readings without an account; a RoxyAPI API key is required for production use and removes the daily limit.** Pricing and plan tiers are listed at https://roxyapi.com/pricing in your local currency. Terms of Service: https://roxyapi.com/policy/terms. Privacy Policy: https://roxyapi.com/policy/privacy.

The plugin itself is GPLv2 or later and the source is available at https://github.com/RoxyAPI/sdk-wordpress.

**Features:**

* Daily, weekly, and monthly horoscope blocks and shortcodes for all 12 zodiac signs
* Single card and three card tarot pulls, plus Celtic Cross and custom spreads
* Pythagorean numerology with Life Path, Expression, Soul Urge, and Personality numbers
* I Ching hexagram casting with full interpretation
* Natal chart calculation with houses, aspects, and planet positions
* Dream symbol dictionary with 2,000+ entries
* Biorhythm, angel number, and crystal reference data
* Parent Astrology Section wrapper block that shares the zodiac sign across all child blocks via block context
* Server side caching with per endpoint TTL to keep your API quota low
* API key stays server side. Never exposed to the browser.

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

Not to get started. A limited number of free readings per day are allowed right after install, with no account, so you can try the plugin. The allowance is counted per site and resets each day. For production use, add an API key from a RoxyAPI plan: one key covers every reading and removes the daily limit. Pick a plan at https://roxyapi.com/pricing. Pricing is shown in your local currency on the pricing page.

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
* Your server's outbound IP address (incidentally captured by the receiving server, like any HTTP request).

No site visitor data is collected by the plugin when a visitor only views a page; their IP, user agent, and any browser-side data are not sent to RoxyAPI in the passive case. When a visitor submits a form-mode shortcode (their birth date, name, or question), the plugin sends only the fields they typed, after they tick the consent checkbox. See https://roxyapi.com/policy/privacy for what RoxyAPI does with the data once received.

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

= Can I try the API before subscribing? =

Yes. The Playground at https://roxyapi.com/api-reference has a test key pre-filled and lets you call every endpoint live. No signup needed. When you are ready to ship, see https://roxyapi.com/pricing for current plans.

= Does this work with Elementor, Divi, or other page builders? =

Yes. All RoxyAPI shortcodes work inside any page builder that supports WordPress shortcodes. Use the shortcode in a text or HTML module. The Gutenberg blocks work in the default WordPress editor.

== Screenshots ==

1. Western natal chart wheel with planet glyphs, aspect lines, and houses.
2. Vedic kundli rendered as an interactive SVG chart. North, South, and East Indian styles.
3. KP chart with planets, nakshatra, star lord, and sub lord.
4. Panchang muhurta table: tithi, nakshatra, yoga, and auspicious times.
5. Shortcodes Library. Browse every shortcode by domain. Search, filter, copy.
6. Start free in seconds, no key required. Browse shortcodes, or add a key for production.

== Changelog ==

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

= 1.0.0 =
Initial release.

== Privacy ==

**This plugin connects to a third-party paid service (RoxyAPI).**

* Service: RoxyAPI (https://roxyapi.com)
* Service operator: RoxyAPI
* Terms of Service: https://roxyapi.com/policy/terms
* Privacy Policy: https://roxyapi.com/policy/privacy
* Pricing: https://roxyapi.com/pricing

**When does the plugin contact RoxyAPI?** Only after you save an API key on the settings page and either click Test Connection or render a RoxyAPI block or shortcode on a page. Saving your API key is the explicit consent for these calls. The plugin does NOT contact RoxyAPI on activation, on update, in the background, or on admin pages that do not display a reading.

**What data is sent?** The reading parameters you supply via the block or shortcode (zodiac sign, birth date, name, location coordinates, question text), your site URL (in an `X-Site-URL` header), and a plugin identifier (in an `X-SDK-Client` header). Your server's outbound IP is incidentally captured by RoxyAPI, like any HTTP request. **No site visitor data is collected by the plugin itself.**

**Where is the API key stored?** Either as the `ROXYAPI_KEY` constant in your `wp-config.php` (recommended) or AES-256-CTR encrypted in the `wp_options` table under `roxyapi_settings`. The plain key is never sent to the browser at any time.

**What does RoxyAPI do with the data?** See https://roxyapi.com/policy/privacy.
