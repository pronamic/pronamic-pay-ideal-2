<?php
/**
 * Pronamic Pay - iDEAL 2.0
 *
 * @author    Pronamic
 * @copyright 2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 *
 * @wordpress-plugin
 * Plugin Name:       Pronamic Pay - iDEAL 2.0
 * Plugin URI:        https://wp.pronamic.directory/plugins/pronamic-pay-ideal-2/
 * Description:       This plugin contains the Pronamic Pay integration for iDEAL 2.0.
 * Version:           1.1.0
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       pronamic-pay-ideal-2
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://wp.pronamic.directory/plugins/pronamic-pay-ideal-2/
 * GitHub URI:        https://github.com/pronamic/pronamic-pay-ideal-2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Gateway.
 */
add_filter(
	'pronamic_pay_gateways',
	function ( $gateways ) {
		$gateways[] = new \Pronamic\WordPress\Pay\Gateways\IDeal2\Integration(
			[
				'id'            => 'ing-ideal-2-test',
				'name'          => 'ING - iDEAL 2.0 - Test',
				'mode'          => 'test',
				'provider'      => 'ing',
				'acquirer_url'  => 'https://api.sandbox.ideal-acquiring.ing.nl',
				'ideal_hub_url' => 'https://merchant-cpsp-mtls.ext.idealapi.nl/v2',
			]
		);

		$gateways[] = new \Pronamic\WordPress\Pay\Gateways\IDeal2\Integration(
			[
				'id'            => 'ing-ideal-2',
				'name'          => 'ING - iDEAL 2.0',
				'mode'          => 'live',
				'provider'      => 'ing',
				'acquirer_url'  => 'https://api.ideal-acquiring.ing.nl',
				'ideal_hub_url' => 'https://merchant-cpsp-mtls.idealapi.nl/v2',
			]
		);

		return $gateways;
	}
);
