<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

use Iterator;

/**
 * Class DbaIterator
 *
 * @package Blar\Dba
 */
class DbaIterator implements Iterator {

    /**
     * @var Dba
     */
    private $dba;

    /**
     * @var string
     */
    private $key;

    /**
     * @param Dba $dba
     */
    public function __construct(Dba $dba) {
        $this->setDba($dba);
    }

    /**
     * @return Dba
     */
    public function getDba(): Dba {
        return $this->dba;
    }

    /**
     * @param Dba $dba
     */
    public function setDba(Dba $dba) {
        $this->dba = $dba;
    }

    /**
     * @return resource
     */
    public function getHandle() {
        return $this->getDba()->getHandle();
    }

    /**
     * @return string
     */
    public function current(): string {
        return $this->getDba()->getValue($this->key());
    }

    /**
     * @return string
     */
    public function key(): string {
        return $this->key;
    }

    public function next() {
        $this->key = dba_nextkey($this->getHandle());
    }

    public function rewind() {
        $this->key = dba_firstkey($this->getHandle());
    }

    /**
     * @return bool
     */
    public function valid(): bool {
        return $this->key !== false;
    }

}
