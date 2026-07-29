<?php
/**
 * Single template for an individual PDF Document.
 * Displays it like a normal post, with the PDF embedded inline.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$file_url = pml_get_file_url( get_the_ID() );
	$terms    = get_the_terms( get_the_ID(), 'pdf_category' );
	?>

	<main id="pml-main" class="pml-main pml-single">
		<article <?php post_class( 'pml-single-article' ); ?>>

			<header class="pml-single-header">
				<p class="pml-back-link">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'pdf_doc' ) ); ?>">&larr; <?php _e( 'Back to Resources', 'pdf-manager-lite' ); ?></a>
				</p>
				<h1 class="pml-single-title"><?php the_title(); ?></h1>
				<div class="pml-single-meta">
					<span class="pml-single-date"><?php echo esc_html( get_the_date() ); ?></span>
					<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
						<span class="pml-single-cats">
							<?php
							foreach ( $terms as $term ) {
								echo '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a> ';
							}
							?>
						</span>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( get_the_content() ) : ?>
				<div class="pml-single-description">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $file_url ) : ?>
				<div class="pml-embed-wrap">
					<iframe class="pml-embed-frame" src="<?php echo esc_url( $file_url ); ?>" title="<?php the_title_attribute(); ?>" loading="lazy"></iframe>
				</div>
				<p class="pml-download">
					<a class="pml-btn" href="<?php echo esc_url( $file_url ); ?>" download><?php _e( 'Download PDF', 'pdf-manager-lite' ); ?></a>
				</p>
			<?php else : ?>
				<p class="pml-no-file"><?php _e( 'No PDF file has been attached to this entry yet.', 'pdf-manager-lite' ); ?></p>
			<?php endif; ?>

		</article>
	</main>

<?php endwhile; ?>

<?php
get_footer();
