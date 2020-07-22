<?php

/**
 * @package   Fapi membership
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http://musilda.com
 * @copyright 2020 Musilda.com
 */

class Fapi_Memberships_Log {


	/**
	 * Instance of this class.
	 *
	 * @since    1.2.4
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Log table name.
	 *
	 * @since    1.2.4
	 *
	 * @var      string
	 */
	protected $table_name = 'fapi_memebership_log';

	/**
	 * Plugin slug.
	 *
	 * @since    1.2.4
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'fapi-membership';

	/**
	 * Limit
	 *
	 * @since    1.2.4
	 *
	 * @var      string
	 */
	protected $limit = 100;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.2.4
	 */
	private function __construct() {

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.2.4
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get logs for table
	 *
	 * @since 1.2.4
	 */
	public function get_logs() {

		global $wpdb;

		if ( isset( $_GET['offset'] ) && $_GET['offset'] > 1 ) {

			$offset = esc_attr( $_GET['offset'] );
			$start  = ( $offset * $this->limit ) - $this->limit;

			$logs = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . $this->table_name . ' ORDER BY date DESC LIMIT ' . $this->limit . ' OFFSET ' . $start . '' );

		} else {

			$logs = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . $this->table_name . ' ORDER BY date DESC LIMIT ' . $this->limit );

		}

		if ( ! empty( $logs ) ) {

			return $logs;

		} else {

			return false;

		}

	}

	/**
	 * Render table
	 *
	 * @since 1.2.4
	 */
	public function render_table() {

		$logs = $this->get_logs();

		if ( false === $logs ) {

			$html = '<p>' . __( 'Nenalezeny žádné záznamy', $this->plugin_slug ) . '</p>';

		} else {

			$html = '<table class="table-bordered" style="table-layout:fixed;">';

			$html .= $this->table_head();

			foreach ( $logs as $log ) {

				$html .= $this->render_table_line( $log );

			}

			$html .= '</table>';

		}

		return $html;

	}

	/**
	 * Render table head
	 *
	 * @since 1.2.4
	 */
	public function table_head() {

		$html = '
    		<tr>
              <th>' . __( 'Datum', $this->plugin_slug ) . '</th>
              <th>' . __( 'Kontext', $this->plugin_slug ) . '</th>
              <th>' . __( 'DATA', $this->plugin_slug ) . '</th>
            </tr>
    	';

		return $html;

	}

	/**
	 * Render table line
	 *
	 * @since 1.2.4
	 */
	public function render_table_line( $log ) {

		$html = '
            <tr>
              <td style="word-wrap:break-word;">' . $log->date . '</td>
              <td style="word-wrap:break-word;">' . $log->context . '</td>
              <td style="word-wrap:break-word;">' . $log->log . '</td>
            </tr>
        ';

		return $html;

	}

	/**
	 * Save log
	 *
	 * @since 1.2.4
	 */
	public function save_log( $data ) {

		if ( ! empty( $data['context'] ) ) {
			$context = $data['context'];
		} else {
			$context = '---';
		}
		$date = date( 'Y-m-d H:i:s' );
		$data = array(
			'log'     => $data['log'],
			'context' => $context,
			'date'    => $date,
		);

		global $wpdb;

		$insert = $wpdb->insert( $wpdb->prefix . $this->table_name, $data );

		return $wpdb->last_query;

	}

	/**
	 * Empty table
	 *
	 * @since 1.2.4
	 */
	public function delete_logs() {

		global $wpdb;

		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $this->table_name );

	}

	/**
	 * Pagination
	 *
	 * @since 1.2.4
	 */
	public function pagination() {

		global $wpdb;

		if ( ! empty( $_GET['order_id'] ) ) {
			$order_id = sanitize_text_field( $_GET['order_id'] );
			$logs     = $this->get_order_logs( $order_id );
		} else {
			$logs = $wpdb->get_results( 'SELECT ID FROM ' . $wpdb->prefix . $this->table_name . ' ORDER BY date DESC' );
		}

		$all   = count( $logs );
		$pages = ceil( $all / $this->limit );
		if ( ! empty( $_GET['offset'] ) ) {
			$current = $_GET['offset'];
		} else {
			$current = 1;
		}

		$html  = '';
		$html .= '<div class="log-pagination">';

		$query_string = $_SERVER['QUERY_STRING'];

		if ( $pages != 1 ) {

			for ( $i = 1; $i <= $pages; $i++ ) {
				if ( $current == $i ) {
					$html .= '<span class="btn btn-default">' . $i . '</span>';
				} else {
					$html .= '<a class="btn btn-primary" href="' . admin_url() . 'admin.php?' . $query_string . '&offset=' . $i . '">' . $i . '</a>';
				}
			}
		}

		$html .= '</div>';

		return $html;

	}


}//end class
