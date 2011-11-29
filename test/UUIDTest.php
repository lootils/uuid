<?php

/**
 * @file
 * Unit tests for UUID v3, v4, and v5.
 *
 * Created by Matt Farina on 2011-11-29.
 */

require_once 'src/uuid.php';

class UUIDTest extends PHPUnit_Framework_TestCase {
  
  public $uuid = NULL;

  /**
   * Test the method UUID::isValid().
   */
  public function testIsValid() {
    $tests = array(
      '6ba7b810-9dad-11d1-80b4-00c04fd430c8' => TRUE,
      '6ba7b811-9dad-11d1-80b4-00c04fd430c8' => TRUE,
      '6ba7b812-9dad-11d1-80b4-00c04fd430c8' => TRUE,
      '6ba7b814-9dad-11d1-80b4-00c04fd430c8' => TRUE,
      '00000000-0000-0000-0000-000000000000' => TRUE,
      '6ba7b810-9dad-11d1-80b4-00c04fd4308' => FALSE,
      '6ba7b810-9dad-11d1-80b4-00c04fd430c8ss' => FALSE,
      '6ba7b84-9dad-11d1-80b4-00c04fd430c8' => FALSE,
    );

    foreach ($tests as $uuid => $expected) {
      $this->assertEquals($expected, UUID::isValid($uuid));
    }
  }

  /**
   * The the v4 random method created a properly formatted UUID.
   */
   public function testV4() {
     $this->assertTrue(UUID::isValid(UUID::createV4()));
   }

  /**
   * Thest that v4 is random.
   */
  public function testV4IsRandom() {
    $this->assertNotEquals(UUID::createV4(), UUID::createV4());
  }

  /**
   * Test v3 UUID.
   */
  public function testV3() {
    $tests = array(

      // From https://github.com/shadowhand/uuid/blob/3.1/master/tests/kohana/UUIDTest.php
      'team' => array(
        'expected' => 'db7ec69b-eb29-37ef-a76d-2e2ef553e92e',
        'namespace' => UUID::NIL,
      ),

      // From http://docs.python.org/library/uuid.html
      'python.org' => array(
        'expected' => '6fa459ea-ee8a-3ca4-894e-db77e160355e',
        'namespace' => UUID::DNS,
      ),

      // These were generated using Python.
      'mattfarina.com' => array(
        'expected' => '20438dde-20d8-349c-937e-4544a202f35a',
        'namespace' => UUID::DNS,
      ),
      'http://mattfarina.com' => array(
        'expected' => '67c6b6cf-7455-30ca-8c6a-1cb02a2ac3df',
        'namespace' => UUID::URL,
      ),
    );

    foreach ($tests as $name => $data) {
      $this->assertEquals($data['expected'], UUID::createV3($data['namespace'], $name));
    }
  }

  /**
   * Test v5 UUID.
   */
  public function testV5() {
    $tests = array(

      // From https://github.com/shadowhand/uuid/blob/3.1/master/tests/kohana/UUIDTest.php
      'team' => array(
        'expected' => 'd221f29a-4332-5f0d-b323-c5206a2e86ce',
        'namespace' => UUID::NIL,
      ),

      // From http://docs.python.org/library/uuid.html
      'python.org' => array(
        'expected' => '886313e1-3b8a-5372-9b90-0c9aee199e5d',
        'namespace' => UUID::DNS,
      ),

      // These were generated using Python.
      'mattfarina.com' => array(
        'expected' => '35e872b4-190a-5faa-a0f6-09da0d4f9c01',
        'namespace' => UUID::DNS,
      ),
      'http://mattfarina.com' => array(
        'expected' => '6b7decb1-f9ad-5821-b676-dc73006cd2d5',
        'namespace' => UUID::URL,
      ),
    );

    foreach ($tests as $name => $data) {
      $this->assertEquals($data['expected'], UUID::createV5($data['namespace'], $name));
    }
  }

  /**
   * Test parsing a UUID as a string when wrapped by {}.
   */
  public function testParseUUID() {
    $uuid = new UUID('{886313e1-3b8a-5372-9b90-0c9aee199e5d}');
    $this->assertEquals('886313e1-3b8a-5372-9b90-0c9aee199e5d', $uuid);
  }

  /**
   * Test setting and getting information around a UUID.
   */
  public function testData() {
    $uuid = new UUID('{35e872b4-190a-5faa-a0f6-09da0d4f9c01}', UUID::V5, UUID::DNS, 'mattfarina.com');

    $this->assertEquals(UUID::V5, $uuid->getVersion());
    $this->assertEquals(UUID::DNS, $uuid->getNamespace());
    $this->assertEquals('mattfarina.com', $uuid->getName());
  }

  /**
   * Test URL Support.
   */
  public function testURN() {
    $uuid = new UUID('urn:uuid:35e872b4-190a-5faa-a0f6-09da0d4f9c01', UUID::V5, UUID::DNS, 'mattfarina.com');

    $this->assertEquals('35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid);
    $this->assertEquals('urn:uuid:35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid->getURN());
  }

  /**
   * Test passing in arrays as a URI type.
   */
  public function testArrays() {
    $uuid = new UUID(array('35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01'), UUID::V5, UUID::DNS, 'mattfarina.com');
    $this->assertEquals('35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid);

    $array = array(
       'time_low' => '35e872b4',
       'time_mid' => '190a',
       'time_hi_version' => '5faa',
       'clock_seq_hi_variant' => 'a0',
       'clock_seq_low' => 'f6',
       'node' => '09da0d4f9c01',
    );
    $uuid2 = new UUID($array, UUID::V5, UUID::DNS, 'mattfarina.com');
    $this->assertEquals('35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid2);
  }

  /**
   * Make sure fields are properly getting into their place.
   */
  public function testFields() {
    $array = array(
       'time_low' => '35e872b4',
       'time_mid' => '190a',
       'time_hi_version' => '5faa',
       'clock_seq_hi_variant' => 'a0',
       'clock_seq_low' => 'f6',
       'node' => '09da0d4f9c01',
    );
    $uuid = new UUID($array, UUID::V5, UUID::DNS, 'mattfarina.com');

    $fields = $uuid->listFields();
    foreach ($fields as $field) {
      $this->assertEquals($array[$field], $uuid->getField($field));
    }
  }
}