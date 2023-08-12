<?php

namespace MediaWiki\Extension\StatusCheck;

use MediaWiki\Extension\StatusCheck\Checks\BaseCheck;
use MediaWiki\MediaWikiServices;

class ChecksRunner {

	/**
	 * @var BaseCheck[]
	 */
	private array $checks = [];

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

	/**
	 * @return void
	 */
	public function enableDefaultChecks() {
		$checksToRun = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'StatusCheck' )
			->get( 'StatusCheckDefaultSet' );

		foreach ( $checksToRun as $checkToRun ) {
			$this->addCheck( new $checkToRun() );
		}
	}

}
