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

declare(strict_types = 1);

$memberships = Fapi_Memberships::get_instance();
if ( ! empty( $_POST['edit'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['fapi_admin_form'] ) );
	if ( isset( $nonce ) && wp_verify_nonce( $nonce, 'fapi-admin-form' ) ) {

		if ( ! empty( $_POST['membership_name'] ) ) {
			$data = $memberships->get_memberships();
			if ( ! empty( $_POST['membership_id'] ) ) {
				$membership_id = sanitize_text_field( wp_unslash( $_POST['membership_id'] ) );
			}

			if ( ! empty( $_POST['membership_name'] ) ) {
				$membership_name = sanitize_text_field( wp_unslash( $_POST['membership_name'] ) );
			}
			if ( ! empty( $_POST['membership_note'] ) ) {
				$membership_note = sanitize_text_field( wp_unslash( $_POST['membership_note'] ) );
			}
			if ( ! empty( $_POST['membership_email'] ) ) {
				$membership_email = sanitize_text_field( wp_unslash( $_POST['membership_email'] ) );
			}
			if ( ! empty( $_POST['membership_redirect'] ) ) {
				$membership_redirect = sanitize_text_field( wp_unslash( $_POST['membership_redirect'] ) );
			}
			if ( ! empty( $_POST['membership_login_redirect'] ) ) {
				$membership_login_redirect = sanitize_text_field( wp_unslash( $_POST['membership_login_redirect'] ) );
			}

			$data[ $membership_id ] = array(
				'name'           => $membership_name,
				'note'           => $membership_note,
				'email'          => $membership_email,
				'redirect'       => $membership_redirect,
				'login_redirect' => $membership_login_redirect,
			);

			update_option( 'fapi_memberships', $data, true );

			wp_safe_redirect( admin_url( '?page=fapi-memebership' ) );

		}
	}
}

$args   = array(
	'post_type'   => 'fapi_emails',
	'post_status' => 'publish',
	'numberposts' => -1,
);
$emails = new WP_Query( $args );
if ( ! empty( sanitize_text_field( wp_unslash( $_GET['edit'] ) ) ) ) {
	$membership_id = esc_attr( sanitize_text_field( wp_unslash( $_GET['edit'] ) ) );
}
$data       = $memberships->get_memberships();
$membership = $data[ $membership_id ];

?>

<div class="wrap">

<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_attr_e( 'Edit memberships sections', 'fapi-membership' ); ?></h3>
			</div>
			<div class="box-body">
				<form method="post" action="">
				<table class="table-bordered">
					<tr>
						<th><?php esc_attr_e( 'Name', 'fapi-membership' ); ?></th>
						<td><input type="text" name="membership_name" style="width:100%" value="
						<?php
						if ( ! empty( $membership['name'] ) ) {
							echo esc_attr( $membership['name'] );
						}
						?>
						" /></td>
					</tr>
					<tr>
						<th><?php esc_attr_e( 'Note', 'fapi-membership' ); ?></th>
						<td><textarea name="membership_note">
						<?php
						if ( ! empty( $membership['note'] ) ) {
							echo esc_attr( $membership['note'] );
						}
						?>
						</textarea></td>
					</tr>
					<tr>
						<th><?php esc_attr_e( 'Email', 'fapi-membership' ); ?></th>
						<td>
							<select name="membership_email">
								<option value="---">---</option>
							<?php
							if ( ! empty( $emails->posts ) ) {
								foreach ( $emails->posts as $email ) {
									if ( ! empty( $membership['email'] ) && $membership['email'] === $email->ID ) {
										$selected = 'selected="selected"';
									} else {
										$selected = ''; }
									echo '<option value="' . esc_attr( $email->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $email->post_title ) . '</option>';
								}
							}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_attr_e( 'Redirect page', 'fapi-membership' ); ?></th>
						<td><input type="text" name="membership_redirect" style="width:100%" value="
						<?php
						if ( ! empty( $membership['redirect'] ) ) {
							echo esc_attr( $membership['redirect'] );
						}
						?>
						" /></td>
					</tr>
					<tr>
						<th><?php esc_attr_e( 'Login redirect page', 'fapi-membership' ); ?></th>
						<td><input type="text" name="membership_login_redirect" style="width:100%" value="
						<?php
						if ( ! empty( $membership['login_redirect'] ) ) {
							echo esc_attr( $membership['login_redirect'] );
						}
						?>
						" /></td>
					</tr>
					<tr>
						<th></th>
						<td class="td_center"><input class="btn btn-success" type="submit" name="edit" value="<?php esc_attr_e( 'Save', 'fapi-membership' ); ?>" /></td>
						<input type="hidden" name="membership_id" value="<?php echo esc_attr( $membership_id ); ?>" />
					</tr>
				</table>
				</form>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>

	</div>
	<div class="clear"></div>
</div>
