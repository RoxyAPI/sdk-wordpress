#!/usr/bin/env bash
# Seed local wp-env with QA pages that exercise every hero shortcode (§3) and
# the daily-text block binding (§6) so manual testing is one click instead of
# 20 minutes of wp-admin shortcode entry.
#
# Idempotent: deletes any prior post with the matching slug before recreating.
# Sets the canonical test API key via the plugin's own Encryption helper so
# the shortcodes can actually call the upstream API.
#
# Usage: bash bin/seed-qa-pages.sh
#
# Prereqs: wp-env running, plugin active. Pass NODE_OPTIONS below if your host
# resolver fails on api.wordpress.org over IPv6 (Node 24 Happy Eyeballs hangs).
#
# Set ROXYAPI_TEST_KEY in your shell to the API key the seeded pages should
# call. Get one from https://roxyapi.com/pricing or use a development key.

set -euo pipefail

# Resolve repo root regardless of where the script is invoked from.
REPO_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." &>/dev/null && pwd)"
cd "$REPO_ROOT"

# Force IPv4 + disable network-family autoselection so wp-env's npm calls do
# not stall on hosts with broken IPv6 routing to api.wordpress.org.
export NODE_OPTIONS="${NODE_OPTIONS:---dns-result-order=ipv4first --no-network-family-autoselection}"

TEST_KEY="${ROXYAPI_TEST_KEY:?Set ROXYAPI_TEST_KEY to a RoxyAPI API key before running this script.}"

WP() { npx wp-env run cli "$@" 2> >(grep -vE "^ℹ|^✔|^Shell cwd was reset" >&2); }

