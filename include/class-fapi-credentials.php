<?php
/**
 * Fapi
 *
 * @package   Fapi membership
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http://musilda.com
 * @copyright 2020 Musilda.com
 *
 */

if ( ! class_exists( 'Fapi_Credentials' ) ) {

	/**
	 * Fapi
	 *
	 * @package   Fapi membership
	 * @author    Vladislav Musílek
	 * @license   GPL-2.0+
	 * @link      http://musilda.com
	 * @copyright 2020 Musilda.com
	 *
	 */
	class Fapi_Credentials {

		/**
		 * Username.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $username = null;

		/**
		 * Password.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $password = null;

		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since     1.0.0
		 */
		public function __construct() {

			$this->init_credentials();

		}

		/**
		 * Set credentials.
		 *
		 * @since    1.0.0
		 */
		public function init_credentials() {

			$option = get_option( 'fapi_memberships_credentials' );

			if ( ! empty( $option['username'] ) ) {
				$this->username = $option['username'];
			}

			if ( ! empty( $option['password'] ) ) {
				$this->password = $option['password'];
			}

		}

		/**
		 * Return username.
		 *
		 * @since    1.0.0
		 *
		 * @return    string
		 */
		public function get_username() {

			return $this->username;

		}

		/**
		 * Return password.
		 *
		 * @since    1.0.0
		 *
		 * @return    string
		 */
		public function get_password() {

			return $this->password;

		}

		/**
		 * Set username.
		 *
		 * @since    1.0.0
		 *
		 * @return    void
		 * @param string $username username.
		 */
		public function set_username( $username ) {

			$this->username = $username;

		}

		/**
		 * Set password.
		 *
		 * @since    1.0.0
		 *
		 * @return    void
		 * @param string $password password.
		 */
		public function set_password( $password ) {

			$this->password = $password;

		}

		/**
		 * Save credentials.
		 *
		 * @since    1.0.0
		 *
		 * @return    void
		 */
		public function save_credentials() {

			if ( empty( $_POST['fapi_credeintials_nonce'] ) ) {
				return;
			}

			$nonce = sanitize_text_field( wp_unslash( $_POST['fapi_credeintials_nonce'] ) );
			if ( isset( $nonce ) && wp_verify_nonce( $nonce, 'fapi-credeintials-nonce' ) ) {

				if ( ! empty( $_POST['credentials'] ) ) {
					$option = array();

					if ( ! empty( $_POST['fapi_username'] ) ) {
						$option['username'] = sanitize_text_field( wp_unslash( $_POST['fapi_username'] ) );
					}

					if ( ! empty( $_POST['fapi_password'] ) ) {
						$option['password'] = sanitize_text_field( wp_unslash( $_POST['fapi_password'] ) );
					}

					if ( ! empty( $option ) ) {
						update_option( 'fapi_memberships_credentials', $option );
					} else {
						delete_option( 'fapi_memberships_credentials' );
					}
				}
			}
		}

	}//end class

}
