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
 * Plugin Name:       Fapi member section
 * Plugin URI:        https:/fapi.cz
 * Description:       Fapi membership
 * Version:           1.0.2
 * Author:            Fapi
 * Author URI:        https://fapi.cz
 * Text Domain:       fapi-member-section
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WC tested up to: 4.0.1
 */

declare(strict_types = 1);

use Fapi\FapiClient\FapiClientFactory;

/* If this file is called directly, abort. */
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FMDIR', plugin_dir_path( __FILE__ ) );
define( 'FMURL', plugin_dir_url( __FILE__ ) );

require_once 'vendor/autoload.php';
require_once 'include/class-fapi-memberships.php';
require_once 'include/class-fapi-credentials.php';
require_once 'include/class-fapi-credentials.php';
require_once 'include/class-fapi-user.php';
require_once 'include/class-fapi-memberships-log.php';

/* Admin settings */
if ( is_admin() ) {
	require_once 'admin/admin-handler.php';
}

/**
 * Load translate.
 */
function fapi_membership_load_plugin_textdomain() {

	$domain = 'fapi-membership';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	load_textdomain( $domain, plugin_dir_path( __FILE__ ) . 'languages/' . $domain . '-' . $locale . '.mo' );

}
add_action( 'init', 'fapi_membership_load_plugin_textdomain' );

/**
 * Custom endpoint.
 */
function fapi_membership_add_json_endpoint() {

	add_rewrite_endpoint( 'fapi-membership', EP_ALL );

}
add_action( 'init', 'fapi_membership_add_json_endpoint' );

/**
 * Add template redirect.
 */
function fapi_membership_json_template_redirect() {

	global $wp_query, $wpdb;

	if ( ! isset( $wp_query->query_vars['fapi-membership'] ) ) {
		return;
	}

	if ( 'sections' === $wp_query->query_vars['fapi-membership'] ) {

		require_once 'include/class-fapi-memberships.php';
		$memberships = Fapi_Memberships::get_instance();
		$data        = $memberships->get_memberships();
		if ( false !== $data ) {
			echo wp_json_encode( $data, true );
		}
		exit();

	}

	if ( 'create' === $wp_query->query_vars['fapi-membership'] ) {
		/* V post id faktury Dotaz na Fapi zda je uhrazeno a  pokud ano, z objektu faktury získat email zákazníka a vytvořit uživatele. */
		if ( empty( $_POST['id'] ) ) {
	
			$data = array(
				'context' => esc_attr__( 'Chyba - není uvedeno id faktury', 'fapi-member-section' ),
				'log'     => sanitize_text_field( wp_unslash( $_POST ) ),
			);
			fapi_memebership_save_log( $data );
			http_response_code( 400 );
			echo esc_attr__( 'Chyba - není uvedeno id faktury', 'fapi-member-section' );
			exit();
		}

		if ( empty( $_GET['section-id'] ) ) {
			
			$data = array(
				'context' => 'Chyba - není uvedeno id sekce',
				'log'     => sanitize_text_field( wp_unslash( $_POST ) ),
			);
			fapi_memebership_save_log( $data );
			http_response_code( 400 );
			echo esc_attr__( 'Chyba - není uvedeno id sekce', 'fapi-member-section' );
			exit();
		}

		$section_id = sanitize_text_field( wp_unslash( $_GET['section-id'] ) );
		$invoice_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );

		/* Check is invoice paid */
		$credentials = new Fapi_Credentials();
		$fapi_client = ( new FapiClientFactory() )->createFapiClient( $credentials->get_username(), $credentials->get_password() );
		$invoice     = $fapi_client->getInvoices()->find( (int) $invoice_id );

		if ( ! empty( $invoice ) ) {

			if ( ! empty( $invoice['paid'] ) && true === $invoice['paid'] ) {

				$data = array(
					'context' => esc_attr__( 'Zaplacení přístupu do sekce s id', 'fapi-member-section' ) . ' ' . $section_id,
					'log'     => $_POST,
				);
				fapi_memebership_save_log( $data );
				$user        = new Fapi_User( $invoice['customer']['email'] );
				$_user       = $user->get_user();
				$memberships = Fapi_Memberships::get_instance();
				$memberships->assing_section_to_user( $_user, $section_id );
				$memberships->send_section_welcome_email( $_user, $section_id );

				http_response_code( 200 );
				echo esc_attr__( 'Zaplacení přístupu do sekce s id', 'fapi-member-section' ) . ' ' . $section_id;
				exit();

			}

			$data = array(
				'context' => esc_attr__( 'Faktura není uhrazena ', 'fapi-member-section' ),
				'log'     => sanitize_text_field( wp_unslash( $_POST['id'] ) ),
			);
			fapi_memebership_save_log( $data );
			http_response_code( 400 );
			echo esc_attr__( 'Faktura není uhrazena ', 'fapi-member-section' );
			exit();
		}

		$data = array(
			'context' => esc_attr__( 'Chyba získání faktury', 'fapi-member-section' ),
			'log'     => sanitize_text_field( wp_unslash( $_POST['id'] ) ),
		);
		fapi_memebership_save_log( $data );
		http_response_code( 400 );
		echo esc_attr__( 'Chyba získání faktury', 'fapi-member-section' );
		exit();
	}

	exit();

}
add_action( 'template_redirect', 'fapi_membership_json_template_redirect' );

