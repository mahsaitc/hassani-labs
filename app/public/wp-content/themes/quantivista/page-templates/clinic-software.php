<?php
/**
 * Template Name: Clinic Software (Persian)
 *
 * Persian sales page for the acupuncture clinic software, with the Zarinpal order form.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/clinic/landing', null, array( 'lang' => 'fa' ) );
get_footer();
