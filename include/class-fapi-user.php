<?php

/**
 * @package   Fapi membership
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http://musilda.com
 * @copyright 2020 Musilda.com
 */

class Fapi_User {

	/**
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $username = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct( $email ) {
		$this->username = $email;

	}

	/**
	 * Return userid
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function get_user() {

		if ( ! username_exists( $this->username ) ) {
			$user = $this->create_user();
		} else {
			$user    = get_user_by( 'login', $this->username );
			$user_id = $user->ID;
		}

		return $user;

	}

	/**
	 * Return user id
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function create_user() {

		$password = wp_generate_password();
		$user_id  = wp_create_user( $this->username, $password, $this->username );
		$user     = new WP_User( $user_id );
		$user->set_role( 'subscriber' );

		$this->send_registration_email( $user );

		return $user;

	}

	/**
	 * Send registration email
	 *
	 * @since    1.0.0
	 */
	public function send_registration_email( $user ) {

		$registration_email = get_option( 'fapi_membership_registration_email' );
		if ( $registration_email != '---' ) {

			$to      = $user->user_email;
			$subject = __( 'Thank you for registration', 'fapi-member-section' );
			$body    = apply_filters( 'the_content', get_post_field( 'post_content', $registration_email ) );
			$body   .= $this->new_password_link( $user->ID );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $to, $subject, $body, $headers );

		}

	}

	/**
	 * Get new password link
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function new_password_link( $user_id ) {

		$url  = is_multisite() ? get_blogaddress_by_id( (int) $blog_id ) : home_url( '', 'https' );
		$user = new WP_User( (int) $user_id );

		$reset_password_key = get_password_reset_key( $user );
		$user_login         = $user->user_login;

		$reset_password_link = '<p><a href="' . network_site_url( 'wp-login.php?action=rp&key=' . $reset_password_key . '.&login=' . rawurlencode( $user_login ), 'login' ) . '">' . network_site_url( 'wp-login.php?action=rp&key=' . $reset_password_key . '&login=' . rawurlencode( $user_login ), 'login' ) . '</a></p>';

		return $reset_password_link;

	}



}//end class

