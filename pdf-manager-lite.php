<?php
/**
 * Plugin Name: Resources Manager
 * Plugin URI:  https://sibasi.co.ke
 * Description: A lightweight plugin to manage resources (PDF documents) with categories. Gives admins a simple upload workflow and viewers a filterable archive page or shortcode grid.
 * Version:     1.0.0
 * Author:      Sibasi Ltd
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pdf-manager-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'PML_VERSION', '1.0.0' );
define( 'PML_PATH', plugin_dir_path( __FILE__ ) );
define( 'PML_URL', plugin_dir_url( __FILE__ ) );

/* ------------------------------------------------------------------------
 * 1. Custom Post Type: pdf_doc (Resources)
 * ---------------------------------------------------------------------- */
function pml_register_post_type() {
	$labels = array(
		'name'               => __( 'Resources', 'pdf-manager-lite' ),
		'singular_name'      => __( 'Resource', 'pdf-manager-lite' ),
		'add_new_item'       => __( 'Add New Resource', 'pdf-manager-lite' ),
		'edit_item'          => __( 'Edit Resource', 'pdf-manager-lite' ),
		'new_item'           => __( 'New Resource', 'pdf-manager-lite' ),
		'view_item'          => __( 'View Resource', 'pdf-manager-lite' ),
		'search_items'       => __( 'Search Resources', 'pdf-manager-lite' ),
		'not_found'          => __( 'No Resources found', 'pdf-manager-lite' ),
		'all_items'          => __( 'All Resources', 'pdf-manager-lite' ),
		'menu_name'          => __( 'Resources Manager', 'pdf-manager-lite' ),
	);

	register_post_type( 'pdf_doc', array(
		'labels'          => $labels,
		'public'          => true,
		'has_archive'     => true,
		'rewrite'         => array( 'slug' => 'resources', 'with_front' => false ),
		'menu_icon'       => 'dashicons-media-document',
		'supports'        => array( 'title', 'editor', 'thumbnail' ),
		'show_in_rest'    => false,
		'menu_position'   => 20,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'pml_register_post_type' );

/* ------------------------------------------------------------------------
 * 2. Custom Taxonomy: pdf_category (Resource Categories)
 * ---------------------------------------------------------------------- */
function pml_register_taxonomy() {
	register_taxonomy( 'pdf_category', 'pdf_doc', array(
		'labels' => array(
			'name'          => __( 'Resource Categories', 'pdf-manager-lite' ),
			'singular_name' => __( 'Resource Category', 'pdf-manager-lite' ),
			'menu_name'     => __( 'Categories', 'pdf-manager-lite' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'resource-category' ),
	) );
}
add_action( 'init', 'pml_register_taxonomy' );

/* ------------------------------------------------------------------------
 * 3. Meta box: attach the actual PDF file to a pdf_doc post
 * ---------------------------------------------------------------------- */
function pml_add_meta_box() {
	add_meta_box(
		'pml_pdf_file',
		__( 'Resource File (PDF)', 'pdf-manager-lite' ),
		'pml_render_meta_box',
		'pdf_doc',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'pml_add_meta_box' );

function pml_render_meta_box( $post ) {
	wp_nonce_field( 'pml_save_meta_box', 'pml_meta_box_nonce' );

	$file_id  = get_post_meta( $post->ID, '_pml_file_id', true );
	$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
	?>
	<p>
		<input type="hidden" name="pml_file_id" id="pml_file_id" value="<?php echo esc_attr( $file_id ); ?>" />
		<button type="button" class="button" id="pml_upload_btn"><?php _e( 'Select / Upload PDF', 'pdf-manager-lite' ); ?></button>
		<button type="button" class="button" id="pml_remove_btn" style="<?php echo $file_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'pdf-manager-lite' ); ?></button>
	</p>
	<p id="pml_file_name" style="font-style:italic;">
		<?php echo $file_url ? esc_html( basename( $file_url ) ) : __( 'No file selected yet.', 'pdf-manager-lite' ); ?>
	</p>
	<script>
	jQuery(document).ready(function ($) {
		var frame;
		$('#pml_upload_btn').on('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({
				title: '<?php echo esc_js( __( 'Select a PDF', 'pdf-manager-lite' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use this PDF', 'pdf-manager-lite' ) ); ?>' },
				library: { type: 'application/pdf' },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$('#pml_file_id').val(att.id);
				$('#pml_file_name').text(att.filename || att.url);
				$('#pml_remove_btn').show();
			});
			frame.open();
		});
		$('#pml_remove_btn').on('click', function (e) {
			e.preventDefault();
			$('#pml_file_id').val('');
			$('#pml_file_name').text('<?php echo esc_js( __( 'No file selected yet.', 'pdf-manager-lite' ) ); ?>');
			$(this).hide();
		});
	});
	</script>
	<?php
}

