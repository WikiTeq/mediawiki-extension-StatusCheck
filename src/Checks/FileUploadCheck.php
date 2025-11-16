<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use MediaWiki\MediaWikiServices;
use StatusValue;
use Title;
use UploadBase;
use User;

/**
 * This check verifies in a very basic way that file can be uploaded
 * into images upload directory. It does not use FauxRequest or Api and
 * instead performs upload via LocalRepo
 */
class FileUploadCheck extends BaseCheck {

	/**
	 * @inheritDoc
	 */
	public function getDescription(): string {
		return wfMessage( 'statuscheck-check-file-upload' );
	}

	/**
	 * @inheritDoc
	 */
	public function perform(): StatusValue {
		if ( !UploadBase::isEnabled() ) {
			return StatusValue::newGood()->error( 'uploaddisabled' );
		}

		// We naively assume that .jpg is in allowed uploads list
		$fileName = 'StatusCheckTestUpload.jpg';
		$mimeType = 'image/jpeg';
		$filePath = __DIR__ . '/data/yuv420.jpg';
		$title = Title::newFromText( $fileName, NS_FILE );
		$user = User::newSystemUser( User::MAINTENANCE_SCRIPT_USER );

		$image = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->newFile( $title );
		if ( $image->exists() ) {
			$image->deleteFile( 'StatusCheck test upload cleanup', $user, true );
		}

		$result = $image->upload(
			$filePath,
			'StatusCheck test upload',
			'',
			0,
			false,
			false,
			$user
		);

		if ( $result->isGood() ) {
			$image->deleteFile( 'StatusCheck test upload cleanup', $user, true );
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getMitigationSuggestions(): string {
		return wfMessage( 'statuscheck-check-file-upload-suggestions' );
	}
}
