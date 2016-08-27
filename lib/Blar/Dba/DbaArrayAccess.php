<?php
/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Dba;

/**
 * Class DbaArrayAccess
 *
 * @package Blar\Dba
 */
trait DbaArrayAccess {

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return $this->exists($offset);
    }

    /**
     * @param string $offset
     * @return string
     */
    public function offsetGet($offset) {
        return $this->getValue($offset);
    }

    /**
     * @param string $offset
     * @param string $value
     */
    public function offsetSet($offset, $value) {
        $this->setValue($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset) {
        $this->removeValues($offset);
    }

}
