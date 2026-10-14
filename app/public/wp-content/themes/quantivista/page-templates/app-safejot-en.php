<?php
/**
 * Template Name: SafeJot (English)
 *
 * English page for the SafeJot Android app. Payment is in toman on the
 * Persian page; international buyers are sent to the contact page.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/apps/landing', null, array( 'app' => 'safejot', 'lang' => 'en' ) );
get_footer();
