<?php
/**
 * Template Name: SafeJot (Persian)
 *
 * Persian sales page for the SafeJot Android app, with the Zarinpal order form.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/apps/landing', null, array( 'app' => 'safejot', 'lang' => 'fa' ) );
get_footer();
