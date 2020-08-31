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
	public $plugin_slug = 'fapi-membership';

	/**
	 * Limit.
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
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Get logs for table.
	 *
	 * @since 1.2.4
	 */
	public function get_logs() {

		global $wpdb;

		if ( isset( $_GET['offset'] ) && $_GET['offset'] > 1 ) {

			$offset = sanitize_text_field( wp_unslash( $_GET['offset'] ) );
			$start  = ( $offset * $this->limit ) - $this->limit;

			$logs = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . $this->table_name . ' ORDER BY date DESC LIMIT %' . $this->limit . ' OFFSET ' . $start );

		} else {

			$logs = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'fapi_memebership_log ORDER BY date DESC LIMIT ' . $this->limit );

		}

		if ( ! empty( $logs ) ) {

			return $logs;

		}

		return false;

	}

	/**
	 * Render table.
	 *
	 * @since 1.2.4
	 */
	public function render_table() {

		$logs = $this->get_logs();

		if ( false === $logs ) {

			$html = '<p>' . esc_attr__( 'Nenalezeny žádné záznamy', 'fapi-membership' ) . '</p>';

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
	 * Render table head.
	 *
	 * @since 1.2.4
	 */
	public function table_head() {

		$html = '
    		<tr>
              <th>' . esc_attr__( 'Datum', 'fapi-membership' ) . '</th>
              <th>' . esc_attr__( 'Kontext', 'fapi-membership' ) . '</th>
              <th>' . esc_attr__( 'DATA', 'fapi-membership' ) . '</th>
            </tr>
    	';

		return $html;

	}

	/**
	 * Render table line.
	 *
	 * @since 1.2.4
	 * @param object $log log object.
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
	 * Save log.
	 *
	 * @since 1.2.4
	 * @param array $data log data.
	 */
	public function save_log( $data ) {

		if ( ! empty( $data['context'] ) ) {
			$context = $data['context'];
		} else {
			$context = '---';
		}
		$date = gmdate( 'Y-m-d H:i:s' );
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
	 * Empty table.
	 *
	 * @since 1.2.4
	 */
	public function delete_logs() {

		global $wpdb;

		$wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %s', $wpdb->prefix . $this->table_name ) );

	}

	/**
	 * Pagination.
	 *
	 * @since 1.2.4
	 */
	public function pagination() {

		global $wpdb;

		if ( ! empty( $_GET['order_id'] ) ) {
			$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
			$logs     = $this->get_order_logs( $order_id );
		} else {
			$logs = $wpdb->get_results( $wpdb->prepare( 'SELECT ID FROM %s ORDER BY date DESC', $wpdb->prefix . $this->table_name ) );
		}

		$all   = count( $logs );
		$pages = ceil( $all / $this->limit );
		if ( ! empty( $_GET['offset'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_GET['log_nonce'] ) );
			if ( isset( $nonce ) && wp_verify_nonce( $nonce ) ) {
				$current = sanitize_text_field( wp_unslash( $_GET['offset'] ) );
			}
		} else {
			$current = 1;
		}

		$html  = '';
		$html .= '<div class="log-pagination">';

		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			$query_string = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
		}

		if ( 1 !== $pages ) {

			for ( $i = 1; $i <= $pages; $i++ ) {
				if ( $current === $i ) {
					$html .= '<span class="btn btn-default">' . $i . '</span>';
				} else {
					$html .= '<a class="btn btn-primary" href="' . esc_url( wp_nonce_url( admin_url() . 'admin.php?' . $query_string . '&offset=' . $i, 'log_nonce' ) ) . '">' . $i . '</a>';
				}
			}
		}

		$html .= '</div>';

		return $html;

	}


}//end class
