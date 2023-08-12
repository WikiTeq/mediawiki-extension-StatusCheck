<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Error;
use MediaWiki\MediaWikiServices;
use Status;
use StatusValue;
use Title;

class SearchCheck extends BaseCheck {

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
		return wfMessage( 'statuscheck-check-search' );
	}

	/**
	 * @inheritDoc
	 */
	public function perform(): StatusValue {
		$type = MediaWikiServices::getInstance()->getSearchEngineConfig()->getSearchType();
		try {
			$engine = MediaWikiServices::getInstance()->getSearchEngineFactory()->create( $type );
		} catch ( Error $e ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				$e->getMessage()
			);
		}

		$result = $engine->searchText( $this->getSearchSample() );

		if ( $result === null ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				'Unable to perform search'
			);
		}

		if ( $result instanceof Status ) {
			return $result->getStatusValue();
		}

		if ( !$result->getTotalHits() ) {
			return StatusValue::newGood()->error(
				'statuscheck-generic-error',
				'Nothing was found during test serach, this may indicate issues with search engine'
			);
		}

		return StatusValue::newGood();
	}

	/**
	 * @return mixed|string
	 */
	private function getSearchSample() {
		$homepageTitle = Title::newMainPage();
		$homepagePage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $homepageTitle );
		$content = $homepagePage->getContent()->getWikitextForTransclusion();
		$words = explode( ' ', $content );
		return $words[0];
	}

	/**
	 * @inheritDoc
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-search-suggestions' );
	}
}
