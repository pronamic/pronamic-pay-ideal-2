<?php
/**
 * Certificate
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Certificate class
 */
class Certificate {
	/**
	 * Privacy Enhanced Mail (PEM).
	 *
	 * @var string
	 */
	private $pem;

	/**
	 * Construct certificate.
	 *
	 * @param string $pem PEM.
	 */
	public function __construct( $pem ) {
		$this->pem = $pem;
	}

	/**
	 * PEM to DER.
	 *
	 * @link https://knowledge.digicert.com/solution/SO26449.html
	 * @link https://www.openssl.org/docs/man1.0.2/man1/x509.html
	 * @link https://stackoverflow.com/questions/36503814/why-are-pem2der-and-der2pem-not-inverses
	 * @return string
	 */
	public function get_der() {
		$value = $this->pem;

		$value = \str_replace( '-----BEGIN CERTIFICATE-----', '', $value );
		$value = \str_replace( '-----END CERTIFICATE-----', '', $value );
		$value = \trim( $value );

		$value = \base64_decode( $value );

		return $value;
	}

	/**
	 * Get fingerprint.
	 *
	 * @return string
	 */
	public function get_fingerprint() {
		$fingerprint = \hash( 'sha1', $this->get_der() );

		/**
		 * Uppercase
		 *
		 * Cannot find private certificate file with fingerprint: b4845cb5cbcee3e1e0afef2662552a2365960e72
		 * (Note: Some acquirers only accept fingerprints in uppercase. Make the value of "KeyName" in your XML data uppercase.).
		 * https://www.ideal-checkout.nl/simulator/
		 *
		 * @since 1.1.11
		 */
		return \strtoupper( $fingerprint );
	}

	/**
	 * Get formatted fingerprint.
	 *
	 * @return string
	 */
	public function get_formatted_fingerprint() {
		$fingerprint = $this->get_fingerprint();

		return \implode( ':', \str_split( $fingerprint, 2 ) );
	}

	/**
	 * Get 'Valid from' timestamp.
	 *
	 * @return int|null
	 */
	public function get_valid_from_time() {
		$info = \openssl_x509_parse( $this->pem );

		if ( ! \is_array( $info ) || ! \array_key_exists( 'validFrom_time_t', $info ) ) {
			return null;
		}

		return $info['validFrom_time_t'];
	}

	/**
	 * Get 'Valid to' timestamp.
	 *
	 * @return int|null
	 */
	public function get_valid_to_time() {
		$info = \openssl_x509_parse( $this->pem );

		if ( ! \is_array( $info ) || ! \array_key_exists( 'validTo_time_t', $info ) ) {
			return null;
		}

		return $info['validTo_time_t'];
	}
}
