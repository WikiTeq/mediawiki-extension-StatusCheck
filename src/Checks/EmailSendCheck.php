<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Exception;
use MailAddress;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Mail\IEmailer;
use StatusValue;

class EmailSendCheck extends BaseCheck {

	public const CONSTRUCTOR_OPTIONS = [
		'StatusCheckEmailAddress',
		'PasswordSender',
	];

	private ServiceOptions $options;
	private IEmailer $emailer;

	public function __construct(
		ServiceOptions $options,
		IEmailer $emailer
	) {
		parent::__construct(
			'statuscheck-check-email-send',
			'statuscheck-check-email-send-suggestions'
		);
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->options = $options;
		$this->emailer = $emailer;
	}

	/**
	 * @inheritDoc
	 */
	public function perform(): StatusValue {
		$to = $this->options->get( 'StatusCheckEmailAddress' );
		if ( !$to ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				'$wgStatusCheckEmailAddress variable must be set'
			);
		}
		$from = $this->options->get( 'PasswordSender' );
		try {
			$result = $this->emailer->send(
				new MailAddress( $to ),
				new MailAddress( $from ),
				'Test',
				'This is a test email!'
			);
		} catch ( Exception $e ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				$e->getMessage()
			);
		}

		return $result;
	}
}
