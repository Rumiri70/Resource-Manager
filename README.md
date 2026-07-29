# Resources Manager - WordPress Plugin

A lightweight, high-performance WordPress plugin to manage resource documents (PDFs) with categories, date filtering, first-page PDF cover previews, and shortcode grid embedding for any page slug.

## Features

- **Resources Manager Admin Interface**: Dedicated custom post type and menu in WP Admin with category management and PDF file upload workflow.
- **Sidebar & Grid Layout**: Clean 2-column sidebar layout featuring:
  - **Date Filters (Top)**: Filter resources by Year and Month with an explicit *Apply Filter* action.
  - **Category Navigation List (Below)**: Vertical list with live resource counts. Clicking any category automatically filters to display files for that category immediately.
- **Automatic PDF 1st-Page Thumbnail Previews**: For entries without a custom featured image thumbnail, the plugin automatically renders Page 1 of the attached PDF document using client-side canvas rendering (PDF.js).
- **Shortcode Support (`[resources_manager]`)**: Easily embed the resources grid on any WordPress page slug (e.g. `/resources/`, `/document-library/`, etc.).
- **Responsive & Accessible**: Desktop 2-column grid layout, auto-stacking on mobile devices.

## Shortcodes

Place any of the following shortcodes inside your WordPress page content:

- `[resources_manager]` - Renders the resources grid with sidebar filters.
- `[resources_grid]` - Alias for resources grid.
- `[pdf_archive]` - Backward-compatible shortcode alias.

## Installation

1. Download or clone this repository into your WordPress plugins directory (`/wp-content/plugins/Resources-manager`).
2. Activate **Resources Manager** through the **Plugins** menu in WordPress.
3. Go to **Resources Manager > Add New Resource** to upload your PDF documents and assign categories.
4. Add `[resources_manager]` to any WordPress page.

## License

GPLv2 or later.
