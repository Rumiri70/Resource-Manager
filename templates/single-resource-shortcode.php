<?php
/**
 * Template partial: single resource shortcode.
 * Used by [single_resource] and [resource].
 *
 * Expected variables injected by handler:
 *   $post_obj         WP_Post - the resource post object
 *   $file_url         string  - attachment URL for the PDF
 *   $file_id          int     - attachment ID
 *   $terms            array   - array of WP_Term objects
 *   $display          string  - 'card' or 'embed'
 *   $show_description bool    - whether to show description
 *   $show_download    bool    - whether to show download buttons
 *   $show_meta        bool    - whether to show date/category pills
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$permalink = get_permalink( $post_obj->ID );
?>

<?php if ( 'embed' === $display ) : ?>

	<!-- Embed Mode: Full Viewer with Details -->
	<div class="pml-single-embed-block">
		<header class="pml-single-header">
			<h3 class="pml-single-title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_obj->ID ) ); ?></a>
			</h3>

			<?php if ( $show_meta ) : ?>
				<div class="pml-single-meta-bar">
					<span class="pml-pill pml-pill-date">
						<svg class="pml-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
						<?php echo esc_html( get_the_date( '', $post_obj->ID ) ); ?>
					</span>
					<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
						<?php foreach ( $terms as $term ) : ?>
							<a class="pml-pill pml-pill-cat" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
								<svg class="pml-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
								<?php echo esc_html( $term->name ); ?>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( $show_description && ! empty( $post_obj->post_content ) ) : ?>
			<div class="pml-single-description">
				<?php echo wp_kses_post( apply_filters( 'the_content', $post_obj->post_content ) ); ?>
			</div>
		<?php endif; ?>

		<section class="pml-single-file-section">
			<?php if ( $file_url ) : ?>
				<div class="pml-embed-wrap">
					<iframe class="pml-embed-frame" src="<?php echo esc_url( $file_url ); ?>" title="<?php echo esc_attr( get_the_title( $post_obj->ID ) ); ?>" loading="lazy"></iframe>
				</div>

				<?php if ( $show_download ) : ?>
					<div class="pml-file-actions">
						<a class="pml-btn pml-btn-download" href="<?php echo esc_url( $file_url ); ?>" download>
							<svg class="pml-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
							<?php esc_html_e( 'Download PDF', 'pdf-manager-lite' ); ?>
						</a>
						<a class="pml-btn pml-btn-secondary" href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer">
							<svg class="pml-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
							<?php esc_html_e( 'Open in New Tab', 'pdf-manager-lite' ); ?>
						</a>
						<a class="pml-btn pml-btn-back" href="<?php echo esc_url( $permalink ); ?>">
							<?php esc_html_e( 'View Resource Page &rarr;', 'pdf-manager-lite' ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="pml-no-file-notice">
					<p><?php esc_html_e( 'No PDF file has been attached to this entry yet.', 'pdf-manager-lite' ); ?></p>
				</div>
			<?php endif; ?>
		</section>
	</div>

<?php else : ?>

	<!-- Card Mode: Standalone Interactive Resource Card -->
	<div class="pml-single-card-block">
		<div class="pml-single-card-inner">
			<div class="pml-single-card-preview">
				<?php if ( has_post_thumbnail( $post_obj->ID ) ) : ?>
					<?php echo get_the_post_thumbnail( $post_obj->ID, 'medium' ); ?>
				<?php elseif ( $file_id && ( $att_img = wp_get_attachment_image( $file_id, 'medium' ) ) ) : ?>
					<?php echo wp_kses_post( $att_img ); ?>
				<?php elseif ( $file_url ) : ?>
					<canvas class="pml-pdf-canvas" data-pdf-url="<?php echo esc_url( $file_url ); ?>"></canvas>
					<span class="pml-pdf-icon pml-pdf-fallback" style="display:none;">PDF</span>
				<?php else : ?>
					<span class="pml-pdf-icon">PDF</span>
				<?php endif; ?>
			</div>

			<div class="pml-single-card-content">
				<?php if ( $show_meta ) : ?>
					<div class="pml-single-meta-bar">
						<span class="pml-pill pml-pill-date">
							<svg class="pml-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
							<?php echo esc_html( get_the_date( '', $post_obj->ID ) ); ?>
						</span>
						<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
							<?php foreach ( $terms as $term ) : ?>
								<a class="pml-pill pml-pill-cat" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
									<?php echo esc_html( $term->name ); ?>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<h3 class="pml-single-card-title">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_obj->ID ) ); ?></a>
				</h3>

				<?php if ( $show_description && ! empty( $post_obj->post_content ) ) : ?>
					<div class="pml-single-card-excerpt">
						<?php echo esc_html( wp_trim_words( strip_shortcodes( $post_obj->post_content ), 28 ) ); ?>
					</div>
				<?php endif; ?>

				<div class="pml-single-card-actions">
					<?php if ( $file_url && $show_download ) : ?>
						<a class="pml-btn pml-btn-download" href="<?php echo esc_url( $file_url ); ?>" download>
							<svg class="pml-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
							<?php esc_html_e( 'Download PDF', 'pdf-manager-lite' ); ?>
						</a>
					<?php endif; ?>

					<a class="pml-btn pml-btn-secondary" href="<?php echo esc_url( $permalink ); ?>">
						<?php esc_html_e( 'View Details &rarr;', 'pdf-manager-lite' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

<?php endif; ?>
