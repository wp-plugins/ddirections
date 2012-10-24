<?php
/*
Plugin Name: DDirections
Plugin URI: http://www.hectorgarrofe.com
Description: Driving directions with geolocation and mobile support.
Version: 1.2
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
	add_options_page('Driving directions', 'Driving directions', 'manage_options', __FILE__, 'posk_render_form');
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
	#wpwrap{
		background: url('<?php echo plugins_url(); ?>/ddirections/images/map-bg.png') no-repeat bottom right;
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
	
	#infobox textarea{
		border: none;
		position: absolute;
		top: 0;
		resize: none;
		width: 300px;
		padding: 10px;
		background: transparent;
		margin-left: 1px;
		height: 87px;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	
	#infobox td{
        position: relative;
		background: url('<?php echo plugins_url(); ?>/ddirections/images/infobox.png') no-repeat 10px -1px;
		height: 200px;		
	}
	
	hr{
		border-bottom: 1px;
	}
	
	#contextual-help-columns{
		background: url('<?php echo plugins_url(); ?>/ddirections/images/hg_logo.png') no-repeat bottom right;
	}
	</style>
	
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Driving Directions</h2>
		<!--<p><?php _e('Fill the fields to configure the plugin.','ddirections'); ?></p>-->

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('posk_plugin_options'); ?>
			<?php $options = get_option('posk_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
			
				<!-- Coordinates -->
				<tr>
					<th scope="row"><?php _e('Address','ddirections'); ?><br /><small> <?php _e('You can use City names or the 41.39, 2.16 format','ddirections'); ?></small></th>
					<td>
						<input type="text" size="53" name="posk_options[coordinates]" value="<?php echo $options['coordinates']; ?>" />
					</td>
				</tr>

				<!-- Text Area Control -->
				<tr id="infobox">
					<th scope="row"><?php _e('Infobox text','ddirections'); ?><br /><small><?php _e('Accepts some HTML tags','ddirections'); ?></small></th>
					<td>
						<textarea name="posk_options[infobox_text]" rows="3" cols="59" type='textarea'><?php echo $options['infobox_text']; ?></textarea>
					</td>
				</tr>

				<!-- Zoom level -->
				<tr>
					<th scope="row"><?php _e('Zoom level','ddirections'); ?></th>
					<td>
						<input type="text" size="20" name="posk_options[zoom]" value="<?php echo $options['zoom']; ?>" />
					</td>
				</tr>

				<!-- Select Drop-Down Control -->
				<tr>
					<th scope="row"><?php _e('Map type','ddirections'); ?></th>
					<td>
						<select name='posk_options[initial_map_type]'>
							<option value='HYBRID' <?php selected('HYBRYD', $options['initial_map_type']); ?>><?php _e('Hybrid','ddirections'); ?></option>
							<option value='ROADMAP' <?php selected('ROADMAP', $options['initial_map_type']); ?>><?php _e('Roadmap','ddirections'); ?></option>
							<option value='SATELLITE' <?php selected('SATELLITE', $options['initial_map_type']); ?>><?php _e('Satellite','ddirections'); ?></option>
							<option value='TERRAIN' <?php selected('TERRAIN', $options['initial_map_type']); ?>><?php _e('Terrain','ddirections'); ?></option>
						</select>
						<span style="color:#666666;margin-left:2px;"><?php _e('The 4 google maps different types','ddirections'); ?></span>
					</td>
				</tr>
				
				<!-- Image URL -->
				<tr>
					<th scope="row"><?php _e('Icon Marker URL','ddirections'); ?></th>
					<td>
						<input type="text" size="50" name="posk_options[icon_url]" value="<?php echo $options['icon_url']; ?>" />
					</td>
				</tr>
				
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes','ddirections'); ?>" />
			</p>
		</form>

	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function posk_validate_options($input) {
	 // strip html from textboxes
	$input['textarea_one'] =  wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
	$input['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
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
add_action('admin_init', 'map_init');

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
    if(empty($controls)) $controls = $options['controls'];
    if(empty($zoom)) $zoom = $options['zoom'];
    if(empty($initial_map_type)) $initial_map_type = $options['initial_map_type'];
    if(empty($infobox_text)) $infobox_text = $options['infobox_text'];
	if(empty($icon_url)) $icon_url = $options['icon_url'];
	
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
		'msg_error4_2' => $msg_error4_2
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
                  <a href="#" onclick="WPmap.getDirections(\'manual\'); return false" class="map-button">'.__('Get the rute','ddirections').'</a>
                  <br />
                  <input id="map-config-address" type="hidden" value="'.$coordinates.'"/>
                 <div id="geo-directions" class="hidden">
                 <p>Tambien puedes...</p>
                  <a href="#" onclick="WPmap.getDirections(\'geo\'); return false" class="map-button">'.__('Use my location','ddirections').'</a>
                  <span id="native-link"></span>
                 </div>
               </div>
               ';
    return $output;
}

// [wpmap_directions_input coordinates="41.39, 2.16"]
// [wpmap_directions_input] without arguments, it uses the default coordinates defined in the Settings Form
add_shortcode('wpmap_directions_input', 'wpmap_directions_input');

// help
include 'help.php';
?>