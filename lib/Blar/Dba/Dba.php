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

    const MODE_READ = 1;

    const MODE_WRITE = 2;

    const MODE_CREATE = 4;

    const MODE_TRUNCATE = 8;

    const MODE_LOCK_DATABASE = 16;

    const MODE_LOCK_LOCKFILE = 32;

    const MODE_LOCK_IGNORE = 64;

    const MODE_TEST = 128;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @param string $fileName
     * @param int $mode
     * @param array $options
     */
    public function __construct($fileName, $mode = self::MODE_READ, $options = []) {
        $this->open($fileName, $mode, $options);
    }

    /**
     * @param string $fileName
     * @param int $mode
     * @param array $options
     *
     * @throws RuntimeException
     */
    public function open($fileName, $mode, $options = []) {
        if(!array_key_exists('persistent', $options)) {
            $options['persistent'] = FALSE;
        }
        if($options['persistent']) {
            $handle = dba_popen($fileName, $this->getMode($mode), $options['driverName']);
        }
        else {
            $handle = dba_open($fileName, $this->getMode($mode), $options['driverName']);
        }
        if(!$handle) {
            $message = sprintf('Cannot open Database "%s" with mode "%s"', $fileName, $this->getMode($mode));
            throw new RuntimeException($message);
        }
        $this->setHandle($handle);
        return $this;
    }

    /**
     * @param int $mode
     *
     * @return string
     */
    public function getMode($mode) {
        $result = '';
        $result .= $this->getFileMode($mode);
        $result .= $this->getLockMode($mode);
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
    public function getFileMode($mode) {
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
    }

    /**
     * @param int $mode
     *
     * @return string
     */
    public function getLockMode($mode) {
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
     * @param string $driverName
     *
     * @return bool
     */
    public static function hasDriver($driverName) {
        return in_array($driverName, self::getDrivers());
    }

    /**
     * @return array
     */
    public static function getDrivers() {
        return dba_handlers();
    }

    /**
     * @return array
     */
    public static function getOpenFiles() {
        return dba_list();
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
     *
     * @return $this
     */
    public function setHandle($handle) {
        $this->handle = $handle;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function insert($key, $value) {
        $key = $this->addNamespaceToKey($key);
        $result = dba_insert($key, $value, $this->getHandle());
        if(!$result) {
            throw new RuntimeException('Insert failed');
        }
        return $this;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function addNamespaceToKey($key) {
        $namespace = $this->getNamespace();
        if($namespace) {
            $key = [$namespace, $key];
        }
        return $key;
    }

    /**
     * @return string
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function remove($key) {
        $key = $this->addNamespaceToKey($key);
        $result = dba_delete($key, $this->getHandle());
        if(!$result) {
            throw new RuntimeException('Remove failed');
        }
        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists($key) {
        $key = $this->addNamespaceToKey($key);
        return dba_exists($key, $this->getHandle());
    }

    /**
     * @param string $key
     * @param int $skip
     *
     * @return string
     */
    public function fetch($key, $skip = 0) {
        $key = $this->addNamespaceToKey($key);
        $value = dba_fetch($key, $skip, $this->getHandle());
        if($value === FALSE) {
            return NULL;
        }
        return $value;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function replace($key, $value) {
        $key = $this->addNamespaceToKey($key);
        $result = dba_replace($key, $value, $this->getHandle());
        if(!$result) {
            throw new RuntimeException('Replace failed');
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function sync() {
        $result = dba_sync($this->getHandle());
        if(!$result) {
            throw new RuntimeException('Sync failed');
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function optimize() {
        $result = dba_optimize($this->getHandle());
        if(!$result) {
            throw new RuntimeException('Optimize failed');
        }
        return $this;
    }

    /**
     * @return DbaIterator
     */
    public function getIterator() {
        return new DbaIterator($this);
    }

}
