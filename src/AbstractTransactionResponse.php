<?php
/**
 * Abstract transaction response
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Abstract transaction response class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
abstract class AbstractTransactionResponse {
	/**
	 * Transaction ID.
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'transactionId' )]
	public $transaction_id;

	/**
	 * Created date timestamp.
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'createdDateTimestamp' )]
	public $created_date_timestamp;

	/**
	 * Expiry date timestamp.
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'expiryDateTimestamp' )]
	public $expiry_date_timestamp;

	/**
	 * Amount.
	 * 
	 * @var Amount
	 */
	#[RemoteApiProperty( 'amount' )]
	public $amount;

	/**
	 * Creditor.
	 * 
	 * @var mixed
	 */
	#[RemoteApiProperty( 'creditor' )]
	public $creditor;

	/**
	 * Description
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'description' )]
	public $description;

	/**
	 * Reference.
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'reference' )]
	public $reference;

	/**
	 * Transaction type.
	 * 
	 * @var string
	 */
	#[RemoteApiProperty( 'transactionType' )]
	public $transaction_type;

	/**
	 * Construct abstract transaction response.
	 * 
	 * @param string $transaction_id         Transaction ID.
	 * @param string $created_date_timestamp Created date timestamp.
	 * @param string $expiry_date_timestamp  Expiry date timestamp.
	 * @param Amount $amount                 Amount.
	 * @param mixed  $creditor               Creditor.
	 * @param string $description            Description.
	 * @param string $reference              Reference.
	 * @param string $transaction_type       Transaction type.
	 */
	public function __construct(
		string $transaction_id,
		string $created_date_timestamp,
		string $expiry_date_timestamp,
		Amount $amount,
		mixed $creditor,
		string $description,
		string $reference,
		string $transaction_type
	) {
		$this->transaction_id         = $transaction_id;
		$this->created_date_timestamp = $created_date_timestamp;
		$this->expiry_date_timestamp  = $expiry_date_timestamp;
		$this->amount                 = $amount;
		$this->creditor               = $creditor;
		$this->description            = $description;
		$this->reference              = $reference;
		$this->transaction_type       = $transaction_type;
	}
}
