<?php

/**
 * @package   Fapi membership
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http://musilda.com
 * @copyright 2020 Musilda.com
 */

class Fapi_Display_Sections {

	/**
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'fapi-membership';

	/**
	 * Sections
	 *
	 * @since    1.0
	 *
	 * @var      array
	 */
	private $sections = null;

	/**
	 *
	 *
	 * @since     1.0
	 */
	public function __construct() {

		$this->sections = maybe_unserialize( get_option( 'fapi_memberships' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0
	 *
	 * @return    Plugin slug variable.
	 */
	private function get_plugin_slug() {

		return $this->plugin_slug;

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0
	 *
	 * @return  array
	 */
	public function get_sections() {

		return $this->sections;

	}

}//end class

