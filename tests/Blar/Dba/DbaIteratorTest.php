<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class DbaIteratorTest
 *
 * @package Blar\Dba
 */
class DbaIteratorTest extends TestCase {

    public function testSimple() {
        $dba = $this->createTestDatabase();

        $dba->addValue('foo', 23);
        $dba->addValue('bar', 42);
        $dba->addValue('foobar', 1337);

        $this->assertEquals(
            [
                'foo' => 23,
                'bar' => 42,
                'foobar' => 1337
            ],
            iterator_to_array($dba)
        );
    }

    public function createTestDatabase() {
        if(Dba::hasDriver('qdbm')) {
            return new Dba(
                $this->getTempFileName(), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                    'driverName' => 'qdbm'
                ]
            );
        }
        if(Dba::hasDriver('gdbm')) {
            return new Dba(
                $this->getTempFileName(), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                    'driverName' => 'gdbm'
                ]
            );
        }
        if(Dba::hasDriver('inifile')) {
            return new Dba(
                $this->getTempFileName(), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                    'driverName' => 'inifile'
                ]
            );
        }
        $this->markTestSkipped('Driver GDBM, GDBM, Inifile is not supported on this system');
    }

    protected function getTempFileName(): string {
        return tempnam(sys_get_temp_dir(), 'temp_test_dba');
    }

}
