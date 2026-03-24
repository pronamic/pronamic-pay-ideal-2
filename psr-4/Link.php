<?php
/**
 * Link
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

declare(strict_types=1);

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Stringable;

/**
 * Link class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class Link implements Stringable {
	/**
	 * Hypertext reference.
	 *
	 * @var string
	 */
	#[RemoteApiProperty( 'href' )]
	public string $href;

	/**
	 * Construct link object.
	 *
	 * @param string $href Hypertext reference.
	 */
	public function __construct( string $href ) {
		$this->href = $href;
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->href;
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
			$object_access->get_property( 'href' )
		);
	}
}
