<?php namespace WPAwesomePlugin;

interface IValidatable {
    /**
     * @return boolean
     */
    function isValid ();

    /**
     * @param $name
     * @param $value
     * @param array $data
     * @return boolean
     */
    function isFieldValid ($name, $value, &$data = array());

    /**
     * @param $data
     * @return array
     */
    function validate ($data);

    /**
     * @param $name
     * @param $value
     * @param array $data
     * @return array
     */
    function validateField ($name, $value, &$data = array());
}
