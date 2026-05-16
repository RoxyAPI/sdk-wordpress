=== RoxyAPI — Astrology, Vedic, Tarot, Numerology ===
Contributors: roxyapi
Tags: astrology, horoscope, tarot, numerology, vedic
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Astrology, daily horoscopes, tarot, numerology, and Vedic birth charts. Blocks and shortcodes for WordPress. Powered by RoxyAPI.

== Description ==

Add astrology, daily horoscopes, tarot card pulls, numerology readings, and Vedic birth charts to any WordPress site. One plugin covers ten spiritual data domains: Western and Vedic astrology, daily / weekly / monthly horoscopes, tarot, numerology, I Ching, dream interpretation, biorhythm, angel numbers, and crystals. Astronomy is cross-checked against the NASA JPL Horizons ephemeris. One API key unlocks 130+ endpoints.

**About the service this plugin connects to**

This plugin is a thin WordPress interface to RoxyAPI, a third-party paid service operated at https://roxyapi.com. The astrology, tarot, numerology, and other calculations all run on RoxyAPI servers. **A RoxyAPI account and API key are required for the plugin to display any data.** Pricing and plan tiers are listed at https://roxyapi.com/pricing in your local currency. Terms of Service: https://roxyapi.com/policy/terms. Privacy Policy: https://roxyapi.com/policy/privacy.

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

Yes. RoxyAPI is a paid third-party service. The plugin cannot display any astrology, tarot, numerology, or other readings without a valid API key from a RoxyAPI plan. Pick a plan at https://roxyapi.com/pricing. Pricing is shown in your local currency on the pricing page. One key covers every reading.

= When does the plugin contact the RoxyAPI service? =

The plugin contacts roxyapi.com only when you take a clear action that requires it:

1. You click the Test Connection button on the settings page.
2. A page on your site that contains a RoxyAPI block or shortcode is rendered (cached for one hour by default to keep your API quota low).

The plugin never contacts RoxyAPI on plugin activation, on plugin update, on any admin page that does not display a reading, or in the background. Saving your API key for the first time is the explicit consent for the render-time calls described above.

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

Yes. Every output element has a .roxyapi-* class. Override in your theme stylesheet. The plugin uses your theme color and spacing tokens by default.

= Which calculation engine powers RoxyAPI? =

RoxyAPI cross-checks its astronomy calculations against the NASA JPL Horizons ephemeris (a public NASA dataset; no affiliation with NASA or JPL). See https://roxyapi.com/methodology for the full breakdown.

= How do I share the zodiac sign across multiple blocks on one page? =

Add the Astrology Section wrapper block, set the sign in its Inspector, then drop child RoxyAPI blocks inside. Every child inherits the sign via block context.

= Can I try the API before subscribing? =

Yes. The Playground at https://roxyapi.com/api-reference has a test key pre-filled and lets you call every endpoint live. No signup needed. When you are ready to ship, see https://roxyapi.com/pricing for current plans.

= Does this work with Elementor, Divi, or other page builders? =

Yes. All RoxyAPI shortcodes work inside any page builder that supports WordPress shortcodes. Use the shortcode in a text or HTML module. The Gutenberg blocks work in the default WordPress editor.

== Screenshots ==

1. Three step onboarding. Get your API key, paste it in, drop a shortcode on a page.
2. Connected admin. Tabbed settings (Connect, Branding, Display, Privacy, Advanced) plus a quick start grid of copy paste shortcodes.
3. Shortcodes Library. Browse all 134 shortcodes by domain. Search, filter, copy.
4. Frontend reading card. Numerology chart with title, narrative description, career and relationships fields, strengths and challenges sections.
5. Visitor form mode. Vedic birth chart input with city autocomplete (no JS required for fallback). Server side submission, GDPR consent gate.
6. Gutenberg editor. RoxyAPI block category with every hero block and the Astrology Section context wrapper.

== Known Limitations ==

Version 1.0.0 ships all 131 RoxyAPI endpoints. 17 hero shortcodes covering the highest demand readings across Western astrology, Vedic astrology (kundli, panchang, mangal dosha, KP chart), tarot (daily, three card, Celtic Cross, yes or no), numerology (life path, full chart), biorhythm, angel numbers, crystals by zodiac, current moon phase, plus two-chart compatibility (synastry, gun milan, Western compatibility). 117 auto generated shortcodes for the long tail.

I Ching, dream symbol, and single crystal lookups are reachable via the auto-generated long-tail shortcodes (browse the full catalog at RoxyAPI then Shortcodes in the WordPress admin sidebar).

Hero block editor sidebars are intentionally minimal in this release. Live previews and richer inspector controls land in a future release.

== Changelog ==

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
