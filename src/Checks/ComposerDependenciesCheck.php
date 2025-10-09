<?php

// We run Phan with both MW 1.39 and MW 1.43, and the suppressions that they
// need are different, and the other one would complain that the suppression is
// unneeded
// @phan-file-suppress UnusedPluginSuppression,UnusedPluginFileSuppression

namespace MediaWiki\Extension\StatusCheck\Checks;

use Composer\Semver\Semver;
use LogicException;
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

		/* composer.json always exists because that comes from core, but
		 * composer.lock only exists if the site updated dependencies, they
		 * might have just cloned with submodules and be using wikimedia/vendor */
		if ( !file_exists( $lockLocation ) ) {
			$status = StatusValue::newGood();
			$status->error(
				'statuscheck-generic-error',
				"composer.lock file not found, checks cannot be performed"
			);
			return $status;
		}

		// Namespaced without aliases
		if ( class_exists( 'ComposerLock' ) ) {
			// MW <= 1.41
			// @phan-suppress-next-line PhanUndeclaredClassMethod
			$lock = new \ComposerLock( $lockLocation );
		} elseif ( class_exists( 'Wikimedia\\Composer\\ComposerLock' ) ) {
			// MW 1.42+
			// @phan-suppress-next-line PhanUndeclaredClassMethod
			$lock = new \Wikimedia\Composer\ComposerLock( $lockLocation );
		} else {
			throw new LogicException( 'Unable to locate ComposerLock class' );
		}

		if ( class_exists( 'ComposerJson' ) ) {
			// MW <= 1.41
			// @phan-suppress-next-line PhanUndeclaredClassMethod
			$json = new \ComposerJson( $jsonLocation );
		} elseif ( class_exists( 'Wikimedia\\Composer\\ComposerJson' ) ) {
			// MW 1.42+
			// @phan-suppress-next-line PhanUndeclaredClassMethod
			$json = new \Wikimedia\Composer\ComposerJson( $jsonLocation );
		} else {
			throw new LogicException( 'Unable to locate ComposerJson class' );
		}

		$status = StatusValue::newGood();

		// Check all the dependencies to see if any are old
		// Phan doesn't understand the ComposerLock and ComposerJson classes
		// were located
		// @phan-suppress-next-line PhanUndeclaredClassMethod
		$installed = $lock->getInstalledDependencies();
		// @phan-suppress-next-line PhanUndeclaredClassMethod
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
