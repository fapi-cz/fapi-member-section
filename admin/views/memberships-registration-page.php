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

if ( ! empty( $_POST['registration-email-submit'] ) && !empty( $_POST['fapi_admin_form'] ) ) {

	$nonce = sanitize_text_field( wp_unslash( $_POST['fapi_admin_form'] ) );
	if ( isset( $nonce ) && wp_verify_nonce( $nonce, 'fapi-admin-form' ) ) {
		if ( ! empty( $_POST['registration_email'] ) ) {
			update_option( 'fapi_membership_registration_email', sanitize_text_field( wp_unslash( $_POST['registration_email'] ) ) );
		}
	}
}


$args               = array(
	'post_type'   => 'fapi_emails',
	'post_status' => 'publish',
	'numberposts' => -1,
);
$emails             = new WP_Query( $args );
$registration_email = (int)get_option( 'fapi_membership_registration_email' );

?>
<div class="wrap">

<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_attr_e( 'Registration email', 'fapi-membership' ); ?></h3>
			</div>
			<div class="box-body">
				<?php
				if ( ! empty( $_POST['updated'] ) ) {
					echo '<p>' . esc_attr__( 'Form was updated!', 'fapi-membership' ) . '</p>';
				}
				?>
				<form method="post" action="<?php echo esc_url( admin_url() ) . 'admin.php?page=fapi-membership-registration'; ?>">
					<table class="table-bordered">
						<tr>
							<th><?php esc_attr_e( 'Select e-mail', 'fapi-membership' ); ?></th>		
							<td>
							<select name="registration_email">
								<option value="---">---</option>
							<?php
							if ( ! empty( $emails->posts ) ) {
								foreach ( $emails->posts as $email ) {
									if ( $registration_email === $email->ID ) {
										$selected = 'selected="selected"';
									} else {
										$selected = '';
									}
									echo '<option value="' . esc_attr( $email->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $email->post_title ) . '</option>';
								}
							}
							?>
							</select>
						</td>
							<td class="td_center"><input class="btn btn-success" type="submit" name="registration-email-submit" value="<?php esc_attr_e( 'Save', 'fapi-membership' ); ?>" /></td>
						</tr>
					</table>
					<?php wp_nonce_field( 'fapi-admin-form', 'fapi_admin_form' ); ?>
					<input type="hidden" name="updated" value="yes" />
				</form>                
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>

	</div>
	<div class="clear"></div>
</div>
