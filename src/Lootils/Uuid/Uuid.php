<?php

/**
 * @file
 * Provide basic v3, v4, and v5 UUID functionality for PHP.
 *
 * Created by Matt Farina on 2011-11-29.
 */

namespace Lootils\Uuid;

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
class Uuid {
  
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
   * @var UUID Version 3.
   */
  const V3 = '3';

  /**
   * @var UUID Version 4.
   */
  const V4 = '4';

  /**
   * @var UUID Version 5.
   */
  const V5 = '5';

  /**
   * @var the first 32 bits of the UUID.
   */
  protected $time_low = NULL;

  /**
   * @var the next 16 bits of the UUID.
   */
  protected $time_mid = NULL;

  /**
   * @var the next 16 bits of the UUID.
   */
  protected $time_hi_version = NULL;

  /**
   * @var the next 8 bits of the UUID.
   */
  protected $clock_seq_hi_variant = NULL;

  /**
   * @var the next 8 bits of the UUID.
   */
  protected $clock_seq_low = NULL;

  /**
   * @var the last 48 bits of the UUID.
   */
  protected $node = NULL;

  /**
   * @var the version of the UUID.
   */
  protected $version = NULL;

  /**
   * @var the namespace used when the UUID was generated.
   */
  protected $namespace = NULL;

  /**
   * @var the name used when the UUID was generated.
   */
  protected $name = NULL;

  /**
   * Return a list of UUID fields. These can be used with the getField() method.
   *
   * @return array
   *  A list of fields on the UUID. These can be used with the getField method.
   */
  public function listFields() {
    return array(
      'time_low',
      'time_mid',
      'time_hi_version',
      'clock_seq_hi_variant',
      'clock_seq_low',
      'node',
    );
  }

  /**
   * Get the value of a UUID field.
   *
   * @param string $name
   *  The name of a field to get the vurrent value. The values are returned in
   *  hex format.
   */
  public function getField($name) {
    if (!in_array($name, $this->listFields())) {
      throw new Exception('A field value was requested for an invalid field name.');
    }

    return $this->$name;
  }

  /**
   * Get the UUID version for this UUID.
   *
   * @return string
   *  The UUID Version number.
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Get the UUID namespace for this UUID.
   *
   * @return string
   *  The UUID namespace if known.
   */
  public function getNamespace() {
    return $this->namespace;
  }

  /**
   * Get the UUID name for this UUID.
   *
   * @return string
   *  The UUID name if known.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set the version of the UUID.
   *
   * @param string $version
   *   3, 4, or 5 which are the possible suppored versions.
   */
  protected function setVersion($version) {
    if ($version == self::V3 || $version == self::V4 || $version == self::V5) {
      $this->version = $version;
    }
    else {
      throw new Exception('An invalid UUID version was specified.');
    }
  }

  /**
   * Get the URN for a URI.
   */
  public function getURN() {
    return 'urn:uuid:' . $this;
  }

  /**
   * Get the UUID.
   * 
   * @return string
   *   A string containing a properly formatted UUID.
   */
  function getUuid() {
    return $this->time_low . '-' . $this->time_mid . '-' . $this->time_hi_version . '-' . $this->clock_seq_hi_variant . $this->clock_seq_low . '-' . $this->node;
  }

  /**
   * Construct a new UUID object.
   *
   * There are a number of ways this UUID object could be generated.
   *  - By the hex UUID: '{12345678-1234-5678-1234-567812345678}'
   *  - Via the fields passed in as an array. For a list of fields see the method
   *    listFields().
   *  - From a URN: 'urn:uuid:12345678-1234-5678-1234-567812345678'
   *
   * @param mixed $uuid
   *  The UUID in a format that can be parsed.
   * @param const $version
   *  (optional but recommended) The UUID version. If none specified we simply don't know it.
   * @param string $namespace
   *  (optional) The namespace used when the UUID was generated. This is useful with v3 and v5 UUIDs.
   * @param string $name
   *  (optional) The name used when the UUID was generated. This is useful with v3 and v5 UUIDs.
   */
  public function __construct($uuid, $version = NULL, $namespace = NULL, $name = NULL) {
    $this->parse($uuid);
    if (!is_null($version)) {
      $this->setVersion($version);
    }
    $this->namespace = $namespace;
    $this->name = $name;
  }

