<?php
/**
 * Amount
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

declare(strict_types=1);

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

	/**
	 * From remote object.
	 *
	 * @param object $data Data.
	 * @return self
	 * @throws \InvalidArgumentException Throws an invalid argument exception if the object does not meet expectations.
	 */
	public static function from_remote_object( $data ) {
		if ( ! \is_object( $data ) ) {
			throw new \InvalidArgumentException( 'JSON is not an object.' );
		}

		$object_access = new ObjectAccess( $data );

		return new self(
			$object_access->get_property( 'amount' )
		);
	}
}
