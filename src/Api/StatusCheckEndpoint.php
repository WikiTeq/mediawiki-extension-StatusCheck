<?php

namespace MediaWiki\Extension\StatusCheck\Api;

use ApiBase;
use ApiMain;
use MediaWiki\Extension\StatusCheck\CheckResult;
use MediaWiki\Extension\StatusCheck\ChecksRunner;
use Wikimedia\ObjectFactory\ObjectFactory;

class StatusCheckEndpoint extends ApiBase {

	private ObjectFactory $objectFactory;

	public function __construct(
		ApiMain $main,
		string $action,
		ObjectFactory $objectFactory
	) {
		parent::__construct( $main, $action );
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->checkUserRightsAny( 'status-check' );

		$params = $this->extractRequestParams();

		$response = [
			'status' => CheckResult::STATUS_RAW_SUCCESS,
			'checkResults' => []
		];

		$runner = new ChecksRunner();
		$runner->enableDefaultChecks(
			$this->objectFactory,
			$this->getConfig()
		);
		$results = $runner->run();

		foreach ( $results as $checkResult ) {
			$responseItem = [
				'name' => $checkResult->getCheck()->getName(),
				'description' => $checkResult->getCheck()->getDescription( $this )->parse(),
				'errors' => [],
				'status' => CheckResult::STATUS_RAW_SUCCESS
			];

			$status = $checkResult->getStatus();
			if ( !$status->isGood() ) {
				foreach ( $status->getErrors() as $errorDetails ) {
					$responseItem['errors'][] = $this->msg(
						$errorDetails['message'],
						...$errorDetails['params']
					)->parse();
				}
				$responseItem['status'] = CheckResult::STATUS_RAW_FAIL;
				$responseItem['suggestion'] = $checkResult->getCheck()->getMitigationSuggestions( $this )->parse();

				$response['status'] = CheckResult::STATUS_RAW_FAIL;
			}

			$response['checkResults'][] = $responseItem;
		}

		$result = $this->getResult();
		$result->addValue( $this->getModuleName(), 'result', $response );
	}

	/**
	 * @inheritDoc
	 */
	public function isWriteMode() {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

}
