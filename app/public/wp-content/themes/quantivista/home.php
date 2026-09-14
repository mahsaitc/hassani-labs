<?php
/**
 * Blog posts index.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/blog/listing', null, array( 'is_index' => true ) );
get_footer();
