<?php
/**
 * Error
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Moneybird
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Exception;
use Throwable;

/**
 * Error class
 */
final class ErrorResponse extends Exception {
	/**
	 * Error code.
	 * 
	 * @var string
	 */
	public $error_code;

	/**
	 * Error message.
	 * 
	 * @var string
	 */
	public $error_message;

	/**
	 * Trace ID.
	 * 
	 * @var string|null
	 */
	public $trace_id;

	/**
	 * Construct error resposne.
	 * 
	 * @param string         $error_code    Error response code.
	 * @param string         $error_message Error response message.
	 * @param int            $code          Code.
	 * @param Throwable|null $previous      The previous exception used for the exception chaining.
	 */
	public function __construct( $error_code, $error_message, $code = 0, ?Throwable $previous = null ) {
		$message = \sprintf(
			'%s - %s',
			$error_code,
			$error_message
		);

		parent::__construct( $message, $code, $previous );

		$this->error_code    = $error_code;
		$this->error_message = $error_message;
	}

	/**
	 * Error from response object.
	 * 
	 * @param object    $data     Data.
	 * @param int       $code     Code.
	 * @param Throwable $previous The previous exception used for the exception chaining.
	 * @return self
	 */
	public static function from_response_object( $data, $code = 0, $previous = null ) {
		$object_access = new ObjectAccess( $data );

		$error_response = new self(
			$object_access->get_property( 'code' ),
			$object_access->get_property( 'message' ),
			$code,
			$previous
		);

		$error_response->trace_id = $object_access->get_optional( 'traceId' );

		return $error_response;
	}
}