function pml_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['pml_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['pml_meta_box_nonce'], 'pml_save_meta_box' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['pml_file_id'] ) ) {
		update_post_meta( $post_id, '_pml_file_id', absint( $_POST['pml_file_id'] ) );
	}
}
add_action( 'save_post_pdf_doc', 'pml_save_meta_box' );

/* ------------------------------------------------------------------------
 * 4. Admin: enqueue media uploader + admin list column tweaks
 * ---------------------------------------------------------------------- */
function pml_admin_enqueue( $hook ) {
	global $post_type;
	if ( 'pdf_doc' !== $post_type ) {
		return;
	}
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'pml_admin_enqueue' );

/* ------------------------------------------------------------------------
 * 5. Front-end assets (Styles & PDF.js preview renderer)
 * ---------------------------------------------------------------------- */
function pml_frontend_assets() {
	global $post;
	$has_shortcode = is_a( $post, 'WP_Post' ) && (
		has_shortcode( $post->post_content, 'resources_manager' ) ||
		has_shortcode( $post->post_content, 'resources_grid' ) ||
		has_shortcode( $post->post_content, 'pdf_archive' ) ||
		has_shortcode( $post->post_content, 'resources_category' )
	);

	if ( is_post_type_archive( 'pdf_doc' ) || is_tax( 'pdf_category' ) || is_singular( 'pdf_doc' ) || $has_shortcode ) {
		wp_enqueue_style( 'pml-frontend', PML_URL . 'assets/css/frontend.css', array(), PML_VERSION );
		wp_enqueue_script( 'pdfjs', PML_URL . 'assets/js/pdf.min.js', array(), '2.16.105', true );
		wp_enqueue_script( 'pml-frontend', PML_URL . 'assets/js/frontend.js', array( 'pdfjs' ), PML_VERSION, true );
		wp_localize_script( 'pml-frontend', 'PML_Settings', array(
			'workerUrl' => PML_URL . 'assets/js/pdf.worker.min.js',
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'pml_frontend_assets' );

/* ------------------------------------------------------------------------
 * 6. Template loader — use plugin templates unless the active theme
 *    provides its own archive-pdf_doc.php / single-pdf_doc.php
 * ---------------------------------------------------------------------- */
function pml_template_loader( $template ) {
	if ( is_post_type_archive( 'pdf_doc' ) || is_tax( 'pdf_category' ) ) {
		$theme_file = locate_template( array( 'archive-pdf_doc.php' ) );
		if ( $theme_file ) {
			return $theme_file;
		}
		return PML_PATH . 'templates/archive-pdf_doc.php';
	}

	if ( is_singular( 'pdf_doc' ) ) {
		$theme_file = locate_template( array( 'single-pdf_doc.php' ) );
		if ( $theme_file ) {
			return $theme_file;
		}
		return PML_PATH . 'templates/single-pdf_doc.php';
	}

	return $template;
}
add_filter( 'template_include', 'pml_template_loader' );

/* ------------------------------------------------------------------------
 * 7. Archive filtering — category + year/month via normal GET request
 * ---------------------------------------------------------------------- */
function pml_filter_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! ( $query->is_post_type_archive( 'pdf_doc' ) || $query->is_tax( 'pdf_category' ) ) ) {
		return;
	}

	// Category filter (dropdown value = term slug).
	if ( ! empty( $_GET['pdf_cat'] ) ) {
		$query->set( 'tax_query', array(
			array(
				'taxonomy' => 'pdf_category',
				'field'    => 'slug',
				'terms'    => sanitize_title( wp_unslash( $_GET['pdf_cat'] ) ),
			),
		) );
	}

	// Date filter (year + optional month).
	if ( ! empty( $_GET['pdf_year'] ) ) {
		$date_query = array( 'year' => absint( $_GET['pdf_year'] ) );
		if ( ! empty( $_GET['pdf_month'] ) ) {
			$date_query['month'] = absint( $_GET['pdf_month'] );
		}
		$query->set( 'date_query', array( $date_query ) );
	}

	$query->set( 'posts_per_page', 20 );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
}
add_action( 'pre_get_posts', 'pml_filter_archive_query' );

/* ------------------------------------------------------------------------
 * 8. Helper functions used by the templates
 * ---------------------------------------------------------------------- */
function pml_get_file_url( $post_id ) {
	$file_id = get_post_meta( $post_id, '_pml_file_id', true );
	return $file_id ? wp_get_attachment_url( $file_id ) : '';
}

/**
 * Build the list of years that have at least one PDF, for the date filter dropdown.
 */
function pml_get_archive_years() {
	global $wpdb;
	$results = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY post_date DESC",
		'pdf_doc'
	) );
	return $results;
}

/**
 * Build the list of months that have at least one PDF published, for date filter dropdown.
 */