/**
 * Create log table.
 *
 * @since    1.0.0
 */
function fapi_create_log_table() {

	global $wpdb;

	$wpdb->hide_errors();

	$collate = '';

	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$collate .= " COLLATE $wpdb->collate";
		}
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fapi_memebership_log (
            `ID` bigint(255) NOT NULL AUTO_INCREMENT,
            `date` DATETIME NOT NULL,
            `log` longtext COLLATE utf8_czech_ci NOT NULL,
            `context` varchar(100) COLLATE utf8_czech_ci NOT NULL,
        PRIMARY KEY (`ID`)
        ) $collate;
    ";

	$wpdb->query( $table );

}
add_action( 'init', 'fapi_create_log_table' );

/**
 * Save Fapi log.
 *
 * @since 1.0.0
 * @param array $data log data.
 */
function fapi_memebership_save_log( $data ) {

	$log = Fapi_Memberships_Log::get_instance();
	$log->save_log( $data );

}

/**
 * Register Custom Post Type.
 *
 * @since 1.0.0
 */
function fapi_membership_cpt() {

	$labels = array(
		'name'                  => _x( 'Emails', 'Post Type General Name', 'fapi-member-section' ),
		'singular_name'         => _x( 'Email', 'Post Type Singular Name', 'fapi-member-section' ),
		'menu_name'             => __( 'Emails', 'fapi-member-section' ),
		'name_admin_bar'        => __( 'Email', 'fapi-member-section' ),
		'archives'              => __( 'Emails Archives', 'fapi-member-section' ),
		'attributes'            => __( 'Email Attributes', 'fapi-member-section' ),
		'parent_item_colon'     => __( 'Parent Email:', 'fapi-member-section' ),
		'all_items'             => __( 'All Emails', 'fapi-member-section' ),
		'add_new_item'          => __( 'Add New Email', 'fapi-member-section' ),
		'add_new'               => __( 'Add New', 'fapi-member-section' ),
		'new_item'              => __( 'New Email', 'fapi-member-section' ),
		'edit_item'             => __( 'Edit Email', 'fapi-member-section' ),
		'update_item'           => __( 'Update Email', 'fapi-member-section' ),
		'view_item'             => __( 'View Email', 'fapi-member-section' ),
		'view_items'            => __( 'View Emails', 'fapi-member-section' ),
		'search_items'          => __( 'Search Email', 'fapi-member-section' ),
		'not_found'             => __( 'Not found', 'fapi-member-section' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'fapi-member-section' ),
		'featured_image'        => __( 'Featured Image', 'fapi-member-section' ),
		'set_featured_image'    => __( 'Set featured image', 'fapi-member-section' ),
		'remove_featured_image' => __( 'Remove featured image', 'fapi-member-section' ),
		'use_featured_image'    => __( 'Use as featured image', 'fapi-member-section' ),
		'insert_into_item'      => __( 'Insert into item', 'fapi-member-section' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'fapi-member-section' ),
		'items_list'            => __( 'Items list', 'fapi-member-section' ),
		'items_list_navigation' => __( 'Items list navigation', 'fapi-member-section' ),
		'filter_items_list'     => __( 'Filter items list', 'fapi-member-section' ),
	);
	$args   = array(
		'label'               => __( 'Email', 'fapi-member-section' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 81,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'fapi_emails', $args );

}
add_action( 'init', 'fapi_membership_cpt', 0 );

/**
 * Redirect users
 */
add_action(
	'template_redirect',
	function() {

		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();

		$memberships = Fapi_Memberships::get_instance();
		$data        = $memberships->get_memberships();

		foreach ( $data as $id => $membership ) {
			$item = get_post_meta( $post_id, 'membership_' . $id, true );
			if ( ! empty( $item ) && $item === $id ) {

				if ( empty( $membership['redirect'] ) ) {
					$redirect = get_home_url();
				} else {
					$redirect = $membership['redirect'];
				}

				if ( ! is_user_logged_in() ) {
					wp_safe_redirect( $redirect );
					exit();
				}

				$user_id   = get_current_user_id();
				$user_item = get_user_meta( $user_id, 'membership_' . $id, true );

				if ( empty( $user_item ) ) {
					wp_safe_redirect( $redirect );
					exit();
				}
			}
		}

	}
);

/**
 * Login redirect.
 *
 * @param string $url url.
 * @param string $request request.
 * @param string $user user.
 */
function membership_login_redirect( $url, $request, $user ) {

	$memberships = Fapi_Memberships::get_instance();
	$data        = $memberships->get_memberships();
	foreach ( $data as $id => $membership ) {
		$user_item = get_user_meta( $user->ID, 'membership_' . $id, true );

		if ( ! empty( $user_item ) && ! empty( $membership['login_redirect'] ) ) {
			return $membership['login_redirect'];
		}
	}

	return $url;

}
add_filter( 'login_redirect', 'membership_login_redirect', 10, 3 );

/**
 * Load textdomain
 */
function memebership_load_plugin_textdomain() {

	$domain = 'fapi-membership';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	$load = load_textdomain( $domain, WP_LANG_DIR . '/fapi-membership-section/' . $domain . '-' . $locale . '.mo' );

	if ( false === $load ) {
		load_textdomain( $domain, FMDIR . 'languages/' . $domain . '-' . $locale . '.mo' );
	}

}
add_filter( 'login_redirect', 'membership_login_redirect', 10, 3 );
