<?php
/**
 * Remote API Property
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Attribute;

/**
 * Remote API Property class
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
final class RemoteApiProperty {
	/**
	 * Name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Construct property.
	 *
	 * @param string $name Name.
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}
}
