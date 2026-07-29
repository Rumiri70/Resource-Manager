<?php
/**
 * Archive template for Resource Documents.
 * Used automatically at /resources/ (and /resource-category/{slug}/) unless the
 * active theme provides its own archive-pdf_doc.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="pml-main" class="pml-main">
	<header class="pml-archive-header">
		<h1><?php is_tax( 'pdf_category' ) ? single_term_title() : _e( 'Resources', 'pdf-manager-lite' ); ?></h1>
	</header>

	<?php include PML_PATH . 'templates/archive-content.php'; ?>
</main>

<?php
get_footer();

