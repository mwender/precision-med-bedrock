<?php
/**
 * Adds `pm-full-width-body` to the body class when the ACF `page_full_width_body`
 * field is enabled on a single page. This drives the Kit CSS rule that removes
 * the fixed-width constraint from the Page Single template's body container,
 * allowing Elementor pages to use full-bleed sections while Gutenberg/standard
 * pages retain the fixed-width layout by default.
 */

add_filter( 'body_class', function ( array $classes ): array {
	if ( is_singular( 'page' ) && function_exists( 'get_field' ) && get_field( 'page_full_width_body' ) ) {
		$classes[] = 'pm-full-width-body';
	}
	return $classes;
} );
