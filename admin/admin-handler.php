<?php 

add_action( 'admin_menu', 'fapimembership_add_plugin_admin_menu' );
function fapimembership_add_plugin_admin_menu(){

    add_menu_page(
        __( 'Členské sekce', 'fapi-membership' ),
        __( 'Členské sekce', 'fapi-membership' ),
        'manage_options',
        'fapi-memebership',
        'fapi_memebership_admin_page',
        'dashicons-text-page'
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

function fapi_memebership_admin_page(){
    include( 'views/memberships-page.php' );
}
function fapi_memebership_log_page(){
    include( 'views/memberships-log.php' );
}

// Load admin style sheet and JavaScript.
add_action( 'admin_enqueue_scripts', 'fapi_membership_enqueue_admin_styles' );
function fapi_membership_enqueue_admin_styles() {

    wp_enqueue_style( 'fapi-admin-styles', FMURL . 'assets/admin.css', array() );

}

/**
 * Add metabox to page
 * 
 */
add_action( 'add_meta_boxes', 'fapi_membership_metabox' );
function fapi_membership_metabox(){
    add_meta_box( 'memberships', __( 'Memberships sections', 'fapi-membership' ), 'memberships_meta_box', 'page', 'side', 'high' );
}

function memberships_meta_box( $object, $box ) {
    
    global $post;    
    $memberships = Fapi_Memberships::get_instance();
    $data = $memberships->get_memberships();

    foreach( $data as $id =>$membership ){
        $item = get_post_meta( $post->ID, 'membership_'.$id, true );
        if( !empty( $item ) && $item == $id ){
            $checked = 'checked="checked"';
        }else{
            $checked = '';
        }
        echo '<p><input type="checkbox" name="membership_'.$id.'" value="'.$id.'" '.$checked.' /> <label>'.$membership['name'].'</label></p>';
    }

}

/**
 * Save data for post from metabox
 * 
 */
add_action( 'save_post', 'fapi_membership_meta_box_setup' );
function fapi_membership_meta_box_setup( $post_id ){

    if( empty( $post_id ) ){ return; }

    $memberships = Fapi_Memberships::get_instance();
    $data = $memberships->get_memberships();

    foreach( $data as $id => $membership ){
        $item = get_post_meta( $post_id, 'membership_'.$id, true );
        if( !empty( $_POST['membership_'.$id] ) ){
            update_post_meta( $post_id, 'membership_'.$id, $_POST['membership_'.$id] );
        }else{
            delete_post_meta( $post_id, 'membership_'.$id );
        }        
    }

}

/**
 * Render fields in user profile
 * 
 */
add_action( 'show_user_profile', 'fapi_memberships_userprofile_fields' );
add_action( 'edit_user_profile', 'fapi_memberships_userprofile_fields' );
function fapi_memberships_userprofile_fields( $user ) {  
    
    if ( !current_user_can( 'edit_user', $user->ID ) )
    return false;

    $memberships = Fapi_Memberships::get_instance();
    $data = $memberships->get_memberships();
    
    echo '<h2>'.__( 'Memberships sections', 'fapi-membership' ).'</h2>';
    echo '<table class="form-table">';

    foreach( $data as $id => $membership ){
        echo '<tr>';
            echo '<th>';
                echo '<label>'.$membership['name'].'</label>';
            echo '</th>';
            echo '<td>';
        $item = get_user_meta( $user->ID, 'membership_'.$id, true );
        if( !empty( $item ) && $item == $id ){
            $checked = 'checked="checked"';
        }else{
            $checked = '';
        }
            echo '<input type="checkbox" name="membership_'.$id.'" value="'.$id.'" '.$checked.' /> <label>'.$membership['name'].'</label>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
}
/**
 * Save data for user profile
 * 
 */
add_action( 'personal_options_update', 'save_membership_userprofile_fields' );
add_action( 'edit_user_profile_update', 'save_membership_userprofile_fields' );
function save_membership_userprofile_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) )
    return false;

    $memberships = Fapi_Memberships::get_instance();
    $data = $memberships->get_memberships();

    foreach( $data as $id =>$membership ){
        
        if( !empty( $_POST['membership_'.$id] ) ){
            update_user_meta( $user_id, 'membership_'.$id, $_POST['membership_'.$id] );
        }else{
            delete_user_meta( $user_id, 'membership_'.$id );
        }        
    }


}
    
    