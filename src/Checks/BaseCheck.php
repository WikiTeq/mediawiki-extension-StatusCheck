<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Config;
use MediaWiki\MediaWikiServices;
use StatusValue;

abstract class BaseCheck {

	/**
	 * @return string
	 */
	abstract public function getName(): string;

	/**
	 * @return string
	 */
	abstract public function getDescription(): string;

	/**
	 * @return StatusValue
	 */
	abstract public function perform(): StatusValue;

	/**
	 * @return string
	 */
	abstract public function getMitigationSuggestions(): string;

	/**
	 * @return Config
	 */
	protected function getConfig(): Config {
		return MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'StatusCheck' );
	}

}
