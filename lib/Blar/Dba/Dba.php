<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use ArrayAccess;
use IteratorAggregate;
use RuntimeException;

/**
 * Class Dba
 *
 * @link http://php.net/dba
 * @package Blar\Dba
 */
class Dba implements IteratorAggregate, ArrayAccess {

    use DbaArrayAccess;

    /**
     * Open database with read access.
     */
    const MODE_READ = 1;

    /**
     * Open database with write (and read) access.
     */
    const MODE_WRITE = 2;

    /**
     * Create database with read and write access.
     */
    const MODE_CREATE = 4;

    const MODE_LOCK_DATABASE = 16;

    const MODE_LOCK_LOCKFILE = 32;

    const MODE_LOCK_IGNORE = 64;

    /**
     * Test database (dont wait for the lock).
     */
    const MODE_TEST = 128;

    /**
     * Truncate an existing database with read and write access.
     */
    const MODE_TRUNCATE = 8;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @param string $driverName
     *
     * @return bool
     */
    public static function hasDriver(string $driverName): bool {
        return in_array($driverName, self::getDrivers());
    }

    /**
     * @return array
     */
    public static function getDrivers(): array {
        return dba_handlers();
    }

    /**
     * @return array
     */
    public static function getOpenFiles(): array {
        return dba_list();
    }

    /**
     * Convert the constants to a string.
     *
     * @param int $mode
     *
     * @return string
     */
    public static function getMode(int $mode): string {
        $result = '';
        $result .= self::getFileMode($mode);
        $result .= self::getLockMode($mode);
        if($mode & self::MODE_TEST) {
            $result .= 't';
        }
        return $result;
    }

    /**
     * @param int $mode
     *
     * @return string
     */
    public static function getFileMode(int $mode): string {
        if($mode & self::MODE_TRUNCATE) {
            return 'n';
        }
        if($mode & self::MODE_CREATE) {
            return 'c';
        }
        if($mode & self::MODE_WRITE) {
            return 'w';
        }
        if($mode & self::MODE_READ) {
            return 'r';
        }
        $message = sprintf('Invalid mode "%s"', $mode);
        throw new RuntimeException($message);
    }

    /**
     * @param int $mode
     *
     * @return string
     */
    public static function getLockMode(int $mode): string {
        $result = '';
        if($mode & self::MODE_LOCK_DATABASE) {
            $result .= 'd';
        }
        if($mode & self::MODE_LOCK_LOCKFILE) {
            $result .= 'l';
        }
        if($mode & self::MODE_LOCK_IGNORE) {
            $result .= '-';
        }
        return $result;
    }

    /**
     * @param string $fileName Filename to the database file.
     * @param int $mode Constants Dba::MODE_*
     * @param array $options
     */
    public function __construct(string $fileName, int $mode = self::MODE_READ, array $options = []) {
        $this->open($fileName, $mode, $options);
    }

    /**
     * Open the database.
     *
     * @param string $fileName Filename to the database file.
     * @param int $mode Constants Dba::MODE_*
     * @param array $options
     *
     * @throws RuntimeException
     */
    public function open(string $fileName, int $mode, array $options = []) {
        if(!array_key_exists('persistent', $options)) {
            $options['persistent'] = FALSE;
        }
        if($options['persistent']) {
            $handle = @dba_popen($fileName, self::getMode($mode), $options['driverName']);
        }
        else {
            $handle = @dba_open($fileName, self::getMode($mode), $options['driverName']);
        }
        if(!$handle) {
            $message = sprintf('Cannot open Database "%s" with mode "%s"', $fileName, self::getMode($mode));
            throw new RuntimeException($message);
        }
        $this->setHandle($handle);
    }

    public function __destruct() {
        dba_close($this->getHandle());
    }

    /**
     * @return resource
     */
    public function getHandle() {
        return $this->handle;
    }

    /**
     * @param resource $handle
     */
    public function setHandle($handle) {
        if(!is_resource($handle)) {
            throw new RuntimeException('Argument is not a resource');
        }
        $this->handle = $handle;
    }

    /**
     * Add a value.
     * If the database supports multiple values for the same key
     *
     * @param string $key
     * @param string $value
     */
    public function addValue(string $key, string $value) {
        $key = $this->addNamespaceToKey($key);
        $result = dba_insert($key, $value, $this->getHandle());
        if(!$result) {
            throw new RuntimeException('Insert failed');
        }
    }

    /**
     * @return string
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace) {
        $this->namespace = $namespace;
    }

    /**
     * Is a namespace is set?
     *
     * @return bool
     */
    public function hasNamespace(): bool {
        return !empty($this->namespace);
    }

    /**
     * Remove values from the database.
     *
     * @param string $key
     */
    public function removeValues(string $key) {
        $key = $this->addNamespaceToKey($key);
        $result = dba_delete($key, $this->getHandle());
        if(!$result) {
            throw new RuntimeException('Remove failed');
        }
    }

    /**
     * Exists a key in the database?
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool {
        $key = $this->addNamespaceToKey($key);
        return dba_exists($key, $this->getHandle());
    }

    /**
     * @param string $key
     * @param int $skip
     *
     * @return string
     */
    public function getValue(string $key, int $skip = 0) {
        $key = $this->addNamespaceToKey($key);
        $value = dba_fetch($key, $skip, $this->getHandle());
        if($value === FALSE) {
            return NULL;
        }
        return $value;
    }

    /**
     * Get multiple values if the database supports it.
     *
     * @param string $key
     *
     * @return array
     */
    public function getValues(string $key): array {
        $result = [];
        for($i = 0; ; $i++) {
            $value = $this->getValue($key, $i);
            if(is_null($value)) {
                break;
            }
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Set value.
     *
     * @param string $key
     * @param string $value
     */
    public function setValue(string $key, string $value) {
        $key = $this->addNamespaceToKey($key);
        $result = dba_replace($key, $value, $this->getHandle());
        if(!$result) {
            throw new RuntimeException('setValue failed');
        }
    }

    /**
     * Set multiple values with the same key.
     *
     * @param string $key
     * @param array $values Array of string values.
     */
    public function setValues(string $key, array $values) {
        if($this->exists($key)) {
            $this->removeValues($key);
        }
        foreach($values as $value) {
            $this->addValue($key, $value);
        }
    }

    /**
     * Sync changes to filesystem.
     */
    public function sync() {
        $result = dba_sync($this->getHandle());
        if(!$result) {
            throw new RuntimeException('Sync failed');
        }
    }

    /**
     * Optimize database
     */
    public function optimize() {
        $result = dba_optimize($this->getHandle());
        if(!$result) {
            throw new RuntimeException('Optimize failed');
        }
    }

    /**
     * @return DbaIterator
     */
    public function getIterator(): DbaIterator {
        return new DbaIterator($this);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function addNamespaceToKey(string $key) {
        if($this->hasNamespace()) {
            $key = [$this->getNamespace(), $key];
        }
        return $key;
    }

}
