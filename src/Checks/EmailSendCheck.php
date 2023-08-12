<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use MailAddress;
use StatusValue;
use UserMailer;

class EmailSendCheck extends BaseCheck {

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return self::class;
	}

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
		$from = $this->getConfig()->get( 'PasswordSender' );
		$result = UserMailer::send(
			new MailAddress( $to ),
			new MailAddress( $from ),
			'Test',
			'This is a test email!'
		);
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-email-send-suggestions' );
	}
}
