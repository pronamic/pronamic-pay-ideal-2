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

		return $fields;
	}

	/**
	 * Get config.
	 *
	 * @param int $post_id Post ID.
	 * @return Config
	 */
	public function get_config( $post_id ) {
		$mode = $this->get_mode();

		$merchant_id = (string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_merchant_id' );

		$config = new Config(
			$merchant_id,
			$this->acquirer_url,
			new SSLContext(
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_acquirer_mtls_ssl_certificate' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_acquirer_mtls_ssl_private_key' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_acquirer_mtls_ssl_private_key_password' ),
			),
			new SSLContext(
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_acquirer_signing_ssl_certificate' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_acquirer_signing_ssl_private_key' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_acquirer_signing_ssl_private_key_password' ),
			),
			$this->ideal_hub_url,
			new SSLContext(
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_ideal_hub_mtls_ssl_certificate' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_ideal_hub_mtls_ssl_private_key' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_ideal_hub_mtls_ssl_private_key_password' ),
			),
			new SSLContext(
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_ideal_hub_signing_ssl_certificate' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_ideal_hub_signing_ssl_private_key' ),
				(string) $this->get_meta( $post_id, '_pronamic_gateway_ideal_2_ideal_hub_signing_ssl_private_key_password' ),
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
