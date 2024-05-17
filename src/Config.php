<?php
/**
 * Config
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use JsonSerializable;
use Pronamic\WordPress\Pay\Core\GatewayConfig;

/**
 * Config class
 */
final class Config extends GatewayConfig implements JsonSerializable {
	/**
	 * Merchant ID.
	 * 
	 * @var string
	 */
	public $merchant_id;

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
	 * Acquirer MTLS SSL context.
	 * 
	 * @var SSLContext
	 */
	public $acquirer_mtls_ssl;

	/**
	 * Acquirer signing SSL context.
	 * 
	 * @var SSLContext
	 */
	public $acquirer_signing_ssl;

	/**
	 * The IDEAL hub MTLS SSL context.
	 * 
	 * @var SSLContext
	 */
	public $ideal_hub_mtls_ssl;

	/**
	 * The IDEAL hub signing SSL context.
	 * 
	 * @var SSLContext
	 */
	public $ideal_hub_signing_ssl;

	/**
	 * Construct config.
	 * 
	 * @param string $merchant_id   Merchant ID.
	 * @param string $acquirer_url  Acquirer URL.
	 * @param string $ideal_hub_url The iDEAL hub URL.
	 */
	public function __construct(
		string $merchant_id,
		string $acquirer_url,
		SSLContext $acquirer_mtls_ssl,
		SSLContext $acquirer_signing_ssl,
		string $ideal_hub_url,
		SSLContext $ideal_hub_mtls_ssl,
		SSLContext $ideal_hub_signing_ssl
	) {
		$this->merchant_id = $merchant_id;

		$this->acquirer_url         = $acquirer_url;
		$this->acquirer_mtls_ssl    = $acquirer_mtls_ssl;
		$this->acquirer_signing_ssl = $acquirer_signing_ssl;

		$this->ideal_hub_url         = $ideal_hub_url;
		$this->ideal_hub_mtls_ssl    = $ideal_hub_mtls_ssl;
		$this->ideal_hub_signing_ssl = $ideal_hub_signing_ssl;
	}

	/**
	 * Serialize to JSON.
	 *
	 * @link https://www.w3.org/TR/json-ld11/#specifying-the-type
	 * @return object
	 */
	public function jsonSerialize(): object {
		return (object) [
			'@type' => __CLASS__,
		];
	}
}
