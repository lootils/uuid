# Universally Unique Identifiers (UUID)

This PHP library, part of [Lootils](http://github.com/mattfarina/Lootils), creates and verifies RFC 4122 compliant version 3, 4, and 5 UUIDs.

[![Build Status](https://secure.travis-ci.org/mattfarina/Lootils-UUID.png?branch=master)](http://travis-ci.org/mattfarina/Lootils-UUID) [![Latest Stable Version](https://poser.pugx.org/lootils/uuid/v/stable.png)](https://packagist.org/packages/lootils/uuid) [![Coverage Status](https://coveralls.io/repos/lootils/uuid/badge.png)](https://coveralls.io/r/lootils/uuid)

For more information on UUIDs see

* [The Wikipedia Artice on UUIDs](https://en.wikipedia.org/wiki/UUID)
* [RFC 4122](https://tools.ietf.org/html/rfc4122)

## UUID Versions

This library works with 3 versions of UUIDs.

* Version 3: UUIDs generated based on a namespace and a name. Internally a MD5 hash is used. _Note: version 5 is preferred over version 3._
* Version 4: Random UUIDs.
* Version 5: UUIDs generated based on a namespace and a name. Internally a sha1 hash is used.

## Usage

I strongly suggest reading the code comments in uuid.php as it is well documented. Reading the tests may be helpful as well as there are examples in there.

### Validating a UUID format

    $valid = \Lootils\Uuid\Uuid::isValid($foo); // $valid will be a bool if it is valid or not.

### The UUID Object

The UUID object is one that can be created though its constructor but most often should happen via one of the version factirues in UUID::createV3, UUID::createV4, UUID::createV5.

Once an object is created there are a number of methods you can use. They include:

* listFields: This lists the available fields.
* getField: This returns the value for a passed in field name.
* getVersion: Returns the UUID version (3, 4, or 5) if it is known.
* getNamespace: Returns the namespace used to generate the UUID if it is known.
* getName: Returns the name used to generate the UUID if it is known.
* getURN: Returns a URN for the UUID.

If you print the uuid you will get it as a string. For example:

    $uuid = \Lootils\Uuid\Uuid::createV4();
    print $uuid; // This will display the UUID in the format 6ba7b810-9dad-11d1-80b4-00c04fd430c8.

If you are not using one of the factories you can create the an object using the following arguments:

    $uuid = new \Lootils\Uuid\Uuid($uuid, $version, $namespace, $name);

The arguments are in the form:

* $uuid: The UUID as can be supplied in 4 different formats.

  * As a hex string in the format '{6ba7b810-9dad-11d1-80b4-00c04fd430c8}'.
  * Via a URN with the format 'urn:uuid:6ba7b810-9dad-11d1-80b4-00c04fd430c8'.
  * Though an array in the form array('35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01').
  * With an array where the keys are the field names as seen on the listField method.

* $version: The version if known. Could be Uuid::V3, Uuid::V4, Uuid::V5.
* $namespace: For v3 and v5, the namespace used if it is known..
* $name: For v3 and v5, the name used if it is known.

### Creating a Random UUID

    $uuid = \Lootils\Uuid\Uuid::createV4(); // $uuid is now a random UUID.

### Creating a v5 UUID

    $uuid = \Lootils\Uuid\Uuid::createV5(\Lootils\Uuid\Uuid::URL, 'http://example.com/foo.html');

### Verifying a v5 UUID

If you know the namespace and name for a v5 UUID you can recreate and verify it. UUIDs created with the same namespace and name will always be the same. For example:

    $uuid1 = \Lootils\Uuid\Uuid::createV5(\Lootils\Uuid\Uuid::URL, 'http://example.com/foo.html');
    $uuid2 = \Lootils\Uuid\Uuid::createV5(\Lootils\Uuid\Uuid::URL, 'http://example.com/foo.html');

In this case `$uuid1` is equal to `$uuid2`.

### Creating a v3 UUID

    $uuid = \Lootils\Uuid\Uuid::createV3(\Lootils\Uuid\Uuid::URL, 'http://example.com/foo.html');

### Verifying a v3 UUID

If you know the namespace and name for a v3 UUID you can recreate and verify it. UUIDs created with the same namespace and name will always be the same. For example:

    $uuid1 = \Lootils\Uuid\Uuid::createV3(\Lootils\Uuid\Uuid::URL, 'http://example.com/foo.html');
    $uuid2 = \Lootils\Uuid\Uuid::createV3(\Lootils\Uuid\Uuid::URL, 'http://example.com/foo.html');

In this case the uuid hex string value for `$uuid1` is equal to `$uuid2`.

### Creating a Custom Namespace

If you want to create a custom namespace (like the URL and DNS ones) for your application you can do so with v3 or v5 methods and a NIL namespace. For example:

    $namespace = \Lootils\Uuid\Uuid::createV5(\Lootils\Uuid\Uuid::NIL, 'my_app_name');


## License

This library was written by Matt Farina and is available under the MIT License.