<?php
/**
 * Template Name: Clinic Software (English)
 *
 * English sales page for the acupuncture clinic software; plans are bought through the store checkout.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/clinic/landing', null, array( 'lang' => 'en' ) );
get_footer();
