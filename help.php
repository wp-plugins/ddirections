<?php

function help_ddirections() {
	$id_direcciones = 'settings_page_ddirections/ddirections';
	$screen = get_current_screen();
	if($screen->id != $id_direcciones)
		return;
		
	$screen->set_help_sidebar( __('<p>For more information visit the plugin\'s website: <a href="http://www.hectorgarrofe.com" target="_blank">DDirections</a></p>','ddirections'));
		
	$screen->add_help_tab(array(
		'id' => 'usage',
		'title' => __('How to use it?', 'ddirections'),
		'content' => '
			<h2>'.__('How to use it?','ddirections').'</h2>
			<p>'.__('To use driving directions you have to fill the fields below.<br />Once this is done, you can output the data in the front end with 3 different shortcodes.','ddirections').'</p>
			<strong>[wpmap_map]</strong>
			<p>'.__('Displays the map showing the chosen location. When the user asks for directions the route is shown in this map too.','ddirections').'</p>
			<strong>[wpmap_directions_input]</strong>
			<p>'.__('Displays the form to interact with the map.','ddirections').'</p>
			<strong>[wpmap_directions_container]</strong>
			<p>'.__('Displays the driving directions instructions.','ddirections').'</p>'
	));


	$screen->add_help_tab(array(
		'id' => 'new_tab',
		'title' => __('Styling', 'ddirections'),
		'content' => '
			<h2>'.__('Styling the form','ddirections').'</h2>
			<p>'.__('You can style the form elements as you want using the classes below:','ddirections').'</p>
			<p>'.__('The <strong>form</strong> is inside a div with <em>#directions</em> id.','ddirections').'</p>
			<p>'.__('The <strong>buttons</strong> of the form has the <em>.map-button</em> class.','ddirections').'</p>
			<p>'.__('The <strong>directions</strong> are inside a div with <em>#dir-container</em> id.','ddirections').'</p>
			<p>'.__('The <strong>map</strong> is inside a div with <em>#map-container</em> id.','ddirections').'</p>'
	));


	// to add another tab in the future
	/*
	$screen->add_help_tab(array(
		'id' => 'new_tab',
		'title' => __('Some text', 'ddirections'),
		'content' => '<h2>hello</h2><p>This is another tab</p>'
	));
	*/
}

add_action('admin_head', 'help_ddirections');
?>