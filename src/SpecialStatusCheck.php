<?php

// We run Phan with both MW 1.39 and MW 1.43, the suppression from 1.39 isn't
// needed on 1.43 and phan would complain about that
// @phan-file-suppress UnusedPluginSuppression,UnusedPluginFileSuppression

namespace MediaWiki\Extension\StatusCheck;

use Html;
use OOUIHTMLForm;
use SpecialPage;
use Wikimedia\ObjectFactory\ObjectFactory;

class SpecialStatusCheck extends SpecialPage {

	private ObjectFactory $objectFactory;

	/**
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		parent::__construct(
			'StatusCheck',
			'status-check'
		);
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		$this->checkPermissions();
		$out = $this->getOutput();
		if ( version_compare( MW_VERSION, '1.41', '>=' ) ) {
			// 1.41 deprecated calling setPageTitle with a string and introduced
			// setPageTitleMsg()
			// Method doesn't exist for Phan on 1.39
			// @phan-suppress-next-line PhanUndeclaredMethod
			$out->setPageTitleMsg( $this->msg( 'statuscheck-title' ) );
		} else {
			$out->setPageTitle( $this->msg( 'statuscheck-title' ) );
		}
		$out->addModuleStyles( 'ext.statusCheck' );
		$out->enableOOUI();
		$this->performWelcome();
	}

	/**
	 * @return void
	 */
	private function performWelcome() {
		$form = new OOUIHTMLForm( [], $this->getContext() );
		$form->setSubmitTextMsg( 'statuscheck-run-checks' );
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
		$out->addHTML( '<br>' );
		$out->addWikiMsg( 'statuscheck-running-checks' );
		$out->addHTML( '<ul>' );

		$runner = new ChecksRunner();
		$runner->enableDefaultChecks(
			$this->objectFactory,
			$this->getConfig()
		);
		$results = $runner->run();

		foreach ( $results as $checkResult ) {
			$checkDetails = [];
			$checkDetails[] = Html::rawElement(
				'li',
				[],
				$checkResult->getCheck()->getDescription()
			);

			$status = $checkResult->getStatus();
			if ( !$status->isGood() ) {
				foreach ( $status->getErrors() as $errorDetails ) {
					$checkDetails[] = Html::rawElement(
						'li',
						[ 'class' => 'status-check-error' ],
						$this->msg(
							$errorDetails['message'],
							...$errorDetails['params']
						)->parse()
					);
				}
				$checkDetails[] = Html::rawElement(
					'li',
					[ 'class' => 'status-check-suggestions' ],
					$checkResult->getCheck()->getMitigationSuggestions()
				);
			} else {
				$checkDetails[] = Html::rawElement(
					'li',
					[ 'class' => 'status-check-success' ],
					$this->msg( 'statuscheck-check-success' )->parse()
				);
			}

			$out->addHTML(
				Html::rawElement(
					'li',
					[],
					$checkResult->getCheck()->getName()
						. Html::rawElement( 'ul', [], implode( "\n", $checkDetails ) )
				)
			);
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
