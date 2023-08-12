<?php

namespace MediaWiki\Extension\StatusCheck;

use Html;
use MediaWiki\Extension\StatusCheck\Checks\ComposerDependenciesCheck;
use MediaWiki\Extension\StatusCheck\Checks\EmailSendCheck;
use MediaWiki\Extension\StatusCheck\Checks\FileUploadCheck;
use MediaWiki\Extension\StatusCheck\Checks\StuckJobsCheck;
use OOUI\ButtonWidget;
use OOUIHTMLForm;
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
		$out->enableOOUI();
		$this->performWelcome();
	}

	/**
	 * @return void
	 */
	private function performWelcome() {
		$form = new OOUIHTMLForm( [], $this->getContext() );
		$form->setSubmitText( 'Run checks' );
		$form->setSubmitCallback(
			[
				$this,
				'performChecks'
			]
		);
		$form->addHeaderHtml( $this->msg( 'status-check-special-welcome' )->parse() );
		$form->showAlways();
	}

	/**
	 * @return void
	 */
	public function performChecks() {
		$out = $this->getOutput();
		$out->addHTML( '<br>Running checks...' );
		$out->addHTML( '<ul>' );

		$runner = new ChecksRunner();
		$runner->enableDefaultChecks();
		$result = $runner->run();

		foreach ( $result->getCheckResults() as $checkResult ) {
			$out->addHTML( '<ul>' );

			$out->addHTML( '<li>' );
			$out->addHTML( $checkResult->getCheck()->getName() );
			$out->addHTML( '</li>' );

			$out->addHTML( '<ul>' );
			$out->addHTML( '<li>' );
			$out->addHTML( $checkResult->getCheck()->getDescription() );
			$out->addHTML( '</li>' );

			if ( !$checkResult->isGood() ) {
				foreach ( $checkResult->getErrorsText() as $errorText ) {
					$out->addHTML( '<li class="status-check-error">' );
					$out->addHTML( $errorText );
					$out->addHTML( '</li>' );
				}
			} else {
				$out->addHTML( '<li class="status-check-success">' );
				$out->addHTML( 'OK' );
				$out->addHTML( '</li>' );
			}

			$out->addHTML( '</ul>' );
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
