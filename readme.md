# FSBO ORM
Namespace: __FSBO__

## Features
- [x] Fully OO. PDO based so PHP7 ready :). 
- [x] Object persistence.
- [x] Simple find search method.
- [x] Promotes proper property visibility usage within a class.
- [x] Promotes lazy laoded relationships within Model classes. 
- [ ] Support for composite keys because some FSBO tables have no primary keys.
- [ ] FSBO history table support.

## How to use

### Empty Object Instantiation
FSBO ORM should never return scalar values or arrays as a result. Return single object only. FSBO\ORM\Exception (extends \Exception) will be thrown if given object can't be located using its primary id can't be found.

```php
Listing::getInstance();
```
Result:
```php
FSBO\ORM\Listing Object
(
    [id] => 
    [password] => 
    [firstName] => 
    [lastName] => 
    [ownerName] => 
    [photos:protected] => 
)
```

### Instantiation by primary key
Throws an exception if record wasn't found.

```php
Listing::getInstance(7010);
```
Result:
```php
FSBO\ORM\Listing Object
(
    [id] => 7010
    [password] => Secret
    [firstName] => Wojtek is COOL''
    [lastName] => HAWKINS
    [ownerName] => Faye A Hawkins
    [photos:protected] => 
)
```

### Update
Properties are assigned directly. Setters and getters are optional.

```php
$listing = Listing::getInstance(7010);
$listing->firstName = "Wojtek is COOL''";
$listing->save(); // "Smart" update will either update or insert this object.
$listing->password = "Secret";
$listing->save();
```

### Insert
This will create new record in db

```php
$listing = Listing::getInstance();
$listing->firstName = 'Some name';
$listing->password = "Secret8";
$listing->save();
```

### Find
Use find() with following parameter.

```php

// Simple queries.
$listings = Listing::find(['where' => ['lastName' => 'Donato'], 'limit' => 2, 'order' => 'lastName DESC']);

// Or, for complex queries.
$listings = Listing::find(['where' => 'szFirstName = "Some name" AND szLastName LIKE "%DOn%" ', 'limit' => 100]);
```
Result:
```php
Array
(
    [0] => FSBO\ORM\Listing Object
        (
            [id] => 20320928
            [password] => 2540snow
            [firstName] => Tony 7010Pre Saved March 
            [lastName] => DONATO
            [ownerName] => Tony Donato
            [photos:protected] => 
        )

    [1] => FSBO\ORM\Listing Object
        (
            [id] => 20571643
            [password] => 2fee7c4f2a8664fc
            [firstName] => David
            [lastName] => DONATO
            [ownerName] => David Donato
            [photos:protected] => 
        )

)
```

### Performance
Object are stored in memory during instantiation, therefore following call won't create 2 DB queries. This object was already fetched from DB. ORM will get by reference.

```php 
$listing1 = Listing::getInstance(7010);
$listing2 = Listing::getInstance(7010);
```
Both produce:
```php
FSBO\ORM\Listing Object
(
    [id] => 7010
    [password] => Secret
    [firstName] => Wojtek is COOL''
    [lastName] => HAWKINS
    [ownerName] => Faye A Hawkins
    [photos:protected] => 
)
```

### Relationships 
Object relationships are lazy loaded and defined within object itself usually using find() method.

```php

// Previously initiated object $listing = Listing::getInstance(20727948);

$captions = $listing->getPhotoCaptions();

foreach($caption as $caption) {
    echo $caption->title;
}
```
Produce:
```php
Array
(
    [0] => FSBO\ORM\PhotoCaption Object
        (
            [id] => 2
            [listingID] => 20727948
            [photoID] => 1
            [title] => 
            [caption] => 
            [type] => 
            [createdAt] => 
        )

    [1] => FSBO\ORM\PhotoCaption Object
        (
            [id] => 3
            [listingID] => 20727948
            [photoID] => 2
            [title] => 
            [caption] => 
            [type] => 
            [createdAt] => 
        )

    [2] => FSBO\ORM\PhotoCaption Object
        (
            [id] => 4
            [listingID] => 20727948
            [photoID] => 3
            [title] => 
            [caption] => 
            [type] => 
            [createdAt] => 
        )

    [3] => FSBO\ORM\PhotoCaption Object
        (
            [id] => 5
            [listingID] => 20727948
            [photoID] => 4
            [title] => 
            [caption] => 
            [type] => 
            [createdAt] => 
        )

    [4] => FSBO\ORM\PhotoCaption Object
        (
            [id] => 6
            [listingID] => 20727948
            [photoID] => 5
            [title] => 
            [caption] => 
            [type] => 
            [createdAt] => 
        )

)
```