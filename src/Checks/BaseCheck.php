<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

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

}
