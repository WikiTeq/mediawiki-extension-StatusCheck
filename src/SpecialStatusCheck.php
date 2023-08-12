<?php

namespace MediaWiki\Extension\StatusCheck;

use MediaWiki\Extension\StatusCheck\Checks\ComposerDependenciesCheck;
use SpecialPage;

class SpecialStatusCheck extends SpecialPage {

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct(
			'StatusCheck',
			'status-check'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'statuscheck-title' ) );
		$out->addModules( 'ext.statusCheck' );

		$out->addHTML( '<br>Running checks...' );
		$out->addHTML( '<ul>' );

		$runner = new ChecksRunner();
		$runner->addCheck( new ComposerDependenciesCheck() );
		$result = $runner->run();

		foreach ( $result->getCheckResults() as $checkResult ) {
			$out->addHTML( '<ul>' );

			$out->addHTML( '<li>' );
			$out->addHTML( $checkResult->getCheck()->getName() );
			$out->addHTML( '</li>' );

			$out->addHTML( '<li>' );
			$out->addHTML( $checkResult->getCheck()->getDescription() );
			$out->addHTML( '</li>' );

			if ( !$checkResult->isGood() ) {
				foreach ( $checkResult->getErrorsText() as $errorText ) {
					$out->addHTML( '<li>' );
					$out->addHTML( $errorText );
					$out->addHTML( '</li>' );
				}
			} else {
				$out->addHTML( '<li>' );
				$out->addHTML( 'OK' );
				$out->addHTML( '</li>' );
			}

			$out->addHTML( '</ul>' );
		}

		$out->addHTML( '</ul>' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getGroupName() {
		return 'other';
	}

}
