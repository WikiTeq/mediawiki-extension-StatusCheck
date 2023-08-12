<?php

namespace MediaWiki\Extension\StatusCheck\Api;

use ApiBase;
use MediaWiki\Extension\StatusCheck\CheckResult;
use MediaWiki\Extension\StatusCheck\ChecksRunner;
use MediaWiki\Extension\StatusCheck\RunResult;

class StatusCheckEndpoint extends ApiBase {
	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->checkUserRightsAny( 'status-check' );

		$params = $this->extractRequestParams();

		$response = [
			'status' => RunResult::STATUS_RAW_SUCCESS,
			'checkResults' => []
		];

		$runner = new ChecksRunner();
		$runner->enableDefaultChecks();
		$result = $runner->run();

		foreach ( $result->getCheckResults() as $checkResult ) {
			$responseItem = [
				'name' => $checkResult->getCheck()->getName(),
				'description' => $checkResult->getCheck()->getDescription(),
				'errors' => [],
				'status' => CheckResult::STATUS_RAW_SUCCESS
			];

			if ( !$checkResult->isGood() ) {
				foreach ( $checkResult->getErrorsText() as $errorText ) {
					$responseItem['errors'][] = $errorText;
				}
				$responseItem['status'] = CheckResult::STATUS_RAW_FAIL;
				$responseItem['suggestion'] = $checkResult->getCheck()->getMitigationSuggestions();
			}

			$response['checkResults'][] = $responseItem;
		}

		if ( !$result->isGood() ) {
			$response['status'] = RunResult::STATUS_RAW_FAIL;
		}

		$result = $this->getResult();
		$result->addValue( $this->getModuleName(), 'result', $response );
	}

	/**
	 * @inheritDoc
	 */
	public function isWriteMode() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
//	public function needsToken() {
//		return true;
//	}

	/**
	 * @inheritDoc
	 */
	public function mustBePosted() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		return [];
	}

}
