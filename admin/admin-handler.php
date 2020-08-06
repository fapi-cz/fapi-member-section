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

add_action( 'admin_menu', 'fapimembership_add_plugin_admin_menu' );
/**
 * Add admin menu
 */
function fapimembership_add_plugin_admin_menu() {

	add_menu_page(
		__( 'Membership sections', 'fapi-membership' ),
		__( 'Membership sections', 'fapi-membership' ),
		'manage_options',
		'fapi-memebership',
		'fapi_memebership_admin_page',
		'dashicons-text-page',
		82
	);
	add_submenu_page(
		'fapi-memebership',
		__( 'Overview', 'fapi-membership' ),
		__( 'Overview', 'fapi-membership' ),
		'manage_options',
		'fapi-memebership',
		'fapi_memebership_admin_page'
	);
	add_submenu_page(
		'fapi-memebership',
		__( 'Registration', 'fapi-membership' ),
		__( 'Registration', 'fapi-membership' ),
		'manage_options',
		'fapi-membership-registration',
		'fapi_memebership_registration_page'
	);
	add_submenu_page(
		'fapi-memebership',
		__( 'Connection', 'fapi-membership' ),
		__( 'Connection', 'fapi-membership' ),
		'manage_options',
		'fapi-membership-connection',
		'fapi_memebership_connection_page'
	);
	add_submenu_page(
		'fapi-memebership',
		__( 'Log', 'fapi-membership' ),
		__( 'Log', 'fapi-membership' ),
		'manage_options',
		'fapi-membership-log',
		'fapi_memebership_log_page'
	);

}

/**
 * Membership admin page
 */
function fapi_memebership_admin_page() {
	if ( ! empty( $_GET['edit'] ) ) {
		$nonce = sanitize_text_field( wp_unslash( $_GET['nonce_edit'] ) );
		if ( isset( $nonce ) && wp_verify_nonce( $nonce ) ) {
			include 'views/memberships-page-edit.php';
		}
	} else {
		include 'views/memberships-page.php';
	}
}

/**
 * Membership rgistration page
 */
function fapi_memebership_registration_page() {
	include 'views/memberships-registration-page.php';
}

/**
 * Membership rgistration page
 */
function fapi_memebership_emails() {
	include 'views/memberships-emails-page.php';
}

/**
 * Membership rgistration page
 */
function fapi_memebership_connection_page() {
	include 'views/memberships-credentials-page.php';
}

/**
 * Membership rgistration page
 */
function fapi_memebership_log_page() {
	include 'views/memberships-log.php';
}

add_action( 'admin_enqueue_scripts', 'fapi_membership_enqueue_admin_styles' );
/**
 * Load admin style sheet and JavaScript.
 */
function fapi_membership_enqueue_admin_styles() {

	wp_enqueue_style( 'fapi-admin-styles', FMURL . 'assets/admin.css', array(), '1.0' );

}

add_action( 'add_meta_boxes', 'fapi_membership_metabox' );
/**
 * Add metabox to page
 */
function fapi_membership_metabox() {
	add_meta_box( 'memberships', __( 'Memberships sections', 'fapi-membership' ), 'memberships_meta_box', 'page', 'side', 'high' );
}

/**
 * Metabox
 *
 * @param object $object post objext.
 * @param string $box box id.
 */
function memberships_meta_box( $object, $box ) {

	global $post;
	$memberships = Fapi_Memberships::get_instance();
	$data        = $memberships->get_memberships();

	foreach ( $data as $id => $membership ) {
		$item = get_post_meta( $post->ID, 'membership_' . $id, true );
		if ( ! empty( $item ) && $item === $id ) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}
		echo '<p><input type="checkbox" name="membership_' . esc_attr( $id ) . '" value="' . esc_attr( $id ) . '" ' . esc_attr( $checked ) . ' /> <label>' . esc_attr( $membership['name'] ) . '</label></p>';
	}

	wp_nonce_field( 'fapi-admin-metabox', 'fapi_admin_metabox' );

}

add_action( 'save_post', 'fapi_membership_meta_box_setup' );
/**
 * Save data for post from metabox
 *
 * @param int $post_id post id.
 */
function fapi_membership_meta_box_setup( $post_id ) {

	if ( empty( $post_id ) ) {
		return; }
	$nonce = sanitize_text_field( wp_unslash( $_POST['fapi_admin_metabox'] ) );
	if ( isset( $nonce ) && wp_verify_nonce( $nonce, 'fapi-admin-metabox' ) ) {
		$memberships = Fapi_Memberships::get_instance();
		$data        = $memberships->get_memberships();

		foreach ( $data as $id => $membership ) {
			$item = get_post_meta( $post_id, 'membership_' . $id, true );
			if ( ! empty( $_POST[ 'membership_' . $id ] ) ) {
				update_post_meta( $post_id, 'membership_' . $id, sanitize_text_field( wp_unslash( $_POST[ 'membership_' . $id ] ) ) );
			} else {
				delete_post_meta( $post_id, 'membership_' . $id );
			}
		}
	}

}

add_action( 'show_user_profile', 'fapi_memberships_userprofile_fields' );
add_action( 'edit_user_profile', 'fapi_memberships_userprofile_fields' );
/**
 * Render fields in user profile
 *
 * @param object $user user object.
 */
function fapi_memberships_userprofile_fields( $user ) {

	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return false;
	}

	$memberships = Fapi_Memberships::get_instance();
	$data        = $memberships->get_memberships();

	echo '<h2>' . esc_attr__( 'Memberships sections', 'fapi-membership' ) . '</h2>';
	echo '<table class="form-table">';

	foreach ( $data as $id => $membership ) {
		echo '<tr>';
			echo '<th>';
				echo '<label>' . esc_attr( $membership['name'] ) . '</label>';
			echo '</th>';
			echo '<td>';
		$item = get_user_meta( $user->ID, 'membership_' . $id, true );
		if ( ! empty( $item ) && $item === $id ) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}
			echo '<input type="checkbox" name="membership_' . esc_attr( $id ) . '" value="' . esc_attr( $id ) . '" ' . esc_attr( $checked ) . ' /> <label>' . esc_attr( $membership['name'] ) . '</label>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
	wp_nonce_field( 'fapi-admin-user', 'fapi_admin_user' );

}

add_action( 'personal_options_update', 'save_membership_userprofile_fields' );
add_action( 'edit_user_profile_update', 'save_membership_userprofile_fields' );
/**
 * Save data for user profile
 *
 * @param int $user_id user id.
 */
function save_membership_userprofile_fields( $user_id ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['fapi_admin_user'] ) );
	if ( isset( $nonce ) && wp_verify_nonce( $nonce, 'fapi-admin-user' ) ) {

		$memberships = Fapi_Memberships::get_instance();
		$data        = $memberships->get_memberships();

		foreach ( $data as $id => $membership ) {

			if ( ! empty( $_POST[ 'membership_' . $id ] ) ) {
				update_user_meta( $user_id, 'membership_' . $id, sanitize_text_field( wp_unslash( $_POST[ 'membership_' . $id ] ) ) );
			} else {
				delete_user_meta( $user_id, 'membership_' . $id );
			}
		}
	}

}


add_action( 'admin_init', 'fapi_membership_output_buffer' );
/**
 * Output buffer
 */
function fapi_membership_output_buffer() {
	ob_start();
}

