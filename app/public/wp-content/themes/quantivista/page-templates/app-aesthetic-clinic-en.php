<?php
/**
 * Template Name: Aesthetic Clinic Suite (English)
 *
 * English page for the aesthetic clinic website and panel. Payment is in
 * toman on the Persian page; international buyers are sent to the contact page.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/apps/landing', null, array( 'app' => 'aesthetic-clinic', 'lang' => 'en' ) );
get_footer();
