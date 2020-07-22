<?php
declare(strict_types = 1);

use Fapi\FapiClient\FapiClientFactory;
use Fapi\FapiClient\Tools\SecurityChecker;

$memberships = Fapi_Memberships::get_instance();
if ( ! empty( $_POST['save'] ) ) {
	if ( ! empty( $_POST['membership_name'] ) ) {
		$data = $memberships->get_memberships();

		if ( empty( $data ) ) {
			$key = 1;
		} else {
			$key = count( $data ) + 1;
		}


		$data[ $key ] = array(
			'name'           => sanitize_text_field( $_POST['membership_name'] ),
			'note'           => sanitize_text_field( $_POST['membership_note'] ),
			'email'          => sanitize_text_field( $_POST['membership_email'] ),
			'redirect'       => sanitize_text_field( $_POST['membership_redirect'] ),
			'login_redirect' => sanitize_text_field( $_POST['membership_login_redirect'] ),
		);

		update_option( 'fapi_memberships', serialize( $data ) );
	}
}


if ( ! empty( $_GET['delete'] ) ) {

	$data = $memberships->get_memberships();

	if ( ! empty( $data[ $_GET['delete'] ] ) ) {
		unset( $data[ $_GET['delete'] ] );
		if ( ! empty( $data ) ) {
			update_option( 'fapi_memberships', serialize( $data ) );
		} else {
			delete_option( 'fapi_memberships' );
		}
	}
	wp_redirect( admin_url() . 'admin.php?page=fapi-memebership' );

}

$args   = array(
	'post_type'   => 'fapi_emails',
	'post_status' => 'publish',
	'numberposts' => -1,
);
$emails = new WP_Query( $args );

?>

<div class="wrap">

<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_attr_e( 'Memberships sections', 'fapi-membership' ); ?></h3>
			</div>
			<div class="box-body">
				<?php $memberships->render_memberships_table(); ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>

	</div>
	<div class="clear"></div>
	
	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_attr_e( 'Add new memberships sections', 'fapi-membership' ); ?></h3>
			</div>
			<div class="box-body">
				<form method="post" action="<?php echo admin_url() . 'admin.php?page=fapi-memebership'; ?>">
				<table class="table-bordered">
					<tr>
						<th><?php esc_attr_e( 'Name', 'fapi-membership' ); ?></th>
						<th><?php esc_attr_e( 'Note', 'fapi-membership' ); ?></th>
						<th><?php esc_attr_e( 'Email', 'fapi-membership' ); ?></th>
						<th><?php esc_attr_e( 'Redirect page', 'fapi-membership' ); ?></th>
						<th><?php esc_attr_e( 'Login redirect page', 'fapi-membership' ); ?></th>
						<th></th>
					</tr>
					<tr>
						<td><input type="text" name="membership_name" style="width:100%" /></td>
						<td><textarea name="membership_note"></textarea></td>
						<td>
							<select name="membership_email">
								<option value="---">---</option>
							<?php
							if ( ! empty( $emails->posts ) ) {
								foreach ( $emails->posts as $email ) {
									echo '<option value="' . $email->ID . '">' . $email->post_title . '</option>';
								}
							}
							?>
							</select>
						</td>
						<td><input type="text" name="membership_redirect" style="width:100%" /></td>
						<td><input type="text" name="membership_login_redirect" style="width:100%" /></td>
						<td class="td_center"><input class="btn btn-success" type="submit" name="save" value="<?php esc_attr_e( 'Create', 'fapi-membership' ); ?>" /></td>
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
