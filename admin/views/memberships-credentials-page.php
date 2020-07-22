<?php
declare(strict_types = 1);

use Fapi\FapiClient\FapiClientFactory;
use Fapi\FapiClient\Tools\SecurityChecker;

$credentials = new Fapi_Credentials();
// Save credentials if form is sent
$credentials->save_credentials();
$credentials->init_credentials();
?>

<div class="wrap">

<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_attr_e( 'Credentials', 'fapi-membership' ); ?></h3>
			</div>
			<div class="box-body">
				<form method="post" action="">
					<table class="table-bordered">
						<tr>
							<th><?php esc_attr_e( 'Username', 'fapi-membership' ); ?></th>
							<th><?php esc_attr_e( 'API key', 'fapi-membership' ); ?></th>
							<th></th>
						</tr>
						<tr>
							<td><input type="text" name="fapi_username" style="width:100%" 
							<?php
							if ( ! empty( $credentials->get_username() ) ) {
								echo 'value="' . $credentials->get_username() . '"'; }
							?>
							 /></td>
							<td><input type="text" name="fapi_password" style="width:100%" 
							<?php
							if ( ! empty( $credentials->get_password() ) ) {
								echo 'value="' . $credentials->get_password() . '"'; }
							?>
							 /></td>
							<td class="td_center"><input class="btn btn-success" type="submit" name="credentials" value="<?php esc_attr_e( 'Save', 'fapi-membership' ); ?>" /></td>
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
