<?php
/**
 * Fallback template.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/blog/listing' );
get_footer();
