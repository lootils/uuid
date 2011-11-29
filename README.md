# Universally Unique Identifiers (UUID)

This PHP library creates and verifies RFC 4122 compliant version 3, 4, and 5 UUIDs.

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

    $util = new UUID();
    $valid = $util->isValid($foo); // $valid will be a bool if it is valid or not.

### Creating a Random UUID

    $util = new UUID();
    $uuid = $util->v4(); // $uuid is now a random UUID.

### Creating a v5 UUID

    $util = new UUID();
    $uuid = $util->v5(UUID::URL, 'http://example.com/foo.html');

### Verifying a v5 UUID

If you know the namespace and name for a v5 UUID you can recreate and verify it. UUIDs created with the same namespace and name will always be the same. For example:

    $util = new UUID();
    $uuid1 = $util->v5(UUID::URL, 'http://example.com/foo.html');
    $uuid2 = $util->v5(UUID::URL, 'http://example.com/foo.html');

In this case `$uuid1` is equal to `$uuid2`.

### Creating a v3 UUID

    $util = new UUID();
    $uuid = $util->v3(UUID::URL, 'http://example.com/foo.html');

### Verifying a v3 UUID

If you know the namespace and name for a v3 UUID you can recreate and verify it. UUIDs created with the same namespace and name will always be the same. For example:

    $util = new UUID();
    $uuid1 = $util->v3(UUID::URL, 'http://example.com/foo.html');
    $uuid2 = $util->v3(UUID::URL, 'http://example.com/foo.html');

In this case `$uuid1` is equal to `$uuid2`.

### Creating a Custom Namespace

If you want to create a custom namespace (like the URL and DNS ones) for your application you can do so with v3 or v5 methods and a NIL namespace. For example:

    $util = new UUID();
    $namespace = $util->v5(UUID::NIL, 'my_app_name');


## License

This library was written by Matt Farina and is available under the MIT License.