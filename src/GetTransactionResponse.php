<?php
/**
 * Get transaction response
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Get transaction response class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class GetTransactionResponse extends AbstractTransactionResponse {
	/**
	 * From remote JSON.
	 * 
	 * @param string $json JSON.
	 * @return self
	 * @throws \InvalidArgumentException Throws an invalid argument exception if the JSON does not meet expectations.
	 */
	public static function from_remote_json( $json ) {
		$data = \json_decode( $json );

		if ( ! \is_object( $data ) ) {
			throw new \InvalidArgumentException( 'JSON is not an object.' );
		}

		$object_access = new ObjectAccess( $data );

		return new self(
			$object_access->get_property( 'transactionId' ),
			$object_access->get_property( 'createdDateTimestamp' ),
			$object_access->get_property( 'expiryDateTimestamp' ),
			Amount::from_remote_object( $object_access->get_property( 'amount' ) ),
			$object_access->get_property( 'creditor' ),
			$object_access->get_property( 'description' ),
			$object_access->get_property( 'reference' ),
			$object_access->get_property( 'transactionType' )
		);
	}
}
