# roxyapi WordPress plugin: Agent Guide

WordPress plugin for [RoxyAPI](https://roxyapi.com). Drop Western and Vedic astrology, forecasts, human design, Chinese astrology, feng shui, numerology, tarot, biorhythm, I Ching, crystals, dream symbols, and angel number readings onto any WordPress page with shortcodes or Gutenberg blocks. One API key, 14+ spiritual data domains, 209+ endpoints.

The upstream OpenAPI spec is the source of truth: `https://roxyapi.com/api/v2/openapi.json`.

## Install

```bash
# WordPress admin
Plugins > Add New > search "Astrology Horoscope Tarot Numerology by Roxy" > Install > Activate
Roxy menu (admin sidebar) > paste API key > Save

# wp-cli
wp plugin install roxyapi --activate
wp option update roxyapi_settings '{"api_key":"..."}' --format=json

# Production: store the key in wp-config.php instead of the database
define( 'ROXYAPI_KEY', getenv( 'ROXYAPI_KEY' ) );
```

## Use a shortcode

Browse the full library at Roxy > Shortcodes in the WordPress admin sidebar. 17 hand-curated hero shortcodes cover the highest-demand readings; every other endpoint is reachable via auto-generated long-tail shortcodes.

Every hero shortcode has two modes, auto detected.

### Static mode: site owner picks the values

Pass all required attributes and the shortcode renders a fixed reading that never changes:

```
[roxy_horoscope sign="aries"]
[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060" tz="America/New_York"]
[roxy_kundli birth_date="1988-01-12" birth_time="09:45" lat="19.0760" lon="72.8777" tz="Asia/Kolkata"]
[roxy_panchang date="2026-10-15" lat="12.9716" lon="77.5946" tz="Asia/Kolkata"]
[roxy_mangal_dosha birth_date="1992-09-08" birth_time="22:30" lat="13.0827" lon="80.2707" tz="Asia/Kolkata"]
[roxy_kp_chart birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]
[roxy_moon_phase]
[roxy_tarot_card spread="three" question="What should I focus on this week"]
[roxy_tarot_yes_no question="Should I take the new job"]
[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]
[roxy_life_path birth_date="1990-05-15"]
[roxy_biorhythm birth_date="1990-05-15" target_date="today"]
[roxy_angel_number number="1111"]
[roxy_crystals_by_zodiac sign="aries"]
```

Two-chart heroes (`[roxy_synastry]`, `[roxy_gun_milan]`, `[roxy_compatibility]`) ship as form-mode only because static mode would need ten or more inline attributes per chart. Drop the shortcode on a page and visitors fill in both birth charts. I Ching, dream symbol, and single-crystal lookups remain available via auto-generated long-tail shortcodes (browse the catalog at Roxy then Shortcodes in the WordPress admin sidebar).

How readings render: chart-shaped endpoints (natal, kundli, KP, panchang, dasha, and more) render as interactive SVG components from `@roxyapi/ui`, loaded from a bundle shipped inside the plugin and themed by the `--roxy-*` CSS custom properties. The remaining content reads render as a server-side card. The plugin fetches server side and embeds the response, so the API key never reaches the browser either way.

Two display controls, and both work the same way: a setting on the Display tab covers the whole site, and an attribute on one shortcode overrides it for that placement. Both default to `inherit`, which means follow the setting. Both apply to the no-JavaScript render as well as the interactive one, so a visitor and a crawler see the same page. A block placed in the editor follows the site setting.

Chart without the written report: `hide_readings="1"` hides the interpretation on that placement, `hide_readings="0"` keeps it even when the site setting is on. Charts, tables, and values are unaffected.

Removing a whole block is the other control, because `hide_readings` deliberately keeps measurements. A chart pattern reports its figure, element, modality, tightness and member planets, so it survives `hide_readings` by design. `hide_sections` takes a comma-separated list of section names and removes those blocks outright: `hide_sections="patterns"` drops the chart patterns block on that placement, `hide_sections="patterns, legend"` drops two, and `hide_sections="none"` keeps every block on one page even when the site setting hides one. Section names are the `part` names the components publish, so `roxy-natal-chart::part(patterns) { display: none }` in a theme stylesheet targets the same block, and the same selector restyles it instead of hiding it.

Browsing months: the two monthly ephemeris readings render Previous and Next links plus a month and year picker. The selected month travels in the page address, so the view can be linked and shared, and it resolves server side with no JavaScript and no key in the browser. Leave the year and month off the shortcode and it opens on the current month.

### Form mode: visitors pick their own values

Leave the required attributes off and the shortcode renders an HTML form. Visitors submit it, the plugin validates the nonce, rate limits per IP, calls the API server side, and renders the result above the form on the next page load:

```
[roxy_horoscope]            -> zodiac sign picker
[roxy_natal_chart]          -> birth date, time, and city picker
[roxy_kundli]               -> Vedic birth date, time, and city picker
[roxy_panchang]             -> date and city picker
[roxy_mangal_dosha]         -> Vedic birth chart input
[roxy_kp_chart]             -> Vedic birth chart input
[roxy_synastry]             -> two birth charts (Western synastry)
[roxy_gun_milan]            -> two birth charts (Vedic Ashtakoota)
[roxy_compatibility]        -> two birth charts (Western compatibility)
[roxy_tarot_card]           -> question input with spread selector
[roxy_tarot_yes_no]         -> question text input
[roxy_numerology]           -> name and birth date form
[roxy_life_path]            -> birth date picker
[roxy_biorhythm]            -> birth date input
[roxy_angel_number]         -> number input
[roxy_crystals_by_zodiac]   -> zodiac sign picker
```

Form submissions post back to the same page. The plugin uses `wp_verify_nonce` for CSRF, gates submission on an explicit GDPR Article 9 consent checkbox, runs `RoxyAPI\Support\RateLimit` for per IP throttling (the ceiling is `RateLimit::DEFAULT_LIMIT`, and each scope gets its own bucket), and calls `wp_remote_request` for the API hit. All output is escaped via `esc_html` or `wp_kses_post`. The API key never reaches the browser in either mode.

Forms that need a city (natal chart, synastry, composite) render an ARIA 1.2 combobox autocomplete that proxies queries through `/wp-json/roxyapi/v1/geocode`. The geocoder route is rate-limited per IP and caches results for 24 hours.

Override the form or result template from your theme by copying the matching file from `templates/` into `your-theme/roxyapi/`.

## Use a Gutenberg block

In the editor, open the inserter and search for "Horoscope", "Tarot", "Numerology", "I Ching", or "Natal Chart". Each block opens a variation picker (Daily, Weekly, Monthly, Celtic Cross, Three Card, Life Path, Expression, Soul Urge, and so on).

Every other reading is a block too. Insert any long-tail reading and its inputs (birth date, name, zodiac sign, and so on) show as sidebar controls generated from the API spec: a date picker for dates, a dropdown for fixed choices, text and number fields for the rest, with a live preview that updates as you type.

Drop one Astrology Section wrapper block on the page, set the zodiac sign in its Inspector, and every child RoxyAPI block inside inherits the sign via block context. No per-block configuration.

## Domains

| Block / shortcode prefix | What it covers                                                                    |
| ------------------------ | --------------------------------------------------------------------------------- |
| Horoscope                | Western horoscopes: daily, weekly, monthly                                        |
| Natal Chart              | Western birth chart: planets, houses, aspects, transits                           |
| Synastry, Compatibility  | Two-chart Western compatibility: synastry aspects and lighter compatibility score |
| Moon Phase               | Current moon phase, illumination, sign, meaning                                   |
| Kundli, Panchang         | Vedic birth chart, daily Vedic almanac (tithi, nakshatra, rahu kaal, abhijit)     |
| Mangal Dosha, KP Chart   | Vedic dosha detection, KP system with 249 sub-lord analysis                       |
| Gun Milan                | Vedic Ashtakoota matrimonial compatibility (36-point)                             |
| Tarot                    | Rider Waite Smith deck: single card, three card, Celtic Cross, custom, yes / no   |
| Numerology, Life Path    | Life path, expression, soul urge, personal year, full chart                       |
| Biorhythm                | Physical, emotional, intellectual, intuitive cycles                               |
| Angel Number             | Number meanings and pattern analysis                                              |
| Crystals by Zodiac       | Healing crystals filtered by zodiac sign                                          |

For everything else (human design bodygraph, forecast timelines, BaZi four pillars and the Chinese almanac, feng shui Kua numbers and flying star charts, KP horary, dasha, navamsa, dream symbols, single-crystal lookup, I Ching casts, full angel number catalog, etc.) use the auto-generated long-tail shortcodes browsable at Roxy > Shortcodes.

## How the plugin reads your API key

Resolution order:

1. `ROXYAPI_KEY` constant in `wp-config.php` (recommended for production)
2. The encrypted value in the `roxyapi_settings` option, decrypted via AES-256-CTR
3. Empty string. With no key the plugin still renders, on a free daily allowance. Once that is used up the reading shows a friendly "temporarily unavailable" message and an admin notice prompts for a paid key. The dashboard widget also surfaces the unconnected state.

The encryption key derives from `ROXYAPI_ENCRYPTION_KEY` constant or `LOGGED_IN_KEY` fallback. Same for the salt. If neither is available, the encryption helper returns `false` and the user gets a clear "could not encrypt" error instead of having their key persisted under a hardcoded secret. Document for production:

```php
define( 'ROXYAPI_KEY',             getenv( 'ROXYAPI_KEY' ) );
define( 'ROXYAPI_ENCRYPTION_KEY',  getenv( 'ROXYAPI_ENCRYPTION_KEY' ) );
define( 'ROXYAPI_ENCRYPTION_SALT', getenv( 'ROXYAPI_ENCRYPTION_SALT' ) );
```

## Settings tabs

The Roxy admin page is split into five tabs:

| Tab      | What it covers                                                                                                                                                                                                                                                                                                                                                                                                         |
| -------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Connect  | API key field (constant override), Test Connection button.                                                                                                                                                                                                                                                                                                                                                             |
| Branding | Four ready-made palettes or seven colors set by hand (accent, page background, card background, text, secondary text, borders, warnings), each with a light and a dark value, plus a light, dark, or auto theme. All of it drives the `--roxy-*` tokens the chart components read. Also the reading language sent on every API call (defaults to the site locale).                                                     |
| Display  | The two display controls, both site-wide here and both overridable per placement: Written readings toggle (off by default; hides the written text and leaves the charts, tables, and values) and Hide sections list (comma-separated section names, each block removed wherever it appears). Both reach the no-JavaScript render too. Also the opt in source line, an optional disclaimer line, and visitor form copy. |
| Privacy  | Consent label shown next to the form opt in checkbox. Privacy policy content is registered for the WP Privacy tool.                                                                                                                                                                                                                                                                                                    |
| Advanced | Cache preset (fresh divides TTLs by 4, balanced uses spec defaults, quota saver multiplies TTLs by 24), connection status panel.                                                                                                                                                                                                                                                                                       |

The settings registry is filterable. Sites that need an extra option can hook `roxyapi_settings_schema` and add a field; the Settings API page picks it up automatically.

## Caching

Every successful response is cached in a WordPress transient with a per endpoint TTL:

| Endpoint family                                                                      | TTL                                  |
| ------------------------------------------------------------------------------------ | ------------------------------------ |
| Daily horoscope, forecast digest                                                     | 1 hour                               |
| Numerology, natal chart, houses, composite, navamsa, dreams, crystals, angel numbers | 1 month (deterministic from input)   |
| Biorhythm, solar and lunar returns, significant dates, timelines, forecast transits  | 1 day                                |
| Tarot, I Ching                                                                       | not cached (randomness is the value) |

Cached responses do not consume RoxyAPI quota. Object cache backends (Redis, Memcached) are picked up automatically.

## Common tasks

| Task                                             | How                                                                                                   |
| ------------------------------------------------ | ----------------------------------------------------------------------------------------------------- |
| Test the API key                                 | Roxy menu (admin sidebar) > Connect > Test Connection                                                 |
| Override the key without touching the database   | `define( 'ROXYAPI_KEY', '...' );` in `wp-config.php`                                                  |
| Clear all cached responses                       | `wp transient delete --all` (or call `\RoxyAPI\Api\Cache::flush_all()` from a one-off)                |
| Hide the written interpretation                  | Display tab, or `hide_readings="1"` on one shortcode                                                  |
| Remove a block such as chart patterns            | Display tab, or `hide_sections="patterns"` on one shortcode (`"none"` keeps every block there)        |
| Add a horoscope to any paragraph                 | Use Block Bindings: bind a `core/paragraph` to source `roxyapi/daily-text` with args `{"sign":"leo"}` |
| Share zodiac sign across many blocks on one page | Add an Astrology Section wrapper block and put the children inside                                    |
| Use the long tail endpoints                      | Pick a generated block from the inserter or use `[roxy_horoscope_weekly sign="leo"]` style shortcodes |

## Gotchas

-   **The API key never goes into the browser.** Every call is server side in PHP, in both the editor preview and the frontend render, so a shortcode is safe on a fully public page.
-   **Date format is `YYYY-MM-DD`, time is `HH:MM`.** Both are strings.
-   **Coordinates are decimal degrees.** Negative for west and south. Never hardcode them: drop the shortcode without `lat`/`lon` and visitors get a city search that fills them in.
-   **Language is a setting, not a shortcode attribute.** Hero shortcodes ignore `lang`. Readings follow the WordPress site language, or **Reading language** on the Branding tab if you want them to differ.
-   **A reading is cached per endpoint.** Attribute changes produce a different cache key and call through immediately, so you never have to clear a cache after editing a shortcode.

## Links

-   Documentation: https://roxyapi.com/docs
-   Interactive API reference: https://roxyapi.com/api-reference
-   Pricing and API keys: https://roxyapi.com/pricing
-   TypeScript SDK: https://github.com/RoxyAPI/sdk-typescript
-   Python SDK: https://github.com/RoxyAPI/sdk-python
-   MCP for AI agents: https://roxyapi.com/docs/mcp
-   Issues: https://github.com/RoxyAPI/sdk-wordpress/issues
