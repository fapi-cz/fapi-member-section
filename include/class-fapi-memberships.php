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
class Fapi_Memberships {

	/**
	 * Plugin slug.
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'fapi-membership';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {

		return $this->plugin_slug;

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Get website plugins data
	 */
	public function get_memberships() {

		$memberships = get_option( 'fapi_memberships' );
		
		if ( empty( $memberships ) ) {
			return false;
		}

		return $memberships;

	}

	/**
	 * Get website plugins data
	 */
	public function render_memberships_table() {

		$memberships = $this->get_memberships();
		if ( false !== $memberships ) {

			echo '<table class="table-bordered">';
			echo '<tr><th>' . esc_attr__( 'Membership name', 'fapi-membership' ) . '</th><th>' . esc_attr__( 'Membership id', 'fapi-membership' ) . '</th><th>' . esc_attr__( 'Membership description', 'fapi-membership' ) . '</th><th>' . esc_attr__( 'Email', 'fapi-membership' ) . '</th><th>' . esc_attr__( 'Redirect page', 'fapi-membership' ) . '</th><th>' . esc_attr__( 'Login redirect page', 'fapi-membership' ) . '</th><th>' . esc_attr__( 'Remove', 'fapi-membership' ) . '</th><th></th></tr>';
			foreach ( $memberships as $id => $membership ) {
				if ( empty( $membership['name'] ) ) {
					continue; }
				echo '<tr>';
					echo '<td>' . esc_attr( $this->get_field_value( $membership, 'name' ) ) . '</td>';
					echo '<td class="td_center">' . esc_attr( $id ) . '</td>';
					echo '<td>' . esc_attr( $this->get_field_value( $membership, 'note' ) ) . '</td>';
					echo '<td>' . esc_attr( get_the_title( $this->get_field_value( $membership, 'email' ) ) ) . '</td>';
					echo '<td>' . esc_attr( $this->get_field_value( $membership, 'redirect' ) ) . '</td>';
					echo '<td>' . esc_attr( $this->get_field_value( $membership, 'login_redirect' ) ) . '</td>';
					echo '<td class="td_center"><a href="' . esc_url( wp_nonce_url( admin_url() . 'admin.php?page=fapi-memebership&delete=' . esc_attr( $id ), 'nonce_delete', 'nonce_delete' ) ) . '" class="btn btn-danger">' . esc_attr__( 'Remove', 'fapi-membership' ) . '</a></td>';
					echo '<td class="td_center"><a href="' . esc_url( wp_nonce_url( admin_url() . 'admin.php?page=fapi-memebership&edit=' . esc_attr( $id ), 'nonce_edit', 'nonce_edit' ) ) . '" class="btn btn-success">' . esc_attr__( 'Edit', 'fapi-membership' ) . '</a></td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo '<p>' . esc_attr__( 'You dont have any memebership yet.', 'fapi-membership' ) . '</p>';
		}

	}

	/**
	 * Get field value
	 *
	 * @param string $field file name.
	 * @param string $key file key.
	 * @param string $type filed type.
	 */
	private function get_field_value( $field, $key, $type = 'string' ) {

		if ( ! empty( $field[ $key ] ) ) {
			return $field[ $key ];
		}

		if ( 'string' === $type ) {
			return '';
		}

		return false;

	}

	/**
	 * Send section welcome email
	 *
	 * @since    1.0.0
	 * @param object $user user object.
	 * @param string $section_id section id.
	 */
	public function send_section_welcome_email( $user, $section_id ) {

		$sections = $this->get_memberships();

		if ( ! empty( $sections[ $section_id ]['email'] ) ) {

			$to      = $user->user_email;
			$subject = get_the_title( $sections[ $section_id ]['email'] );
			$body    = apply_filters( 'the_content', get_post_field( 'post_content', $sections[ $section_id ]['email'] ) );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $to, $subject, $body, $headers );

		}

	}

	/**
	 * Assing section to user
	 *
	 * @since    1.0.0
	 * @param object $user user object.
	 * @param string $section_id section id.
	 */
	public function assing_section_to_user( $user, $section_id ) {

		update_user_meta( $user->ID, 'membership_' . $section_id, $section_id );

	}

}//end class
