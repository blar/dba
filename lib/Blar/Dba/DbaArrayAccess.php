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
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return $this->exists($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->fetch($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->replace($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        $this->remove($offset);
    }

}
