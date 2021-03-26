<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       gabrieltumbaga.com
 * @since      1.0.0
 *
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/includes
 * @author     Gabriel Tumbaga <gabriel@iahsp.com>
 */
class Iahsp_Functionality_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'iahsp-functionality',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
