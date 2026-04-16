# roxyapi WordPress plugin: Agent Guide

WordPress plugin for [RoxyAPI](https://roxyapi.com). Drop horoscopes, tarot, numerology, I Ching, natal charts, and more onto any WordPress page with shortcodes or Gutenberg blocks. One API key, ten spiritual data domains, 130+ endpoints.

> Before writing any code in this repo, read the build runbook at `https://github.com/RoxyAPI/sdk-wordpress/blob/main/AGENTS.md` and the upstream spec at `https://roxyapi.com/api/v2/openapi.json`.

## Install

```bash
# WordPress admin
Plugins > Add New > search "RoxyAPI" > Install > Activate
Settings > RoxyAPI > paste API key > Save

# wp-cli
wp plugin install roxyapi --activate
wp option update roxyapi_settings '{"api_key":"..."}' --format=json

# Production: store the key in wp-config.php instead of the database
define( 'ROXYAPI_KEY', getenv( 'ROXYAPI_KEY' ) );
```

## Use a shortcode

Every hero shortcode has two modes, auto detected.

### Static mode: site owner picks the values

Pass all required attributes and the shortcode renders a fixed reading that never changes:

```
[roxy_horoscope sign="aries"]
[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060"]
[roxy_tarot_card spread="three" question="What should I focus on"]
[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]
[roxy_iching question="Should I take the new job"]
[roxy_dream symbol="water"]
[roxy_biorhythm birth_date="1990-05-15" target_date="today"]
[roxy_angel_number number="1111"]
[roxy_crystal name="amethyst"]
[roxy_compatibility sign_a="leo" sign_b="aquarius"]
```

### Form mode: visitors pick their own values

Leave the required attributes off and the shortcode renders an HTML form. Visitors submit it, the plugin validates the nonce, rate limits per IP, calls the API server side, and renders the result above the form on the next page load:

```
[roxy_horoscope]        -> zodiac sign picker
[roxy_numerology]       -> name and birth date form
[roxy_iching]           -> question text area
[roxy_natal_chart]      -> birth date, time, and location picker
[roxy_tarot_card]       -> question input with spread selector
[roxy_dream]            -> dream symbol search
[roxy_biorhythm]        -> birth date input
[roxy_angel_number]     -> number input
[roxy_crystal]          -> crystal name search
[roxy_compatibility]    -> two sign picker
[roxy_life_path]        -> birth date picker
```

Form submissions post back to the same page. The plugin uses `wp_verify_nonce` for CSRF, `RoxyAPI\Support\RateLimit` for per IP throttling (20 requests per hour by default, configurable in Settings), and `wp_remote_request` for the API call. All output is escaped via `esc_html` or `wp_kses_post`. The API key never reaches the browser in either mode.

Override the form or result template from your theme by copying the matching file from `templates/` into `your-theme/roxyapi/`.

## Use a Gutenberg block

In the editor, open the inserter and search for "Horoscope", "Tarot", "Numerology", "I Ching", or "Natal Chart". Each block opens a variation picker (Daily, Weekly, Monthly, Love, Career, Chinese, Celtic Cross, Three Card, Life Path, Expression, Soul Urge, and so on).

Drop one Astrology Section wrapper block on the page, set the zodiac sign in its Inspector, and every child RoxyAPI block inside inherits the sign via block context. No per-block configuration.

## Domains

| Block / shortcode prefix | What it covers |
|---|---|
| Horoscope | Western horoscopes: daily, weekly, monthly, love, career, Chinese, Burmese |
| Natal Chart | Western birth chart: planets, houses, aspects, transits |
| Tarot | Rider Waite Smith deck: single card, three card, Celtic Cross, custom layouts |
| Numerology | Life path, expression, soul urge, personal year, full chart |
| I Ching | Hexagram casting and interpretation |
| Dreams | Symbol dictionary with 3,000 entries |
| Biorhythm | Physical, emotional, intellectual, intuitive cycles |
| Angel Number | Number meanings and pattern analysis |
| Crystal | Properties, zodiac and chakra pairings |

For Vedic astrology, KP system, panchang, dasha calculations, and other long tail endpoints, use the auto-generated shortcodes. Run `wp shortcode list | grep roxy_` to see every available tag on your install.

## How the plugin reads your API key

Resolution order:

1. `ROXYAPI_KEY` constant in `wp-config.php` (recommended for production)
2. The encrypted value in the `roxyapi_settings` option, decrypted via AES-256-CTR
3. Empty string (every shortcode renders a friendly placeholder pointing at the settings page)

The encryption key derives from `ROXYAPI_ENCRYPTION_KEY` constant or `LOGGED_IN_KEY` fallback. Same for the salt. Document for production:

```php
define( 'ROXYAPI_KEY',             getenv( 'ROXYAPI_KEY' ) );
define( 'ROXYAPI_ENCRYPTION_KEY',  getenv( 'ROXYAPI_ENCRYPTION_KEY' ) );
define( 'ROXYAPI_ENCRYPTION_SALT', getenv( 'ROXYAPI_ENCRYPTION_SALT' ) );
```

## Caching

Every successful response is cached in a WordPress transient with a per endpoint TTL:

| Endpoint family | TTL |
|---|---|
| Daily horoscope | 1 hour |
| Numerology, natal chart, dreams, crystals, angel numbers | 1 month (deterministic from input) |
| Biorhythm | 1 day |
| Tarot, I Ching | not cached (randomness is the value) |

Cached responses do not consume RoxyAPI quota. Object cache backends (Redis, Memcached) are picked up automatically.

## Common tasks

| Task | How |
|---|---|
| Test the API key | Settings > RoxyAPI > Test Connection |
| Override the key without touching the database | `define( 'ROXYAPI_KEY', '...' );` in `wp-config.php` |
| Change cache TTL | Settings > RoxyAPI > Cache tab |
| Clear all cached responses | Settings > RoxyAPI > Cache tab > Flush, or `wp roxyapi cache flush` |
| Add a horoscope to any paragraph | Use Block Bindings: bind a `core/paragraph` to source `roxyapi/daily-text` with args `{"sign":"leo"}` |
| Share zodiac sign across many blocks on one page | Add an Astrology Section wrapper block and put the children inside |
| Use the long tail endpoints | Pick a generated block from the inserter or use `[roxy_horoscope_weekly sign="leo"]` style shortcodes |

## Gotchas

- **The API key never goes into the browser.** Do not refactor any block to fetch from `roxyapi.com` client side. Editor previews use server side render. Frontend renders use PHP `render.php` files.
- **Shortcodes return, never echo.** WordPress filters break otherwise.
- **Date format is `YYYY-MM-DD`, time is `HH:MM`.** Both are strings.
- **Coordinates are decimal degrees.** Negative for west and south.
- **Block apiVersion is locked to 3.** Schema rejects any other value.
- **Variations are not separate blocks.** Each hero block ships a `variations.php` file. This keeps the inserter clean.
- **Hero shortcodes always win over generated ones with the same name.** The Registrar checks `shortcode_exists()` before registering generated entries.
- **Plain text editing your generated PHP is pointless.** `npm run generate` overwrites `src/Generated/` and `blocks/generated/`: Edit the templates in `bin/templates/` instead.

## Links

- Documentation: https://roxyapi.com/docs
- Interactive API reference: https://roxyapi.com/api-reference
- Pricing and API keys: https://roxyapi.com/pricing
- TypeScript SDK: https://github.com/RoxyAPI/sdk-typescript
- Python SDK: https://github.com/RoxyAPI/sdk-python
- MCP for AI agents: https://roxyapi.com/docs/mcp
- Issues: https://github.com/RoxyAPI/sdk-wordpress/issues
