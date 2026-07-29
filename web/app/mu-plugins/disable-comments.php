<?php
/**
 * Plugin Name: Disable Comments
 * Description: Globally disables comments and pingbacks/trackbacks site-wide, including the admin UI, REST API endpoints, and XML-RPC.
 */

namespace PrecisionMed\DisableComments;

/**
 * Close comments and pings on the front end, regardless of per-post settings.
 */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );

/**
 * Hide any existing comments rather than deleting them.
 */
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

/**
 * Remove comments/discussion support from all post types.
 */
add_action( 'init', function (): void {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}, 100 );

/**
 * Remove comments/discussion admin menu pages.
 */
add_action( 'admin_menu', function (): void {
	remove_menu_page( 'edit-comments.php' );
} );

/**
 * Remove the Discussion settings page (no UI purpose once comments are off).
 */
add_action( 'admin_menu', function (): void {
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
} );

/**
 * Redirect any direct requests to admin comment screens back to the dashboard.
 */
add_action( 'admin_init', function (): void {
	global $pagenow;

	if ( $pagenow === 'edit-comments.php' ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
} );

/**
 * Remove the comments admin bar item.
 */
add_action( 'wp_before_admin_bar_render', function (): void {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'comments' );
} );

/**
 * Remove comments-related dashboard widgets.
 */
add_action( 'wp_dashboard_setup', function (): void {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );

/**
 * Disable support for comments and trackbacks in posts.
 */
add_filter( 'wp_count_comments', function () {
	return (object) [
		'approved'            => 0,
		'moderated'           => 0,
		'spam'                => 0,
		'trash'               => 0,
		'post-trashed'        => 0,
		'total_comments'      => 0,
		'all'                 => 0,
	];
} );

/**
 * Disable the REST API comments endpoints (/wp/v2/comments and per-post comment discovery).
 */
add_filter( 'rest_endpoints', function ( array $endpoints ): array {
	foreach ( $endpoints as $route => $endpoint ) {
		if ( str_starts_with( $route, '/wp/v2/comments' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

/**
 * Strip the `comment_status`/`ping_status` fields the REST API otherwise exposes as open.
 */
add_filter( 'rest_prepare_post', __NAMESPACE__ . '\\force_rest_comment_status_closed', 10, 1 );
add_filter( 'rest_prepare_page', __NAMESPACE__ . '\\force_rest_comment_status_closed', 10, 1 );

function force_rest_comment_status_closed( \WP_REST_Response $response ): \WP_REST_Response {
	if ( isset( $response->data['comment_status'] ) ) {
		$response->data['comment_status'] = 'closed';
	}
	if ( isset( $response->data['ping_status'] ) ) {
		$response->data['ping_status'] = 'closed';
	}
	return $response;
}

/**
 * Disable comment-related XML-RPC methods (new comments, edits, and pingbacks).
 */
add_filter( 'xmlrpc_methods', function ( array $methods ): array {
	unset(
		$methods['wp.newComment'],
		$methods['wp.getComments'],
		$methods['wp.getComment'],
		$methods['wp.editComment'],
		$methods['wp.deleteComment'],
		$methods['wp.getCommentStatusList'],
		$methods['wp.getCommentCount'],
		$methods['pingback.ping'],
		$methods['pingback.extensions.getPingbacks']
	);
	return $methods;
} );

/**
 * Remove the X-Pingback HTTP header and pingback/wlwmanifest link tags from <head>.
 */
add_filter( 'wp_headers', function ( array $headers ): array {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

/**
 * Remove comment reply script enqueue since threaded comments are unused.
 */
add_action( 'wp_enqueue_scripts', function (): void {
	wp_dequeue_script( 'comment-reply' );
} );

/**
 * Ensure comment feeds are also emptied out.
 */
add_filter( 'feed_links_show_comments_feed', '__return_false' );
