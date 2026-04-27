# WordPress.org Asset Files

This directory holds the plugin assets that get pushed to the WordPress.org SVN `assets/` directory by the 10up deploy action. Drop the actual image files in here and tag a release.

| File                  | Dimensions  | Notes                                                          |
| --------------------- | ----------- | -------------------------------------------------------------- |
| `icon-128x128.png`    | 128 by 128  | Standard icon shown in plugin browser                          |
| `icon-256x256.png`    | 256 by 256  | High DPI icon                                                  |
| `banner-772x250.png`  | 772 by 250  | Standard banner shown on plugin listing page                   |
| `banner-1544x500.png` | 1544 by 500 | High DPI banner                                                |
| `screenshot-1.png`    | any         | Plugin settings page where you paste your RoxyAPI key          |
| `screenshot-2.png`    | any         | Daily Horoscope block in the Gutenberg editor with sign picker |
| `screenshot-3.png`    | any         | Frontend rendering of a daily horoscope on a WordPress page    |
| `screenshot-4.png`    | any         | Tarot card pull with three card spread                         |
| `screenshot-5.png`    | any         | I Ching hexagram cast with interpretation                      |
| `screenshot-6.png`    | any         | RoxyAPI inserter category showing the ten hero blocks          |

Screenshot captions come from the `== Screenshots ==` block in `readme.txt`. Number them sequentially. Captions and files must match.

Banner design notes:

- Left third: logo and plugin name. Right two thirds: tagline. Dark mode safe background.
- No screenshots inside the banner. They look bad at 772 by 250.
- Must read at both 772 by 250 and 1544 by 500.

Icon design notes:

- SVG preferred. Single glyph, high contrast, works on light and dark WP admin backgrounds.
- If using PNG, ship both 128 and 256 sizes.

Reference: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
