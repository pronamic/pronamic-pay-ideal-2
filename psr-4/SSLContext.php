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
	 * Key.
	 * 
	 * @var string
	 */
	public $key;

	/**
	 * Key password.
	 * 
	 * @var string
	 */
	public $key_password;

	/**
	 * Construct SSL context.
	 * 
	 * @param string $certificate          Certificate.
	 * @param string $key          Key.
	 * @param string $key_password Key password.
	 */
	public function __construct( $certificate, $key, $key_password ) {
		$this->certificate  = $certificate;
		$this->key          = $key;
		$this->key_password = $key_password;
	}
}
