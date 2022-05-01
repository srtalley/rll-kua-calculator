<?php

namespace DigitalSLP\Clever_SSO;
use \DustySun\WP_Settings_API\v2 as DSWPSettingsAPI;

class DigitalSLP_Clever_SSO_Settings {

	private $rll_kua_calc_plugin_hook;

	private $rll_kua_calc_settings_page;

	private $rll_kua_calc_settings = array();

	private $rll_kua_calc_main_settings = array();

	private $rll_kua_calc_theme_customizer;

	// Create the object
	public function __construct() {

		// create the various menu pages 
		add_action( 'admin_menu', array($this, 'rll_kua_calc_create_admin_page'));

		// Register the menu
		add_action( 'admin_menu', array($this, 'rll_kua_calc_admin_menu' ));

		// Add settings & support links
   		add_filter( 'plugin_action_links', array($this,'rll_kua_calc_add_action_plugin'), 10, 5 );

	} // end public function __construct()

	public function rll_kua_calc_create_admin_page() {
		// set the settings api options
		$ds_api_settings = array(
			'json_file' => plugin_dir_path( __FILE__ ) . '/rl-kua-calc.json',
			'register_settings' => true,
			// 'views_dir' => plugin_dir_path( __FILE__ ) . '/admin/views'
		);
		// Create the settings object
		$this->rll_kua_calc_settings_page = new DSWPSettingsAPI\SettingsBuilder($ds_api_settings);

		// Create the customizer
 		// Get the current settings
		$this->rll_kua_calc_settings = $this->rll_kua_calc_settings_page->get_current_settings();

		// Get the plugin options
		$this->rll_kua_calc_main_settings = $this->rll_kua_calc_settings_page->get_main_settings();
	} // end function rll_kua_calc_create_admin_page

	// Adds admin menu under the Sections section in the Dashboard
	public function rll_kua_calc_admin_menu() {
		$this->rll_kua_calc_plugin_hook = add_submenu_page(
			'options-general.php',
			__('RLL Kua Calc', 'redlotus'),
			__('RLL Kua Calc', 'redlotus'),
			'manage_options',
			'rll-kua-calculator',
			array($this, 'rll_kua_calc_menu_options')
		);

	} // end public function rll_kua_calc_admin_menu()

	
	// Create the actual options page
	public function rll_kua_calc_menu_options() {
		$rll_kua_calc_settings_title = $this->rll_kua_calc_main_settings['name'];

		// Create the main page HTML
		$this->rll_kua_calc_settings_page->build_settings_panel($rll_kua_calc_settings_title);
	} // end function

	//function to add settings links to plugins area
	public function rll_kua_calc_add_action_plugin( $actions, $plugin_file ) {

		$plugin = plugin_basename(__DIR__) . '/dslp-clever-sso.php';

		if ($plugin == $plugin_file) {

			$site_link = array('support' => '<a href="' . $this->rll_kua_calc_main_settings['item_uri'] . '" target="_blank">' . __('Support', $this->rll_kua_calc_main_settings['text_domain']) . '</a>');
			$actions = array_merge($site_link, $actions);

			if ( is_plugin_active( $plugin) ) {
				$settings = array('settings' => '<a href="admin.php?page=' . $this->rll_kua_calc_main_settings['page_slug'] . '">' . __('Settings', $this->rll_kua_calc_main_settings['text_domain']) . '</a>');
				$actions = array_merge($settings, $actions);
			} //end if is_plugin_active
		}
		return $actions;

	} // end function rll_kua_calc_add_action_plugin

	public function rll_kua_calc_upgrade_process(){
		$update_db_flag = false;
		$db_plugin_settings = get_option('rll_kua_calc_main_settings');
		
   } // end function rll_kua_calc_upgrade_process

	public function rll_kua_calc_wp_upgrade_complete( $upgrader_object, $options ) {

	} // end function rll_kua_calc_wp_upgrade_complete

	
   
} // end class DigitalSLP_Clever_SSO_Settings
if( is_admin() )
    $rll_kua_calc_settings_page = new DigitalSLP_Clever_SSO_Settings();
