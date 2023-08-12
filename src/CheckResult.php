<?php

namespace MediaWiki\Extension\StatusCheck;

use MediaWiki\Extension\StatusCheck\Checks\BaseCheck;
use StatusValue;

class CheckResult {

	public const STATUS_RAW_SUCCESS = 'OK';
	public const STATUS_RAW_FAIL = 'FAIL';

	/**
	 * @var StatusValue
	 */
	private StatusValue $status;

	/**
	 * @var BaseCheck
	 */
	private BaseCheck $check;

	/**
	 * @param BaseCheck $check
	 * @param StatusValue $status
	 */
	public function __construct( BaseCheck $check, StatusValue $status ) {
		$this->check = $check;
		$this->status = $status;
	}

	/**
	 * @return BaseCheck
	 */
	public function getCheck(): BaseCheck {
		return $this->check;
	}

	/**
	 * @return StatusValue
	 */
	public function getStatus(): StatusValue {
		return $this->status;
	}

	/**
	 * @return bool
	 */
	public function isGood(): bool {
		return $this->status->isGood();
	}

	/**
	 * @return array
	 */
	public function getErrorsText(): array {
		return array_map( static function ( array $error ) {
			return wfMessage( $error['message'], ...$error['params'] );
		}, $this->status->getErrors() );
	}

}
