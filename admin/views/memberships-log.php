<?php

    if( isset( $_GET['delete'] ) ){

        $log = Fapi_Memberships_Log::get_instance();
        $log->delete_logs();
        wp_redirect( admin_url() . 'admin.php?page=fapi-membership-log' );

    }

?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <div style="clear:both;"></div>  

    <div class="t-col-12">
        <div class="toret-box box-info">
            <div class="box-header">
                <h3 class="box-title"><?php _e( 'Záznamy', 'fapi-membership' ); ?></h3>
            </div>
            <p><a href="<?php echo admin_url(); ?>admin.php?page=fapi-membership-log&delete=log" class="btn btn-info" style="margin-left:10px;"><?php _e( 'Smazat log', 'fapi-membership' ); ?></a></p>
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
