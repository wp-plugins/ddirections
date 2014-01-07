<?php
/*
Plugin Name: DDirections
Plugin URI: http://www.hectorgarrofe.com
Description: Driving directions with geolocation and mobile support.
Version: 1.4.1
Author: Hector Garrofe
Author URI: http://www.hectorgarrofe.com
*/


// localize the plugin
function ddirections_lang_init() {
  load_plugin_textdomain( 'ddirections', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
}
add_action('init', 'ddirections_lang_init');

/*
Para poner imagenes en el plugin:
	<?php echo plugins_url(); ?>/ddirections/images/pc-icon.png
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
// ------------------------------------------------------------------------
// THIS IS USEFUL IF YOU REQUIRE A MINIMUM VERSION OF WORDPRESS TO RUN YOUR
// PLUGIN. IN THIS PLUGIN THE WP_EDITOR() FUNCTION REQUIRES WORDPRESS 3.3 
// OR ABOVE. ANYTHING LESS SHOWS A WARNING AND THE PLUGIN IS DEACTIVATED.                    
// ------------------------------------------------------------------------
function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.3", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

// ------------------------------------------------------------------------
// PLUGIN PREFIX:
// ------------------------------------------------------------------------
// A PREFIX IS USED TO AVOID CONFLICTS WITH EXISTING PLUGIN FUNCTION NAMES.
// WHEN CREATING A NEW PLUGIN, CHANGE THE PREFIX AND USE YOUR TEXT EDITORS 
// SEARCH/REPLACE FUNCTION TO RENAME THEM ALL QUICKLY.
// ------------------------------------------------------------------------

// 'posk_' prefix is derived from [p]plugin [o]ptions [s]tarter [k]it

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'posk_add_defaults');
register_uninstall_hook(__FILE__, 'posk_delete_plugin_options');
add_action('admin_init', 'posk_init' );
add_action('admin_menu', 'posk_add_options_page');
add_filter( 'plugin_action_links', 'posk_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'posk_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function posk_delete_plugin_options() {
	delete_option('posk_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'posk_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function posk_add_defaults() {
	$tmp = get_option('posk_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('posk_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	'coordinates' => '41.39, 2.16',
						'infobox_text' => 'default',
						'zoom' => '12',
						'initial_map_type' => 'ROADMAP',
						'icon_url' => ''
		);
		update_option('posk_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'posk_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function posk_init(){
	register_setting( 'posk_plugin_options', 'posk_options', 'posk_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'posk_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function posk_add_options_page() {
	add_options_page('Driving Directions', 'Driving Directions', 'manage_options', 'ddirections', 'posk_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function posk_render_form() {
	?>
	
	<style>
	#configuration-form{
		width: 60%;
		float: left;


	}
	
	.wrap small{
		font-style: italic;
		color: #616161;
	}
	
	.info-image{
		float: left;
		margin-left: 10px;
		margin-top: 20px;
	}

	hr{
		border-bottom: 1px;
	}
	
	#contextual-help-columns{
		background: url('<?php echo plugins_url(); ?>/ddirections/images/hg_logo.png') no-repeat bottom right;
	}
	
	#map-canvas{
		width: 35%;
		height: 300px;
		float: left;
	}
	</style>

	<div class="wrap">

		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Driving Directions</h2>
		<!--<p><?php _e('Fill the fields to configure the plugin.','ddirections'); ?></p>-->

		<!-- Beginning of the Plugin Options Form -->
		<form id="configuration-form" method="post" action="options.php">
			<?php settings_fields('posk_plugin_options'); ?>
			<?php $options = get_option('posk_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
			
				<!-- Coordinates -->
				<tr>
					<th scope="row"><?php _e('Address','ddirections'); ?><br /><small> <?php _e('City names or the 41.39, 2.16 format','ddirections'); ?></small></th>
					<td>
						<input id="coordinates" type="text" size="53" name="posk_options[coordinates]" value="<?php echo $options['coordinates']; ?>" />
					</td>
				</tr>

				<!-- Text Area Control -->
				<tr id="infobox">
					<th scope="row"><?php _e('Infobox text','ddirections'); ?><br /><small><?php _e('Accepts some HTML tags','ddirections'); ?></small></th>
					<td>
						<textarea id="infobox_text" name="posk_options[infobox_text]" rows="3" cols="59" type='textarea'><?php echo $options['infobox_text']; ?></textarea>
					</td>
				</tr>

				<!-- Zoom level -->
				<tr>
					<th scope="row"><?php _e('Zoom level','ddirections'); ?></th>
					<td>
						<input id="zoom_level" type="text" size="20" name="posk_options[zoom]" value="<?php echo $options['zoom']; ?>" />
					</td>
				</tr>

				<!-- Select Drop-Down Control -->
				<tr>
					<th scope="row"><?php _e('Map type','ddirections'); ?></th>
					<td>
						<select id="map_type" name='posk_options[initial_map_type]'>
							<option value='HYBRID' <?php selected('HYBRYD', $options['initial_map_type']); ?>><?php _e('Hybrid','ddirections'); ?></option>
							<option value='ROADMAP' <?php selected('ROADMAP', $options['initial_map_type']); ?>><?php _e('Roadmap','ddirections'); ?></option>
							<option value='SATELLITE' <?php selected('SATELLITE', $options['initial_map_type']); ?>><?php _e('Satellite','ddirections'); ?></option>
							<option value='TERRAIN' <?php selected('TERRAIN', $options['initial_map_type']); ?>><?php _e('Terrain','ddirections'); ?></option>
						</select>
						<span style="color:#666666;margin-left:2px;"><?php _e('The 4 google maps different types','ddirections'); ?></span>
					</td>
				</tr>
				
				<!-- Icon Marker -->
				<tr>
					<th scope="row"><?php _e('Icon marker','ddirections'); ?></th>
					<td>
						<label for="upload_image">
							<input id="upload_image" type="text" size="36" name="posk_options[icon_url]" value="<?php echo $options['icon_url']; ?>" /> 
							<input id="upload_image_button" class="button" type="button" value="Upload Image" />
							<br /><span style="color:#666666;">Enter a URL or upload an image</span>
						</label>
					</td>
				</tr>

				<!-- Scroll on instruction click -->

				<tr>
					<th scope="row"><?php _e('Scroll on instruction click?','ddirections'); ?></th>
					<td>
						<?php
						if ($options['scrolltop']=='yes'){
							echo '<input type="radio" name="posk_options[scrolltop]" value="yes" checked>Yes&nbsp;&nbsp;';
							echo '<input type="radio" name="posk_options[scrolltop]" value="no"> No';
						} else {
							echo '<input type="radio" name="posk_options[scrolltop]" value="yes"> Yes&nbsp;&nbsp;';
							echo '<input type="radio" name="posk_options[scrolltop]" value="no" checked> No';
						}
						?>

					</td>
				</tr>
				
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes','ddirections'); ?>" />
			</p>
			
		</form>
		<div id="map-canvas"></div>

	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function posk_validate_options($input) {
	 // strip html from textboxes
	$input['textarea_one'] = wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
	$input['txt_one'] = wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
	return $input;
}

// Display a Settings link on the main Plugins page
function posk_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$posk_links = '<a href="'.get_admin_url().'options-general.php?page=ddirections/ddirections.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $posk_links );
	}

	return $links;
}

function wpmap_map($atts, $content = null){

	// Styles
	wp_register_style('wptuts-style', plugins_url( '/map/map.css', __FILE__ ), array(), '20120208', 'all' );  

	wp_enqueue_style ('wptuts-style');
	
    // Scripts
    wp_register_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
    wp_enqueue_script('google-maps');

	wp_register_script( 'wptuts-custom', plugins_url( '/map/map.js', __FILE__ ), '', '', true);
    wp_enqueue_script('wptuts-custom');
	
	// loading options
    extract(shortcode_atts(array(
		'zoom' => '',
		'infobox_text' => '',
		'initial_map_type' => ''
		), $atts));

	$options = get_option('posk_options');    
	
	if(isset($controls)){
		$controls = $options['controls'];
	} else {
		$controlls = "";
	}

	if(isset($zoom)){
		$zoom = $options['zoom'];
	} else {
		$zoom = "";
	}
	
	if(isset($initial_map_type)){
		$initial_map_type = $options['initial_map_type'];
	} else {
		$initial_map_type = "";
	}
	
	if(isset($infobox_text)){
		$infobox_text = $options['infobox_text'];
	} else {
		$infobox_text = "";
	}
	
	if(isset($icon_url)){
		$icon_url = $options['icon_url'];
	} else {
		$icon_url = "";
	}

	if(isset($scrolltop)){
		$scrolltop = $options['scrolltop'];
	} else {
		$scrolltop = "";
	}
	
	// set message variables to translate the JS
	$msg_ok1 = __('Thanks!<br /> You are at <strong> ','ddirections');
	$msg_ok2 = __('</strong> from us.<br />The driving directions are below the map.','ddirections');
	$msg_open_in_google_maps = __('Open in Google Maps','ddirections');
	$msg_sorry1 = __('Sorry, we can\'t provide directions to that address (you maybe too far away, are you in the same country as us?) Please try again.','ddirections');
	$msg_sorry2 = __('Sorry we didn\'t understand the address you entered. Please try again.','ddirections');
	$msg_sorry3 = __('Sorry, there was a problem generating the directions. Please try again.','ddirections');
	$msg_error1 = __('This website does not have permission to use the Geolocation API.','ddirections');
	$msg_error2 = __('Sorry, your current position cannot be determined, please enter your address instead.','ddirections');
	$msg_error3 = __('Sorry, we\'re having trouble trying to determine your current location, please enter your address instead.','ddirections');
	$msg_error4_1 = __('The position could not be determined due to','ddirections');
	$msg_error4_2 = __('an unknown error (Code: ','ddirections');
	
	
	if(isset($_REQUEST['ddirections_widget_address'])){
		$dd_address = $_REQUEST['ddirections_widget_address'];
	} else {
		$dd_address = null;
	}
	
	// select which variables share between php and javascript
	$variables_mapa = array(
		'infobox_text' => $infobox_text,
		'zoom' => $zoom,
		'initial_map_type' => $initial_map_type,
		'icon_url' => $icon_url,
		'msg_ok1' => $msg_ok1,
		'msg_ok2' => $msg_ok2,
		'msg_open_in_google_maps' => $msg_open_in_google_maps,
		'msg_sorry1' => $msg_sorry1,
		'msg_sorry2' => $msg_sorry2,
		'msg_sorry3' => $msg_sorry3,
		'msg_error1' => $msg_error1,
		'msg_error2' => $msg_error2,
		'msg_error3' => $msg_error3,
		'msg_error4_1' => $msg_error4_1,
		'msg_error4_2' => $msg_error4_2,
		'ddirections_widget_address' => $dd_address
    );

	// send viariable to javascript
	wp_localize_script( 'wptuts-custom', 'mapa_data', $variables_mapa );
	
    $output = sprintf(('<div id="map-container" data-map-infowindow="%s" data-map-zoom="%s"></div>'), $infobox_text, $zoom);
    return $output;

}

// [wpmap_map zoom="12" infobox_text="default" initial_map_type="ROADMAP"]
// [wpmap_map] without arguments, it uses the default zoo, infobox_text and initial_map_type defined in the Settings Form
add_shortcode('wpmap_map', 'wpmap_map');

function wpmap_directions_container($atts, $content = null){
    $output = '<div id="dir-container" ></div>';
    return $output;
}
// [wpmap_directions_container]
add_shortcode('wpmap_directions_container', 'wpmap_directions_container');

function wpmap_directions_input($atts, $content = null){
   // loading options
    extract(shortcode_atts(array(
		'coordinates' => ''
		), $atts));

	$options = get_option('posk_options');

    if(empty($coordinates)) $coordinates = $options['coordinates'];


    $output = '<div id="directions">
                  <p>'.__('To start the route enter your initial location:','ddirections').'</p>
                  <input id="from-input" type="text" value="" size="20" placeholder="'.__('Address','ddirections').'" />
                  <select style="display:none;" onchange="" id="unit-input">
                      <option value="metric" selected="selected">'.__('Metric','ddirections').'</option>
                      <option value="imperial">'.__('Imperial','ddirections').'</option>
                  </select>
                  <a href="#" onclick="WPmap.getDirections(\'manual\'); return false" class="map-button">'.__('Get the route','ddirections').'</a>
                  <br />
                  <input id="map-config-address" type="hidden" value="'.$coordinates.'"/>
                 <div id="geo-directions">
                 <p>'.__('You can also...','ddirections').'</p>
                  <a href="#" onclick="WPmap.getDirections(\'geo\'); return false" class="map-button">'.__('Use my location','ddirections').'</a>

                  <span id="native-link"></span>
                 </div>


               </div>
               ';
    return $output;
}

function load_scroll(){
	$options = get_option('posk_options');
	if($options['scrolltop']=='yes'){
		echo '
			<script>
			$(document).ready(function() {
				$("body").on("click", ".adp-directions tr",function(event){
					$("html, body").animate({ scrollTop: $("#map-container").offset().top -70}, 800);
				});
			});
			</script>
			';
	} else if($options['scrolltop']=='no'){
		//
	}
	else{
		//
	}
}

add_action('wp_footer', 'load_scroll');

// [wpmap_directions_input coordinates="41.39, 2.16"]
// [wpmap_directions_input] without arguments, it uses the default coordinates defined in the Settings Form
add_shortcode('wpmap_directions_input', 'wpmap_directions_input');

// help
include 'help.php';

// add 3.5 media uploader behavior to the Upload Button
add_action('admin_enqueue_scripts', 'my_admin_scripts');
 
function my_admin_scripts() {
    if (isset($_GET['page']) && $_GET['page'] == 'ddirections') {
        wp_enqueue_media();
		
		wp_register_script('admin_google_maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false');
        wp_enqueue_script('admin_google_maps');
		
        wp_register_script('custom-js', WP_PLUGIN_URL.'/ddirections/js/custom.js', array('jquery','admin_google_maps'));
        wp_enqueue_script('custom-js');
    }

}

// WIDGET
class DDirectionsWidget extends WP_Widget{
  
  function DDirectionsWidget(){
    $widget_ops = array('classname' => 'DDirectionsWidget', 'description' => 'Displays the DDirections widget' );
    $this->WP_Widget('DDirectionsWidget', 'Driving Directions', $widget_ops);
  }


  function form($instance){
    $instance = wp_parse_args( (array) $instance, array( 'page_id' => '' ), array( 'title' => '' ) );
    $page_id = $instance['page_id'];
    $title = $instance['title'];
    
    if(empty($title)) $title = __('Enter your address:','ddirections');
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>"><?=__('Title','ddirections')?>:<br /><small><?=__('Enter the title for requesting an address','ddirections')?></small><input class="widefat" id="<?php echo $this->get_field_id('page_id'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  <p><label for="<?php echo $this->get_field_id('page_id'); ?>"><?=__('Page ID','ddirections')?><br /><small><?=__('Enter the page ID of the page you are showing the Driving Directions','ddirections')?></small><input class="widefat" id="<?php echo $this->get_field_id('page_id'); ?>" name="<?php echo $this->get_field_name('page_id'); ?>" type="text" value="<?php echo attribute_escape($page_id); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance){
    $instance = $old_instance;
    $instance['page_id'] = $new_instance['page_id'];
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance){
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    
    
    $page_id = $instance['page_id'];
    $title = $instance['title'];
    
    if(empty($title)) $title = __('Enter your address:','ddirections');    
    
    $page_id = empty($page_id) ? ' ' : apply_filters('widget_title', $page_id);
    $title = empty($title) ? ' ' : apply_filters('widget_title', $title);    
 
    echo $before_title . $title . $after_title;
	
    // WIDGET CODE GOES HERE
    //echo "<h1>The widget appears here!</h1>";
	$directions_page_url = get_permalink($page_id);
    if(empty($directions_page_url))
        $directions_page_url = get_permalink(get_page_by_path($page_id));    
    if(empty($directions_page_url))
        $directions_page_url = get_permalink(get_page_by_title($page_id));
	
    echo '<form method="post" action="'.$directions_page_url.'">';
	echo '<input type="text" id="ddirections_widget_address" name="ddirections_widget_address" value="'.$_REQUEST['ddirections_widget_address'].'"></input>';
	echo '<input type="submit" value="Get directions"/>';
    echo '</form>';
    
	echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("DDirectionsWidget");') );
?>