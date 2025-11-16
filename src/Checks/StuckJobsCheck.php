<?php

// We run Phan with both MW 1.39 and MW 1.43, and the suppressions that they
// need are different, and the other one would complain that the suppression is
// unneeded
// @phan-file-suppress UnusedPluginSuppression,UnusedPluginFileSuppression

namespace MediaWiki\Extension\StatusCheck\Checks;

use Job;
use JobQueueError;
use MediaWiki\MediaWikiServices;
use StatusValue;

class StuckJobsCheck extends BaseCheck {

	public function __construct() {
		parent::__construct(
			'statuscheck-check-stuck-jobs',
			'statuscheck-check-stuck-jobs-suggestions'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function perform(): StatusValue {
		$group = MediaWikiServices::getInstance()->getJobQueueGroup();

		$status = StatusValue::newGood();

		if ( !$group->queuesHaveJobs() ) {
			return $status;
		}

		foreach ( $group->getQueuesWithJobs() as $queueType ) {
			$queue = $group->get( $queueType );
			try {
				$abandoned = $queue->getAbandonedCount();
			} catch ( JobQueueError $e ) {
				// JobQueueError was switched in MW 1.42 from an MWException
				// subclass to a base `Exception` subclass which removed the
				// `getText()` method that MWException provided. To simplify
				// things, `getText()`
				if ( method_exists( $e, 'getText' ) ) {
					// The method exists, but phan doesn't know that, see
					// https://github.com/phan/phan/issues/2628
					// @phan-suppress-next-line PhanUndeclaredMethod
					$message = $e->getText();
				} else {
					$message = $e->__toString();
				}
				return StatusValue::newGood()->error(
					'statuscheck-generic-error',
					$message
				);
			}
			if ( $abandoned > 0 ) {
				/** @var Job $abandonedJob */
				foreach ( $queue->getAllAbandonedJobs() as $abandonedJob ) {
					$status->error(
						'statuscheck-generic-error',
						"{$abandonedJob->getType()} job is stuck {$abandonedJob->getLastError()}"
					);
				}
			}
		}

		return $status;
	}
}
