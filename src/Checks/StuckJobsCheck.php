<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Job;
use JobQueueError;
use MediaWiki\MediaWikiServices;
use StatusValue;

class StuckJobsCheck extends BaseCheck {

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
		return wfMessage( 'statuscheck-check-stuck-jobs' );
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
				return StatusValue::newGood()->error(
					'statuscheck-generic-error',
					$e->getText()
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

	/**
	 * @inheritDoc
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-stuck-jobs-suggestions' );
	}
}