  /**
   * Parse the UUID from the available formats.
   *
   * @todo this should be written prettier. For realz.
   */
  protected function parse($uuid) {
    // The UUID as a standard string was passed in.
    if (is_string($uuid)) {
      if (substr($uuid, 0, 1) === '{' && substr($uuid, -1, 1) === '}') {
        $string = substr($uuid, 1, strlen($uuid) - 2);
        $this->parseStringToParts($string);
      }
      // The case where a URL was supplied.
      elseif (substr($uuid, 0, 9) === 'urn:uuid:') {
        $string = substr($uuid, 9);
        $this->parseStringToParts($string);
      }
      else {
        throw new Exception('The UUID string supplied could not be parsed.');
      }
    }
    elseif (is_array($uuid)) {

      if (count($uuid) != 6) {
        throw new Exception('The UUID array supplied could not be parsed.');
      }

      // For the case where a UUID is passed in via the format:
      // array('35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01');
      if (isset($uuid[0]) && !empty($uuid[0])) {
        $this->time_low = $uuid[0];
        $this->time_mid = $uuid[1];
        $this->time_hi_version =$uuid[2];
        $this->clock_seq_hi_variant = $uuid[3];
        $this->clock_seq_low =$uuid[4];
        $this->node = $uuid[5];
      }
      // For the case where the UUID is passed in via the format:
      // array(
      //  'time_low' => '35e872b4',
      //  'time_mid' => '190a',
      //  'time_hi_version' => '5faa',
      //  'clock_seq_hi_variant' => 'a0',
      //  'clock_seq_low' => 'f6',
      //  'node' => '09da0d4f9c01',
      // );
      elseif (isset($uuid['time_low']) && !empty($uuid['time_low'])) {
        $this->time_low = $uuid['time_low'];
        $this->time_mid = $uuid['time_mid'];
        $this->time_hi_version =$uuid['time_hi_version'];
        $this->clock_seq_hi_variant = $uuid['clock_seq_hi_variant'];
        $this->clock_seq_low =$uuid['clock_seq_low'];
        $this->node = $uuid['node'];
      }
      else {
        throw new Exception('The UUID array supplied could not be parsed.');
      }
    }
    else {
      throw new Exception('The UUID supplied could not be parsed.');
    }
  }

  /**
   * Parse a string in the form of 12345678-1234-5678-1234-567812345678.
   */
  protected function parseStringToParts($string) {
    $parts = explode('-', $string);
        
    if (count($parts) != 5) {
      throw new Exception('The UUID string supplied could not be parsed.');
    }

    foreach ($parts as $id => $part) {
      switch ($id) {
        case 0:
          $this->time_low = $part;
          break;
        case 1:
          $this->time_mid = $part;
          break;
        case 2:
          $this->time_hi_version = $part;
          break;
        case 3:
          $this->clock_seq_hi_variant = substr($part, 0, 2);
          $this->clock_seq_low = substr($part, 2);
          break;
        case 4:
          $this->node = $part;
          break;
      }
    }
  }

  /**
   * Display the UUID as a string in the format 6ba7b810-9dad-11d1-80b4-00c04fd430c8.
   */
  public function __toString() {
    return $this->getUuid();
  }

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
  public static function createV5($namespace, $name) {

    // If the namespace is not a valid UUID we throw an error.
    if (!self::isValid($namespace)) {
      throw new Exception('The UUID provided for the namespace is not valid.');
    }

    $bin = self::bin($namespace);

    $hash = sha1($bin . $name);

    return new self (sprintf('{%08s-%04s-%04x-%04x-%12s}',
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
    ), self::V5, $namespace, $name);
  }

  /**
   * Version 4 UUIDs are random.
   *
   * @see https://en.wikipedia.org/wiki/UUID#Version_4_.28random.29
   *
   * @return string
   *  A properly formatted v4 UUID.
  */
  public static function createV4() {
    return new self (sprintf('{%04x%04x-%04x-%04x-%04x-%04x%04x%04x}',
      // 32 bits for "time_low"
      mt_rand(0, 65535), mt_rand(0, 65535),

      // 16 bits for "time_mid"
      mt_rand(0, 65535),

      // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
      mt_rand(0, 4095) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
    ));
  }

  /**
   * Version 3 UUID are based on namespace and name utilizing a md5 hash.
   *
   * If you are considering using v3 consider using v5 instead as that is what
   * is recommended.
   *
   * @see https://en.wikipedia.org/wiki/UUID#Version_3_.28MD5_hash.29
   */
  public static function createV3($namespace, $name) {
    // If the namespace is not a valid UUID we throw an error.
    if (!self::isValid($namespace)) {
      throw new Exception('The UUID provided for the namespace is not valid.');
    }

    $bin = self::bin($namespace);

    $hash = md5($bin . $name);

    return new self (sprintf('{%08s-%04s-%04x-%04x-%12s}',

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
    ), self::V3, $namespace, $name);

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