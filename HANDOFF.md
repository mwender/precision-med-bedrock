# precision-med.test — Session Handoff

**Reference site:** https://precision-health-guide.base44.app/  
**Local site:** https://precision-med.test/  
**Last session:** 2026-05-20

---

## Pages completed this session

| Page | Post ID | Status |
|---|---|---|
| The Program | — | Complete |
| Why Precision Medicine | — | Complete |
| Our Team | 153 | Complete |

---

## Our Team page — what was built

The page was rebuilt from scratch (post 153). Structure:

- **Hero** — rendered automatically by the Single Page template. Do not build a hero container in Elementor page content; the template handles it for all pages.
- **Team Section** — 3 alternating member rows (photo left / text right, photo right / text left, photo left / text right). Each row: image column (~40%) + text column (~55%), `flex-start` alignment so heading sits at the top of the photo.
- **CTA Section** — cream background (`#F8F6F2`), "Meet Your Team in Person" heading, paragraph, navy button.

### Profile photos (sideloaded from Unsplash)
| Member | Role | Attachment ID | URL |
|---|---|---|---|
| Your Physician Name, MD | Physician | 486 | `/app/uploads/2026/05/photo-1612349317150-e413f6a5b16d.jpg` |
| Your Dietician Name, RD | Registered Dietician | 487 | `/app/uploads/2026/05/photo-1559839734-2b71ea197ec2.jpg` |
| Your PT Name, DPT | Physical Therapist | 488 | `/app/uploads/2026/05/photo-1571019613454-1cb2f99b2d8b.jpg` |

Photos are black & white (`filter: grayscale(100%)`).

### Known issue / next-session TODO
The team member image cards (photo + name/specialty overlay) and the specialty/bio text in the text columns were built with **HTML widgets**. This works but is not ideal — the user flagged it as too liberal. On the next session, **rebuild those widgets using native Elementor elements**:
- Specialty label → native Heading or Text Editor widget with typography controls
- Bio paragraph → native Text Editor widget
- Image card with overlay → explore a native Image widget inside a container with an absolutely-positioned inner container for the overlay (Elementor Pro supports `position: absolute` on containers)

---

## Key technical facts

### Stack
- Bedrock WordPress at `/Users/mwender/webdev/laravel-valet/bedrock/precision-med.com/app/`
- Webroot: `web/`
- Local URL: `https://precision-med.test/`

### Elementor MCP
- Transport: HTTP with Application Password
- Auth header: `WP_MCP_BASIC_AUTH` in `.env`
- Kit post ID: **6** — never run `update-page-settings` on the kit; it wipes brand colors/typography

### Full-width pages
- ACF field `page_full_width_body` (toggle) + mu-plugin at `web/app/mu-plugins/precision-med-page-classes.php` adds `pm-full-width-body` body class
- Set for all Elementor pages. New pages need this toggled on + `_elementor_edit_mode: builder` set via WP-CLI: `wp post meta update <ID> _elementor_edit_mode builder --path=web/wp`

### Elementor gotchas learned
- **Use `flex_gap`, not `gap`** — Elementor's CSS generator reads `flex_gap` to emit `--widgets-spacing-row/column`. Setting `gap` is silently ignored.
- **Boxed trap** — `content_width: "boxed"` on a container constrains its children to a fixed width. Fixing children is not enough if the outer wrapper is also boxed; fix or replace the outer wrapper too.
- **Single Page template owns the hero** — Every page's hero (dark navy bg, eyebrow, H1, subtitle) is rendered by the theme template, not Elementor page content. Never build a hero section in page data.
- **Prefer native Elementor widgets** — Only fall back to HTML widgets when no native widget can do the job (e.g., `position: absolute` overlay). Headings, paragraphs, images, buttons, and counters all have native widgets.

### Brand colors
| Token | Hex | Usage |
|---|---|---|
| Primary (navy) | `#182543` | Hero bg, button bg, dark text |
| Gold | `#E0BD52` | Eyebrow labels, specialty labels |
| Cream | `#F3EACE` | H1 on dark bg, button text |
| Background | `#F5F7F9` | Team section bg |
| CTA section | `#F8F6F2` | Warm cream section bg |
| Muted text | `#646F87` | Body/bio paragraphs |

### Typography
- Headings: **Cormorant Garamond**, weight 300
- Body / labels: **Inter**

---

## Pages not yet built / remaining work
- Contact page (check current state)
- Any remaining pages from the reference site
- Revisit Our Team: replace HTML widgets with native Elementor elements (see above)
