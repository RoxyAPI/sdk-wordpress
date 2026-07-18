# WordPress.org Asset Files

This directory holds the plugin assets that get pushed to the WordPress.org SVN `assets/` directory by the 10up deploy action. Drop the actual image files in here and tag a release.

| File                  | Dimensions  | Notes                                        |
| --------------------- | ----------- | -------------------------------------------- |
| `icon-128x128.png`    | 128 by 128  | Standard icon shown in plugin browser        |
| `icon-256x256.png`    | 256 by 256  | High DPI icon                                |
| `banner-772x250.png`  | 772 by 250  | Standard banner shown on plugin listing page |
| `banner-1544x500.png` | 1544 by 500 | High DPI banner                              |
| `screenshot-1.png`    | 1280 by 900 | Western natal chart wheel                    |
| `screenshot-2.png`    | 1280 by 900 | The admin Shortcodes Library                 |
| `screenshot-3.png`    | 1280 by 900 | Vedic kundli chart                           |
| `screenshot-4.png`    | 1280 by 900 | Detailed panchang                            |
| `screenshot-5.png`    | 1280 by 900 | Human Design bodygraph                       |
| `screenshot-6.png`    | 1280 by 900 | Astrocartography map                         |
| `screenshot-7.png`    | 1280 by 900 | Daily horoscope card                         |
| `screenshot-8.png`    | 1280 by 900 | Connect / first-run onboarding               |

Screenshot captions come from the `== Screenshots ==` block in `readme.txt`. Number them sequentially, captions and files must match. **Keep every screenshot the same size (1280 by 900)** so the wp.org carousel does not crop or misalign them. Regenerate with the Playwright recipe in the plugin repo `CLAUDE.md` (the wp.org screenshots section).

Banner design notes:

-   Left third: logo and plugin name. Right two thirds: tagline. Dark mode safe background.
-   No screenshots inside the banner. They look bad at 772 by 250.
-   Must read at both 772 by 250 and 1544 by 500.

Icon design notes:

-   SVG preferred. Single glyph, high contrast, works on light and dark WP admin backgrounds.
-   If using PNG, ship both 128 and 256 sizes.

Reference: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
