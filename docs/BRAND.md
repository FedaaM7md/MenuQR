# MenuQR — Brand Guide

**Product:** MenuQR · المنيو الرقمي  
**Owner:** Fedaa Mohamed · فداء محمد  
**Owner credit:** a small footer line, “Designed & built by Fedaa Mohamed” /
“تصميم وتطوير: فداء محمد”, linked to fedaamohamed.com. Never under the logo.

## 1. Idea

MenuQR should feel like a real printed restaurant menu that happens to work on a
phone. Paper, ink, a saffron accent and a kitchen ticket. Not a generic SaaS
dashboard with purple gradients.

Three references in the real world:

1. a well-printed Egyptian menu card with dotted leaders between dish and price;
2. a kitchen ticket clipped above the pass;
3. a maître d’s ledger with today’s numbers.

## 2. Logo

The mark is a cloche (plate cover) drawn as a clean line, with a QR finder pattern
inside the dome and a plate line underneath. It is original artwork, drawn for
this product, and exists as:

- `resources/views/components/logo.blade.php` (inline SVG, adapts to light/dark);
- `public/favicon.svg`;
- `public/icons/icon-192.png`, `icon-512.png`, `icon-maskable-512.png`
  (built by `scripts/make-icons.php`).

The wordmark is **MenuQR** set in El Messiri 700. No owner name sits under the
logo; the credit lives in the footer only.

Clear space: at least the height of the cloche handle on every side. Minimum size:
24 px for the mark, 96 px wide for the full lockup.

## 3. Colour

| Token | Hex | Use |
|---|---|---|
| charcoal | `#23201C` | Primary text, dark surfaces, primary button |
| charcoal-soft | `#35302A` | Sidebar hover, raised dark panel |
| charcoal-deep | `#171511` | Kitchen background, modal backdrop |
| saffron | `#E3A72F` | Accent, active nav, demo bar, chart highlight |
| saffron-deep | `#8F5E0A` | Small saffron text on cream (AA contrast) |
| saffron-soft | `#F7E7BE` | Badges and gentle highlights |
| saffron-50 | `#FDF7E7` | Tinted rows and panels |
| olive | `#6B7A3A` | “Ready / available” states, secondary marks |
| olive-deep | `#4F5C2A` | Olive text on light surfaces |
| olive-soft | `#E7EBD8` | Preparing badge, calm success |
| cream | `#FBF5E9` | Page background |
| paper | `#FFFDF6` | Cards, menu cards, forms |
| sand | `#F4ECDC` | Section bands, table headers |
| tomato | `#C8452F` | Destructive actions, cancelled status |
| tomato-deep | `#9E3221` | Error text |
| tomato-soft | `#F7DDD6` | Error / cancelled background |
| line | `#E2D7C3` | Borders, rules, dotted leaders |
| line-strong | `#CBBB9F` | Input borders, stronger rules |
| muted | `#756B5C` | Secondary text |
| ink-soft | `#4A443A` | Body text on paper |

Do not introduce indigo, violet, teal or glass effects. Status colour is the only
place where red/green family colours appear.

## 4. Typography

Self-hosted via npm `@fontsource/*` (OFL 1.1); no CDN.

| Role | Family | Weights |
|---|---|---|
| Display / headings | El Messiri | 500, 600, 700 |
| UI / body | Tajawal | 400, 500, 700 |
| Numbers | Tajawal with `font-variant-numeric: tabular-nums` | 400, 500, 700 |

Rules:

- Headings use El Messiri; body, labels, tables and buttons use Tajawal.
- Prices, totals, order numbers, dates and quantities use `.tnum` (tabular numerals).
- Arabic uses normal letter spacing; never letter-space Arabic.
- Latin all-caps eyebrows are allowed for small labels only.
- English page titles use sentence case. Arabic headings use normal sentence case.

## 5. Shape and depth

- Radius: 0 for QR cards, 2–6 px for panels and inputs. No 24 px rounded cards.
- Shadows are paper-like: one tight line plus a soft far shadow. No glass blur cards.
- Rules matter: 1 px `line`, dotted leaders, dashed ticket cuts.
- The workflow/world uses “paper + ink”; the kitchen uses inverted charcoal with
  cream tickets.

## 6. Layout personality

- Customer menu: a single printed column, mobile-first, category dividers with
  dotted leaders; sticky cart bar in charcoal.
- Landing: real menu card and kitchen ticket as the hero, not a floating mockup.
- Admin: dark charcoal sidebar, cream working surface, data-dense tables, tabular
  numbers, ledger-like stat blocks.
- Kitchen: dark board, three status columns, large cream tickets readable from a
  distance.
- Print: A4 QR cards with generous white space and the plate mark.

## 7. Voice

Arabic: clear Modern Standard Arabic that reads naturally to Egyptians. Short
sentences, concrete verbs, no hype. Example: «الطلب يروح للمطبخ على طول» rather
than «حلول متكاملة بضغطة زر».

English: plain product English, short and specific. “Print the codes”, not
“unlock a seamless QR experience”.

Allowed: hospitality (“اطلب من هنا”, “شكرًا لطلبك”), operational precision
(“قيد التحضير”, “تم التقديم”).
Avoid: “seamless”, “elevate”, “empower”, “unlock”, “revolutionize”, “next-level”,
“all-in-one”, “بكل سهولة”, “ارتقِ”, “حلول متكاملة”.

## 8. Glossary (AR / EN)

| Arabic | English | Note |
|---|---|---|
| المنيو | menu | Never “قائمة الطعام” in UI |
| صنف / أصناف | item / items | A dish |
| قسم / أقسام | category / categories | Menu section |
| اختيارات | choices | Option groups and add-ons |
| طاولة / طاولات | table / tables | Standard Arabic UI term |
| طلب / طلبات | order / orders | |
| جديد | New | Status |
| قيد التحضير | Preparing | Status |
| جاهز | Ready | Status |
| تم التقديم | Served | Status |
| ملغي | Cancelled | Status |
| داخل المطعم | Dine-in | |
| تيك أواي | Takeaway | Egyptian usage |
| نسخة تجريبية | Demo | Always labelled |

## 9. Do / Don’t

Do:

- show the product doing its job with real demo data;
- use dotted leaders, rules and ticket cuts;
- keep money tabular and right-aligned in tables;
- label demo data and demo accounts;
- give one clear primary action per screen.

Don’t:

- put “Fedaa” in the product name, H1, page titles or demo business names;
- use emoji as icons;
- use gradient blobs, glass cards, sparkle badges or “trusted by” rows;
- invent customer numbers or testimonials;
- use more than one accent colour on a single card.

## 10. RTL / LTR

- `<html lang="ar" dir="rtl">` or `lang="en" dir="ltr"`; never a hardcoded `dir`.
- Use logical utilities (`ps-*`, `pe-*`, `ms-*`, `me-*`, `start-*`, `end-*`).
- Dotted leaders, prices and totals mirror correctly.
- Arabic-Indic digits are optional in Egypt; this build uses Western digits with
  tabular spacing, plus Arabic currency labels. The decision is recorded in
  `docs/DECISIONS.md`.
- Language switch keeps the current page and translates every visible string.
