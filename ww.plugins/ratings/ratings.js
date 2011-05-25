/**
 * ratings.js, KV-Webme Ratings Plugin
 *
 * allows for item ratings using the following syntax:
 *
 * <div id="ITEM_ID" type="ITEM_TYPE(optional)"></div>
 *
 * $( "ITEM_ID" ).ratings( );
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

(function( $ ){

	// { methods
	var methods = {

		selector : null,
		tooltipTimeout	: false,
		settings : {
			saveRemotely : '',
		},

		// { init method
		init : function( ){
			
			methods.selector = this;

			// { set up each item to be rated
			this.each( function( ){
				var $this = $( this );
				var data = $this.data( 'ratings' );

				if( !data ){

					$this.data( 'ratings', {
						selector:	$this,
						name		: $this.attr( 'id' ),
						type		: $this.attr( 'type' )
					});

				}

				methods.writeElements( $this );
				$this.append(
					'<a class="rateme" style="display:inline;margin-left:4px" type="' 
					+ $this.data( 'ratings' ).type + '" name="' 
					+ $this.data( 'ratings' ).name + '">Rate Me</a>'
				);

				methods.getRating( $this.data( 'ratings' ).name );

			});
			// }

      // { add general rating events

				$( '.ratings-wrapper' ).hover( function( ){
					clearTimeout( methods.tooltipTimeout );
					methods.tooltip( $( this ) );
					$.get( methods.settings.saveRemotely + '/ww.plugins/ratings/info.php',
						{ 'name' : $( this ).parent( ).data( 'ratings' ).name },
						function( html ){
							$( '#ratings-tooltip' ).html( html );
						}
					);
				}, function( ){
					methods.tooltipTimeout = setTimeout( 
						methods.tooltipLeave,
						1000
					);
				});

        // { star click, mouseenter, mouseleave
        $( '#rateme-dialog .star' ).live( {
          click : function( ){
						var stars = $( '#rateme-dialog .star' );
            methods.opacityAll( 4, 0.4, stars );
            methods.opacityAll( $( this ).index( ), 1, stars );
						var name = $( '#rateme-dialog' ).attr( 'name' );
            methods.saveRating(
							$( '#' + name ),
							$( this ).index( )
						);
						$( '#rateme-dialog' ).dialog( 'close' );
						methods.refresh( );
           },
          mouseenter : function( ){
            methods.opacityAll( 
							$( this ).index( ),
							1,
							$( '#rateme-dialog .star' )
						);
          },
          mouseleave : function( ){
            methods.opacityAll( 
							4, 
							0.4, 
							$( '#rateme-dialog .star' )
						);
          }
         });
         // }

        // { rate me click event
        $( '.rateme' ).click( function( ){
					$( '#rateme-dialog' ).remove( );
					var name = $( this ).parent( ).data( 'ratings' ).name;
          $( 'body' ).append(
						'<div id="rateme-dialog" title="Rate Me" name="'
						+ name + '"></div>'
					);
          methods.writeElements( $( '#rateme-dialog' ) );
          $( '#rateme-dialog' ).dialog({
            resizable : false,
            height : 140,
            modal : true
          }); 
        }); 
         // }
      // }


		},
		// }

		// { refresh
		refresh : function( ){

			methods.selector.each( function( ){
				methods.getRating( $( this ).data( 'ratings' ).name );
			});

		},
		// }

		// { tooltip
		tooltip : function( $this ){

			// remove previous tooltip if present
			$( '#ratings-tooltip' ).remove( );

			$( 'body' ).prepend( '<div id="ratings-tooltip" style="background:#fff;'
				+ 'border:1px solid #000;height:40px;border-radius:5px;padding:4px;'
				+ 'z-index:100;position:absolute;display:none"><img src="/ww.plugins'
				+ '/ratings/i/loading.gif"/></div>'
			);
	
			var tip = $( '#ratings-tooltip' );
			var topOffset = tip.height( );
			var x = ( $this.offset( ).left ) + 'px';  
			var y = ( $this.offset( ).top + topOffset - 20 ) + 'px';
			tip.css({ 'top' : y, 'left' : x });

			tip.fadeIn( 'fast' );

			// { fix some browser's mouseleave bug
			var t = setTimeout( methods.tooltipLeave, 2000 );
			$( document ).bind( 'mousemove.ratings', function( ){
				clearTimeout( t );
				t = setTimeout( methods.tooltipLeave, 2000 );
			});
			// }
		},
		// }

		// { tooltipLeave
		tooltipLeave : function( ){

			$( document ).unbind( 'mousemove.ratings' );	
			$( '#ratings-tooltip' ).fadeOut( 'fast' );			

		},
		// }

    // { saveRating
    saveRating : function( $this, index ){
      $.get( methods.settings.saveRemotely + '/ww.plugins/ratings/save.php', {
          'name' : $this.data( 'ratings' ).name,
          'type' : $this.data( 'ratings' ).type,
          'rating' : index
        }
      );
    },  
    // }

    // { getRating
    getRating : function( id ){
      $.get( methods.settings.saveRemotely + '/ww.plugins/ratings/get_rating.php', {
          'name' : id
        },  
        function( rating ){
          if( rating == 'login' )
            $( '#' + id ).html( '<a href="/_r?type=loginpage"><i>login to rate this item</i></a>' );
          else if( rating != 0 ){ 
            methods.opacityAll(
							parseInt( rating ),
							1,
							$( '.star', '#' + id )
						);  
					}
        }   
      );
    },  
    // }

    // { opactiyAll
    opacityAll : function( index, value, stars ){
      stars.each( function( i ){

        $( this ).css({
          'opacity' : value,
          'filter' : 'alpha( opacity=' + ( value * 100 ) + ' )'
        }); 

        if( index == i ) 
          return false;
    
      }); 
    },  
    // }

    // { writeElements
    writeElements : function( sel ){

      var html = '<div class="ratings-wrapper" style="display:inline-block">'
        + '<div class="stars">'
        + '<img src="/ww.plugins/ratings/i/star.gif" class="star"/>'
        + '<img src="/ww.plugins/ratings/i/star.gif" class="star"/>'
        + '<img src="/ww.plugins/ratings/i/star.gif" class="star"/>'
        + '<img src="/ww.plugins/ratings/i/star.gif" class="star"/>'
        + '<img src="/ww.plugins/ratings/i/star.gif" class="star"/>'
        + '</div>';

      $( sel ).html( html );

      $( '.star' ).css({ 'opacity' : .4, 'filter' : 'alpha( opacity=40 )' }); 

    }   
    // }

	};

	// { $.fn.ratings
	$.fn.ratings = function( options ){

		$.extend( methods.settings, options );

		methods.init.apply( this );	

		return this;
  }	
	// }

})( jQuery );
