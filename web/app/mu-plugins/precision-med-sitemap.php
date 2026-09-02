<?php
/**
 * Trims WordPress core's sitemap to the pages that are actually content.
 *
 * Core adds a `users` provider that lists an author archive for every user who
 * has published something. On this site that exposed /author/kabernathy/ and
 * /author/mwender/ — thin archives of a six-page brochure site, and a public
 * listing of admin usernames. Neither belongs in the index we hand to Google.
 *
 * Removing the provider drops the sub-sitemap from wp-sitemap.xml. It does not
 * deindex the author archives themselves; if those ever need to go, noindex
 * them separately.
 */

add_filter( 'wp_sitemaps_add_provider', function ( $provider, string $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );
