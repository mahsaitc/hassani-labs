<?php
/**
 * Template Name: Aesthetic Clinic Suite (Persian)
 *
 * Persian sales page for the aesthetic clinic website and panel, with the
 * Zarinpal order form.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/apps/landing', null, array( 'app' => 'aesthetic-clinic', 'lang' => 'fa' ) );
get_footer();
