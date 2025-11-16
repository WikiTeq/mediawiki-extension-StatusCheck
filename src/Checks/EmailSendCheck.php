<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Exception;
use MailAddress;
use StatusValue;
use UserMailer;

class EmailSendCheck extends BaseCheck {

	public function __construct() {
		parent::__construct(
			'statuscheck-check-email-send',
			'statuscheck-check-email-send-suggestions'
		);
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
}
