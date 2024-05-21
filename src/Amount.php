<?php
/**
 * Amount
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Amount class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class Amount implements RemoteSerializable {
	/**
	 * Amount in cents.
	 *
	 * @var int
	 */
	#[RemoteApiProperty( 'amount' )]
	public int $amount;

	/**
	 * Construct amount.
	 *
	 * @param int $amount Amount in cents.
	 */
	public function __construct( int $amount ) {
		$this->amount = $amount;
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
