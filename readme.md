# Elementor Section Disabler

A lightweight WordPress plugin that adds a **Hide on live site** toggle to Elementor elements. Disabled elements stay fully visible and editable inside the Elementor editor, but are **not rendered at all** on the live frontend, so they are missing from the HTML source as well.

Handy for staging content, seasonal blocks, work-in-progress sections, or temporarily pulling something offline without deleting it.

---

## Features

- Adds a **Disable Section** control under the **Advanced** tab.
- Works on Containers, Sections, Columns and Widgets.
- Element stays visible in the editor, faded to grey with diagonal stripes, a dashed outline and a label.
- Not rendered on the real frontend (Elementor's `should_render` filter), so crawlers and AI agents never see the content. A CSS class with `display:none` remains as a fallback.
- No settings page, no database bloat, no frontend JavaScript, no dependencies beyond Elementor.

---

## Requirements

- WordPress 5.0+ (tested on 7.x)
- Elementor (free) — Flexbox Containers supported
- PHP 7.4 or newer

---

## Installation

### Manual

1. Create a folder `elementor-section-disabler` in `wp-content/plugins/` and place `elementor-section-disabler.php` inside it, so the path is:
   ```
   wp-content/plugins/elementor-section-disabler/elementor-section-disabler.php
   ```
2. Go to **Plugins** in wp-admin and activate the plugin.

### As ZIP

1. Zip the `elementor-section-disabler` folder.
2. wp-admin → **Plugins → Add New → Upload Plugin** → choose the ZIP → **Install Now** → **Activate**.

---

## Usage

1. Edit a page with Elementor.
2. Select a Container (or Section / Column / Widget).
3. Open the **Advanced** tab → **Disable Section**.
4. Switch **Hide on live site** to **Yes**.

In the editor the element stays visible, faded to grey and covered with diagonal stripes, with a *"Hidden on live site"* label. On the published page it is gone, including from the page source. Set the toggle back to **No** to bring it back.

> The **Preview** (eye icon) reflects the live state, so disabled elements appear hidden there too — that's expected.

---

## How it works

On render the plugin checks Elementor's edit mode. Inside the editor nothing is hidden, so the element stays fully editable. On the real frontend the plugin hooks into `elementor/frontend/{widget|section|column|container}/should_render` and returns `false` for flagged elements, so Elementor skips them completely and no markup is output.

As a safety net, flagged elements that are rendered anyway receive an `esd-hidden` class, and a tiny inline stylesheet applies `display:none !important`.

The editor look is added through Elementor's `prefix_class`, so it also shows for elements that are re-rendered live via JavaScript while editing. The grey fade is a `backdrop-filter` on an overlay, so Elementor's own editing handles keep their colours; browsers without `backdrop-filter` fall back to grayscale + opacity.

---

## Supported elements

| Element              | Supported |
|----------------------|:---------:|
| Container (Flexbox)  | ✅        |
| Section (legacy)     | ✅ \*     |
| Column (legacy)      | ✅ \*     |
| Widgets              | ✅        |

\* The hook for legacy Sections / Columns relies on Elementor's internal section IDs and may vary between versions. If the toggle doesn't appear there, it fails silently — no errors.

---

## Notes & caveats

- **Not in the page source anymore.** Since 1.1.0 flagged elements are not part of the rendered HTML, so they are not visible in the page source, the DOM, or to crawlers and AI agents reading the page.
- **Not a security feature.** The content is still stored in the page's Elementor data in the database and stays readable for everyone with edit access. Don't rely on this for confidential content.
- **SEO / accessibility:** hidden elements are skipped by browsers, screen readers, and search engines alike. Don't hide content you still want indexed.
- **Caches:** if you use a page cache or CDN, purge it after toggling an element so the old HTML disappears.
- **Control ID:** the toggle is stored under the `esd_disable` key. Changing that key in a future version would reset existing toggles, so keep it stable across updates.

---

## Changelog

### 1.1.0
- Flagged elements are no longer rendered on the frontend (via `should_render`) instead of only being hidden with `display:none`; the CSS class stays as a fallback.
- New editor look: disabled elements are faded to grey and covered with diagonal stripes.

### 1.0.0
- Initial release.
- Disable toggle for Containers, Sections, Columns and Widgets.
- Editor highlight + live-frontend hiding.

---

## License

**GNU GPL v3** - Take a look at the `LICENSE` file

---

## Author

**MBR-CRYPT from INTERCAT** — [intercat.it](https://intercat.it)
