<?php namespace WPAwesomePlugin;

/**
 * Interface IPersistable
 * @package WPAwesomePlugin
 */
interface IPersistable {
    function setId ($id);
    function getId ($exceptionIfNotDefined = false);
    function hasId ();
    function load ();
    function create ($notify);
    function update ($notify);
}
