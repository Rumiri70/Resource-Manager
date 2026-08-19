<?php
/**
 * Uninstall handler for Resources Manager.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Removes all post meta stored by this plugin (_pml_file_id) from every
 * pdf_doc post, keeping the media-library attachments themselves intact.
 */

// WordPress safety check — bail if not called by WP uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete plugin-specific post meta from all pdf_doc posts.
$wpdb->delete(
	$wpdb->postmeta,
	array( 'meta_key' => '_pml_file_id' ),
	array( '%s' )
);

// Remove the pdf_category terms (optional — keeps DB clean).
$terms = get_terms( array(
	'taxonomy'   => 'pdf_category',
	'hide_empty' => false,
	'fields'     => 'ids',
) );

if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	foreach ( $terms as $term_id ) {
		wp_delete_term( (int) $term_id, 'pdf_category' );
	}
}
