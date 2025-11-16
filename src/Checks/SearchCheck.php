<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use Error;
use MediaWiki\MediaWikiServices;
use SearchEngine;
use Status;
use StatusValue;
use Title;

class SearchCheck extends BaseCheck {

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

		$result = $engine->searchText( $this->getSearchSample( $engine ) );

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
			// Warning for the next developer that tries to debug this: if
			// `$wgUsePigLatinVariant` is true and you are using Sqlite, search
			// for page text does not seem to work at all
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
	private function getSearchSample( SearchEngine $engine ) {
		$homepageTitle = Title::newMainPage();
		$homepagePage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $homepageTitle );
		$content = $homepagePage->getContent()->getWikitextForTransclusion();
		$words = explode( ' ', $content );
		// Find the first word without special characters, e.g. on the default
		// MediaWiki install if the main page is unchanged the `mainpagetext`
		// message makes the first word `<strong>MediaWiki` and the search for
		// that fails. The text that goes into the search database is processed
		// by SearchUpdate::updateText()
		$okayChars = $engine->legalSearchChars() . '&#;';
		$okayRegex = "/^[$okayChars]+$/";
		$filtered = array_filter(
			$words,
			static fn ( $word ) => preg_match( $okayRegex, $word )
		);
		if ( count( $filtered ) ) {
			$filtered = array_values( $filtered );
			return $filtered[0];
		}
		return $words[0];
	}

	/**
	 * @inheritDoc
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-search-suggestions' );
	}
}
