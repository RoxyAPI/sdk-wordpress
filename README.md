# RoxyAPI for WordPress

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/roxyapi.svg)](https://wordpress.org/plugins/roxyapi/)
[![WordPress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/roxyapi.svg)](https://wordpress.org/plugins/roxyapi/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/rating/roxyapi.svg)](https://wordpress.org/plugins/roxyapi/)
[![WordPress Tested](https://img.shields.io/wordpress/plugin/tested/roxyapi.svg)](https://wordpress.org/plugins/roxyapi/)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](LICENSE)

## Ship a complete astrology, tarot, or numerology site this weekend. Not this quarter.

The only multi domain spiritual intelligence plugin for WordPress. Drop daily horoscopes, tarot pulls, numerology charts, I Ching casts, dream symbol lookups, and natal chart calculators onto any page. One API key, ten spiritual data domains, 130+ endpoints. Verified against NASA JPL Horizons.

Interactive forms for your visitors. Gutenberg blocks for your editor. Shortcodes for anywhere else. Server side rendering keeps your API key out of the browser. Transient caching keeps your quota intact.

- **Ten hero blocks and shortcodes** covering western astrology, vedic astrology, tarot, numerology, I Ching, dreams, biorhythm, angel numbers, crystals, and location.
- **120+ auto generated shortcodes** for the long tail. Every endpoint in the RoxyAPI OpenAPI spec is reachable from a shortcode.
- **Form mode on every hero shortcode.** Let visitors submit their own sign, name, birth date, or question and render a personalized reading. No JavaScript required.
- **Zero client side secrets.** All calls run in PHP. The API key never reaches the browser.

## Ship this weekend

Three pages any spiritual practitioner can launch in an afternoon:

- **Daily horoscope page.** One shortcode, twelve sign picker, cached for one hour per sign. `[roxy_horoscope]`
- **Numerology reading page.** A form that asks for name and birth date, returns Life Path, Expression, Soul Urge, Personality numbers, and a narrative interpretation. `[roxy_numerology]`
- **Tarot reader page.** A form that takes a question and draws a three card spread with interpretation. `[roxy_tarot_card spread="three"]`

All three pages monetize the same way: drop an email opt in, a Stripe buy button, or a Patreon link below the shortcode. The plugin handles the reading, you keep the audience.

## Shortcodes

Every hero shortcode works in two modes. Pass all required attributes for a **static reading** that the site owner controls. Omit them and the shortcode renders a **form** so visitors submit their own inputs.

### Static mode (site owner picks the values)

```
[roxy_horoscope sign="aries"]
[roxy_tarot_card spread="three" question="What should I focus on"]
[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]
[roxy_iching question="Should I take the new job"]
[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060"]
[roxy_life_path birth_date="1990-05-15"]
[roxy_dream symbol="water"]
[roxy_biorhythm birth_date="1990-05-15"]
[roxy_angel_number number="1111"]
[roxy_crystal name="amethyst"]
[roxy_compatibility sign_a="leo" sign_b="aquarius"]
```

### Form mode (visitors pick the values)

Leave the attributes off and the shortcode renders an input form for your visitors:

```
[roxy_horoscope]        → zodiac sign picker
[roxy_numerology]       → name and birth date form
[roxy_iching]           → question text area
[roxy_natal_chart]      → birth date, time, and city picker
[roxy_tarot_card]       → question input with spread selector
[roxy_dream]            → dream symbol search
[roxy_biorhythm]        → birth date input
[roxy_angel_number]     → number input
[roxy_crystal]          → crystal name search
[roxy_compatibility]    → two sign picker
[roxy_life_path]        → birth date picker
```

Form submissions post back to the same page over HTTPS. The plugin validates the nonce, rate limits per IP, calls RoxyAPI server side, and renders the result above the form. Nothing to wire up, nothing to style. Override the template from your theme if you want a custom layout.

## Gutenberg blocks

In the block editor, open the inserter and pick a block from the **RoxyAPI** category. Ten hero blocks, each with a variation picker:

- **Horoscope** (daily, weekly, monthly, love, career, Chinese)
- **Natal Chart**
- **Tarot** (daily, three card, Celtic Cross)
- **Numerology** (life path, expression, soul urge, full chart)
- **I Ching**
- **Dream Symbol**
- **Biorhythm**
- **Angel Number**
- **Crystal**
- **Astrology Section** wrapper that shares a default zodiac sign and birth date with every child block inside it via block context

Every block renders server side through the same RoxyAPI client the shortcodes use. Same caching, same rate limiting, same secret handling.

## Quick start

1. **Install.** Plugins, Add New, search "RoxyAPI", click Install Now, then Activate.
2. **Get an API key.** Sign up at [roxyapi.com/pricing](https://roxyapi.com/pricing). Starter plan is $39 per month, 5,000 requests across all ten domains.
3. **Paste the key.** Settings, RoxyAPI, paste the key, Save Changes. The plugin encrypts the key at rest via AES 256 CTR.
4. **Drop a shortcode.** Add `[roxy_horoscope]` to any page and publish. Visitors can now pick their sign and read their horoscope.

That is the whole setup. Thirty minutes from install to live page.

## Store the key outside the database

For production hosts that inject secrets via environment variables (Pantheon, WP Engine, Kinsta, Bedrock), define the constants in `wp-config.php` and the plugin picks them up automatically:

```php
define( 'ROXYAPI_KEY',             getenv( 'ROXYAPI_KEY' ) );
define( 'ROXYAPI_ENCRYPTION_KEY',  getenv( 'ROXYAPI_ENCRYPTION_KEY' ) );
define( 'ROXYAPI_ENCRYPTION_SALT', getenv( 'ROXYAPI_ENCRYPTION_SALT' ) );
```

When `ROXYAPI_KEY` is defined, the settings field is read only and the constant takes priority. Your key never enters the database.

## Ten domains, 130+ endpoints, one key

| Domain | What you get |
|---|---|
| Western astrology | Natal charts, horoscopes (daily, weekly, monthly, love, career), transits, synastry, moon phases, compatibility |
| Vedic astrology | Kundli, nakshatras, Dasha, Panchang, KP system, doshas, yogas, muhurta |
| Tarot | Rider Waite Smith deck, single card, three card, Celtic Cross, custom spreads |
| Numerology | Life path, expression, soul urge, personal year, personality, karmic analysis |
| I Ching | Hexagrams, trigrams, coin casting, daily readings |
| Dreams | Symbol dictionary with 3,000 entries, pattern analysis |
| Crystals | Healing properties, zodiac and chakra pairings, birthstones |
| Angel numbers | Meanings, daily guidance, repetition analysis |
| Biorhythm | Physical, emotional, intellectual, intuitive cycles |
| Location | City geocoding for birth chart coordinates |

Every endpoint is cached with a per endpoint TTL so cached responses do not cost API quota. Object cache backends (Redis, Memcached) are picked up automatically.

## Architecture

- **Server side rendering.** The plugin makes every API call in PHP. The key never reaches the browser.
- **Encrypted at rest storage.** AES 256 CTR with a key derived from `ROXYAPI_ENCRYPTION_KEY` constant or `LOGGED_IN_KEY` fallback. Ported from the Google Site Kit `Data_Encryption` pattern.
- **Rate limiting.** Visitor submitted forms are rate limited per IP per shortcode via WordPress transients. Default is 20 requests per hour, configurable in settings.
- **Theme aware.** Every class is prefixed `.roxyapi-*` and uses `var(--wp--preset--color--*)` tokens from the active theme `theme.json`. Override from your child theme or just override the class.
- **Full i18n.** Text domain `roxyapi`. Auto loaded from translate.wordpress.org.
- **WCAG 2.1 AA.** Proper heading order, labeled inputs, keyboard navigation, color contrast.

## Other SDKs

RoxyAPI ships four official clients. Same data, different stacks:

- **WordPress plugin** (this repo): [wordpress.org/plugins/roxyapi](https://wordpress.org/plugins/roxyapi/)
- **TypeScript SDK:** [github.com/RoxyAPI/sdk-typescript](https://github.com/RoxyAPI/sdk-typescript)
- **Python SDK:** [github.com/RoxyAPI/sdk-python](https://github.com/RoxyAPI/sdk-python)
- **MCP server for AI agents:** [roxyapi.com/docs/mcp](https://roxyapi.com/docs/mcp)

## Links

- [Documentation](https://roxyapi.com/docs)
- [Interactive API reference](https://roxyapi.com/api-reference)
- [Pricing and API keys](https://roxyapi.com/pricing)
- [WordPress.org listing](https://wordpress.org/plugins/roxyapi/)
- [Issues](https://github.com/RoxyAPI/sdk-wordpress/issues)

## Development

Clone the repo and spin up wp-env to hack on the plugin:

```bash
git clone https://github.com/RoxyAPI/sdk-wordpress.git roxyapi
cd roxyapi
composer install
npm install
npx wp-env start
```

The first `composer install` and `npm install` generate `composer.lock` and `package-lock.json`. These lockfiles are committed to the repo so CI can run `npm ci` reproducibly. If you bump a dependency, commit the updated lockfile in the same PR.

The plugin is mounted at `http://localhost:8888` with Plugin Check auto activated. Default login is `admin` / `password`.

Tests, linting, static analysis, and the build:

```bash
vendor/bin/phpunit
vendor/bin/phpcs
vendor/bin/phpstan analyze
npm run build:all
```

Regenerate the auto generated PHP client and long tail shortcodes from the live OpenAPI spec:

```bash
npm run generate
```

CI runs `npm run generate:check` on every pull request and fails if the repo drifts from the live spec. Resolve by regenerating and committing the diff.

See `AGENTS.md` for the full agent facing guide and `CLAUDE.md` for maintainer specific notes.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE). WordPress plugins must ship under a GPL compatible license, so this repo uses GPL-2.0-or-later while the sibling TypeScript and Python SDKs use MIT.
