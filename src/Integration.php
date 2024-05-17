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
			]
		);

		parent::__construct( $args );

		$this->set_mode( $args['mode'] );
	}

	/**
	 * Get settings fields.
	 *
	 * @return array<int, array<string, mixed>>>
	 */
	public function get_settings_fields() {
		$fields = parent::get_settings_fields();

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

		$config = new Config();

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
