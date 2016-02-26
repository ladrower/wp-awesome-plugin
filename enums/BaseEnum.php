<?php namespace WPAwesomePlugin;

abstract class BaseEnum {

    protected static $constants = null;
    static function getConstants () {
        if (is_null(static::$constants)) {
            $thisEnum = new \ReflectionClass(get_called_class());
            static::$constants = $thisEnum->getConstants();
        }
        return static::$constants;
    }

}
