<?php
/**
 * Class Listing generated Thursday, 17-Mar-16 17:43:05 EDT.
 */
namespace FSBO\ORM;

use FSBO\ORM;

class Listing extends ORM {

	const TABLE = 'tblListing';

	/* Used to properly identified stored object in runtime. */
	const PRIMARY_KEY = 'iListingID';

	/** @var [int(11)] $id This is either loaded from DB or auto generated on save(). */
	public $id;

	/** @var [varchar(20)] $password */
	public $password;

	/** @var [varchar(25)] $firstName */
	public $firstName = null;

	/** @var [varchar(25)] $lastName */
	public $lastName = null;

	/** @var [varchar(50)] $ownerName */
	public $ownerName;

	protected $photos = null;

	// In PHP 5.6 this should become const.
	static function selector() {
		return [
			'iListingID' => 'id',
			'szPassword' => 'password',
			'szFirstName' => 'firstName',
			'UPPER(szLastName)' => 'lastName', // <-- Note: MySQL applayed function on selector.
			'szOwnerName' => 'ownerName',
		];
	}

	protected function updater() {
		return [
			'szPassword' => $this->password,
			'szFirstName' => $this->firstName,
			'szLastName' => $this->lastName,
			'szOwnerName' => $this->ownerName,
		];
	}

	/**
	 * Lazy loaded relation. Subsequent call to this function won't produce new DB calls.
	 *
	 * @return null|PhotoCaption
	 */
	public function getPhotoCaptions() {
		if(is_null($this->photos)) {
			$this->photos = ORM\PhotoCaption::find([
				'where' => [
					'listingID' => 20727948 // @TODO change to $this->id
				]
			]);
		}
		return $this->photos;
	}

}