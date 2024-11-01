<?php
/**
 * Exit if accessed directly
 *
 * @package    disable-wp-user-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Class for the ajax calls.
 */
class Dwul_User_Register_Ajax_Callback {

	/**
	 * Start up
	 */
	public function __construct() {

		add_action( 'wp_ajax_dwul_action_callback', array( $this, 'dwul_action_callback' ) );
		add_action( 'wp_ajax_nopriv_dwul_action_callback', array( $this, 'dwul_action_callback' ) );
		add_action( 'wp_ajax_dwul_enable_user_email', array( $this, 'dwul_enable_user_email' ) );
		add_action( 'wp_ajax_nopriv_dwul_enable_user_email', array( $this, 'dwul_enable_user_email' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'dwul_ajax_script' ) );
		add_action( 'wp_login', array( $this, 'dwul_disable_user_call_back' ), 10, 2 );
		add_filter( 'login_message', array( $this, 'dwul_disable_user_login_message' ) );
	}

	/**
	 * Ajax Action
	 */
	public function dwul_action_callback() {

		global $wpdb;

		$disable_nonce = isset( $_REQUEST['nonce_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce_data'] ) ) : '';
		if ( ! wp_verify_nonce( $disable_nonce, 'disable_user_nonce' ) ) {
			$response_message = __( 'Invalid Nonce', 'disable-wp-user-login' );
			echo esc_html( $response_message );
			die();
		}

		$disableemail       = isset( $_REQUEST['useremail'] ) ?
		sanitize_text_field( wp_unslash( $_REQUEST['useremail'] ) ) : '';
		$table_name         = $wpdb->prefix . 'dwul_disable_user_email';
		$exitingusertbl     = $wpdb->prefix . 'users';
		$registered_users   = $wpdb->get_col( $wpdb->prepare( 'SELECT `user_email` FROM %1s', $exitingusertbl ) );
		$existing_users     = $wpdb->get_col( $wpdb->prepare( 'SELECT `useremail` FROM %1s', $table_name ) );
		$user               = get_user_by( 'email', $disableemail );

		if ( 'administrator' == $user->roles[0] ) {
			$response_message = __( 'User with Administrator role can\'t be disabled.', 'disable-wp-user-login' );
			echo esc_html( $response_message );
			die();
		}

		if ( ! in_array( $disableemail, $registered_users ) ) {
			$response_message = __( 'User does not exist', 'disable-wp-user-login' );
			echo esc_html( $response_message );
			die();
		}

		if ( in_array( $disableemail, $existing_users ) ) {
			$response_message = __( 'User is already disabled.', 'disable-wp-user-login' );
			echo esc_html( $response_message );
			die();
		}

		$insertdata = $wpdb->insert(
			$table_name,
			array( 'useremail' => $disableemail ),
			array( '%s' )
		);
		$response_message = ( $insertdata ) ?
			__( 'success', 'disable-wp-user-login' ) :
			__( 'Something went wrong.', 'disable-wp-user-login' );
			echo esc_html( $response_message );
			die();
	}

	/**
	 * Add scripts.
	 *
	 * @since    1.0.1
	 */
	public function dwul_ajax_script() {

		wp_enqueue_style(
			'user_custom_style',
			DWUL_PLUGIN_PATH . '/assets/css/admin-user-disable.css',
			array(),
			DWUL_PLUGIN_VERSION
		);

		wp_enqueue_style(
			'select2-style',
			DWUL_PLUGIN_PATH . '/assets/css/select2.min.css',
			array(),
			DWUL_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'select2-script',
			DWUL_PLUGIN_PATH . '/assets/js/select2.min.js',
			array( 'jquery' ),
			DWUL_PLUGIN_VERSION,
			true
		);

		wp_enqueue_script(
			'backend-custom-script',
			DWUL_PLUGIN_PATH . '/assets/js/admin-user-disable.js',
			array( 'jquery' ),
			DWUL_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'backend-custom-script',
			'backend_custom_object',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'error'         => __( 'Something went wrong with database.', 'disable-wp-user-login' ),
			)
		);

	}

	/**
	 * Add options page.
	 *
	 * @since    1.0.1
	 * @param string $user_login user login email.
	 * @param string $user null.
	 */
	public function dwul_disable_user_call_back( $user_login, $user = null ) {

		global $wpdb;
		$array = array();
		$usertable = $wpdb->prefix . 'dwul_disable_user_email';

		if ( ! $user ) {
			$user = get_user_by( 'login', $user_login );
		}
		if ( ! $user ) {
			// not logged in - definitely not disabled.
			return;
		}

		$existing_users = $wpdb->get_col( $wpdb->prepare( 'SELECT `useremail` FROM %1s', $usertable ) );

		foreach ( $existing_users as $email ) {

			$result = get_user_by( 'email', $email );

			$array[] = $result->data->user_login;
		}

		// Is the use logging in disabled?
		if ( in_array( $user_login, $array ) ) {
			// Clear cookies, a.k.a log user out.
			wp_clear_auth_cookie();

			// Build login URL and then redirect.
			$login_url = site_url( 'wp-login.php', 'login' );
			$login_url = add_query_arg( 'disabled', '1', $login_url );
			wp_redirect( $login_url );
			exit;
		}
	}

	/**
	 * Disable user login message
	 *
	 * @since    1.0.1
	 * @param string $message the message html.
	 */
	public function dwul_disable_user_login_message( $message ) {

		// Show the error message if it seems to be a disabled user.
		if ( isset( $_GET['disabled'] ) && 1 == $_GET['disabled'] ) {
			$message = '<div id="login_error">' .
				apply_filters( 'ja_disable_users_notice', __( 'User Account Disable', 'ja_disable_users' ) ) .
			'</div>';
		}

		return $message;
	}

	/**
	 * Add options page.
	 *
	 * @since    1.0.1
	 */
	public function dwul_enable_user_email() {

		global $wpdb;
		$enable_nonce = isset( $_REQUEST['nonce_data'] ) ?
		sanitize_text_field( wp_unslash( $_REQUEST['nonce_data'] ) ) : '';
		if ( ! wp_verify_nonce( $enable_nonce, 'enable_user_nonce' ) ) {
			$successresponse = '90';
			echo esc_html( $successresponse );
			die();
		}
		$tblname = $wpdb->prefix . 'dwul_disable_user_email';
		$activateuserid = isset( $_REQUEST['activateuserid'] ) ?
		sanitize_text_field( wp_unslash( $_REQUEST['activateuserid'] ) ) : '';
		$delquery = $wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE id = %d', $tblname, $activateuserid ) );
		$response = ( $delquery ) ? '1' : '20';
		echo esc_html( $response );
		die();

	}

}
$wpdru_ajax_call_back = new Dwul_User_Register_Ajax_Callback();
