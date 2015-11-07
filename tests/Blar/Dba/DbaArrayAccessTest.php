<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use PHPUnit_Framework_TestCase as TestCase;

class DbaArrayAccessTest extends TestCase {

    public function testSetAndGet() {
        $dba = $this->createTestDatabase();
        $dba['foo'] = 23;
        $dba['bar'] = 42;
        $dba['foobar'] = 1337;

        $this->assertEquals(23, $dba['foo']);
        $this->assertEquals(42, $dba['bar']);
        $this->assertEquals(1337, $dba['foobar']);
    }

    public function createTestDatabase() {
        var_dump(Dba::getDrivers());
        if(Dba::hasDriver('qdbm')) {
            return new Dba(
                tempnam(sys_get_temp_dir(), 'temp_test_dba'), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, array(
                    'driverName' => 'qdbm'
                )
            );
        }
        if(Dba::hasDriver('gdbm')) {
            return new Dba(
                tempnam(sys_get_temp_dir(), 'temp_test_dba'), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, array(
                    'driverName' => 'gdbm'
                )
            );
        }
        if(Dba::hasDriver('inifile')) {
            return new Dba(
                tempnam(sys_get_temp_dir(), 'temp_test_dba'), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, array(
                    'driverName' => 'inifile'
                )
            );
        }
        $this->markTestSkipped('Driver GDBM, GDBM, Inifile is not supported on this system');
    }

    public function testIsset() {
        $dba = $this->createTestDatabase();
        $dba['foo'] = 23;
        $dba['bar'] = 42;

        $this->assertTrue(isset($dba['foo']));
        $this->assertTrue(isset($dba['bar']));
        $this->assertFalse(isset($dba['foobar']));
    }

    public function testUnset() {
        $dba = $this->createTestDatabase();
        $dba['foo'] = 23;
        $dba['bar'] = 42;

        unset($dba['bar']);

        $this->assertTrue(isset($dba['foo']));
        $this->assertFalse(isset($dba['bar']));
        $this->assertFalse(isset($dba['foobar']));
    }

}
