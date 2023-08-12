<?php

namespace MediaWiki\Extension\StatusCheck;

class RunResult {

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
	 * @return bool
	 */
	public function isGood(): bool {
		foreach ( $this->checkResults as $checkResult ) {
			if ( !$checkResult->isGood() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return CheckResult[]
	 */
	public function getCheckResults(): array {
		return $this->checkResults;
	}

}
