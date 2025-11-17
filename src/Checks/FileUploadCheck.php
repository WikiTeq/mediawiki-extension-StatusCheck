<?php

namespace MediaWiki\Extension\StatusCheck\Checks;

use FileDeleteForm;
use MediaWiki\Permissions\PermissionManager;
use RepoGroup;
use StatusValue;
use TitleFactory;
use UploadBase;
use User;
use Wikimedia\UUID\GlobalIdGenerator;

/**
 * This check verifies in a very basic way that file can be uploaded
 * into images upload directory. It does not use FauxRequest or Api and
 * instead performs upload via LocalRepo
 */
class FileUploadCheck extends BaseCheck {

	private GlobalIdGenerator $globalIdGenerator;
	private PermissionManager $permissionManager;
	private RepoGroup $repoGroup;
	private TitleFactory $titleFactory;

	public function __construct(
		GlobalIdGenerator $globalIdGenerator,
		PermissionManager $permissionManager,
		RepoGroup $repoGroup,
		TitleFactory $titleFactory
	) {
		parent::__construct(
			'statuscheck-check-file-upload',
			'statuscheck-check-file-upload-suggestions'
		);
		$this->globalIdGenerator = $globalIdGenerator;
		$this->permissionManager = $permissionManager;
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
		$filePath = __DIR__ . '/data/yuv420.jpg';
		$user = User::newSystemUser( User::MAINTENANCE_SCRIPT_USER );
		$scope = $this->permissionManager->addTemporaryUserRights(
			$user,
			[ 'upload', 'delete' ]
		);

		$titleAndImage = $this->getTitleAndImage();
		if ( $titleAndImage === null ) {
			return StatusValue::newFatal(
				'statuscheck-fileupload-uuid-collision'
			);
		}
		[ $title, $image ] = $titleAndImage;

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
			$result->merge(
				FileDeleteForm::doDelete(
					$title,
					$image,
					null,
					'StatusCheck test upload cleanup',
					// Suppress
					false,
					$user
				)
			);
		}

		return $result;
	}

	/**
	 * Try to get a Title object and an associated LocalFile object, for a file
	 * that does not already exist, using UUIDs to avoid collision. Try twice
	 * before giving up - the odds of colliding once are negligible, twice
	 * indicates something is probably wrong.
	 */
	private function getTitleAndImage(): ?array {
		$uuid = $this->globalIdGenerator->newUUIDv4();
		$fileName = "StatusCheckTestUpload-$uuid.jpg";
		$title = $this->titleFactory->newFromText( $fileName, NS_FILE );
		$image = $this->repoGroup->getLocalRepo()->newFile( $title );
		if ( !$image->exists() ) {
			return [ $title, $image ];
		}

		$uuid2 = $this->globalIdGenerator->newUUIDv4();
		$fileName2 = "StatusCheckTestUpload-$uuid2.jpg";
		$title2 = $this->titleFactory->newFromText( $fileName2, NS_FILE );
		$image2 = $this->repoGroup->getLocalRepo()->newFile( $title2 );
		if ( !$image2->exists() ) {
			return [ $title2, $image2 ];
		}

		return null;
	}
}
