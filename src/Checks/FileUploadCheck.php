<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use RepoGroup;
use StatusValue;
use TitleFactory;
use UploadBase;
use User;

/**
 * This check verifies in a very basic way that file can be uploaded
 * into images upload directory. It does not use FauxRequest or Api and
 * instead performs upload via LocalRepo
 */
class FileUploadCheck extends BaseCheck {

	private RepoGroup $repoGroup;
	private TitleFactory $titleFactory;

	public function __construct(
		RepoGroup $repoGroup,
		TitleFactory $titleFactory
	) {
		parent::__construct(
			'statuscheck-check-file-upload',
			'statuscheck-check-file-upload-suggestions'
		);
		$this->repoGroup = $repoGroup;
		$this->titleFactory = $titleFactory;
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
		$title = $this->titleFactory->newFromText( $fileName, NS_FILE );
		$user = User::newSystemUser( User::MAINTENANCE_SCRIPT_USER );

		$image = $this->repoGroup->getLocalRepo()->newFile( $title );
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
}
