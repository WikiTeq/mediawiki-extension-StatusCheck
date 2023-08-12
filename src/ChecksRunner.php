<?php

namespace MediaWiki\Extension\StatusCheck;

use MediaWiki\Extension\StatusCheck\Checks\BaseCheck;

class ChecksRunner {

	/**
	 * @var BaseCheck[]
	 */
	private array $checks = [];

	/**
	 * @var bool
	 */
	private bool $isGood = true;

	/**
	 * @param BaseCheck $check
	 *
	 * @return void
	 */
	public function addCheck( BaseCheck $check ) {
		$this->checks[] = $check;
	}

	/**
	 * @return RunResult
	 */
	public function run() {
		$result = new RunResult();
		foreach ( $this->checks as $check ) {
			$status = $check->perform();
			$result->addResult(
				new CheckResult(
					$check,
					$status
				)
			);
		}
		return $result;
	}

}
