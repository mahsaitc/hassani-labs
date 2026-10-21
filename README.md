# Hassani Labs

Website of **Hassani Labs**, the studio of Mahsa Hassani (physician and computer engineer): who I am, my work
(case studies, skills, timeline, blog) and a store for my products and services, starting with the acupuncture
clinic software. WordPress + WooCommerce with a custom theme.

Started from the Quantivista site; the portfolio content comes from mahsahassani.dev. Code names (theme folder
`quantivista`, `qv_` prefixes, text domain) are kept on purpose; only what visitors see was renamed.

> Work in progress — full setup notes are added as milestones land.

## Stack

- WordPress 7.1, PHP 8.2, MySQL 8.4 (Local by Flywheel)
- WooCommerce, Advanced Custom Fields, Polylang, Wordfence (SEO tags and the contact form are built into the theme)
- Custom theme: `app/public/wp-content/themes/quantivista`

## Repository layout

```
app/public/wp-content/themes/quantivista/   custom theme (tracked)
app/public/wp-content/mu-plugins/           site functionality (tracked)
scripts/                                    provisioning & seed scripts
```

WordPress core, third-party plugins, uploads and `wp-config.php` are not tracked — see `.gitignore`.

## Clinic software pages

Sales pages for the acupuncture clinic software (sold and installed per clinic), with the same layout as the other product pages (`template-parts/apps/`); texts in theme `inc/apps.php` (`acupuncture-clinic`):

- `/clinic-software-fa/` (Persian, template "Clinic Software (Persian)"): order form → Zarinpal in toman. Orders: dashboard **App Orders**. Price: 30,000,000 toman, and $100 on the English page (**App Orders → Settings**).
- `/clinic-software/` (English, template "Clinic Software (English)"): the dollar price; buyers pay with an Iranian card on the Persian page or contact you.
- Screenshots: `assets/img/apps/acupuncture-clinic-*.webp` (Persian) and `-en.webp` (English), 1280x800, taken from the software's demo mode (made-up doctors and patients). Icon: `acupuncture-clinic-icon.webp`.
- Earlier versions sold three plans: **Clinic Orders** (`clinic-orders.php`) keeps those orders, and the hidden service products `QV-CLINIC-*` are no longer shown on the page. `inc/clinic.php` and `template-parts/clinic/` are no longer used by the pages.

## Website sales pages (Aesthetic Clinic Suite)

- Pages `/aesthetic-clinic-suite/` (English) and `/fa/aesthetic-clinic-suite-fa/` (Persian), listed under **Websites** and **Medical & healthcare** on the Products page. Same layout as the app pages (`template-parts/apps/`); texts in theme `inc/apps.php` (`aesthetic-clinic`).
- Persian page: order form → Zarinpal in toman; after payment the buyer is told you will get in touch to deliver the source and install it (or gets a personal download link if one is set). Orders: dashboard **App Orders**. Price: 30,000,000 toman, and $150 on the English page (**App Orders → Settings**).
- English page: the dollar price; buyers pay with an Iranian card on the Persian page or contact you.
- Screenshots: `assets/img/apps/aesthetic-clinic-*.webp` (Persian) and `-en.webp` (English), 1280x800, taken from the suite's site and panel with sample data (no real patient or person in them). Icon: `aesthetic-clinic-icon.webp`.
- Existing site: `wp eval-file scripts/seed.php catalog` creates the pages and adds them to the categories.

## Languages (English and Persian)

- [Polylang](https://wordpress.org/plugins/polylang/) (free): English at the site root, Persian under `/fa/`.
  The header has an EN / فا switcher that opens the same page in the other language.
- `wp eval-file scripts/seed.php languages` sets up both languages and creates the Persian version of every page,
  project, article, timeline entry and hero slide, linked to its English original, plus the Persian menus. Persian
  portfolio and article texts are in `scripts/seed/data/portfolio.fa.json`; page texts in `scripts/seed/translations.php`.
  After that, edit translations in the dashboard (each post shows its other-language version in the Languages box).
- The Persian content is created in two extra WP-CLI processes (`scripts/seed/translations.php`, then
  `scripts/seed/finish.php`, which links the pages and rebuilds the permalinks). If the seed prints a warning that a
  step did not run, run those two files yourself with `wp eval-file`, in that order. Both are safe to re-run.
- Theme texts (buttons, labels, forms): `themes/quantivista/languages/fa_IR.po`. After editing it, rebuild with
  `wp i18n make-mo` and `wp i18n make-php` on that file. Customizer texts that you change appear under
  Languages > Translations.
- Persian pages load `main-rtl.css` (right-to-left) and the Vazirmatn font. `main-rtl.css` and `woocommerce-rtl.css` are
  generated: edit `main.css` / `woocommerce.css`, then run `scripts/build-rtl.sh`.
- The store (WooCommerce) stays English with the foreign gateway; Persian buyers order on `/fa/clinic-software-fa/`
  with Zarinpal. Translating WooCommerce products needs the paid Polylang for WooCommerce, not used here.

## Before launch

- Hosting checklist (wp-config, server rules, admin account, Search Console): `docs/DEPLOY.fa.md`.
- SEO without a plugin: `inc/seo.php` prints the meta description, Open Graph and JSON-LD, and turns itself off if
  Yoast SEO or Rank Math is active. Copies that are not `production` (Local) are `noindex`.

- Sample store products (FlowPilot, Nexus CRM and the others) are seeded as **drafts**. Delete them or replace
  them with real products; only the clinic software products are real.
- Policy pages are draft templates: complete the business details and governing law, and have them reviewed.
- Bank details for the bank-transfer payment method are empty: fill them in WooCommerce > Settings > Payments.
- Testimonials and client logos are empty on purpose. Add real ones only, with the client's permission.
- Add a photo on the About page (featured image) and a résumé link (`qv_resume_url` field) if wanted.
