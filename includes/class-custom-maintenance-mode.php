<?php
/**
 * Core plugin class: settings registration and frontend maintenance handling.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Custom_Maintenance_Mode {

	/**
	 * The single option name used to store all plugin settings.
	 */
	const OPTION_NAME = 'cmm_settings';

	/**
	 * Registers all hooks used by the plugin.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'template_redirect', array( $this, 'maybe_show_maintenance_page' ) );
	}

	/**
	 * Adds the "Maintenance Mode" page under Settings.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Maintenance Mode', 'custom-maintenance-mode' ),
			__( 'Maintenance Mode', 'custom-maintenance-mode' ),
			'manage_options',
			'cmm_settings_page',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registers the setting, section and fields via the Settings API.
	 */
	public function register_settings() {
		register_setting(
			'cmm_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_default_settings(),
			)
		);

		add_settings_section(
			'cmm_main_section',
			'',
			'__return_false',
			'cmm_settings_page'
		);

		add_settings_field(
			'cmm_enabled',
			__( 'Enable Maintenance Mode', 'custom-maintenance-mode' ),
			array( $this, 'render_enabled_field' ),
			'cmm_settings_page',
			'cmm_main_section'
		);

		add_settings_field(
			'cmm_title',
			__( 'Maintenance Title', 'custom-maintenance-mode' ),
			array( $this, 'render_title_field' ),
			'cmm_settings_page',
			'cmm_main_section'
		);

		add_settings_field(
			'cmm_message',
			__( 'Maintenance Message', 'custom-maintenance-mode' ),
			array( $this, 'render_message_field' ),
			'cmm_settings_page',
			'cmm_main_section'
		);

		add_settings_field(
			'cmm_end_time',
			__( 'Maintenance End Time', 'custom-maintenance-mode' ),
			array( $this, 'render_end_time_field' ),
			'cmm_settings_page',
			'cmm_main_section'
		);
	}

	/**
	 * Renders the settings page markup.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Maintenance Mode', 'custom-maintenance-mode' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'cmm_settings_group' );
				do_settings_sections( 'cmm_settings_page' );
				submit_button( __( 'Save Settings', 'custom-maintenance-mode' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Field: enable/disable checkbox.
	 */
	public function render_enabled_field() {
		$settings = $this->get_settings();
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]" value="1" <?php checked( 1, $settings['enabled'] ); ?>>
			<?php esc_html_e( 'Enable maintenance mode', 'custom-maintenance-mode' ); ?>
		</label>
		<?php
	}

	/**
	 * Field: maintenance title text input.
	 */
	public function render_title_field() {
		$settings = $this->get_settings();
		?>
		<input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[title]" value="<?php echo esc_attr( $settings['title'] ); ?>">
		<?php
	}

	/**
	 * Field: maintenance message textarea.
	 */
	public function render_message_field() {
		$settings = $this->get_settings();
		?>
		<textarea class="large-text" rows="5" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[message]"><?php echo esc_textarea( $settings['message'] ); ?></textarea>
		<?php
	}

	/**
	 * Field: optional maintenance end time.
	 */
	public function render_end_time_field() {
		$settings = $this->get_settings();
		?>
		<input type="datetime-local" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[end_time]" value="<?php echo esc_attr( $settings['end_time'] ); ?>">
		<p class="description"><?php esc_html_e( 'Optional. Leave empty if you do not want to show an expected completion time.', 'custom-maintenance-mode' ); ?></p>
		<?php
	}

	/**
	 * Sanitizes and validates settings before they are saved.
	 *
	 * @param array $input Raw values submitted from the settings form.
	 * @return array Sanitized values to store in the option.
	 */
	public function sanitize_settings( $input ) {
		$output = $this->get_default_settings();

		$output['enabled'] = ! empty( $input['enabled'] ) ? 1 : 0;

		if ( isset( $input['title'] ) ) {
			$title = sanitize_text_field( wp_unslash( $input['title'] ) );
			if ( '' !== $title ) {
				$output['title'] = $title;
			}
		}

		if ( isset( $input['message'] ) ) {
			$message = sanitize_textarea_field( wp_unslash( $input['message'] ) );
			if ( '' !== $message ) {
				$output['message'] = $message;
			}
		}

		if ( ! empty( $input['end_time'] ) ) {
			$end_time = sanitize_text_field( wp_unslash( $input['end_time'] ) );
			if ( false !== strtotime( $end_time ) ) {
				$output['end_time'] = $end_time;
			}
		}

		return $output;
	}

	/**
	 * Default settings used when an option is missing or incomplete.
	 *
	 * @return array
	 */
	private function get_default_settings() {
		return array(
			'enabled'  => 0,
			'title'    => __( 'Website Under Maintenance', 'custom-maintenance-mode' ),
			'message'  => __( 'We are currently performing maintenance. Please check back soon.', 'custom-maintenance-mode' ),
			'end_time' => '',
		);
	}

	/**
	 * Current settings, merged with defaults for any missing keys.
	 *
	 * @return array
	 */
	private function get_settings() {
		$settings = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( $settings, $this->get_default_settings() );
	}

	/**
	 * Validates the stored end time and returns a future timestamp, or false.
	 *
	 * A past or invalid end time is treated the same as no end time.
	 *
	 * @param string $end_time Raw stored end time value.
	 * @return int|false
	 */
	private function get_valid_end_timestamp( $end_time ) {
		if ( empty( $end_time ) ) {
			return false;
		}

		$timestamp = strtotime( $end_time );

		if ( false === $timestamp || $timestamp <= time() ) {
			return false;
		}

		return $timestamp;
	}

	/**
	 * Shows the maintenance page to disallowed visitors and stops execution.
	 */
	public function maybe_show_maintenance_page() {
		$settings = $this->get_settings();

		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->render_maintenance_page( $settings );
		exit;
	}

	/**
	 * Outputs the maintenance HTML page with the correct HTTP headers.
	 *
	 * @param array $settings Current plugin settings.
	 */
	private function render_maintenance_page( $settings ) {
		status_header( 503 );
		nocache_headers();

		$end_time_ts = $this->get_valid_end_timestamp( $settings['end_time'] );

		if ( $end_time_ts ) {
			header( 'Retry-After: ' . ( $end_time_ts - time() ) );
		}

		$site_name = get_bloginfo( 'name' );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $settings['title'] . ' - ' . $site_name ); ?></title>
			<link rel="stylesheet" href="<?php echo esc_url( CMM_PLUGIN_URL . 'assets/css/maintenance.css' ); ?>">
		</head>
		<body>
			<main class="cmm-maintenance">
				<h1 class="cmm-site-name"><?php echo esc_html( $site_name ); ?></h1>
				<h2 class="cmm-title"><?php echo esc_html( $settings['title'] ); ?></h2>
				<p class="cmm-message"><?php echo nl2br( esc_html( $settings['message'] ) ); ?></p>
				<?php if ( $end_time_ts ) : ?>
					<p class="cmm-end-time">
						<?php
						printf(
							/* translators: %s: expected completion date and time. */
							esc_html__( 'Expected back online: %s', 'custom-maintenance-mode' ),
							esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $end_time_ts ) )
						);
						?>
					</p>
				<?php endif; ?>
			</main>
		</body>
		</html>
		<?php
	}
}
