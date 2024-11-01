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
 * Class user register block.
 */
class Dwul_User_Register_Block {

	/**
	 * Start up.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'dwul_add_plugin_setting_page' ) );
	}

	/**
	 * Function to create a menu "Block User".
	 *
	 * @since    1.0.1
	 */
	public function dwul_add_plugin_setting_page() {
		add_options_page(
			'Settings Admin',
			'Block User',
			'manage_options',
			'dwul-block-user-setting',
			array( $this, 'dwul_create_admin_page_form' )
		);
	}

	/**
	 * Create form.
	 *
	 * @since    1.0.1
	 */
	public function dwul_create_admin_page_form() {
		?>
		<section class="disableuser-section">
			<div class="container">
				<div class="wrapper">
					<?php $this->dwul_create_disableuser_field(); ?>
					<div class="disableuser-list">
						<div class="list-table">
							<?php $this->dwul_disable_email_list(); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Create Form.
	 *
	 * @since    1.0.1
	 */
	public function dwul_create_disableuser_field() {
		$args = array(
			'role__not_in' => array( 'administrator' ),
			'orderby'      => 'login',
			'order'        => 'ASC',
			'fields'       => 'all',

		);
		$user_query = get_users( $args );
		?>

		<div class="disableuser-form">
			<h1><?php echo esc_html( 'Disable User', 'disable-wp-user-login' ); ?></h1>
			<form method="post">
				<div class="form-fields">
					<label for="<?php echo esc_attr( 'Select User Email', 'disable-wp-user-login' ); ?>"
						class="input-email-label label"><?php echo esc_html( 'Select User Email', 'disable-wp-user-login' ); ?></label>
					<select id="useremail" name="useremail">
						<option></option>
						<?php
						if ( ! empty( $user_query ) && is_array( $user_query ) ) :
							foreach ( $user_query as $exiting ) :
								?>
						<option id="<?php echo esc_attr( $exiting->ID ); ?>"
							user-role="<?php echo esc_attr( $exiting->roles[0] ); ?>">
								<?php echo esc_html( $exiting->user_email ); ?></option>
								<?php
							endforeach;
						else :
							?>
						<option><?php echo esc_html( 'No User Found.', 'disable-wp-user-login' ); ?></option>
						<?php endif; ?>
					</select>
					<span class="error-message"></span>
				</div>
				<div class="disable-button">
					<a id="disableuser" class="button-primary disable-user-btn"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'disable_user_nonce' ) ); ?>"><?php echo esc_html( 'Disable User', 'disable-wp-user-login' ); ?></a>
					<img src="<?php echo esc_url( DWUL_PLUGIN_PATH . '/assets/images/loaderimage.gif' ); ?>" id="processimage"
						alt="loader-img">
				</div>
			</form>
		</div>
		<?php
	}


	/**
	 * User Listing.
	 *
	 * @since    1.0.1
	 */
	public function dwul_disable_email_list() {

		global $wpdb;
		$output = '';
		$table_name = $wpdb->prefix . 'dwul_disable_user_email';
		$getresult = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %1s',
				$table_name
			)
		);

		if ( count( $getresult ) > 0 ) {
			foreach ( $getresult as $result ) {
				$nonce = wp_create_nonce( 'enable_user_nonce' );
				$output .= "<tr id='userid" . $result->id . "'>";
				$output .= '<td>' . $result->id . '</td>';
				$output .= '<td>' . $result->useremail . '</td>';
				$output .= "<td><a href='javascript:void(0)' data-enb-nonce=" . $nonce . ' id=' . $result->id . '>Enable User</a></td>';
				$output .= '</tr>';
			}
		} else {
			$output .= '<tr><td>No record found..</td></tr>';
		}
		?>

		<table class="customdisableemail">
			<thead>
				<tr class="centered-text">
					<th colspan="3"><?php echo esc_html( 'Disable List', 'disable-wp-user-login' ); ?></th>
				</tr>
				<tr>
					<th scope="col" class="id-list"><?php echo esc_html( 'ID', 'disable-wp-user-login' ); ?></th>
					<th scope="col"><?php echo esc_html( 'Email', 'disable-wp-user-login' ); ?></th>
					<th scope="col"><?php echo esc_html( 'Action', 'disable-wp-user-login' ); ?> </th>
				</tr>
			</thead>
			<tbody>
				<?php echo wp_kses_post( $output ); ?>
			</tbody>
		</table>
		<?php
	}
}

if ( is_admin() ) {
	$wpdru_settings_page = new Dwul_User_Register_Block();
}
