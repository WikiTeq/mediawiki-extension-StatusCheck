<?php

namespace MediaWiki\Extension\StatusCheck;

use Config;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\StatusCheck\Checks\BaseCheck;
use MediaWiki\Extension\StatusCheck\Checks\ComposerDependenciesCheck;
use MediaWiki\Extension\StatusCheck\Checks\EmailSendCheck;
use MediaWiki\Extension\StatusCheck\Checks\FileUploadCheck;
use MediaWiki\Extension\StatusCheck\Checks\SearchCheck;
use MediaWiki\Extension\StatusCheck\Checks\StuckJobsCheck;
use Wikimedia\ObjectFactory\ObjectFactory;

class ChecksRunner {

	private const DEFAULT_CHECKS = [
		'ComposerDependencies' => [
			'class' => ComposerDependenciesCheck::class,
		],
		'EmailSend' => [
			'class' => EmailSendCheck::class,
			'services' => [
				'Emailer',
			],
		],
		'FileUpload' => [
			'class' => FileUploadCheck::class,
			'services' => [
				'RepoGroup',
				'TitleFactory',
			],
		],
		'Search' => [
			'class' => SearchCheck::class,
			'services' => [
				'SearchEngineConfig',
				'SearchEngineFactory',
				'TitleFactory',
				'WikiPageFactory',
			],
		],
		'StuckJobs' => [
			'class' => StuckJobsCheck::class,
			'services' => [
				'JobQueueGroup',
			],
		],
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
	 * @return CheckResult[]
	 */
	public function run() {
		$results = [];
		foreach ( $this->checks as $check ) {
			$status = $check->perform();
			$results[] = new CheckResult( $check, $status );
		}
		return $results;
	}

	/**
	 * @return void
	 */
	public function enableDefaultChecks(
		ObjectFactory $objectFactory,
		Config $mainConfig
	) {
		foreach ( self::DEFAULT_CHECKS as $spec ) {
			$extraArgs = [];
			if ( defined( $spec['class'] . '::CONSTRUCTOR_OPTIONS' ) ) {
				$extraArgs = [
					new ServiceOptions(
						$spec['class']::CONSTRUCTOR_OPTIONS,
						$mainConfig
					),
				];
			}
			// ObjectFactory::createObject accepts an array, not just a
			// callable (phan bug)
			// @phan-suppress-next-line PhanTypeInvalidCallableArrayKey
			$checkToRun = $objectFactory->createObject(
				$spec,
				[
					'assertClass' => BaseCheck::class,
					'extraArgs' => $extraArgs,
				]
			);
			$this->addCheck( $checkToRun );
		}
	}

}
