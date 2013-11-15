<?php

/**
 * @file
 * Unit tests for UUID v3, v4, and v5.
 *
 * Created by Matt Farina on 2011-11-29.
 */

// Include the composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

use Lootils\Uuid\Uuid;

class UUIDTest extends PHPUnit_Framework_TestCase {
  
  public $uuid = NULL;

  /**
   * Test the method Uuid::isValid().
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
      $this->assertEquals($expected, Uuid::isValid($uuid));
    }
  }

  /**
   * The the v4 random method created a properly formatted UUID.
   */
   public function testV4() {

    $var = 0;
    do {
      ++$var;

      $uuid = Uuid::createV4();
      $this->assertTrue(Uuid::isValid($uuid));
      $this->assertEquals('4', substr($uuid, 14, 1));
      $this->assertTrue(in_array(substr($uuid, 19, 1), array('8', '9', 'a', 'b')));
    } while ($var < 1000);
   }

  /**
   * Thest that v4 is random.
   */
  public function testV4IsRandom() {
    $this->assertNotEquals(Uuid::createV4(), Uuid::createV4());
  }

  /**
   * Test v3 UUID.
   */
  public function testV3() {
    $tests = array(

      // From https://github.com/shadowhand/uuid/blob/3.1/master/tests/kohana/UUIDTest.php
      'team' => array(
        'expected' => 'db7ec69b-eb29-37ef-a76d-2e2ef553e92e',
        'namespace' => Uuid::NIL,
      ),

      // From http://docs.python.org/library/uuid.html
      'python.org' => array(
        'expected' => '6fa459ea-ee8a-3ca4-894e-db77e160355e',
        'namespace' => Uuid::DNS,
      ),

      // These were generated using Python.
      'mattfarina.com' => array(
        'expected' => '20438dde-20d8-349c-937e-4544a202f35a',
        'namespace' => Uuid::DNS,
      ),
      'http://mattfarina.com' => array(
        'expected' => '67c6b6cf-7455-30ca-8c6a-1cb02a2ac3df',
        'namespace' => Uuid::URL,
      ),
    );

    foreach ($tests as $name => $data) {
      $uuid = Uuid::createV3($data['namespace'], $name)->getUuid();
      $this->assertEquals($data['expected'], $uuid);
      $this->assertEquals('3', substr($uuid, 14, 1));
      $this->assertTrue(in_array(substr($uuid, 19, 1), array('8', '9', 'a', 'b')));
    }
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID provided for the namespace is not valid.
   */
  public function testInvalidNamespaceCreateV3Exception() {
    $uuid = Uuid::createV3('foo', 'bar');
  }

  /**
   * Test v5 UUID.
   */
  public function testV5() {
    $tests = array(

      // From https://github.com/shadowhand/uuid/blob/3.1/master/tests/kohana/UUIDTest.php
      'team' => array(
        'expected' => 'd221f29a-4332-5f0d-b323-c5206a2e86ce',
        'namespace' => Uuid::NIL,
      ),

      // From http://docs.python.org/library/uuid.html
      'python.org' => array(
        'expected' => '886313e1-3b8a-5372-9b90-0c9aee199e5d',
        'namespace' => Uuid::DNS,
      ),

      // These were generated using Python.
      'mattfarina.com' => array(
        'expected' => '35e872b4-190a-5faa-a0f6-09da0d4f9c01',
        'namespace' => Uuid::DNS,
      ),
      'http://mattfarina.com' => array(
        'expected' => '6b7decb1-f9ad-5821-b676-dc73006cd2d5',
        'namespace' => Uuid::URL,
      ),
    );

    foreach ($tests as $name => $data) {
      $uuid = Uuid::createV5($data['namespace'], $name)->getUuid();
      $this->assertEquals($data['expected'], $uuid);
      $this->assertEquals('5', substr($uuid, 14, 1));
      $this->assertTrue(in_array(substr($uuid, 19, 1), array('8', '9', 'a', 'b')));
    }
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID provided for the namespace is not valid.
   */
  public function testInvalidNamespaceCreateV5Exception() {
    $uuid = Uuid::createV5('foo', 'bar');
  }

  /**
   * Test parsing a UUID as a string when wrapped by {}.
   */
  public function testParseUUID() {
    $uuid = new Uuid('{886313e1-3b8a-5372-9b90-0c9aee199e5d}');
    $this->assertEquals('886313e1-3b8a-5372-9b90-0c9aee199e5d', $uuid->getUuid());
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID string supplied could not be parsed.
   */
  public function testStringToPartsException() {
    $uuid = new Uuid('{35e872b4-190a-5faa-a0f6-09da0d4f9c01-453}');
  }

  /**
   * Test setting and getting information around a UUID.
   */
  public function testData() {
    $uuid = new Uuid('{35e872b4-190a-5faa-a0f6-09da0d4f9c01}', Uuid::V5, Uuid::DNS, 'mattfarina.com');

    $this->assertEquals(Uuid::V5, $uuid->getVersion());
    $this->assertEquals(Uuid::DNS, $uuid->getNamespace());
    $this->assertEquals('mattfarina.com', $uuid->getName());
  }

  /**
   * Test URL Support.
   */
  public function testURN() {
    $uuid = new Uuid('urn:uuid:35e872b4-190a-5faa-a0f6-09da0d4f9c01', Uuid::V5, Uuid::DNS, 'mattfarina.com');

    $this->assertEquals('35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid->getUuid());
    $this->assertEquals('urn:uuid:35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid->getURN());
  }

  /**
   * Test passing in arrays as a URI type.
   */
  public function testArrays() {
    $uuid = new Uuid(array('35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01'), UUID::V5, UUID::DNS, 'mattfarina.com');
    $this->assertEquals('35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid->getUuid());

    $array = array(
       'time_low' => '35e872b4',
       'time_mid' => '190a',
       'time_hi_version' => '5faa',
       'clock_seq_hi_variant' => 'a0',
       'clock_seq_low' => 'f6',
       'node' => '09da0d4f9c01',
    );
    $uuid2 = new Uuid($array, Uuid::V5, Uuid::DNS, 'mattfarina.com');
    $this->assertEquals('35e872b4-190a-5faa-a0f6-09da0d4f9c01', $uuid2->getUuid());
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID array supplied could not be parsed.
   */
  public function testParseLongArrayException() {
    $uuid = new Uuid(array('35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01', 'foo'));
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID array supplied could not be parsed.
   */
  public function testParseArrayStructureException() {
    $array = array('foo', '35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01');
    unset($array[0]);
    $uuid = new Uuid($array);
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID string supplied could not be parsed.
   */
  public function testParseStringException() {
    $uuid = new Uuid('foo');
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID supplied could not be parsed.
   */
  public function testParseUnknownType() {
    $test = new StdClass();
    $uuid = new Uuid($test);
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage An invalid UUID version was specified.
   */
  public function testInvalidSetVersionException() {
    $uuid = new Uuid(array('35e872b4', '190a', '5faa', 'a0', 'f6', '09da0d4f9c01'), 7);
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage The UUID provided for the namespace is not valid.
   */
  public function testInvalidUuidBinException() {
    $uuid = Uuid::bin('foo');
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
    $uuid = new Uuid($array, Uuid::V5, Uuid::DNS, 'mattfarina.com');

    $fields = $uuid->listFields();
    foreach ($fields as $field) {
      $this->assertEquals($array[$field], $uuid->getField($field));
    }
  }

  /**
   * @expectedException \Lootils\Uuid\Exception
   * @expectedExceptionMessage A field value was requested for an invalid field name.
   */
  public function testGetFieldException() {
    $array = array(
       'time_low' => '35e872b4',
       'time_mid' => '190a',
       'time_hi_version' => '5faa',
       'clock_seq_hi_variant' => 'a0',
       'clock_seq_low' => 'f6',
       'node' => '09da0d4f9c01',
    );
    $uuid = new Uuid($array, Uuid::V5, Uuid::DNS, 'mattfarina.com');
    $uuid->getField('foo');
  }

  /**
   * Test __toString converting an object to a valid UUID.
   */
  public function testToString() {
    $this->assertEquals('20438dde-20d8-349c-937e-4544a202f35a', (string)Uuid::createV3(Uuid::DNS, 'mattfarina.com')->getUuid());
  }
}