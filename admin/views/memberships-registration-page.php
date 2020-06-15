<?php

if( !empty( $_POST['registration-email-submit'] ) ){
    if( !empty( $_POST['registration_email'] ) ){
        update_option( 'fapi_membership_registration_email', sanitize_text_field( $_POST['registration_email'] ) );
    }
}


$args = array(
    'post_type' => 'fapi_emails',
    'post_status' => 'publish',
    'numberposts' => -1
);
$emails = new WP_Query( $args );
$registration_email = get_option( 'fapi_membership_registration_email' );

?>
<div class="wrap">

<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

<div class="t-col-12">
        <div class="toret-box box-info">
            <div class="box-header">
                <h3 class="box-title"><?php esc_attr_e( 'Registration email', 'fapi-membership' ); ?></h3>
            </div>
            <div class="box-body">
                <form method="post" action="<?php echo admin_url().'admin.php?page=fapi-memebership'; ?>">
                    <table class="table-bordered">
                        <tr>
                            <th><?php esc_attr_e( 'Select e-mail', 'fapi-membership' ); ?></th>
                        
                            <td>
                            <select name="registration_email">
                                <option value="---">---</option>
                            <?php
                            if( !empty( $emails->posts ) ){
                                foreach( $emails->posts as $email ){
                                    if( $registration_email == $email->ID ){
                                        $selected = 'selected="selected"';
                                    }else{
                                        $selected = '';
                                    }
                                    echo '<option value="'.$email->ID.'" '.$selected.'>'.$email->post_title.'</option>';
                                }
                            }
                            ?>
                            </select>
                        </td>
                            <td class="td_center"><input class="btn btn-success" type="submit" name="registration-email-submit" value="<?php esc_attr_e( 'Save', 'fapi-membership' ); ?>" /></td>
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