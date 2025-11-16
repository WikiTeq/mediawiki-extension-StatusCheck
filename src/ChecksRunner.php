<?php

namespace MediaWiki\Extension\StatusCheck;

use MediaWiki\Extension\StatusCheck\Checks\BaseCheck;
use MediaWiki\Extension\StatusCheck\Checks\ComposerDependenciesCheck;
use MediaWiki\Extension\StatusCheck\Checks\EmailSendCheck;
use MediaWiki\Extension\StatusCheck\Checks\FileUploadCheck;
use MediaWiki\Extension\StatusCheck\Checks\SearchCheck;
use MediaWiki\Extension\StatusCheck\Checks\StuckJobsCheck;

class ChecksRunner {

	private const DEFAULT_CHECKS = [
		'ComposerDependencies' => ComposerDependenciesCheck::class,
		'EmailSend' => EmailSendCheck::class,
		'FileUpload' => FileUploadCheck::class,
		'Search' => SearchCheck::class,
		'StuckJobs' => StuckJobsCheck::class,
	];

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
		foreach ( self::DEFAULT_CHECKS as $checkToRun ) {
			$this->addCheck( new $checkToRun() );
		}
	}

}