function pml_get_archive_months( $year = 0 ) {
	global $wpdb;
	if ( $year ) {
		$results = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT MONTH(post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND YEAR(post_date) = %d ORDER BY MONTH(post_date) ASC",
			'pdf_doc',
			$year
		) );
	} else {
		$results = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT MONTH(post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY MONTH(post_date) ASC",
			'pdf_doc'
		) );
	}
	return array_map( 'absint', $results );
}

/* ------------------------------------------------------------------------
 * 9. Shortcodes: [resources_manager], [resources_grid], [pdf_archive]
 *    Lets the resources grid be embedded inside any page slug.
 * ---------------------------------------------------------------------- */
function pml_shortcode_archive( $atts ) {
	$atts = shortcode_atts( array(
		'posts_per_page' => 20,
		'category'       => '',
	), $atts, 'resources_manager' );

	$paged = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page', 1 ) );
	if ( isset( $_GET['paged'] ) ) {
		$paged = max( 1, absint( $_GET['paged'] ) );
	}

	$current_cat   = isset( $_GET['pdf_cat'] ) ? sanitize_title( wp_unslash( $_GET['pdf_cat'] ) ) : ( ! empty( $atts['category'] ) ? sanitize_title( $atts['category'] ) : '' );
	$current_year  = isset( $_GET['pdf_year'] ) ? absint( $_GET['pdf_year'] ) : '';
	$current_month = isset( $_GET['pdf_month'] ) ? absint( $_GET['pdf_month'] ) : '';

	$query_args = array(
		'post_type'      => 'pdf_doc',
		'post_status'    => 'publish',
		'posts_per_page' => absint( $atts['posts_per_page'] ),
		'paged'          => $paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( $current_cat ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'pdf_category',
				'field'    => 'slug',
				'terms'    => $current_cat,
			),
		);
	}

	if ( $current_year ) {
		$date_query = array( 'year' => $current_year );
		if ( $current_month ) {
			$date_query['month'] = $current_month;
		}
		$query_args['date_query'] = array( $date_query );
	}

	$pml_query = new WP_Query( $query_args );

	ob_start();
	include PML_PATH . 'templates/archive-content.php';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'resources_manager', 'pml_shortcode_archive' );
add_shortcode( 'resources_grid', 'pml_shortcode_archive' );
add_shortcode( 'pdf_archive', 'pml_shortcode_archive' );

/* ------------------------------------------------------------------------
 * 10. Shortcode: [resources_category category="slug"]
 *     Renders a clean grid of resources filtered to a single category.
 *     No sidebar, no date filter — ideal for embedding on landing pages.
 *
 *     Attributes:
 *       category       (string)  – Required. Slug of the pdf_category term.
 *       posts_per_page (int)     – Default 12. Use -1 for all.
 *       columns        (int)     – Grid columns 1–4. Default 3.
 *       show_title     (string)  – 'yes'|'no'. Show the category name as heading. Default 'yes'.
 * ---------------------------------------------------------------------- */
function pml_shortcode_category( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'       => '',
			'posts_per_page' => 12,
			'columns'        => 3,
			'show_title'     => 'yes',
		),
		$atts,
		'resources_category'
	);

	// Sanitise attributes.
	$category       = sanitize_title( $atts['category'] );
	$posts_per_page = absint( $atts['posts_per_page'] ) ?: -1;
	$columns        = min( 4, max( 1, absint( $atts['columns'] ) ) );
	$show_title     = ( 'no' !== strtolower( trim( $atts['show_title'] ) ) );

	// Guard: category is required.
	if ( empty( $category ) ) {
		return '<p class="pml-notice">' . esc_html__( 'Please specify a category for the [resources_category] shortcode.', 'pdf-manager-lite' ) . '</p>';
	}

	// Verify the term actually exists.
	$term = get_term_by( 'slug', $category, 'pdf_category' );
	if ( ! $term || is_wp_error( $term ) ) {
		return '<p class="pml-notice">' . esc_html__( 'The specified resource category was not found.', 'pdf-manager-lite' ) . '</p>';
	}

	$query_args = array(
		'post_type'      => 'pdf_doc',
		'post_status'    => 'publish',
		'posts_per_page' => $posts_per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'pdf_category',
				'field'    => 'slug',
				'terms'    => $category,
			),
		),
	);

	$pml_cat_query = new WP_Query( $query_args );

	ob_start();
	include PML_PATH . 'templates/category-grid.php';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'resources_category', 'pml_shortcode_category' );

/* ------------------------------------------------------------------------
 * 11. Flush rewrite rules on activation / deactivation
 * ---------------------------------------------------------------------- */
function pml_activate() {
	pml_register_post_type();
	pml_register_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pml_activate' );

function pml_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pml_deactivate' );

// Uninstall cleanup is handled by uninstall.php.
register_uninstall_hook( __FILE__, '__return_false' ); // Signals WP to use uninstall.php.


