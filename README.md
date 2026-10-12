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

## Before launch

- Sample store products (FlowPilot, Nexus CRM and the others) are seeded as **drafts**. Delete them or replace
  them with real products; only the clinic software products are real.
- Policy pages are draft templates: complete the business details and governing law, and have them reviewed.
- Bank details for the bank-transfer payment method are empty: fill them in WooCommerce > Settings > Payments.
- Testimonials and client logos are empty on purpose. Add real ones only, with the client's permission.
- Add a photo on the About page (featured image) and a résumé link (`qv_resume_url` field) if wanted.
