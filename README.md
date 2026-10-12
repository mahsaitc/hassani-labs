# Quantivista

Corporate website and software store for **Quantivista** — web software, mobile apps and website design services.
WordPress + WooCommerce with a custom theme.

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
