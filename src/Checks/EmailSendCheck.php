<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Exception;
use MailAddress;
use StatusValue;
use UserMailer;

class EmailSendCheck extends BaseCheck {

	/**
	 * @inheritDoc
	 */
	public function getDescription(): string {
		return wfMessage( 'statuscheck-check-email-send' );
	}

	/**
	 * @inheritDoc
	 */
	public function perform(): StatusValue {
		$to = $this->getConfig()->get( 'StatusCheckEmailAddress' );
		if ( !$to ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				'$wgStatusCheckEmailAddress variable must be set'
			);
		}
		$from = $this->getConfig()->get( 'PasswordSender' );
		try {
			$result = UserMailer::send(
				new MailAddress( $to ),
				new MailAddress( $from ),
				'Test',
				'This is a test email!'
			);
		} catch ( Exception $e ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				'Nothing was found during test serach, this may indicate issues with search engine'
			);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-email-send-suggestions' );
	}
}
