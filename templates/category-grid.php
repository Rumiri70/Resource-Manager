<?php
/**
 * Template partial: category grid.
 * Used exclusively by the [resources_category] shortcode (pml_shortcode_category).
 *
 * Expected variables injected by the shortcode handler:
 *   $pml_cat_query  WP_Query  - the pre-built query.
 *   $term           WP_Term   - the matched pdf_category term.
 *   $columns        int       - number of CSS grid columns (1-4).
 *   $show_title     bool      - whether to render the category heading.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pml-category-section pml-columns-<?php echo esc_attr( $columns ); ?>">

	<?php if ( $show_title ) : ?>
		<h2 class="pml-category-heading"><?php echo esc_html( $term->name ); ?></h2>
	<?php endif; ?>

	<?php if ( $pml_cat_query->have_posts() ) : ?>
		<div class="pml-grid pml-cat-grid">
			<?php while ( $pml_cat_query->have_posts() ) : $pml_cat_query->the_post(); ?>
				<?php
				$file_url = pml_get_file_url( get_the_ID() );
				$file_id  = get_post_meta( get_the_ID(), '_pml_file_id', true );
				$terms    = get_the_terms( get_the_ID(), 'pdf_category' );
				?>
				<a class="pml-card" href="<?php the_permalink(); ?>">

					<div class="pml-card-icon" aria-hidden="true">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium' ); ?>
						<?php elseif ( $file_id && ( $att_img = wp_get_attachment_image( $file_id, 'medium' ) ) ) : ?>
							<?php echo wp_kses_post( $att_img ); ?>
						<?php elseif ( $file_url ) : ?>
							<canvas class="pml-pdf-canvas" data-pdf-url="<?php echo esc_url( $file_url ); ?>"></canvas>
							<span class="pml-pdf-icon pml-pdf-fallback" style="display:none;">PDF</span>
						<?php else : ?>
							<span class="pml-pdf-icon">PDF</span>
						<?php endif; ?>
					</div>

					<div class="pml-card-body">
						<h3 class="pml-card-title"><?php echo esc_html( get_the_title() ); ?></h3>
						<div class="pml-card-meta">
							<span class="pml-card-date"><?php echo esc_html( get_the_date() ); ?></span>
							<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
								<span class="pml-card-cats">
									<?php echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>

				</a>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<p class="pml-no-results">
			<?php esc_html_e( 'No resources found in this category.', 'pdf-manager-lite' ); ?>
		</p>
	<?php endif; ?>

</div>
