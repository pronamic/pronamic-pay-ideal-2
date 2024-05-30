<?php
/**
 * Create transaction request
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Create transaction request class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class CreateTransactionRequest implements RemoteSerializable {
	/**
	 * Amount.
	 *
	 * @var Amount
	 */
	#[RemoteApiProperty( 'amount' )]
	public Amount $amount;

	/**
	 * Transaction description
	 *
	 * Example: Cookie
	 * Minimum length: 1
	 * Maximum length: 35
	 *
	 * The description of the reason for the transaction (or transactions
	 * associated with a QR code). The text here is shown to the user while
	 * he/she is trying to make the transaction. It will also be shown on the
	 * bank statement of the user after payment (as part of the remittance
	 * information). Corresponding to iDEAL 1.0 field 'transaction.description'
	 * or to iDEAL 1.0 QR code field 'description'.
	 *
	 * @var string
	 */
	#[RemoteApiProperty( 'description' )]
	public string $description;

	/**
	 * Reference.
	 *
	 * Example: iDEALpurchase21
	 * Minimum length: 1
	 * Maximum length: 35
	 * Pattern: [a-zA-Z0-9]{1,35}
	 *
	 * The external transaction reference used to reference the transaction
	 * (or transactions associated with a QR code) in the calling party's
	 * system. Corresponding to iDEAL 1.0 field 'transaction.purchaseId' or
	 * iDEAL 1.0 QR code field 'reference' It will also be shown on the bank
	 * statement of the user after payment (as part of the remittance
	 * information). As this field is used to reconcile, a restricted set of
	 * characters must be used that can be supported within SEPA.
	 *
	 * @var string
	 */
	#[RemoteApiProperty( 'reference' )]
	public string $reference;

	/**
	 * Creditor.
	 *
	 * @var CreateTransactionCreditor
	 */
	#[RemoteApiProperty( 'creditor' )]
	public CreateTransactionCreditor $creditor;

	/**
	 * Transaction callback URL.
	 *
	 * Example: https://checkout.company.com/transaction/webhook/transaction-callback
	 *
	 * Callback URL to which the Merchant/CPSP will be notified about the
	 * transaction payout/cancel. If it's not set, no callback will be done on
	 * a successful payment. This URL will not be modified by iDEAL, therefore
	 * the callback will be done on the provided URL. Also, the provided
	 * callback url should follow the endpoints provided in the callback APIs.
	 * A minimum length of 1 and a maximum length of 512 should be considered
	 * for this field. (They are not explicitly mentioned as properties since
	 * this causes code generation issues for URI fields)
	 * 
	 * @var string|null
	 */
	#[RemoteApiProperty( 'transactionCallbackUrl' )]
	public ?string $transaction_callback_url;

	/**
	 * Return URL.
	 *
	 * The return URL to redirect a user back to the merchant website once the
	 * transaction is completed. Corresponding to iDEAL 1.0 field
	 * 'merchantReturnUrl'. A minimum length of 1 and a maximum length of 580
	 * should be considered for this field. (They are not explicitly mentioned
	 * as properties since this causes code generation issues for URI fields)
	 *
	 * @var string
	 */
	#[RemoteApiProperty( 'returnUrl' )]
	public string $return_url;

	/**
	 * Issuer ID
	 *
	 * The business identifier code for the Issuer. Corresponding to iDEAL 1.0
	 * field 'issuer.issuerId'.
	 *
	 * @link https://en.wikipedia.org/wiki/ISO_9362
	 * @var string
	 */
	#[RemoteApiProperty( 'issuerId' )]
	public ?string $issuer_id;

	/**
	 * Construct create transaction request.
	 *
	 * @param Amount                    $amount      Amount.
	 * @param string                    $description Description.
	 * @param string                    $reference   Reference.
	 * @param CreateTransactionCreditor $creditor    Creditor.
	 * @param string                    $return_url  Return URL.
	 */
	public function __construct( Amount $amount, string $description, string $reference, CreateTransactionCreditor $creditor, string $return_url ) {
		$this->amount      = $amount;
		$this->description = $description;
		$this->reference   = $reference;
		$this->creditor    = $creditor;
		$this->return_url  = $return_url;
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
