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

		\Pronamic\WpExtendedSslSupport\Plugin::bootstrap();
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
		 * The iDEAL Hub mTLS fields.
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
		 * The iDEAL Hub signing fields.
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

		$fields[] = [
			'section'     => 'advanced',
			'meta_key'    => '_pronamic_gateway_ideal_2_reference',
			'title'       => __( 'Reference', 'pronamic-pay-ideal-2' ),
			'type'        => 'text',
			'classes'     => [ 'regular-text', 'code' ],
			'tooltip'     => sprintf(
				/* translators: %s: <code>reference</code> */
				__( 'The iDEAL %s parameter.', 'pronamic-pay-ideal-2' ),
				sprintf( '<code>%s</code>', 'purchaseID' )
			),
			'description' => sprintf(
				'%s %s<br />%s',
				__( 'Available tags:', 'pronamic-pay-ideal-2' ),
				sprintf(
					'<code>%s</code> <code>%s</code>',
					'{order_id}',
					'{payment_id}'
				),
				sprintf(
					/* translators: %s: default code */
					__( 'Default: <code>%s</code>', 'pronamic-pay-ideal-2' ),
					'{payment_id}'
				)
			),
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

		$private_key = get_post_meta( $post_id, $field['meta_key'], true );

		?>
		<p>
			<?php

			printf(
				'<label class="pronamic-pay-form-control-file-button button">%s <input type="file" name="%s" /></label>',
				esc_html__( 'Upload', 'pronamic-pay-ideal-2' ),
				'_pronamic_gateway_ideal_private_key_file'
			);

			if ( ! empty( $private_key ) ) {
				\wp_nonce_field( 'pronamic_pay_download_secret_key', 'pronamic_pay_download_secret_key_nonce' );

				echo ' ';

				submit_button(
					__( 'Download', 'pronamic-pay-ideal-2' ),
					'secondary',
					'download_secret_key',
					false
				);
			}

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
		];

		$valid_from = $certificate->get_valid_from_time();

		if ( null !== $valid_from ) {
			$defintiions[] = [
				'name'  => \__( 'Valid From', 'pronamic-pay-ideal-2' ),
				'value' => \date_i18n( $date_format, $valid_from ),
			];
		}

		$valid_to = $certificate->get_valid_to_time();

		if ( null !== $valid_to ) {
			$defintiions[] = [
				'name'  => \__( 'Valid To', 'pronamic-pay-ideal-2' ),
				'value' => \date_i18n( $date_format, $valid_to ),
			];
		}

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
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Is handled upstream?

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
			if ( ! isset( $_FILES[ $name ]['error'] ) ) {
				continue;
			}

			if ( ! isset( $_FILES[ $name ]['tmp_name'] ) ) {
				continue;
			}

			if ( \UPLOAD_ERR_OK !== $_FILES[ $name ]['error'] ) {
				continue;
			}

			$value = \file_get_contents( \sanitize_text_field( \wp_unslash( $_FILES[ $name ]['tmp_name'] ) ) );

			\update_post_meta( $post_id, $name, $value );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Get config.
	 *
	 * @param int $post_id Post ID.
	 * @return Config
	 */
	public function get_config( $post_id ) {
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
			)
		);

		$config->reference = (string) $this->get_meta( $post_id, 'ideal_2_reference' );

		$config->mode = $this->get_mode();

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

		return $gateway;
	}
}
