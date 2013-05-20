<?php 
	/*
	Plugin Name: WP App promoter
	Description: A simple plugin for promoting your iPhone app or Android app
	Version: 0.1
	Author: Joakim Nystrom
	Author URI: http://jnystromdesign.se/
	*/
	
	/**
	 *
	 * Released under the GPL license
	 * http://www.opensource.org/licenses/gpl-license.php
	 *
	 * This is an add-on for WordPress
	 * http://wordpress.org/
	 *
	 * **********************************************************************
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 * **********************************************************************
	 */

class wp_admin_settings_page{
   
    public function __construct(){
        if(is_admin())
        {
	    	add_action('admin_menu', array($this, 'add_plugin_page'));
	    	add_action('admin_init', array($this, 'page_init'));
		}
    }
	
	// Add admin page to menu
    public function add_plugin_page(){
		add_options_page(
			'Settings Admin', 
			'App promoter', 
			'manage_options', 
			'app-promoter-settings', 
			array($this, 'create_admin_page')
		);
	}

	// Print the html for the admin page
    public function create_admin_page(){?>
	
	<div class="wrap">
	    
	    <?php screen_icon(); ?>
	    
	    <h2>App promoter settings</h2>			
	    
	    <form method="post" action="options.php">
	       
	        <?php
             // This prints out all hidden setting fields
		   	 settings_fields('app_promotor_option_group');	
		    do_settings_sections('app-promoter-settings');
			?>

	        <?php submit_button(); ?>
	    
	    </form>

	</div>
	<?php
    }
	
    public function page_init()
    {					
		register_setting(
			'app_promotor_option_group', 		// option_group 
			'app_promotor_setting_values', 		// option_name 
			array($this, 'check_ID') 			// callback_method for sanitizing data 
			);
			
	       add_settings_section(
		    'app_promotor_setting_values',		// id
		    'Setting for apps',					// title
		    array($this, 'print_section_info'),	// callback for filling section with content
		    'app-promoter-settings'				// page - must match slug
		);	
			
		add_settings_field(
		    'iphone_app_id', 					// id
		    'iPhone App ID', 					// title
		    array($this, 'create_an_id_field'), // callback
		    'app-promoter-settings',			// page
		    'app_promotor_setting_values', 		// section
		    array('id'=>'iphone_app_id')		// args
		);

		add_settings_field(
		    'android_app_id', 
		    'Android App ID', 
		    array($this, 'create_an_id_field'), 
		    'app-promoter-settings',
		    'app_promotor_setting_values',
		    array('id'=>'android_app_id')			
		);	

		add_settings_field(
		    'android_popup_text', 
		    'Android Popup text', 
		    array($this, 'create_an_id_field'), 
		    'app-promoter-settings',
		    'app_promotor_setting_values',
		    array('id'=>'android_popup_text')			
		);				
    }
	
    public function check_ID($input){
        
	    $field_keys = array('iphone_app_id', 'android_app_id', 'android_popup_text');

        foreach($input as $key=>$value)
        {
        	if( in_array($key, $field_keys) )
        	{
		   		if(get_option($key) === FALSE)
		   		{
					add_option($key, $value);
		    	}
		    	else
		    	{
					update_option($key, $value);
		    	}
        	}
        }
        return true;
    }
	
    public function print_section_info(){
		print 'Enter your setting below:';
    }
	
    public function create_an_id_field($values){
        ?><input type="text" id="<?php echo $values['id'] ?>" name="app_promotor_setting_values[<?php echo $values['id'] ?>]" value="<?=get_option($values['id']); ?>" /><?php
    }
}

$app_promoter_admin_page = new wp_admin_settings_page(); // Instanciate a new admin page object

// Adds the neat iphone popup.
function add_app_promoters()
{
	if( get_option('iphone_app_id') === FALSE )  return false; 	// Abort if we have no settings saved!
	if(get_option('iphone_app_id') === '' ) return false; // Abort if we have empty values saved!

	$app_id = get_option( 'iphone_app_id'); 
	echo '<meta name="apple-itunes-app" content="app-id=' . $app_id . '">'; // Add the apple-itunes-app meta tag
}

add_action('wp_head', 'add_app_promoters', 5);

function add_android_script(){
	
	if( get_option('android_app_id') === FALSE) return false;		// Abort if we have no settings saved!
	if( get_option('android_app_id') === '')  return false; 	// Abort if we have empty values saved!

	$android_app_id = get_option('android_app_id');
	$android_popup_text = get_option('android_popup_text');

	// Ugly fix for fallback text, since default parameter doesn´t work
	if($android_popup_text === false){ 
		$android_popup_text = 'Would you like to download our app on Google Play?'; 
	}elseif($android_popup_text === '' ){
		$android_popup_text = 'Would you like to download our app on Google Play?';
	}
	
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']); 
	
	if(stripos($ua,'android') !== false) // Make sure we have an Android user
	{
		// Add some scripts for cookie setting, Android recogninition and Confirm-popup
		$android_script = 

<<<EOT
<script>
	
	var ua = navigator.userAgent.toLowerCase();
	var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");

	function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	if(isAndroid) {
	
		if(readCookie('decline_app_download') === null){
			var app_id = '$android_app_id';
			var app_url = 'http://play.google.com/store/apps/details?id=' + app_id;

			var goto_store = confirm('$android_popup_text');

			if(goto_store){
				window.location = app_url;	
			}else{
				createCookie("decline_app_download", true, 7);
			}
		}
	}
</script>
EOT;

	echo $android_script; // Output the scrips
	}
}

add_action('wp_head', 'add_android_script', 15);