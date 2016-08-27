<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

class DbaTest extends TestCase {

    protected function getTempFileName(): string {
        return tempnam(sys_get_temp_dir(), 'temp_test_dba');
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

    public function testLockModes() {
        $this->assertSame('d', Dba::getLockMode(Dba::MODE_LOCK_DATABASE));
        $this->assertSame('l', Dba::getLockMode(Dba::MODE_LOCK_LOCKFILE));
        $this->assertSame('-', Dba::getLockMode(Dba::MODE_LOCK_IGNORE));
    }

    public function testFileModes() {
        $this->assertSame('r', Dba::getFileMode(Dba::MODE_READ));
        $this->assertSame('w', Dba::getFileMode(Dba::MODE_WRITE));
        $this->assertSame('c', Dba::getFileMode(Dba::MODE_CREATE));
        $this->assertSame('n', Dba::getFileMode(Dba::MODE_TRUNCATE));

        $this->assertSame('w', Dba::getFileMode(Dba::MODE_READ | Dba::MODE_WRITE));

        $this->assertSame('c', Dba::getFileMode(Dba::MODE_READ | Dba::MODE_WRITE | Dba::MODE_CREATE));
        $this->assertSame('c', Dba::getFileMode(Dba::MODE_READ | Dba::MODE_CREATE));
        $this->assertSame('c', Dba::getFileMode(Dba::MODE_WRITE | Dba::MODE_CREATE));

        $this->assertSame('n', Dba::getFileMode(Dba::MODE_READ | Dba::MODE_WRITE | Dba::MODE_TRUNCATE));
        $this->assertSame('n', Dba::getFileMode(Dba::MODE_READ | Dba::MODE_TRUNCATE));
        $this->assertSame('n', Dba::getFileMode(Dba::MODE_WRITE | Dba::MODE_TRUNCATE));
    }

    public function testInsertAndReplaceAndRemove() {
        $dba = $this->createTestDatabase();

        $dba->addValue('foo', 23);
        $dba->addValue('bar', 42);
        $dba->addValue('foobar', 1337);

        $dba->removeValues('foo');
        $dba->setValue('foobar', 42);

        $this->assertSame(
            [
                'bar' => '42',
                'foobar' => '42'
            ],
            iterator_to_array($dba)
        );
    }

    public function testWithNamespace() {
        $dba = new Dba(
            $this->getTempFileName(), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                'driverName' => 'inifile'
            ]
        );
        $dba->setNamespace('foo');
        $dba->addValue('a', 23);
        $dba->addValue('b', 42);
        $dba->addValue('c', 1337);

        $dba->setNamespace('bar');
        $dba->addValue('a', 1337);
        $dba->addValue('b', 42);
        $dba->addValue('c', 23);
        $dba->addValue('c', 42);

        $dba->setNamespace('');
        $this->assertNull($dba->getValue('a'));
        $this->assertNull($dba->getValue('b'));
        $this->assertNull($dba->getValue('c'));

        $dba->setNamespace('foo');
        $this->assertEquals(23, $dba->getValue('a'));
        $this->assertEquals(42, $dba->getValue('b'));
        $this->assertEquals(1337, $dba->getValue('c'));

        $dba->setNamespace('bar');
        $this->assertEquals(1337, $dba->getValue('a'));
        $this->assertEquals(42, $dba->getValue('b'));
        $this->assertEquals(23, $dba->getValue('c'));
    }

    public function testMultipleValues() {
        $dba = new Dba(
            $fileName = $this->getTempFileName(), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                'driverName' => 'inifile'
            ]
        );
        $dba->setNamespace('foo');
        $dba->addValue('foo', 23);
        $dba->addValue('foo', 42);
        $dba->addValue('foo', 1337);

        $this->assertSame(['23', '42', '1337'], $dba->getValues('foo'));
    }

    public function testMultipleValuesAtOnce() {
        $dba = new Dba(
            $fileName = $this->getTempFileName(), Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                'driverName' => 'inifile'
            ]
        );
        $dba->setNamespace('foo');
        $dba->setValues('foo', ['23', '42', '1337']);

        $this->assertSame(['23', '42', '1337'], $dba->getValues('foo'));
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
            [
                'foo' => 23,
                'bar' => 42
            ],
            iterator_to_array($dba)
        );
    }

    public function testCreateAndReadConstantDatabase() {
        $fileName = tempnam(sys_get_temp_dir(), 'temp_test_cdb');

        $dba = new Dba(
            $fileName, Dba::MODE_CREATE | Dba::MODE_TRUNCATE, [
                'driverName' => 'cdb_make'
            ]
        );

        $dba->addValue('foo', 23);
        $dba->addValue('bar', 42);
        $dba->addValue('foobar', 1337);

        $this->assertEquals([], iterator_to_array($dba));
        unset($dba);

        $dba = new Dba(
            $fileName, Dba::MODE_READ, [
                'driverName' => 'cdb'
            ]
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
     * @expectedException RuntimeException
     */
    public function testAddValue() {
        $dba = new Dba(
            $this->getTempFileName(), Dba::MODE_WRITE, [
                'driverName' => 'cdb'
            ]
        );

        $dba->addValue('foo', 23);
        $dba->addValue('bar', 42);
        $dba->addValue('foobar', 1337);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidEngine() {
        $dba = new Dba(
            $this->getTempFileName(), Dba::MODE_WRITE, [
                'driverName' => 'invalid'
            ]
        );
    }

}
