<?php namespace WPAwesomePlugin;

abstract class ValidationEnum extends BaseEnum {
    const REQUIRED = 0;
    const MINLENGTH = 1;
    const MAXLENGTH = 2;
    const REGEXP = 3;
    const EMAIL = 4;
    const PHONE = 5;
    const EMAIL_UNIQUE = 6;
    const MATCH_FIELD = 7;
    const USERNAME_UNIQUE = 8;
    const INT_NULLABLE = 9;
    const STRING_NULLABLE = 10;
    const IMMUTABLE = 11;
    const PATTERN = 12;
    const PATTERN_OR_EMPTY = 13;
    const USERNAME = 14;
    const NOT_EMPTY_NULLABLE = 15;
}