echo "==> Setting encrypted test API key"
WP wp eval "
\$plain = '$TEST_KEY';
\$encrypted = \\RoxyAPI\\Support\\Encryption::encrypt( \$plain );
if ( \$encrypted === false ) {
    fwrite( STDERR, \"encryption helper returned false; check ROXYAPI_ENCRYPTION_KEY / LOGGED_IN_KEY\n\" );
    exit( 1 );
}
update_option( 'roxyapi_settings', array( 'api_key_encrypted' => \$encrypted ) );
echo \"  ok (option roxyapi_settings.api_key_encrypted is base64 ciphertext)\n\";
"

# Page content. One section per hero with a static and a form invocation,
# plus a block-bindings example for §6. Use Gutenberg block markup so the
# editor opens cleanly and the front-end renders identically.
read -r -d '' QA_PAGE_CONTENT <<'EOF' || true
<!-- wp:heading {"level":1} -->
<h1>RoxyAPI QA — Hero shortcodes (auto-seeded)</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every hero is exercised twice: <strong>static</strong> with required attrs (renders the result) and <strong>form mode</strong> (renders the visitor form). Re-run <code>bash bin/seed-qa-pages.sh</code> to refresh.</p>
<!-- /wp:paragraph -->

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>1. Horoscope (hand-written hero)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect a daily reading for Leo.</p><!-- /wp:paragraph -->
[roxy_horoscope sign="leo"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect a sign picker.</p><!-- /wp:paragraph -->
[roxy_horoscope]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>2. Natal chart</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect a Western birth chart for a July birth (Sun in Cancer).</p><!-- /wp:paragraph -->
[roxy_natal_chart birth_date="1985-07-22" birth_time="06:15" lat="34.0522" lon="-118.2437" tz="America/Los_Angeles"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect birth-detail form with city autocomplete.</p><!-- /wp:paragraph -->
[roxy_natal_chart]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>3. Tarot card (dispatch hero)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static three-card</strong> — expect three cards.</p><!-- /wp:paragraph -->
[roxy_tarot_card spread="three" question="What should I focus on this week"]

<!-- wp:paragraph --><p><strong>Default</strong> — expect a single daily card (legacy behaviour preserved).</p><!-- /wp:paragraph -->
[roxy_tarot_card]

<!-- wp:paragraph --><p><strong>Form (opt-in)</strong> — expect question + spread picker.</p><!-- /wp:paragraph -->
[roxy_tarot_card mode="form"]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>4. Numerology</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect numerology chart.</p><!-- /wp:paragraph -->
[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect name + birth-date form.</p><!-- /wp:paragraph -->
[roxy_numerology]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>5. Life path</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect life-path number.</p><!-- /wp:paragraph -->
[roxy_life_path birth_date="1990-05-15"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect birth-date picker.</p><!-- /wp:paragraph -->
[roxy_life_path]

<!-- wp:paragraph --><p><strong>Static-mode opt-out</strong> — expect the legacy missing-attrs error (not a form).</p><!-- /wp:paragraph -->
[roxy_life_path mode="static"]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>6. Vedic kundli (NEW v1.1)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect twelve-rashi chart for a January birth in Mumbai (Sun in Capricorn).</p><!-- /wp:paragraph -->
[roxy_kundli birth_date="1988-01-12" birth_time="09:45" lat="19.0760" lon="72.8777" tz="Asia/Kolkata"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect Vedic birth-detail form with city autocomplete.</p><!-- /wp:paragraph -->
[roxy_kundli]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>7. Detailed Panchang (NEW v1.1)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect tithi, nakshatra, rahu kaal, abhijit muhurta, etc. (mid-October Bangalore).</p><!-- /wp:paragraph -->
[roxy_panchang date="2026-10-15" lat="12.9716" lon="77.5946" tz="Asia/Kolkata"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect date + city autocomplete.</p><!-- /wp:paragraph -->
[roxy_panchang]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>8. Mangal Dosha (NEW v1.1)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect dosha presence + description.</p><!-- /wp:paragraph -->
[roxy_mangal_dosha birth_date="1992-09-08" birth_time="22:30" lat="13.0827" lon="80.2707" tz="Asia/Kolkata"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect Vedic birth-detail form.</p><!-- /wp:paragraph -->
[roxy_mangal_dosha]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>9. KP chart (NEW v1.1)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect KP cusps, planets, sub-lords, significators.</p><!-- /wp:paragraph -->
[roxy_kp_chart birth_date="1990-05-15" birth_time="14:30" lat="28.6139" lon="77.2090" tz="Asia/Kolkata"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect Vedic birth-detail form.</p><!-- /wp:paragraph -->
[roxy_kp_chart]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>10. Synastry (NEW v1.1, two-chart)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Form-only</strong> — expect Person 1 and Person 2 fieldsets with city autocomplete.</p><!-- /wp:paragraph -->
[roxy_synastry]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>11. Gun Milan / 36-point Ashtakoota (NEW v1.1, two-chart)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Form-only</strong> — expect two birth-chart fieldsets for matrimonial compatibility.</p><!-- /wp:paragraph -->
[roxy_gun_milan]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>12. Western compatibility score (NEW v1.1, two-chart)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Form-only</strong> — expect two birth-chart fieldsets.</p><!-- /wp:paragraph -->
[roxy_compatibility]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>13. Moon phase (NEW v1.1)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect current phase, illumination, sign, meaning. No form.</p><!-- /wp:paragraph -->
[roxy_moon_phase]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>14. Tarot yes / no (NEW v1.1)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static with question</strong> — expect Yes / No / Maybe answer.</p><!-- /wp:paragraph -->
[roxy_tarot_yes_no question="Should I take the new job"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect question text input.</p><!-- /wp:paragraph -->
[roxy_tarot_yes_no mode="form"]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>15. Biorhythm</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect today's biorhythm cycles.</p><!-- /wp:paragraph -->
[roxy_biorhythm birth_date="1990-05-15" target_date="today"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect birth-date + optional-target-date form.</p><!-- /wp:paragraph -->
[roxy_biorhythm]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>16. Angel number</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect 1111 meaning.</p><!-- /wp:paragraph -->
[roxy_angel_number number="1111"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect number-input form.</p><!-- /wp:paragraph -->
[roxy_angel_number]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>17. Crystals by zodiac (NEW v1.1, replaces single-crystal lookup)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p><strong>Static</strong> — expect Aries crystals (zodiac sign Title-cased before sending per spec).</p><!-- /wp:paragraph -->
[roxy_crystals_by_zodiac sign="aries"]

<!-- wp:paragraph --><p><strong>Form</strong> — expect zodiac sign picker.</p><!-- /wp:paragraph -->
[roxy_crystals_by_zodiac]

<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

<!-- wp:heading -->
<h2>§6 — Block Binding (paragraph bound to roxyapi/daily-text)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph --><p>Expect the paragraph below to render the daily-horoscope <em>overview</em> string for Leo (server-side, no shortcode).</p><!-- /wp:paragraph -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"roxyapi/daily-text","args":{"sign":"leo"}}}}} -->
<p>If you see this literal sentence, the binding did not resolve.</p>
<!-- /wp:paragraph -->
EOF

# Encode the page content as base64 to survive the wp-cli command boundary
# without quoting hell. wp post create reads --post_content from a string;
# stdin redirection via `wp post create -` does not exist in 2.x.
ENCODED=$(printf '%s' "$QA_PAGE_CONTENT" | base64 -w0)

echo "==> Replacing prior QA page (if any) with fresh content"
PAGE_ID=$(WP wp eval "
\$existing = get_posts( array(
    'name' => 'roxyapi-qa-heroes',
    'post_type' => 'page',
    'post_status' => 'any',
    'posts_per_page' => -1,
) );
foreach ( \$existing as \$p ) {
    wp_delete_post( \$p->ID, true );
}
\$content = base64_decode( '$ENCODED' );
\$id = wp_insert_post( array(
    'post_title'   => 'RoxyAPI QA — Hero Shortcodes',
    'post_name'    => 'roxyapi-qa-heroes',
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_content' => \$content,
) );
if ( is_wp_error( \$id ) ) {
    fwrite( STDERR, \$id->get_error_message() . \"\n\" );
    exit( 1 );
}
echo \"\\nROXYAPI_PAGE_ID=\$id\";
" 2>&1 | grep -oE 'ROXYAPI_PAGE_ID=[0-9]+' | head -1 | cut -d= -f2)

# wp-env exposes WP at localhost:8888 by default; the wp-cli `option get
# siteurl` round-trip would be more correct but runs the editor URL through
# the same noisy wp-env wrapper. Hardcode is fine because the script is
# wp-env-only by design (see top-of-file usage note).
SITE_URL='http://localhost:8888'

echo
echo "Done. Open in browser:"
echo "  Front-end: $SITE_URL/?page_id=$PAGE_ID  (or $SITE_URL/roxyapi-qa-heroes/)"
echo "  Editor:    $SITE_URL/wp-admin/post.php?action=edit&post=$PAGE_ID"
echo "  Settings:  $SITE_URL/wp-admin/admin.php?page=roxyapi"
