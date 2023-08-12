<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Composer\Semver\Semver;
use ComposerJson;
use ComposerLock;
use StatusValue;

class ComposerDependenciesCheck extends BaseCheck {

	/**
	 * @return string
	 */
	public function getName(): string {
		return self::class;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return wfMessage( 'statuscheck-check-composer-dependencies' );
	}

	/**
	 * @return string
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-composer-dependencies-suggestions' );
	}

	/**
	 * @return StatusValue
	 */
	public function perform(): StatusValue {
		$IP = wfDetectInstallPath();

		$lockLocation = "$IP/composer.lock";
		$jsonLocation = "$IP/composer.json";

		$lock = new ComposerLock( $lockLocation );
		$json = new ComposerJson( $jsonLocation );

		$status = StatusValue::newGood();

		// Check all the dependencies to see if any are old
		$installed = $lock->getInstalledDependencies();
		foreach ( $json->getRequiredDependencies() as $name => $version ) {
			if ( isset( $installed[$name] ) ) {
				if ( !SemVer::satisfies( $installed[$name]['version'], $version ) ) {
					$status->error(
						'statuscheck-generic-error',
						"$name: {$installed[$name]['version']} installed, $version required."
					);
				}
			} else {
				$status->error(
					'statuscheck-generic-error',
					"$name: not installed, $version required."
				);
			}
		}

		return $status;
	}

}
