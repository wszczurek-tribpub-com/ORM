# FSBO ORM
Namespace: __FSBO__

## Features
- [x] Fully OO. PDO based so PHP7 ready :). 
- [x] Object persistence.
- [x] Simple find search method.
- [x] Promotes proper property visibility usage within a class.
- [x] Promotes lazy laoded relationships within Model classes. 
- [ ] Support for composite keys.
- [ ] FSBO history table support.

## How to use

### Instantiation
FSBO ORM should never return scalar values or arrays as a result. Return single object only. FSBO\ORM\Exception (extends \Exception) will be thrown if given object can't be located using its primary id can't be found.

```php
Listing::getInstance();
```