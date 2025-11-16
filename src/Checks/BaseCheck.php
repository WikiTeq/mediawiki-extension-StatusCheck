<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Config;
use MediaWiki\MediaWikiServices;
use StatusValue;

abstract class BaseCheck {

	private string $descriptionKey;
	private string $mitigationKey;

	public function __construct(
		string $descriptionKey,
		string $mitigationKey
	) {
		$this->descriptionKey = $descriptionKey;
		$this->mitigationKey = $mitigationKey;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return static::class;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return wfMessage( $this->descriptionKey );
	}

	/**
	 * @return StatusValue
	 */
	abstract public function perform(): StatusValue;

	/**
	 * @return string
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( $this->mitigationKey );
	}

	/**
	 * @return Config
	 */
	protected function getConfig(): Config {
		return MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'StatusCheck' );
	}

}
