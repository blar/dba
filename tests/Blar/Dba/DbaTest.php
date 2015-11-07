<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use PHPUnit_Framework_TestCase as TestCase;
use Trowable;

class DbaTest extends TestCase {

    public function testInsertAndReplaceAndRemove() {
        $dba = $this->createTestDatabase();

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);

        $dba->remove('foo');
        # $dba->insert('bar', 69);
        $dba->replace('foobar', 42);

        $this->assertEquals(
            [
                'bar' => 42,
                'foobar' => 42
            ],
            iterator_to_array($dba)
        );
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

    public function testWithNamespace() {
        $fileName = tempnam(sys_get_temp_dir(), 'temp_test_dba');
        $dba = new Dba(
            $fileName, Dba::MODE_CREATE | Dba::MODE_TRUNCATE, array(
                'driverName' => 'inifile'
            )
        );
        $dba->setNamespace('foo');
        $dba->insert('a', 23);
        $dba->insert('b', 42);
        $dba->insert('c', 1337);

        $dba->setNamespace('bar');
        $dba->insert('a', 1337);
        $dba->insert('b', 42);
        $dba->insert('c', 23);

        // zend_mm_heap corrupted
        // var_dump($dba->fetch(['user']));

        $dba->setNamespace(NULL);
        $this->assertNull($dba->fetch('a'));
        $this->assertNull($dba->fetch('b'));
        $this->assertNull($dba->fetch('c'));

        $dba->setNamespace('foo');
        $this->assertEquals(23, $dba->fetch('a'));
        $this->assertEquals(42, $dba->fetch('b'));
        $this->assertEquals(1337, $dba->fetch('c'));

        $dba->setNamespace('bar');
        $this->assertEquals(1337, $dba->fetch('a'));
        $this->assertEquals(42, $dba->fetch('b'));
        $this->assertEquals(23, $dba->fetch('c'));
    }

    public function testArrayAccess() {
        $dba = $this->createTestDatabase();

        $dba['foo'] = 23;
        $dba['bar'] = 42;
        $dba['foobar'] = 1337;
        unset($dba['foobar']);

        $this->assertTrue(isset($dba['foo']));
        $this->assertFalse(isset($dba['foobar']));

        $this->assertEquals(
            array(
                'foo' => 23,
                'bar' => 42
            ),
            iterator_to_array($dba)
        );
    }

    public function testCreateAndReadConstantDatabase() {
        $fileName = tempnam(sys_get_temp_dir(), 'temp_test_cdb');

        $dba = new Dba(
            $fileName, Dba::MODE_CREATE | Dba::MODE_TRUNCATE, array(
                'driverName' => 'cdb_make'
            )
        );

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);

        $this->assertEquals(array(), iterator_to_array($dba));
        unset($dba);

        $dba = new Dba(
            $fileName, Dba::MODE_READ, array(
                'driverName' => 'cdb'
            )
        );

        $this->assertEquals(
            [
                'foo' => 23,
                'bar' => 42,
                'foobar' => 1337
            ],
            iterator_to_array($dba)
        );
    }

    /**
     * @expectedException Exception
     */
    public function testInsert() {
        $this->markTestSkipped();
        $fileName = tempnam(sys_get_temp_dir(), 'temp_test_cdb');

        $dba = new Dba(
            $fileName, Dba::MODE_WRITE, array(
                'driverName' => 'cdb'
            )
        );

        $dba->insert('foo', 23);
        $dba->insert('bar', 42);
        $dba->insert('foobar', 1337);
    }

}
