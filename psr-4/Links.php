<?php
/**
 * Links
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Links class
 *
 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
 */
final class Links {
	/**
	 * Redirect URL.
	 *
	 * @var Link
	 */
	#[RemoteApiProperty( 'redirectUrl' )]
	public Link $redirect_url;

	/**
	 * Return URL.
	 *
	 * @var Link
	 */
	#[RemoteApiProperty( 'returnUrl' )]
	public Link $return_url;

	/**
	 * Construct links object.
	 *
	 * @param Link $redirect_url Redirect URL.
	 * @param Link $return_url   Return URL.
	 */
	public function __construct( Link $redirect_url, Link $return_url ) {
		$this->redirect_url = $redirect_url;
		$this->return_url   = $return_url;
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
			Link::from_remote_object( $object_access->get_property( 'redirectUrl' ) ),
			Link::from_remote_object( $object_access->get_property( 'returnUrl' ) )
		);
	}
}
