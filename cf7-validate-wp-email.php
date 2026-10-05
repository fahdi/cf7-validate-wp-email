<?php
/**
 * Contact form 7 Validate Email
 *
 * @since             2020.11.0
 * @package           cf7_validate_email
 *
 * @wordpress-plugin
 * Plugin Name:       Contact form 7 Validate Email by checking against wp user
 * Plugin URI:        http://www.fahdmurtaza.com/get-quote
 * Description:       Rejects Contact Form 7 submissions whose email address does not belong to an existing WordPress user.
 * Version:           1.3.0
 * Author:            Fahad Murtaza
 * Author URI:        http://www.fahdmurtaza.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-validate-email
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings. Every value can be overridden with the matching filter.
 *
 * - form_id:      CF7 form ID to validate (0 = all forms).
 * - field:        CF7 field name holding the email address.
 * - message:      Generic rejection message. Must NOT reveal whether the
 *                 address is registered (user enumeration).
 * - rate_limit:   Max failed attempts per IP within rate_window seconds (0 = off).
 * - rate_window:  Window in seconds for the rate limit.
 */
function gravixar_settings() {
	$defaults = array(
		'form_id'     => 1315,
		'field'       => 'your-email',
		'message'     => __( 'We were unable to process this request. Please contact the site owner for access.', 'cf7-validate-email' ),
		'rate_limit'  => 5,
		'rate_window' => 10 * MINUTE_IN_SECONDS,
	);

	$settings = apply_filters( 'cf7_validate_wp_email_settings', $defaults );

	return wp_parse_args( is_array( $settings ) ? $settings : array(), $defaults );
}

add_filter( 'wpcf7_validate', 'gravixar_email_exists', 10, 2 );

/**
 * Validate the submitted email against existing WordPress users.
 *
 * The same generic message is returned for malformed addresses, unknown
 * addresses and rate-limited clients so the response is not an oracle for
 * which addresses have accounts.
 *
 * @param WPCF7_Validation $result
 * @param array            $tags
 * @return WPCF7_Validation
 */
function gravixar_email_exists( $result, $tags ) {
	$settings = gravixar_settings();
	$form_id  = (int) $settings['form_id'];

	if ( $form_id > 0 ) {
		$posted_form = isset( $_POST['_wpcf7'] ) ? absint( wp_unslash( $_POST['_wpcf7'] ) ) : 0;
		if ( $posted_form !== $form_id ) {
			return $result;
		}
	}

	$submission = class_exists( 'WPCF7_Submission' ) ? WPCF7_Submission::get_instance() : null;
	if ( ! $submission ) {
		return $result;
	}

	$field = (string) $settings['field'];
	$email = $submission->get_posted_data( $field );
	if ( is_array( $email ) ) {
		$email = reset( $email );
	}
	$email = sanitize_email( (string) $email );

	if ( gravixar_is_rate_limited( $settings ) ) {
		$result->invalidate( $field, $settings['message'] );
		return $result;
	}

	if ( ! is_email( $email ) || ! email_exists( $email ) ) {
		gravixar_record_failed_attempt( $settings );

		/**
		 * Fires when a submission is rejected. Hook this to alert on abuse.
		 *
		 * @param string $email Sanitized email that was rejected ('' if malformed).
		 * @param string $ip    Client IP.
		 */
		do_action( 'cf7_validate_wp_email_rejected', $email, gravixar_client_ip() );

		$result->invalidate( $field, $settings['message'] );
	}

	return $result;
}

/**
 * Best-effort client IP (REMOTE_ADDR only; proxies must be configured upstream).
 */
function gravixar_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

function gravixar_rate_limit_key() {
	return 'cf7vwe_rl_' . md5( gravixar_client_ip() );
}

/**
 * @param array $settings
 * @return bool
 */
function gravixar_is_rate_limited( $settings ) {
	$limit = (int) $settings['rate_limit'];
	if ( $limit <= 0 ) {
		return false;
	}

	$attempts = (int) get_transient( gravixar_rate_limit_key() );

	return $attempts >= $limit;
}

/**
 * @param array $settings
 */
function gravixar_record_failed_attempt( $settings ) {
	$limit = (int) $settings['rate_limit'];
	if ( $limit <= 0 ) {
		return;
	}

	$key      = gravixar_rate_limit_key();
	$attempts = (int) get_transient( $key );
	set_transient( $key, $attempts + 1, max( 60, (int) $settings['rate_window'] ) );
}
