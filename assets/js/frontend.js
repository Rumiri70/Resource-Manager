( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		// PDF First Page Renderer using PDF.js
		if ( typeof pdfjsLib !== 'undefined' ) {
			if ( typeof PML_Settings !== 'undefined' && PML_Settings.workerUrl ) {
				pdfjsLib.GlobalWorkerOptions.workerSrc = PML_Settings.workerUrl;
			}

			var canvases = document.querySelectorAll( '.pml-pdf-canvas' );
			canvases.forEach( function ( canvas ) {
				var pdfUrl = canvas.getAttribute( 'data-pdf-url' );
				if ( ! pdfUrl ) {
					return;
				}

				var loadingTask = pdfjsLib.getDocument( pdfUrl );
				loadingTask.promise.then( function ( pdf ) {
					return pdf.getPage( 1 );
				} ).then( function ( page ) {
					var container = canvas.parentElement;
					var containerWidth = container ? container.clientWidth : 230;
					var unscaledViewport = page.getViewport( { scale: 1 } );
					var scale = ( containerWidth > 0 ? containerWidth : 230 ) / unscaledViewport.width;
					var viewport = page.getViewport( { scale: scale } );

					canvas.width = viewport.width;
					canvas.height = viewport.height;

					var context = canvas.getContext( '2d' );
					var renderContext = {
						canvasContext: context,
						viewport: viewport
					};

					return page.render( renderContext ).promise;
				} ).catch( function ( err ) {
					console.warn( 'PDF preview render failed for:', pdfUrl, err );
					canvas.style.display = 'none';
					var fallback = canvas.parentElement ? canvas.parentElement.querySelector( '.pml-pdf-fallback' ) : null;
					if ( fallback ) {
						fallback.style.display = 'inline-block';
					}
				} );
			} );
		}
	} );
} )();


