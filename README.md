
Kayo JSON mapper
================

[![Build Status](https://travis-ci.org/tsufeki/kayo-json-mapper.svg?branch=master)](https://travis-ci.org/tsufeki/kayo-json-mapper)

Map JSON data to PHP objects and back. No custom annotations needed.

Installation
------------

With [Composer](https://getcomposer.org/):
```
$ composer require tsufeki/kayo-json-mapper
```

Usage
-----

```php
use Tsufeki\KayoJsonMapper\MapperBuilder;

$mapper = MapperBuilder::create()
    ->getMapper();

$serialized = '{"foo": [1, 2], "bar": "baz"}';

// Pass data and the expected type:
$object = $mapper->load(json_decode($serialized), AClass::class);

$serialized2 = json_encode($mapper->dump($object));
```

### Configuration

Kayo is designed to load/dump data without the need for special per class
configuration such as annotations etc. All necessary information is gathered
from reflection and doc comments.

However, its general behaviour can be customized in many ways through
[`MapperBuilder`](src/Tsufeki/KayoJsonMapper/MapperBuilder.php) methods.

### Types & loading

All types recognized by phpDocumentor can be loaded, even union type (`A|B`) --
but please note that objects are differentiated on their shape (i.e.
properties) so `throwOnMissingProperty(true)` and
`throwOnUnknownProperty(true)` are usually necessary.

License
-------
MIT - see [LICENCE](LICENSE).

