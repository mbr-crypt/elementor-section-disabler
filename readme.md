# Elementor Section Disabler

A lightweight WordPress plugin that adds a **Hide on live site** toggle to Elementor elements. Disabled elements stay fully visible and editable inside the Elementor editor, but are removed from the live frontend with `display:none`.

Handy for staging content, seasonal blocks, work-in-progress sections, or temporarily pulling something offline without deleting it.

---

## Features

- Adds a **Disable Section** control under the **Advanced** tab.
- Works on Containers, Sections, Columns and Widgets.
- Element stays visible in the editor, marked with a dashed outline and a label.
- Hidden on the real frontend via a CSS class (no inline-style conflicts).
- No settings page, no database bloat, no frontend JavaScript, no dependencies beyond Elementor.

---

## Requirements

- WordPress 5.0+ (tested on 6.x)
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

In the editor the element stays visible with a red dashed outline and a *"Hidden on live site"* label. On the published page it is gone. Set the toggle back to **No** to bring it back.

> The **Preview** (eye icon) reflects the live state, so disabled elements appear hidden there too — that's expected.

---

## How it works

On render the plugin checks Elementor's edit mode. Inside the editor nothing is hidden, so the element stays fully editable. Only on the real frontend the wrapper receives an `esd-hidden` class, and a tiny inline stylesheet applies `display:none !important`.

The editor highlight is added through Elementor's `prefix_class`, so it also shows for elements that are re-rendered live via JavaScript while editing.

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

- **`display:none` is visual only.** The element is still present in the page's HTML source and the DOM — it just isn't rendered. Don't use this to hide sensitive content; anyone viewing the page source or DevTools can still read it.
- **SEO / accessibility:** hidden elements are skipped by browsers, screen readers, and generally ignored by search engines. Don't hide content you still want indexed.
- **Control ID:** the toggle is stored under the `esd_disable` key. Changing that key in a future version would reset existing toggles, so keep it stable across updates.

---

## Changelog

### 1.0.0
- Initial release.
- Disable toggle for Containers, Sections, Columns and Widgets.
- Editor highlight + live-frontend hiding.

---

## License

Recommended: **GNU GPL v2 or later** — the standard for WordPress/Elementor plugins, since they are derivative works of GPL-licensed code. Add a matching `LICENSE` file to the repository.

---

## Author

**INTERCAT** — [intercat.it](https://intercat.it)
