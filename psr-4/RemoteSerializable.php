<?php
/**
 * Remote serializable
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Remote serializable class
 */
interface RemoteSerializable {
	/**
	 * Remote serialize.
	 *
	 * @return mixed
	 */
	public function remote_serialize();
}
