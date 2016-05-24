<?php
/**
 * Class PhotoCaption generated Friday, 15-Apr-16 15:19:27 UTC.
 */
namespace FSBO\ORM;

use FSBO\ORM;

class PhotoCaption extends ORM {

	const TABLE = 'tblPhotoCaption';

	const PRIMARY_KEY = 'iPhotoCaptionID';

	/** @var [int(11)] $id */
	public $id;

	/** @var [int(11)] $listingID */
	public $listingID;

	/** @var [int(11)] $photoID */
	public $photoID;

	/** @var [varchar(40)] $title */
	public $title = null;

	/** @var [varchar(80)] $caption */
	public $caption = null;

	/** @var [varchar(50)] $type */
	public $type = null;

	/** @var [timestamp] $createdAt */
	public $createdAt = null;

	static function selector() {
		return [
			'iPhotoCaptionID' => 'id',
			'iListingID' => 'listingID',
			'iPhotoID' => 'photoID',
			'szTitle' => 'title',
			'szCaption' => 'caption',
			'szType' => 'type',
			'dtCreatedAt' => 'createdAt'
		];
	}

	protected function updater() {
		return [
			'iListingID' => $this->listingID,
			'iPhotoID' => $this->photoID,
			'szTitle' => $this->title,
			'szCaption' => $this->caption,
			'szType' => $this->type,
			'dtCreatedAt' => $this->createdAt
		];
	}

}