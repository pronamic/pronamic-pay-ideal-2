<?php
/**
 * Create transaction response
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Create transaction response class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class CreateTransactionResponse extends AbstractTransactionResponse {
	/**
	 * Links.
	 * 
	 * @var Links
	 */
	public Links $links;

	/**
	 * Construct create transaction response.
	 * 
	 * @param string $transaction_id         Transaction ID.
	 * @param string $created_date_timestamp Created date timestamp.
	 * @param string $expiry_date_timestamp  Expiry date timestamp.
	 * @param Amount $amount                 Amount.
	 * @param mixed  $creditor               Creditor.
	 * @param string $description            Description.
	 * @param string $reference              Reference.
	 * @param string $transaction_type       Transaction type.
	 * @param Links  $links                  Links.
	 */
	public function __construct(
		string $transaction_id,
		string $created_date_timestamp,
		string $expiry_date_timestamp,
		Amount $amount,
		mixed $creditor,
		string $description,
		string $reference,
		string $transaction_type,
		Links $links
	) {
		parent::__construct(
			$transaction_id,
			$created_date_timestamp,
			$expiry_date_timestamp,
			$amount,
			$creditor,
			$description,
			$reference,
			$transaction_type
		);

		$this->links = $links;
	}

	/**
	 * From remote object..
	 * 
	 * @param mixed $data Object.
	 * @return self
	 * @throws \InvalidArgumentException Throws an invalid argument exception if the JSON does not meet expectations.
	 */
	public static function from_remote_object( $data ) {
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
			$object_access->get_property( 'transactionType' ),
			Links::from_remote_object( $object_access->get_property( 'links' ) ),
		);
	}
}
