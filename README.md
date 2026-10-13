# Hassani Labs

Website of **Hassani Labs**, the studio of Mahsa Hassani (physician and computer engineer): who I am, my work
(case studies, skills, timeline, blog) and a store for my products and services, starting with the acupuncture
clinic software. WordPress + WooCommerce with a custom theme.

Started from the Quantivista site; the portfolio content comes from mahsahassani.dev. Code names (theme folder
`quantivista`, `qv_` prefixes, text domain) are kept on purpose; only what visitors see was renamed.

> Work in progress — full setup notes are added as milestones land.

## Stack

- WordPress 7.1, PHP 8.2, MySQL 8.4 (Local by Flywheel)
- WooCommerce, Advanced Custom Fields, Yoast SEO, Contact Form 7, Wordfence
- Custom theme: `app/public/wp-content/themes/quantivista`

## Repository layout

```
app/public/wp-content/themes/quantivista/   custom theme (tracked)
app/public/wp-content/mu-plugins/           site functionality (tracked)
scripts/                                    provisioning & seed scripts
```

WordPress core, third-party plugins, uploads and `wp-config.php` are not tracked — see `.gitignore`.

## Clinic software pages

Sales pages for the acupuncture clinic software (sold and installed per clinic):

- `/clinic-software-fa/` (Persian, template "Clinic Software (Persian)"): plans and an order form. Orders are saved under **Clinic Orders** in the dashboard and paid through Zarinpal in toman, independent of the store currency. Set the merchant ID, test mode, plan prices, demo link and notification email at **Clinic Orders → Settings**. A plan without a price, or no merchant ID, takes the order as a request without payment.
- `/clinic-software/` (English, template "Clinic Software (English)"): plans are the hidden service products `QV-CLINIC-BASIC`, `QV-CLINIC-PRO` and `QV-CLINIC-RENEW`, bought through the normal store checkout. Set their prices in Products; until then the page shows "Price on request".

Code: `mu-plugins/quantivista-core/clinic-orders.php`, `themes/quantivista/inc/clinic.php`, `template-parts/clinic/`, `assets/css/clinic-software.css`. The Persian font is Vazirmatn (SIL OFL 1.1).

## Portfolio

- Projects are the `qv_project` post type at `/work/` (case study pages at `/work/<slug>/`), with a
  Technology taxonomy and fields for tagline, year, repository link, demo link, stack and metrics.
- Content source: `scripts/seed/data/portfolio.json` (projects, timeline, skills, blog posts). Edit it and re-run
  `wp eval-file scripts/seed.php portfolio blog`, or edit the posts in the admin afterwards.
- Founder name, role and intro on the homepage: Appearance > Customize > Hassani Labs Options.

## Contact form

The Contact page (both languages) uses a built-in form (`mu-plugins/quantivista-core/contact.php`), so no form
plugin is needed. Each message is saved under **Messages** in the dashboard and emailed to the contact email set in
the Customizer (Hassani Labs Options > Contact & Footer). Social profiles and the map location are set there too.

## Product categories and the Products menu

- Categories are in two groups, **Software by platform** (mobile apps, Windows/desktop, web applications, websites,
  WordPress plugins and themes, bots) and **Software by field** (medical, engineering, legal, education, business and
  accounting, e-commerce, AI and automation, restaurants, real estate). List, Persian names, icons and descriptions:
  `scripts/seed/data/catalog.php`.
- The **Products** page (`/products/`, Persian `/fa/products-fa/`, template "Products Catalogue") shows every category as
  a card. A category with no products yet says "Coming soon" and links to the contact page for custom software.
- The main menu's **Products** item opens a mega menu with both groups. In English a category opens its store page;
  in Persian (the store is English only) it opens its card on the Persian Products page.
- Each category's Persian name and description, icon, and "Product pages" (pages shown as its products, e.g.
  `clinic-software` under Medical and Web applications) can be edited in Products > Categories.
- To add the categories, pages and menu to a site that is already set up, without touching anything else:
  `wp eval-file scripts/seed.php catalog`.

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

- Sample store products (FlowPilot, Nexus CRM and the others) are seeded as **drafts**. Delete them or replace
  them with real products; only the clinic software products are real.
- Policy pages are draft templates: complete the business details and governing law, and have them reviewed.
- Bank details for the bank-transfer payment method are empty: fill them in WooCommerce > Settings > Payments.
- Testimonials and client logos are empty on purpose. Add real ones only, with the client's permission.
- Add a photo on the About page (featured image) and a résumé link (`qv_resume_url` field) if wanted.
