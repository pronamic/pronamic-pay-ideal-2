<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Pronamic\WordPress\Pay\Core\Gateway as PronamicGateway;
use Pronamic\WordPress\Pay\Core\ModeTrait;
use Pronamic\WordPress\Pay\Core\PaymentMethod;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Gateway class
 */
final class Gateway extends PronamicGateway {
	use ModeTrait;

	/**
	 * Config.
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * Constructs and initializes an Wordline Open Banking gateway
	 *
	 * @param Config $config Config.
	 */
	public function __construct( Config $config ) {
		parent::__construct();

		$this->config = $config;

		$this->set_method( self::METHOD_HTTP_REDIRECT );

		$this->supports = [
			'payment_status_request',
		];

		$ideal_payment_method = new PaymentMethod( PaymentMethods::IDEAL );
		$ideal_payment_method->set_status( 'active' );

		$this->register_payment_method( $ideal_payment_method );
	}

	/**
	 * Start
	 *
	 * @see PronamicGateway::start()
	 *
	 * @param Payment $payment Payment.
	 */
	public function start( Payment $payment ) {
		$client = new Client( $this->config );
	}

	/**
	 * Update status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 * @throws \Exception Throws an execution if private key cannot be read.
	 */
	public function update_status( Payment $payment ) {
		$client = new Client( $this->config );
	}
}
