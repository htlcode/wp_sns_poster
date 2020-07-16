(function($) {
	if (typeof json_posts !== 'undefined') {
  		$( "#url" ).autocomplete({
	      minLength: 0,
	      source: json_posts,
	      focus: function( event, ui ) {
	        $( "#url" ).val( ui.item.label );
	        return false;
	      },
	      select: function( event, ui ) {
	        $( "#url" ).val( ui.item.permalink );
	        return false;
	      } 
	    });
	}
})( jQuery );