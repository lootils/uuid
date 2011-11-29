<?php

/**
 * @file
 * Provide basic v3, v4, and v5 UUID functionality for PHP.
 *
 * Created by Matt Farina on 2011-11-29.
 */

/**
 * This class creates RFC 4122 compliant Universally Unique Identifiers (UUID).
 *
 * The generated UUIDs can be version 3, 4, or 5. Functionality is provided to
 * validate UUIDs as well as validate name based UUIDs.
 *
 * @see https://tools.ietf.org/html/rfc4122
 * @see https://en.wikipedia.org/wiki/UUID
 *
 * @author Matt Farina <matt@mattfarina.com>
 * @copyright MIT License
 * @version 1.0
 */
class UUID {
  
  /**
   * @var string DNS namespace from RFC 4122 appendix C.
   */
  const DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

  /**
   * @var string URL namespace from RFC 4122 appendix C.
   */
  const URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

  /**
   * @var string ISO OID namespace from RFC 4122 appendix C.
   */
  const OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';

  /**
   * @var string X.500 namespace from RFC 4122 appendix C.
   */
  const X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

  /**
   * @var string NULL UUID string from RFC 4122.
   */
  const NIL = '00000000-0000-0000-0000-000000000000';

  /**
   * Validate if a UUID has a valid format.
   *
   * @param string $uuid
   *  The string to validate if it is in the proper UUID format.
   *
   * @param bool
   *  TRUE if the format is valid and FALSE otherwise.
   */
  public static function isValid($uuid) {
    return (preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1);
  }

  /**
   * Version 5 UUIDs are based on a namespace and name.
   *
   * If you have the same namespace and name you can recreate the namespace.
   * V5 UUIDs are prefered over v3. V5 is based on sha1 while v3 is based on md5.
   *
   * @see https://en.wikipedia.org/wiki/UUID#Version_5_.28SHA-1_hash.29
   *
   * @param string $namespace
   *  The UUID of the given namespace.
   * @param string $name
   *  The name we are creating the UUID for.
   */
  public static function v5($namespace, $name) {

    // If the namespace is not a valid UUID we throw an error.
    if (!self::isValid($namespace)) {
      throw new Exception('The UUID provided for the namespace is not valid.');
    }

    $bin = self::bin($namespace);

    $hash = sha1($bin . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',
      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 5
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  /**
   * Version 4 UUIDs are random.
   *
   * @see https://en.wikipedia.org/wiki/UUID#Version_4_.28random.29
   *
   * @return string
   *  A properly formatted v4 UUID.
  */
  public static function v4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand(0, 65535), mt_rand(0, 65535),

      // 16 bits for "time_mid"
      mt_rand(0, 65535),

      // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
      mt_rand(0, 4095),

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
    );
  }

  /**
   * Version 3 UUID are based on namespace and name utilizing a md5 hash.
   *
   * If you are considering using v3 consider using v5 instead as that is what
   * is recommended.
   *
   * @see https://en.wikipedia.org/wiki/UUID#Version_3_.28MD5_hash.29
   */
  public static function v3($namespace, $name) {
    // If the namespace is not a valid UUID we throw an error.
    if (!self::isValid($namespace)) {
      throw new Exception('The UUID provided for the namespace is not valid.');
    }

    $bin = self::bin($namespace);

    $hash = md5($bin . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 3
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
  );

  }

  /**
   * Utility function to convert hex into bin for a UUID.
   *
   * @param string $uuid
   *  A UUID to convert into binary format.
   *
   * @return string
   *  A UUID in binary format.
   */
  public static function bin($uuid) {
    if (!self::isValid($uuid)) {
      throw new Exception('The UUID provided for the namespace is not valid.');
    }

    // Get hexadecimal components of namespace
    $hex = str_replace(array('-','{','}'), '', $uuid);

    $bin = '';

    // Convert to bits
    for ($i = 0; $i < strlen($hex); $i += 2) {
        $bin .= chr(hexdec($hex[$i] . $hex[$i+1]));
    }

    return $bin;
  }

}