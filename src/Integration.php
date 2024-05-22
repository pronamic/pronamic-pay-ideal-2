<?php
/**
 * Integration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\Core\Gateway as PronamicGateway;

/**
 * Integration class
 */
final class Integration extends AbstractGatewayIntegration {
	/**
	 * Acquirer URL.
	 *
	 * @var string
	 */
	public $acquirer_url;

	/**
	 * The iDEAL hub URL.
	 *
	 * @var string
	 */
	public $ideal_hub_url;

	/**
	 * Construct iDEAL 2.0 integration.
	 *
	 * @param array<string, mixed> $args Arguments.
	 * @return void
	 */
	public function __construct( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'id'            => 'ideal-2',
				'name'          => 'iDEAL 2.0',
				'mode'          => PronamicGateway::MODE_LIVE,
				'url'           => \__( 'https://www.ideal.nl/en/', 'pronamic-pay-ideal-2' ),
				'product_url'   => \__( 'https://www.ideal.nl/en/', 'pronamic-pay-ideal-2' ),
				'manual_url'    => null,
				'dashboard_url' => null,
				'provider'      => null,
				'supports'      => [
					'payment_status_request',
				],
				'acquirer_url'  => '',
				'ideal_hub_url' => '',
			]
		);

		parent::__construct( $args );

		$this->set_mode( $args['mode'] );

		$this->acquirer_url  = $args['acquirer_url'];
		$this->ideal_hub_url = $args['ideal_hub_url'];

		/**
		* Support TLS Client Certificates.
		*
		* @link https://core.trac.wordpress.org/ticket/34883#comment:3
		* @link https://github.com/WordPress/Requests/issues/377
		*/
		\add_action(
			'http_api_curl',
			function ( $handle, $parsed_args ) {
				if ( \array_key_exists( 'ssl_private_key_password', $parsed_args ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
					\curl_setopt( $handle, \CURLOPT_SSLKEYPASSWD, $parsed_args['ssl_private_key_password'] );
				}

				/**
				* Curl blob option.
				*
				* @link https://github.com/php/php-src/blob/php-8.1.0/ext/curl/interface.c#L2935-L2955
				*/
				if ( \array_key_exists( 'ssl_certificate_blob', $parsed_args ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
					\curl_setopt( $handle, \CURLOPT_SSLCERT_BLOB, $parsed_args['ssl_certificate_blob'] );
				}

				if ( \array_key_exists( 'ssl_private_key_blob', $parsed_args ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
					\curl_setopt( $handle, \CURLOPT_SSLKEY_BLOB, $parsed_args['ssl_private_key_blob'] );
				}
			},
			10,
			2
		);
	}

	/**
	 * Get settings fields.
	 *
	 * @return array<int, array<string, mixed>>>
	 */
	public function get_settings_fields() {
		$fields = parent::get_settings_fields();

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Merchant ID', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_merchant_id',
			'type'     => 'text',
			'classes'  => [ 'code' ],
		];

		/**
		 * Acquirer mTLS.
		 */
		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Acquirer mTLS Certificate', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_acquirer_mtls_certificate',
			'type'     => 'file',
			'callback' => [ $this, 'field_certificate' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Acquirer mTLS Private Key', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_acquirer_mtls_private_key',
			'type'     => 'file',
			'callback' => [ $this, 'field_private_key' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Acquirer mTLS Private Key Password', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_acquirer_mtls_private_key_password',
			'type'     => 'text',
			'classes'  => [ 'code' ],
		];

		/**
		 * Acquirer signing.
		 */
		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Acquirer Signing Certificate', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_acquirer_signing_certificate',
			'type'     => 'file',
			'callback' => [ $this, 'field_certificate' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Acquirer Signing Private Key', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_acquirer_signing_private_key',
			'type'     => 'file',
			'callback' => [ $this, 'field_private_key' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'Acquirer Signing Private Key Password', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_acquirer_signing_private_key_password',
			'type'     => 'text',
			'classes'  => [ 'code' ],
		];

		/**
		 * iDEAL Hub mTLS.
		 */
		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'iDEAL Hub mTLS Certificate', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_ideal_hub_mtls_certificate',
			'type'     => 'file',
			'callback' => [ $this, 'field_certificate' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'iDEAL Hub mTLS Private Key', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_ideal_hub_mtls_private_key',
			'type'     => 'file',
			'callback' => [ $this, 'field_private_key' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'iDEAL Hub mTLS Private Key Password', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_ideal_hub_mtls_private_key_password',
			'type'     => 'text',
			'classes'  => [ 'code' ],
		];

		/**
		 * iDEAL Hub signing.
		 */
		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'iDEAL Hub Signing Certificate', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_ideal_hub_signing_certificate',
			'type'     => 'file',
			'callback' => [ $this, 'field_certificate' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'iDEAL Hub Signing Private Key', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_ideal_hub_signing_private_key',
			'type'     => 'file',
			'callback' => [ $this, 'field_private_key' ],
		];

		$fields[] = [
			'section'  => 'general',
			'title'    => \__( 'iDEAL Hub Signing Private Key Password', 'pronamic-pay-ideal-2' ),
			'meta_key' => '_pronamic_gateway_ideal_2_ideal_hub_signing_private_key_password',
			'type'     => 'text',
			'classes'  => [ 'code' ],
		];

		return $fields;
	}

	/**
	 * Field private key.
	 *
	 * @param array<string, mixed> $field Field.
	 * @return void
	 */
	public function field_private_key( $field ) {
		$post_id = (int) \get_the_ID();

		$private_key          = get_post_meta( $post_id, '_pronamic_gateway_ideal_private_key', true );
		$private_key_password = get_post_meta( $post_id, '_pronamic_gateway_ideal_private_key_password', true );
		$number_days_valid    = get_post_meta( $post_id, '_pronamic_gateway_number_days_valid', true );

		if ( ! empty( $private_key_password ) && ! empty( $number_days_valid ) ) {
			if ( \function_exists( '\escapeshellarg' ) ) {
				$filename = __( 'ideal.key', 'pronamic-pay-ideal-2' );

				$command = sprintf(
					'openssl genrsa -aes128 -out %s -passout pass:%s 2048',
					\escapeshellarg( $filename ),
					\escapeshellarg( $private_key_password )
				);

				?>

				<p><?php esc_html_e( 'OpenSSL command', 'pronamic-pay-ideal-2' ); ?></p>
				<input id="pronamic_ideal_openssl_command_key" name="pronamic_ideal_openssl_command_key" value="<?php echo esc_attr( $command ); ?>" type="text" class="large-text code" readonly="readonly"/>

				<?php
			}
		}

		?>
		<p>
			<?php

			if ( ! empty( $private_key ) ) {
				\wp_nonce_field( 'pronamic_pay_download_secret_key', 'pronamic_pay_download_secret_key_nonce' );

				submit_button(
					__( 'Download', 'pronamic-pay-ideal-2' ),
					'secondary',
					'download_secret_key',
					false
				);

				echo ' ';
			}

			printf(
				'<label class="pronamic-pay-form-control-file-button button">%s <input type="file" name="%s" /></label>',
				esc_html__( 'Upload', 'pronamic-pay-ideal-2' ),
				'_pronamic_gateway_ideal_private_key_file'
			);

			?>
		</p>
		<?php
	}

	/**
	 * Field certificate.
	 *
	 * @param array<string, mixed> $field Field.
	 * @return void
	 */
	public function field_certificate( $field ) {
		$post_id = (int) \get_the_ID();

		$meta_value = (string) \get_post_meta( $post_id, $field['meta_key'], true );

		if ( '' === $meta_value ) {
			return;
		}

		$certificate = new Certificate( $meta_value );

		$date_format = \__( 'M j, Y @ G:i', 'pronamic-pay-ideal-2' );

		$defintiions = [
			[
				'name'  => \__( 'SHA Fingerprint', 'pronamic-pay-ideal-2' ),
				'value' => $certificate->get_formatted_fingerprint(),
			],
			[
				'name'  => \__( 'Valid From', 'pronamic-pay-ideal-2' ),
				'value' => \date_i18n( $date_format, $certificate->get_valid_from_time() ),
			],
			[
				'name'  => \__( 'Valid To', 'pronamic-pay-ideal-2' ),
				'value' => \date_i18n( $date_format, $certificate->get_valid_to_time() ),
			],
		];

		?>

		<dl>
			<?php foreach ( $defintiions as $definition ) { ?>

				<dt><?php echo \esc_html( $definition['name'] ); ?></dt>
				<dd><?php echo \esc_html( $definition['value'] ); ?></dd>

			<?php } ?>
		</dl>

		<?php
	}

	/**
	 * Save post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post( $post_id ) {
		$files = [
			'_pronamic_gateway_ideal_2_acquirer_mtls_certificate',
			'_pronamic_gateway_ideal_2_acquirer_mtls_private_key',
			'_pronamic_gateway_ideal_2_acquirer_signing_certificate',
			'_pronamic_gateway_ideal_2_acquirer_signing_private_key',
			'_pronamic_gateway_ideal_2_ideal_hub_mtls_certificate',
			'_pronamic_gateway_ideal_2_ideal_hub_mtls_private_key',
			'_pronamic_gateway_ideal_2_ideal_hub_signing_certificate',
			'_pronamic_gateway_ideal_2_ideal_hub_signing_private_key',
		];

		foreach ( $files as $name ) {
			if ( ! array_key_exists( $name, $_FILES ) ) {
				continue;
			}

			$file = $_FILES[ $name ];

			if ( \UPLOAD_ERR_OK !== $file['error'] ) {
				continue;
			}

			$value = \file_get_contents( \wp_unslash( $file['tmp_name'] ) );

			\update_post_meta( $post_id, $name, $value );
		}
	}

	/**
	 * Get config.
	 *
	 * @param int $post_id Post ID.
	 * @return Config
	 */
	public function get_config( $post_id ) {
		$mode = $this->get_mode();

		$config = new Config(
			(string) $this->get_meta( $post_id, 'ideal_2_merchant_id' ),
			$this->acquirer_url,
			new SSLContext(
				(string) $this->get_meta( $post_id, 'ideal_2_acquirer_mtls_certificate' ),
				(string) $this->get_meta( $post_id, 'ideal_2_acquirer_mtls_private_key' ),
				(string) $this->get_meta( $post_id, 'ideal_2_acquirer_mtls_private_key_password' ),
			),
			new SSLContext(
				(string) $this->get_meta( $post_id, 'ideal_2_acquirer_signing_certificate' ),
				(string) $this->get_meta( $post_id, 'ideal_2_acquirer_signing_private_key' ),
				(string) $this->get_meta( $post_id, 'ideal_2_acquirer_signing_private_key_password' ),
			),
			$this->ideal_hub_url,
			new SSLContext(
				(string) $this->get_meta( $post_id, 'ideal_2_ideal_hub_mtls_certificate' ),
				(string) $this->get_meta( $post_id, 'ideal_2_ideal_hub_mtls_private_key' ),
				(string) $this->get_meta( $post_id, 'ideal_2_ideal_hub_mtls_private_key_password' ),
			),
			new SSLContext(
				(string) $this->get_meta( $post_id, 'ideal_2_ideal_hub_signing_certificate' ),
				(string) $this->get_meta( $post_id, 'ideal_2_ideal_hub_signing_private_key' ),
				(string) $this->get_meta( $post_id, 'ideal_2_ideal_hub_signing_private_key_password' ),
			),
		);

		return $config;
	}

	/**
	 * Get gateway.
	 *
	 * @param int $post_id Post ID.
	 * @return Gateway
	 */
	public function get_gateway( $post_id ) {
		$gateway = new Gateway( $this->get_config( $post_id ) );

		$gateway->set_mode( $this->mode );

		return $gateway;
	}
}
