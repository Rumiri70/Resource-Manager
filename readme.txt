=== Resources Manager ===
Contributors: sibasi
Tags: resources, pdf, documents, archive, category, filter, shortcode
Requires at least: 5.5
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

A lightweight plugin for managing resources (PDF documents) with categories, filterable shortcode grids, and automatic PDF first page previews.

== Description ==

**Admin side**
* Adds a "Resources Manager" menu with a custom post type for Resources.
* Each entry has a title, optional description, featured image (optional custom thumbnail),
  one or more Resource Categories, and a PDF file picked from the Media Library.

**Visitor side**
* Shortcode `[resources_manager]` (or `[resources_grid]` / `[pdf_archive]`) can be placed on any page slug to render the filterable resources grid.
* An automatic archive page at `/resources/` lists every published resource as a card grid.
* Visitors can filter by Resource Category, Year, and Month.
* Automatically renders Page 1 of the PDF document as the thumbnail preview if no custom featured image is uploaded.
* Clicking a resource opens its single page with inline PDF embed and download button.

== Installation ==

1. Upload the `Resources-manager` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Resources Manager > Add New Resource to upload your first PDF.
4. Place `[resources_manager]` on any page slug (e.g. `/resources`).

== Shortcodes ==

* `[resources_manager]` - Renders the resources grid with category & date filters.
* `[resources_grid]` - Alias for resources manager grid.
* `[pdf_archive]` - Backward-compatible shortcode alias.

