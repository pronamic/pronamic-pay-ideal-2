<?php
/**
 * SSL context
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * SSL context class
 */
final class SSLContext {
	/**
	 * Certificate.
	 * 
	 * @var string
	 */
	public $certificate;

	/**
	 * Private key.
	 * 
	 * @var string
	 */
	public $private_key;

	/**
	 * Private key password.
	 * 
	 * @var string
	 */
	public $private_key_password;

	/**
	 * Construct SSL context.
	 * 
	 * @param string $certificate          Certificate.
	 * @param string $private_key          Private key.
	 * @param string $private_key_password Private key password.
	 */
	public function __construct( $certificate, $private_key, $private_key_password ) {
		$this->certificate          = $certificate;
		$this->private_key          = $private_key;
		$this->private_key_password = $private_key_password;
	}
}
