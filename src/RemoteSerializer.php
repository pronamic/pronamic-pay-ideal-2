<?php
/**
 * Remote serializer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Remote serializer class
 */
final class RemoteSerializer {
	/**
	 * Serialize.
	 *
	 * @param object $item Item.
	 * @return object
	 */
	public function serialize( $item ) {
		$data = [];

		$reflection_object = new \ReflectionObject( $item );

		$properties = $reflection_object->getProperties();

		foreach ( $properties as $property ) {
			if ( ! $property->isInitialized( $item ) ) {
				continue;
			}

			$value = $property->getValue( $item );

			if ( null === $value ) {
				continue;
			}

			$attributes = $property->getAttributes( RemoteApiProperty::class );

			foreach ( $attributes as $attribute ) {
				$remote_api_property = $attribute->newInstance();

				$data[ $remote_api_property->name ] = $this->get_value( $value );
			}
		}

		return (object) $data;
	}

	/**
	 * Get value.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function get_value( $value ) {
		if ( $value instanceof RemoteSerializable ) {
			return $value->remote_serialize();
		}

		if ( \is_array( $value ) ) {
			return \array_map(
				[ $this, 'get_value' ],
				$value
			);
		}

		return $value;
	}
}
