/**
 * api.js, KV-Webme Themes Repository
 *
 * file for accessing apects of the repository via js
 */

/**
 * add .themesCarousel
 */
(function( $ ){

	var Carousel = {

		themes : {},

		position : 0,

		tempFade : false,

		selector : null,

		// holds the current number of items being displayed
		// - always less than settings.items
		current : 0,

		// how the themes are filtered,
		// should be made into an option at
		// some stage
		filter : 'recent',

		settings : {
			items	: 6,
			rows	: 2,
			repeat	: true,
			fade : false,
			loop : true,
			// shows a select box of methods
			// of filtering the themes by categories
			filterOptions : true,
			// custom display function
			// must increment this.current and
			// this.position
			display : null,
		},

		themesLength : function( ){
			var size = 0, key;

			for ( key in this.themes )
				++size;

			return size;
		},

		mergeThemes : function( new_themes ){
	    var length = this.themesLength( );

  	  for( var key in new_themes ){ 
    	  this.themes[ length ] = new_themes[ key ];
      	++length;
	    } 
		},

		search : function( value ){
			alert( value );
		},

		filterBy : function( by ){ // changes how themes are filtered
			this.themes = {};
			this.filter = by;
			this.tempFade = true;
			this.displayNext( );
		},

		getThemes : function( ){
			$.ajax({
				url : '/ww.incs/proxy.php?url=http://kvweb.me/ww.plugins/themes-api/'
//				url : '/ww.plugins/themes-api/'
				+ 'api.php?' + this.filter + '=true&count=' + this.settings.items
				+ '&start=' + this.themesLength( ),
				success : function( complete ){
					Carousel.mergeThemes( complete );
					Carousel.displayNext( );
				},
				error : function( msg ){
					if( msg.responseText == 'none' ){
						Carousel.position = 0;
						Carousel.displayNext( );
					}
				},
				dataType : 'json'
			});
		}, 	 

		displayThemes : function( ){ 

			// this should be incremented in a custom display function
			this.current = 0;

			// allow for custom display function
			if( typeof( this.settings.display ) == 'function' )
				return this.settings.display( );

			var html = '<div class="themes-container"><table><tr>';

	    for( var i = 0; i < this.settings.items; ++i ){

				if( this.themes[ this.position ] == undefined ) 
	        break;

				if( i % ( this.settings.items / this.settings.rows ) === 0 )
					html += '</tr><tr>';

				var middle = ( !( ( i - 1 ) % this.settings.items ) ) ?
					' middle' :
					'';

				html += '<td class="theme-container' + middle + '">'
				+ '<h2><a href="http://kvweb.me">' + this.themes[ this.position ].name + '</a></h2>'
				+ '<a href="http://kvweb.me/"><img src="' + this.themes[ this.position ].screenshot + '" width="240px" height="172px"/>'
				+ '<p>' + this.themes[ this.position ].short_description + '</p>'
				+ '<p><a href="http://kvweb.me/">Read More..</a></p>'
				+ '</td>';

				++this.position;
				++this.current;

			}   

			html += '</tr></table></div>';

			return html;
		},

		displayNext : function( ){
      if( this.position >= this.themesLength( ) )
        this.getThemes( );
      else{  
				if( this.settings.fade == true || this.tempFade == true ){
					this.fadeNext( );
					this.tempFade = false;
				}
				else
					this.slideNext( );
			} 
		}, 

		fadeNext : function( ){
			var html = this.displayThemes( );
			$( '.slider', this.selector )
				.css({ 'display' : 'none' })
				.html( html )
				.fadeIn( 'slow' );
		}, 

		slideNext : function( ){
			var html = this.displayThemes( );
			$( '.themes-container', this.selector ).css({ 'left' : '-850px' });
			$( '.slider', this.selector )
				.append( $( html ).css({ 'left' : '0' }) )
				.css({ 'left' : '850px' } ) 
				.animate({ 'left' : '0' }, 2000, function( ){
					$( '.slider .themes-container:first', this.selector ).remove( );
				});  
		}, 

		displayPrevious : function( ){
			// { if position = 0
			if( this.position == this.settings.items ){
				if( this.settings.loop == true )
					this.position = this.themesLength( ) - this.current;
				else
					return;
			}
			else
				this.position -= ( this.current + this.settings.items );
			// }

			if( this.settings.fade == true )
				this.fadeNext( );
			else
				this.slidePrevious( );
		},

		slidePrevious : function( ){
	    var html = this.displayThemes( );
  	  $( '.themes-container', this.selector ).css({ 'left' : '850px' }); 
    	$( '.slider', this.selector )
      	.prepend( $( html ).css({ 'left' : '0' }) )
	      .css({ 'left' : '-850px' } ) 
  	    .animate({ 'left' : '0' }, 2000, function( ){   
    	    $( '.slider .themes-container:last', this.selector ).remove( );
      	}); 
		},

		init : function( ){

			// fade in the first time
			this.tempFade = true;
			this.displayNext( );

			// { add some css rules
			var height = ( 340 * this.settings.rows ) + 'px';
	    $( this.selector ).css({ 'overflow-x' : 'hidden', 'width' : '850px' }); 

	    $( this.selector ).html(
				'<div class="themes-carousel-wrapper">'
				+ '<div class="themes-carousel-options"></div>'
				+ '<div style="position:relative;height:'
				+ height + '" class="slider">'
					+ '<br style="clear:both"/>'
				+ '</div></div>'
			);

			if( this.settings.filterOptions ){
				var filter = '<select name="themes_filter">'
					+ '<option value="recent" selected="selected">Recently Added</option>'
					+ '<option value="downloads">Most Downloads</option>'
					+ '<option value="rating">Highest Rated</option>'
				+ '</select>';

				$( '.themes-carousel-options' ).html( filter );

				$( 'select[name="themes_filter"]' ).click(function( ){
					var value = $( this ).val( );
					Carousel.filterBy( value );
				});
			}
			// }
		} 

	}

	$.fn.themesCarousel = function( options, value ){

		if( typeof( options ) == 'string' ){
			switch( options ){
				case 'next':
					Carousel.displayNext( );
				break;
				case 'previous':
					Carousel.displayPrevious( );
				break;
				case 'search':
					if( value != null )
						Carousel.search( value );
				break;
			}
		}
		else{
			$.extend( this.settings, options );
			Carousel.selector = this.selector;
			Carousel.init( );
		}

		return this;

	}

})( jQuery );

