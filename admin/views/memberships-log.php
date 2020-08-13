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

if ( isset( $_GET['delete'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_GET['edit_nonce'] ) );
	if ( isset( $nonce ) && wp_verify_nonce( $nonce ) ) {
		$log = Fapi_Memberships_Log::get_instance();
		$log->delete_logs();
		wp_safe_redirect( admin_url() . 'admin.php?page=fapi-membership-log' );
	}
}

?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<div style="clear:both;"></div>  

	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_attr_e( 'Záznamy', 'fapi-membership' ); ?></h3>
			</div>
			<p><a href="<?php echo esc_url( wp_nonce_url( admin_url() . 'admin.php?page=fapi-membership-log&delete=log', 'edit_nonce' ) ); ?>" class="btn btn-info" style="margin-left:10px;"><?php esc_attr_e( 'Smazat log', 'fapi-membership' ); ?></a></p>
			<div class="box-body">
				<?php

					$log = Fapi_Memberships_Log::get_instance();
					echo $log->render_table();

				?>
				<div class="clear"></div>
				<?php echo $log->pagination(); ?>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>                 
	</div>
<div class="clear"></div>    
</div>
<div class="clear"></div>
