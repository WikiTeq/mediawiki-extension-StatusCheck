<?php

namespace MediaWiki\Extension\StatusCheck\Api;

use ApiBase;
use MediaWiki\Extension\StatusCheck\Checks\ComposerDependenciesCheck;
use MediaWiki\Extension\StatusCheck\ChecksRunner;

class StatusCheckEndpoint extends ApiBase {
	/**
	 * @inheritDoc
	 */
	public function execute() {

		$this->checkUserRightsAny( 'status-check' );

		$params = $this->extractRequestParams();

		$response = [
			'status' => 'OK',
			'checkResults' => []
		];

		$runner = new ChecksRunner();
		$runner->addCheck( new ComposerDependenciesCheck() );
		$result = $runner->run();

		foreach ( $result->getCheckResults() as $checkResult ) {
			$responseItem = [
				'name' => $checkResult->getCheck()->getName(),
				'description' => $checkResult->getCheck()->getDescription(),
				'errors' => [],
				'status' => 'OK'
			];

			if ( !$checkResult->isGood() ) {
				foreach ( $checkResult->getErrorsText() as $errorText ) {
					$responseItem['errors'][] = $errorText;
				}
				$responseItem['status'] = 'FAIL';
				$responseItem['suggestion'] = $checkResult->getCheck()->getMitigationSuggestions();
			}

			$response['checkResults'][] = $responseItem;
		}

		if ( !$result->isGood() ) {
			$response['status'] = 'FAIL';
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
