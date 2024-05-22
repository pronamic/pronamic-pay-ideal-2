<?php
/**
 * Transaction status
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Pronamic\WordPress\Pay\Payments\PaymentStatus;

/**
 * Transaction status enum
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
enum TransactionStatus: string {
	/**
	 * The transaction is created and is open to be scanned by the user. The final status is not known.
	 * 
	 * @var string
	 */
	case Open = 'OPEN';

	/**
	 * The transaction has been identified and locked by a user. The final status is not known.
	 * 
	 * @var string
	 */
	case Identified = 'IDENTIFIED';

	/**
	 * The transaction has expired.
	 * 
	 * @var string
	 */
	case Expired = 'EXPIRED';

	/**
	 * The transaction was canceled.
	 * 
	 * @var string
	 */
	case Cancelled = 'CANCELLED';

	/**
	 * The transaction has succeeded.
	 * 
	 * @var string
	 */
	case Success = 'SUCCESS';

	/**
	 * The transaction has failed.
	 * 
	 * @var string
	 */
	case Failure = 'FAILURE';

	/**
	 * To Pronamic status.
	 * 
	 * @return string
	 */
	public function to_pronamic_status() {
		// phpcs:ignore PHPCompatibility.Variables.ForbiddenThisUseContexts.OutsideObjectContext -- Incorrect error? 
		return match ( $this ) {
			TransactionStatus::Open       => PaymentStatus::OPEN,
			TransactionStatus::Identified => PaymentStatus::OPEN,
			TransactionStatus::Expired    => PaymentStatus::EXPIRED,
			TransactionStatus::Cancelled  => PaymentStatus::CANCELLED,
			TransactionStatus::Success    => PaymentStatus::SUCCESS,
			TransactionStatus::Failure    => PaymentStatus::FAILURE,
		};
	}
}
