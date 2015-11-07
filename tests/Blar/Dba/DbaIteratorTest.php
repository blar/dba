<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use PHPUnit_Framework_TestCase as TestCase;

class DbaIteratorTest extends TestCase {

    public function testSimple() {
        $dba = $this->createTestDatabase();

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);

        $this->assertEquals(
            array(
                'foo' => 23,
                'bar' => 42,
                'foobar' => 1337
            ),
            iterator_to_array($dba)
        );
    }

    public function createTestDatabase() {
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

}
