<?php
/**
 * Create transaction creditor
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Create transaction creditor class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class CreateTransactionCreditor implements RemoteSerializable {
	/**
	 * Merchant country code.
	 *
	 * Minimum length: 2
	 * Maximum length: 2
	 * Example: NL
	 *
	 * Two-letter country code according to ISO 3166-1 alpha-2 standard.
	 * It indicates the origin country of the (sub)merchant.
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'countryCode' )]
	public string $country_code;

	/**
	 * Construct create transaction creditor object.
	 *
	 * @param string $country_code Country code.
	 */
	public function __construct( $country_code ) {
		$this->country_code = $country_code;
	}

	/**
	 * Remote serialize.
	 *
	 * @return mixed
	 */
	public function remote_serialize() {
		$serializer = new RemoteSerializer();

		return $serializer->serialize( $this );
	}
}
