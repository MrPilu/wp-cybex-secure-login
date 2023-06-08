(function($) {
	$(document).ready( function() {
		var product = $( '.cbx_product_box' ),
			max = 0;
		$( product ).each( function () {
			if ( $( this ).height() > max )
				max = $( this ).height();
		});		
		$( '.cbx_product_box' ).css( 'height', max + 'px' );

		if ( $( '.cbx-filter' ).length ) {
			var prvPos = $( '.cbx-filter' ).offset().top;
			var maxPos = prvPos + $( '.cbx-products' ).outerHeight() - $( '.cbx-filter' ).outerHeight();

			$( window ).scroll( function() {
				if ( $( window ).width() > 580 ) {
					var scrPos = Number( $( document ).scrollTop() ) + 40;
					if ( scrPos > maxPos ) {
						$( '.cbx-filter' ).removeClass( 'cbx_fixed' );
					} else if ( scrPos > prvPos ) {
						$( '.cbx-filter' ).addClass( 'cbx_fixed' );
					} else {
						$( '.cbx-filter' ).removeClass( 'cbx_fixed' );
					}
				}
			});
		}
		$( '.cbx-menu-item-icon' ).click( function() {
			if ( $( this ).hasClass( 'cbx-active' ) ) {
				$( this ).removeClass( 'cbx-active' );
				$( '.cbx-nav-tab-wrapper, .cbx-help-links-wrapper' ).hide();
			} else {
				$( this ).addClass( 'cbx-active' );
				$( '.cbx-nav-tab-wrapper, .cbx-help-links-wrapper' ).css( 'display', 'inline-block' );
			}
		});
		$( '.cbx-filter-top h2' ).click( function() {
			if ( $( '.cbx-filter-top' ).hasClass( 'cbx-opened' ) ) {
				$( '.cbx-filter-top' ).removeClass( 'cbx-opened' );
			} else {
				$( '.cbx-filter-top' ).addClass( 'cbx-opened' );
			}
		});
		
	});
})(jQuery);
