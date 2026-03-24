<?php
/**
 * Remote serializable
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

declare(strict_types=1);

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
