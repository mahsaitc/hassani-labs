<?php
/**
 * Template Name: Clinic Software (English)
 *
 * English sales page for the acupuncture clinic software (the shared app landing, inc/apps.php).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/apps/landing', null, array( 'app' => 'acupuncture-clinic', 'lang' => 'en' ) );
get_footer();
