<?php
/**
 * Uninstall handler for Resources Manager.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * By default, no data is deleted to prevent accidental loss of resources.
 * Data is only removed if the administrator explicitly enabled data deletion
 * in Resources Manager -> Settings by typing "DELETE".
 */

// WordPress safety check — bail if not called by WP uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Check if administrator enabled data deletion on uninstall.
$delete_data = (bool) get_option( 'pml_delete_data_on_uninstall', 0 );

if ( ! $delete_data ) {
	// Default: Keep all posts, categories, attachments, and metadata intact.
	return;
}

global $wpdb;

// 1. Delete all pdf_doc custom post type entries (and their postmeta).
$pdf_posts = get_posts( array(
	'post_type'      => 'pdf_doc',
	'post_status'    => 'any',
	'numberposts'    => -1,
	'fields'         => 'ids',
) );

if ( ! empty( $pdf_posts ) ) {
	foreach ( $pdf_posts as $post_id ) {
		wp_delete_post( $post_id, true ); // Force delete bypasses trash.
	}
}

// 2. Delete plugin-specific post meta if any leftovers exist.
$wpdb->delete(
	$wpdb->postmeta,
	array( 'meta_key' => '_pml_file_id' ),
	array( '%s' )
);

// 3. Delete custom taxonomy terms.
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

// 4. Clean up plugin options.
delete_option( 'pml_delete_data_on_uninstall' );
