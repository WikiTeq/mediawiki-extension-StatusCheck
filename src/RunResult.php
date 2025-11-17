<?php

namespace MediaWiki\Extension\StatusCheck;

class RunResult {

	public const STATUS_RAW_SUCCESS = 'OK';
	public const STATUS_RAW_FAIL = 'FAIL';

	/**
	 * @var CheckResult[]
	 */
	private array $checkResults = [];

	/**
	 * @inheritDoc
	 */
	public function __construct() {
	}

	/**
	 * @param CheckResult $checkResult
	 *
	 * @return void
	 */
	public function addResult( CheckResult $checkResult ): void {
		$this->checkResults[] = $checkResult;
	}

	/**
	 * @return CheckResult[]
	 */
	public function getCheckResults(): array {
		return $this->checkResults;
	}

}
