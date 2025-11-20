<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Message;
use MessageLocalizer;
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

	public function getDescription( MessageLocalizer $localizer ): Message {
		return $localizer->msg( $this->descriptionKey );
	}

	/**
	 * @return StatusValue
	 */
	abstract public function perform(): StatusValue;

	public function getMitigationSuggestions( MessageLocalizer $localizer ): Message {
		return $localizer->msg( $this->mitigationKey );
	}

}
