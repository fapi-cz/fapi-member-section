<?php

/**
 * @package   Fapi membership
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http://musilda.com
 * @copyright 2020 Musilda.com
 */
use Fapi\FapiClient\FapiClientFactory;
use Fapi\FapiClient\Tools\SecurityChecker;

class Fapi_Credentials {

	/**
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $username = null;

	/**
	 * 
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $password = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

        $option = maybe_unserialize( get_option( 'fapi_memberships_credentials' ) );
        if( !empty( $option['username'] ) ){
            $this->username = $option['username'];
        }
        if( !empty( $option['password'] ) ){
            $this->password = $option['password'];
        }
	
	}

	/**
	 * Return username
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function get_username() {
		return $this->username;
	}

	/**
	 * Return password
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function get_password() {
		return $this->password;
	}
	
	/**
	 * Set username
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function set_username( $username ) {
		$this->username = $username;
	}

	/**
	 * Set password
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function set_password( $password ) {
		$this->password = $password;
    }
    
    /**
	 * Save credentials
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function save_credentials() {
		if( !empty( $_POST['credentials'] ) ){
            $option = array();
            if( !empty( $_POST['fapi_username'] ) ){
                $option['username'] = sanitize_text_field( $_POST['fapi_username'] ); 
            }
            if( !empty( $_POST['fapi_password'] ) ){
                $option['password'] = sanitize_text_field( $_POST['fapi_password'] ); 
            }
            if(!empty( $option ) ){
                update_option( 'fapi_memberships_credentials', $option );
            }else{
                delete_option( 'fapi_memberships_credentials' );
            }
        }
	}

	/**
	 * Check connection
	 *
	 * @since    1.0.0
	 *
	 * @return    string
	 */
	public function check_connection() {
		
		$html = '';

		if( !empty( $_GET['check'] ) ){

			$fapiClient = ( new FapiClientFactory() )->createFapiClient( $this->get_username(), $this->get_password() );
			$checkConnection = $fapiClient->checkConnection();
			
			if( empty( $checkConnection ) ){

				$html .= '<div class="t-col-12">';
					$html .= '<div class="toret-box box-info">';
						$html .= '<div class="box-body">';
						 	$html .= '<p style="color:red;">'.esc_attr__( 'Connection failed. Check your credentials.', 'fapi-membership' ).'</p>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';

			}else{

				$html .= '<div class="t-col-12">';
					$html .= '<div class="toret-box box-info">';
						$html .= '<div class="box-body">';
						 	$html .= '<p style="color:green;">'.esc_attr__( 'Connection test was successfull', 'fapi-membership' ).'</p>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';

			}

		}

		return $html;

	}

	
	


}//End class

