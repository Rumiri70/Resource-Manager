<?php
/**
 * Single template for an individual PDF Document.
 * Displays it with a clean layout, proper content hierarchy, and a responsive related resources sidebar.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$current_id = get_the_ID();
	$file_url   = pml_get_file_url( $current_id );
	$terms      = get_the_terms( $current_id, 'pdf_category' );
	$term_ids   = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'term_id' ) : array();

	// Query Related Resources (same category if available, excluding current post)
	$related_args = array(
		'post_type'      => 'pdf_doc',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'post__not_in'   => array( $current_id ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( ! empty( $term_ids ) ) {
		$related_args['tax_query'] = array(
			array(
				'taxonomy' => 'pdf_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			),
		);
	}

	$related_query = new WP_Query( $related_args );
	?>

	<main id="pml-main" class="pml-main pml-single">
		<div class="pml-single-layout">

			<!-- Main Content Column -->
			<article <?php post_class( 'pml-single-article' ); ?>>

				<header class="pml-single-header">
					<h1 class="pml-single-title"><?php echo esc_html( get_the_title() ); ?></h1>

					<!-- 1. Metadata Pills Row: Date & Categories -->
					<div class="pml-single-meta-bar">
						<span class="pml-pill pml-pill-date" title="<?php esc_attr_e( 'Published Date', 'pdf-manager-lite' ); ?>">
							<svg class="pml-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
							<?php echo esc_html( get_the_date() ); ?>
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
				</header>

				<!-- 2. Resource Description / Details -->
				<?php if ( get_the_content() ) : ?>
					<div class="pml-single-description">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>

				<!-- 3. File Display (Embedded Viewer & Action Bar) -->
				<section class="pml-single-file-section">
					<?php if ( $file_url ) : ?>
						<div class="pml-embed-wrap">
							<iframe class="pml-embed-frame" src="<?php echo esc_url( $file_url ); ?>" title="<?php the_title_attribute(); ?>" loading="lazy"></iframe>
						</div>
						<div class="pml-file-actions">
							<a class="pml-btn pml-btn-download" href="<?php echo esc_url( $file_url ); ?>" download>
								<svg class="pml-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
								<?php esc_html_e( 'Download PDF', 'pdf-manager-lite' ); ?>
							</a>
							<a class="pml-btn pml-btn-secondary" href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer">
								<svg class="pml-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
								<?php esc_html_e( 'Open in New Tab', 'pdf-manager-lite' ); ?>
							</a>
						</div>
					<?php else : ?>
						<div class="pml-no-file-notice">
							<p><?php esc_html_e( 'No PDF file has been attached to this entry yet.', 'pdf-manager-lite' ); ?></p>
						</div>
					<?php endif; ?>
				</section>

				<!-- 4. Back to Resources Navigation -->
				<footer class="pml-single-footer">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'pdf_doc' ) ); ?>" class="pml-btn pml-btn-back">
						&larr; <?php esc_html_e( 'Back to All Resources', 'pdf-manager-lite' ); ?>
					</a>
				</footer>

			</article>

			<!-- Right Sidebar: Related Resources -->
			<aside class="pml-single-sidebar">
				<div class="pml-related-widget">
					<h3 class="pml-related-title"><?php esc_html_e( 'Related Resources', 'pdf-manager-lite' ); ?></h3>

					<?php if ( $related_query->have_posts() ) : ?>
						<div class="pml-related-list">
							<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
								<?php
								$rel_file_url = pml_get_file_url( get_the_ID() );
								$rel_file_id  = get_post_meta( get_the_ID(), '_pml_file_id', true );
								$rel_terms    = get_the_terms( get_the_ID(), 'pdf_category' );
								?>
								<a class="pml-related-card" href="<?php the_permalink(); ?>">
									<div class="pml-related-thumb" aria-hidden="true">
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'thumbnail' ); ?>
										<?php elseif ( $rel_file_id && ( $att_img = wp_get_attachment_image( $rel_file_id, 'thumbnail' ) ) ) : ?>
											<?php echo wp_kses_post( $att_img ); ?>
										<?php elseif ( $rel_file_url ) : ?>
											<canvas class="pml-pdf-canvas" data-pdf-url="<?php echo esc_url( $rel_file_url ); ?>"></canvas>
											<span class="pml-pdf-icon pml-pdf-fallback" style="display:none;">PDF</span>
										<?php else : ?>
											<span class="pml-pdf-icon">PDF</span>
										<?php endif; ?>
									</div>
									<div class="pml-related-body">
										<h4 class="pml-related-item-title"><?php echo esc_html( get_the_title() ); ?></h4>
										<div class="pml-related-meta">
											<span class="pml-related-date"><?php echo esc_html( get_the_date() ); ?></span>
											<?php if ( $rel_terms && ! is_wp_error( $rel_terms ) ) : ?>
												<span class="pml-cat-pill"><?php echo esc_html( $rel_terms[0]->name ); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</a>
							<?php endwhile; ?>
						</div>
					<?php else : ?>
						<p class="pml-no-related"><?php esc_html_e( 'No other related resources available.', 'pdf-manager-lite' ); ?></p>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>

					<div class="pml-related-all">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'pdf_doc' ) ); ?>" class="pml-view-all-link">
							<?php esc_html_e( 'Browse all resources &rarr;', 'pdf-manager-lite' ); ?>
						</a>
					</div>
				</div>
			</aside>

		</div>
	</main>

<?php endwhile; ?>

<?php
get_footer();

