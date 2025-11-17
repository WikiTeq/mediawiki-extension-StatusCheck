<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Exception;
use MailAddress;
use MediaWiki\Config\ServiceOptions;
use StatusValue;
use UserMailer;

class EmailSendCheck extends BaseCheck {

	public const CONSTRUCTOR_OPTIONS = [
		'StatusCheckEmailAddress',
		'PasswordSender',
	];

	private ServiceOptions $options;

	public function __construct( ServiceOptions $options ) {
		parent::__construct(
			'statuscheck-check-email-send',
			'statuscheck-check-email-send-suggestions'
		);
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->options = $options;
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
