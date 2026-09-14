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
