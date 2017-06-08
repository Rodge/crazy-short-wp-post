<?php
/*
Plugin Name: Crazy Short Post

Description: Loops through each WP post type and shows in a list the title and content. 
Options are how many posts to be displayed (max), sorting by Date and Type (Post/Page) and a 
"Reset to Default" button that resets the settings to default 
(5 posts, [Sort by] <Date or Title>, [Filter by] <Post or Page>). 
The chosen options are stored in a database the WP way. 

Author: Roger Stein Grading
*/

//define plugin defaults
DEFINE( "RCSP_MAXPOSTS"		, "5"			);
DEFINE( "RCSP_ORDERBY"		, "post_date"	);
DEFINE( "RCSP_POST_TYPE"	, "post"		);
DEFINE( "RCSP_LISTSTART"	, "<ul>"		);
DEFINE( "RCSP_LISTEND"		, "</ul>"		);
DEFINE( "RCSP_ITEMSTART"	, "<li>"		);
DEFINE( "RCSP_ITEMEND"		, "</li>"		);    

// Tells wordpress to register the Crazy Short Post shortcode
add_shortcode( "rsg-crazy-short-post", "rcsp_handler" );
$options;

function rcsp_handler ( $sc_attributes ) {
	//process incoming attributes assigning defaults if required
  	$sc_attributes = shortcode_atts( 
  		array(
	    	"maxposts" 	=> RCSP_MAXPOSTS	,
	    	"orderby" 	=> RCSP_ORDERBY		,
	    	"post_type" => RCSP_POST_TYPE	,
	    	"liststart" => RCSP_LISTSTART	, 
	    	"listend" 	=> RCSP_LISTEND		,           
	    	"itemstart" => RCSP_ITEMSTART	,
	    	"itemend" 	=> RCSP_ITEMEND            
	  	), 
	  	$sc_attributes 
	);
  	//run function that actually does the work of the plugin
  	$rcsph_output = rcsp_function( $sc_attributes );
  	//send back text to replace shortcode in post
  	return $rcsph_output;
}

function rcsp_function ( $fromHandler ) {
	global $options;
	$options = rscp_getOptions( $fromHandler );

	rscp_displayForms();

	global $post;
	$args = array( 	
		"posts_per_page" 	=> $options[ "maxposts"  ]	, 
		"order" 			=> "ASC"					, 
		"orderby" 			=> $options[ "orderby"   ]	, 
		"post_type" 		=> $options[ "post_type" ] 	,
		"post_status"  		=> "publish" 
	);

	$postslist = get_posts( $args );

	$rcsp_output = 
	"<ul>";
	foreach ( $postslist as $post ) {
	  	setup_postdata( $post ); 
		$rcsp_output .= rscp_outputToAppend( $post );
	}
	$rcsp_output .= 
	"</ul>"; 
	wp_reset_postdata( );

  	return $rcsp_output;
}

function rscp_getOptions ( $fromHandler ) {
	$options;
	if ( isset( $_POST[ "rscp_maxposts" ] ) ) { // In the POST, maxposts is always set also (and only) if orderby or post_type is set. 
		$options = array(
			"maxposts"	=> $_POST[ "rscp_maxposts"  ] ,
			"orderby"	=> $_POST[ "rscp_orderby"   ] ,
			"post_type" => $_POST[ "rscp_post_type" ]
		);
	} elseif ( ! ($options = get_option( "options" )) ) {
		$options = $fromHandler;
	}

	update_option( "options", $options );

	return $options;
}

function rscp_displayForms ( ) {
	// Good to know (after hours of debugging and googling) that using prefix on names make this work...!
	echo "
		<select name='rscp_maxposts' form='rscp_criterias'>
			<option value=" 			. (-1) . rcsp_selectedOrNot( 'maxposts'  , (-1)         ) . ">" . "All"   . "</option>";
		for ($i = 5; $i <= 100; $i += 5) { echo "
			<option value=" 			. $i   . rcsp_selectedOrNot( 'maxposts'  , $i           ) . ">" . $i      . "</option>";
	    } echo "
		</select>
		<select name='rscp_orderby' form='rscp_criterias'>
	  		<option value='post_date'"  . 		 rcsp_selectedOrNot( 'orderby'   , 'post_date'  ) . ">" . "Date"  . "</option>
	  		<option value='post_title'" . 		 rcsp_selectedOrNot( 'orderby'   , 'post_title' ) . ">" . "Title" . "</option>
		</select>
		<select name='rscp_post_type' form='rscp_criterias'>
	  		<option value='post'"		.		 rcsp_selectedOrNot( 'post_type' , 'post'       ) . ">" . "Post"  . "</option>
	  		<option value='page'"		.		 rcsp_selectedOrNot( 'post_type' , 'page'       ) . ">" . "Page"  . "</option>
		</select>
		
		<form action='#' method='post' id='rscp_criterias'>
	  		<input type='submit' value='Submit'>
		</form>
		<form action='#' method='post'>
  			<input type='hidden' name='rscp_maxposts' value="	.	RCSP_MAXPOSTS 	.">
  			<input type='hidden' name='rscp_orderby' value="	.	RCSP_ORDERBY 	.">
  			<input type='hidden' name='rscp_post_type' value="	.	RCSP_POST_TYPE 	.">
  			<input type='submit' value='Reset to Default'>
  		</form>
	";
}

function rcsp_selectedOrNot ( $attribute , $value ) {
	global $options;
	return $options[ $attribute ] == $value ? " selected='selected'" : "";
}

function rscp_outputToAppend ( $post ) {
	return "
		<li>" . 
			"<div>" . 
				"<h1>" . $post->post_title   . "</h1>" 	  . 
				"<p>"  . $post->post_content . "</p><br>" . 
			"</div>"   . 
		"</li>"
	;
}

?>
