<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use PHPUnit_Framework_TestCase as TestCase;

class DbaTest extends TestCase {

    public function createTestDatabase() {
        if(!Dba::hasDriver('gdbm')) {
            $this->markTestSkipped('Driver GDBM is not supported on this system');
        }
        return new Dba(tempnam(sys_get_temp_dir(), 'temp_test_dba'), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
            'driverName' => 'gdbm'
        ]);
    }

    public function testInsertAndReplaceAndRemove() {
        $dba = $this->createTestDatabase();

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);

        $dba->remove('foo');
        # $dba->insert('bar', 69);
        $dba->replace('foobar', 42);

        $this->assertEquals([
            'bar' => 42,
            'foobar' => 42
        ], iterator_to_array($dba));
    }

    public function testArrayAccess() {
        $dba = $this->createTestDatabase();

        $dba['foo'] = 23;
        $dba['bar'] = 42;
        $dba['foobar'] = 1337;
        unset($dba['foobar']);

        $this->assertTrue(isset($dba['foo']));
        $this->assertFalse(isset($dba['foobar']));

        $this->assertEquals([
            'foo' => 23,
            'bar' => 42
        ], iterator_to_array($dba));
    }

    public function testIterator() {
        $dba = $this->createTestDatabase();

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);

        $this->assertEquals([
            'foo' => 23,
            'bar' => 42,
            'foobar' => 1337
        ], iterator_to_array($dba));
    }

    public function testCreateAndReadConstantDatabase() {
        $fileName = tempnam(sys_get_temp_dir(), 'temp_test_cdb');

        $dba = new Dba($fileName, Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
            'driverName' => 'cdb_make'
        ]);

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);

        $this->assertEquals([], iterator_to_array($dba));
        unset($dba);

        $dba = new Dba($fileName, Dba::MODE_READ, [
            'driverName' => 'cdb'
        ]);

        $this->assertEquals([
            'foo' => 23,
            'bar' => 42,
            'foobar' => 1337
        ], iterator_to_array($dba));
    }

    /**
     * @expectedException Exception
     */
    public function testInsert() {
        $fileName = tempnam(sys_get_temp_dir(), 'temp_test_cdb');

        $dba = new Dba($fileName, Dba::MODE_WRITE, [
            'driverName' => 'cdb'
        ]);

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);
    }

}
