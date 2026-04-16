=== RoxyAPI: Astrology, Tarot, Numerology, Horoscope and More ===
Contributors: roxyapi
Tags: astrology, tarot, numerology, horoscope, zodiac
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Astrology, tarot, numerology, dreams, I Ching, biorhythm and more. Drop blocks and shortcodes onto any WordPress page. Powered by RoxyAPI.

== Description ==

RoxyAPI for WordPress lets you add daily horoscopes, tarot pulls, numerology readings, I Ching casts, and natal charts to any WordPress site. Calculations are verified against NASA JPL Horizons. One API key unlocks 10 spiritual data domains and 130+ endpoints.

**Features:**

* Daily, weekly, and monthly horoscope blocks and shortcodes for all 12 zodiac signs
* Single card and three card tarot pulls, plus Celtic Cross and custom spreads
* Pythagorean numerology with Life Path, Expression, Soul Urge, and Personality numbers
* I Ching hexagram casting with full interpretation
* Natal chart calculation with houses, aspects, and planet positions
* Dream symbol dictionary with 3,000+ entries
* Biorhythm, angel number, and crystal reference data
* Parent Astrology Section wrapper block that shares the zodiac sign across all child blocks via block context
* Server side caching with per endpoint TTL to keep your API quota low
* API key stays server side. Never exposed to the browser.

== Installation ==

1. In your WordPress admin, go to Plugins, Add New, search for "RoxyAPI", and click Install Now.
2. Activate the plugin.
3. Sign up for a RoxyAPI account at https://roxyapi.com and get your API key.
4. Go to Settings, RoxyAPI and paste your key.
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

Yes. Sign up at https://roxyapi.com. The Starter plan starts at $39 per month and includes 5,000 API requests across all 10 domains.

= What data is sent to RoxyAPI? =

When you render a block or shortcode, the plugin sends only the parameters you provide (zodiac sign, birth date, name, question text). No site data, no visitor data, no analytics. See https://roxyapi.com/policy/privacy for details.

= Is the API key safe? =

Yes. The plugin makes API calls server side in PHP. The key is never sent to the browser. You can also store the key in wp-config.php with define('ROXYAPI_KEY', '...') if you do not want it in the database. Stored keys are encrypted at rest via AES-256-CTR.

= Does this work with caching plugins? =

Yes. The plugin uses WordPress transients, which automatically use Redis or Memcached if you have a persistent object cache.

= Can I customize the styling? =

Yes. Every output element has a .roxyapi-* class. Override in your theme stylesheet. The plugin uses your theme color and spacing tokens by default.

= Which calculation engine powers RoxyAPI? =

RoxyAPI astronomy calculations are verified against NASA JPL Horizons. See https://roxyapi.com/methodology for the full breakdown.

= How do I share the zodiac sign across multiple blocks on one page? =

Add the Astrology Section wrapper block, set the sign in its Inspector, then drop child RoxyAPI blocks inside. Every child inherits the sign via block context.

= Is there a free plan or trial? =

RoxyAPI offers plans starting at $39/month for 5,000 requests. There is no free tier, but the interactive tool pages at roxyapi.com let you preview every endpoint before subscribing. All plans include a 10-day evaluation period.

= Does this work with Elementor, Divi, or other page builders? =

Yes. All RoxyAPI shortcodes work inside any page builder that supports WordPress shortcodes. Use the shortcode in a text or HTML module. The Gutenberg blocks work in the default WordPress editor.

== Screenshots ==

1. Plugin settings page where you paste your RoxyAPI key.
2. The Daily Horoscope block in the Gutenberg editor with sign picker.
3. Frontend rendering of a daily horoscope on a WordPress page.
4. Tarot card pull with three card spread.
5. I Ching hexagram cast with interpretation.
6. Astrology Section wrapper block sharing context across children.

== Known Limitations ==

Version 1.0.0 ships the Horoscope hero shortcode and block with the full two mode experience (static and visitor form). The other hero shortcodes (Natal Chart, Tarot, Numerology, Life Path, I Ching, Dream, Biorhythm, Angel Number, Crystal, Compatibility) are registered and render a friendly placeholder. Full templates land in v1.1.

All hero blocks appear in the inserter under the RoxyAPI category so your pages do not break when v1.1 ships. The auto generated long tail shortcodes for the 120+ other RoxyAPI endpoints also ship in v1.1 via the npm run generate pipeline.

The Block Bindings API source roxyapi/daily-text is registered but returns empty strings until the generated PHP client layer lands in v1.1.

== Changelog ==

= 1.0.0 =
* Initial release.
* Horoscope hero block and shortcode with daily, weekly, monthly, love, career, and Chinese variations.
* Auto detecting form mode. Drop the shortcode with no attributes and visitors pick their own values.
* Settings page with API key field, wp config constant override, and test connection button.
* Encryption at rest via AES 256 CTR. Ported from Google Site Kit.
* Server side caching with per endpoint TTL.
* Rate limiting per IP and shortcode to protect the owner API quota.
* Onboarding admin notice via wp_admin_notice (WP 6.4+).
* Custom RoxyAPI block category with ten hero block slots, nine reserved for v1.1.
* Astrology Section wrapper block that shares a default zodiac sign and birth date with every child block inside it via block context.
* Block Bindings API source roxyapi/daily-text (wired in v1.1).
* X-SDK-Client and User-Agent headers matching the TypeScript and Python SDK pattern so RoxyAPI can identify plugin traffic.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Privacy ==

This plugin connects to RoxyAPI (https://roxyapi.com) to fetch astrology, tarot, numerology, dreams, I Ching, biorhythm, crystals, and angel numbers data. To use it you need a RoxyAPI account and API key.

When the plugin is used, the following data is sent to RoxyAPI: parameters you supply via blocks or shortcodes (zodiac sign, birth date, name, location coordinates, question text). No visitor data is collected by the plugin itself.

Terms of Service: https://roxyapi.com/terms
Privacy Policy: https://roxyapi.com/policy/privacy
