<?php
/**
 * Shared archive markup: sidebar filter + results grid.
 * Included by templates/archive-pdf_doc.php and the [resources_manager] shortcode.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories       = get_terms( array( 'taxonomy' => 'pdf_category', 'hide_empty' => true ) );
$years            = pml_get_archive_years();
$current_cat      = isset( $_GET['pdf_cat'] ) ? sanitize_title( wp_unslash( $_GET['pdf_cat'] ) ) : '';
$current_year     = isset( $_GET['pdf_year'] ) ? absint( $_GET['pdf_year'] ) : '';
$current_month    = isset( $_GET['pdf_month'] ) ? absint( $_GET['pdf_month'] ) : '';
$available_months = pml_get_archive_months( $current_year );
$months           = array(
	1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
	5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
	9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
);

// Determine active query object (custom shortcode query or global query)
$query_obj = isset( $pml_query ) && $pml_query instanceof WP_Query ? $pml_query : $GLOBALS['wp_query'];

// Base page URL without filter params
$current_page_url = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );
if ( empty( $current_page_url ) ) {
	$current_page_url = get_post_type_archive_link( 'pdf_doc' );
}

// Calculate total published PDF count for "All Categories" link
$total_published = wp_count_posts( 'pdf_doc' )->publish;
?>
<div class="pml-archive-layout">

	<!-- Sidebar Filters -->
	<aside class="pml-sidebar">
		
		<!-- 1. Dates Filter (Top) -->
		<div class="pml-widget pml-widget-date">
			<h3 class="pml-widget-title"><?php _e( 'Filter by Date', 'pdf-manager-lite' ); ?></h3>
			<form class="pml-date-form" method="get" action="<?php echo esc_url( $current_page_url ); ?>">
				<?php if ( $current_cat ) : ?>
					<input type="hidden" name="pdf_cat" value="<?php echo esc_attr( $current_cat ); ?>" />
				<?php endif; ?>

				<div class="pml-field-group">
					<label for="pml_year"><?php _e( 'Year', 'pdf-manager-lite' ); ?></label>
					<select name="pdf_year" id="pml_year">
						<option value=""><?php _e( 'All Years', 'pdf-manager-lite' ); ?></option>
						<?php foreach ( $years as $y ) : ?>
							<option value="<?php echo esc_attr( $y ); ?>" <?php selected( $current_year, (int) $y ); ?>>
								<?php echo esc_html( $y ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="pml-field-group">
					<label for="pml_month"><?php _e( 'Month', 'pdf-manager-lite' ); ?></label>
					<select name="pdf_month" id="pml_month">
						<option value=""><?php _e( 'All Months', 'pdf-manager-lite' ); ?></option>
						<?php foreach ( $months as $num => $label ) : ?>
							<?php if ( empty( $available_months ) || in_array( $num, $available_months, true ) ) : ?>
								<option value="<?php echo esc_attr( $num ); ?>" <?php selected( $current_month, $num ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="pml-date-actions">
					<button type="submit" class="pml-btn pml-btn-submit"><?php _e( 'Apply Filter', 'pdf-manager-lite' ); ?></button>
					<?php if ( $current_year || $current_month ) : ?>
						<?php
						$clear_date_url = remove_query_arg( array( 'pdf_year', 'pdf_month', 'paged' ) );
						?>
						<a href="<?php echo esc_url( $clear_date_url ); ?>" class="pml-btn-clear"><?php _e( 'Reset Date', 'pdf-manager-lite' ); ?></a>
					<?php endif; ?>
				</div>
			</form>
		</div>

		<!-- 2. Categories List (Below) -->
		<div class="pml-widget pml-widget-categories">
			<h3 class="pml-widget-title"><?php _e( 'Categories', 'pdf-manager-lite' ); ?></h3>
			<ul class="pml-cat-list">
				<?php
				$all_cat_params = array_filter( array(
					'pdf_year'  => $current_year,
					'pdf_month' => $current_month,
				) );
				$all_cat_url = add_query_arg( $all_cat_params, $current_page_url );
				?>
				<li class="pml-cat-item <?php echo empty( $current_cat ) ? 'is-active' : ''; ?>">
					<a href="<?php echo esc_url( $all_cat_url ); ?>">
						<span class="pml-cat-name"><?php _e( 'All Categories', 'pdf-manager-lite' ); ?></span>
						<span class="pml-cat-count"><?php echo esc_html( $total_published ); ?></span>
					</a>
				</li>
				<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
					<?php foreach ( $categories as $cat ) : ?>
						<?php
						$cat_params = array_filter( array(
							'pdf_cat'   => $cat->slug,
							'pdf_year'  => $current_year,
							'pdf_month' => $current_month,
						) );
						$cat_url   = add_query_arg( $cat_params, $current_page_url );
						$is_active = ( $current_cat === $cat->slug );
						?>
						<li class="pml-cat-item <?php echo $is_active ? 'is-active' : ''; ?>">
							<a href="<?php echo esc_url( $cat_url ); ?>">
								<span class="pml-cat-name"><?php echo esc_html( $cat->name ); ?></span>
								<span class="pml-cat-count"><?php echo esc_html( $cat->count ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>

	</aside>

	<!-- Main Content Area -->
	<main class="pml-main-content">
		<?php if ( $query_obj->have_posts() ) : ?>
			<div class="pml-grid">
				<?php while ( $query_obj->have_posts() ) : $query_obj->the_post(); ?>
					<?php
					$file_url = pml_get_file_url( get_the_ID() );
					$file_id  = get_post_meta( get_the_ID(), '_pml_file_id', true );
					?>
					<a class="pml-card" href="<?php the_permalink(); ?>">
						<div class="pml-card-icon" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium' ); ?>
							<?php elseif ( $file_id && ( $att_img = wp_get_attachment_image( $file_id, 'medium' ) ) ) : ?>
								<?php echo $att_img; ?>
							<?php elseif ( $file_url ) : ?>
								<canvas class="pml-pdf-canvas" data-pdf-url="<?php echo esc_url( $file_url ); ?>"></canvas>
								<span class="pml-pdf-icon pml-pdf-fallback" style="display:none;">PDF</span>
							<?php else : ?>
								<span class="pml-pdf-icon">PDF</span>
							<?php endif; ?>
						</div>
						<div class="pml-card-body">
							<h3 class="pml-card-title"><?php the_title(); ?></h3>
							<div class="pml-card-meta">
								<span class="pml-card-date"><?php echo esc_html( get_the_date() ); ?></span>
								<?php
								$terms = get_the_terms( get_the_ID(), 'pdf_category' );
								if ( $terms && ! is_wp_error( $terms ) ) :
									?>
									<span class="pml-card-cats">
										<?php echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<div class="pml-pagination">
				<?php
				$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
				echo paginate_links( array(
					'total'   => $query_obj->max_num_pages,
					'current' => $paged,
					'base'    => add_query_arg( 'paged', '%#%' ),
					'format'  => '',
				) );
				?>
			</div>

		<?php else : ?>
			<p class="pml-no-results"><?php _e( 'No resources match those filters.', 'pdf-manager-lite' ); ?></p>
		<?php endif; ?>
	</main>

</div>


