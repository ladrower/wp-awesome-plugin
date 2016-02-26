<?php namespace WPAwesomePlugin;

abstract class EntityAbstract implements IValidatable {

    const TYPE = null;

    const VALIDATION_PROP = 'validation';
    const VALIDATION_MESSAGES_PROP = 'validation_messages';
    const METAFIELD_PROP = 'metafield';

    const METABOX_PREFIX = '_pref_';

    protected $errors = array();

    protected $fieldValues;

    protected static $fields = array();

    /**
     * @param array $data Fields data
     */
    public function __construct($data = array())
    {
        $this->fieldValues = array();
        foreach (array_keys(static::$fields) as $key) {
            $this->fieldValues[$key] = null;
        }
        $this->populate($data);
    }

    public function __get($name)
    {
        if ($this->isFieldExist($name)) {
            return $this->fieldValues[$name];
        } else {
            throw new \Exception("Access to nonexistent field");
        }
    }

    public function __set($name, $value)
    {
        if ($this->isFieldExist($name)) {
            $this->isFieldValid($name, $value, $this->fieldValues) && ($this->fieldValues[$name] = $value);
        } else {
            throw new \Exception("Access to nonexistent field");
        }
    }

    public function populate ($data)
    {
        $errors = array();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->fieldValues)
                && count($errors[$key] = $this->validateField($key, $value, $data)) === 0) {
                $this->fieldValues[$key] = $value;
            }
        }
        $this->errors = array_filter($errors);
        return $this;
    }

    public function getValidationMessage($field, $code)
    {
        return isset(static::$fields[$field])
        && isset(static::$fields[$field][self::VALIDATION_MESSAGES_PROP])
        && isset(static::$fields[$field][self::VALIDATION_MESSAGES_PROP][$code])
            ? __(static::$fields[$field][self::VALIDATION_MESSAGES_PROP][$code])
            : null;
    }

    public function validateField ($name, $value, &$data = array())
    {
        $errors = array();
        if ($this->isFieldExist($name) && !$this->isFieldValidationSkipped(static::$fields[$name], $value)
            && is_array(static::$fields[$name])
            && isset(static::$fields[$name][self::VALIDATION_PROP])) {
            foreach (static::$fields[$name][self::VALIDATION_PROP] as $validationKey => $validationParam) {
                if ($validationParam === false) {
                    continue;
                }
                switch ($validationKey) {
                    case ValidationEnum::REQUIRED:
                        !ValidationHelper::validateRequired($value)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Value is required')
                        )));
                        break;
                    case ValidationEnum::EMAIL:
                        !ValidationHelper::validateEmail($value)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Invalid Email')
                        )));
                        break;
                    case ValidationEnum::EMAIL_UNIQUE:
                        !ValidationHelper::validateEmailUnique($value, $this->$validationParam)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Email already registered')
                        )));
                        break;
                    case ValidationEnum::MINLENGTH:
                        !ValidationHelper::validateMinlength($value, $validationParam)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Value is too short')
                        )));
                        break;
                    case ValidationEnum::MAXLENGTH:
                        !ValidationHelper::validateMaxlength($value, $validationParam)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Value is too long')
                        )));
                        break;
                    case ValidationEnum::MATCH_FIELD:
                        !ValidationHelper::validateEquals($value, $data[$validationParam])
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __("{$name} does not match {$validationParam}")
                        )));
                        break;
                    case ValidationEnum::USERNAME_UNIQUE:
                        !ValidationHelper::validateUsernameUnique($value, $this->$validationParam)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Username already registered')
                        )));
                        break;
                    case ValidationEnum::INT_NULLABLE:
                        !ValidationHelper::validateIntNullable($value)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Field should be numeric')
                        )));
                        break;
                    case ValidationEnum::STRING_NULLABLE:
                        !ValidationHelper::validateStringNullable($value)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Field should be a string')
                        )));
                        break;
                    case ValidationEnum::IMMUTABLE:
                        !ValidationHelper::validateImmutable($value, $this->fieldValues[$name])
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Field cannot be changed after first set')
                        )));
                        break;
                    case ValidationEnum::PATTERN_OR_EMPTY:
                        !ValidationHelper::validatePatternOrEmpty($value, $validationParam)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Field should match a pattern')
                        )));
                        break;
                    case ValidationEnum::PATTERN:
                        !ValidationHelper::validatePattern($value, $validationParam)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Field should match a pattern')
                        )));
                        break;
                    case ValidationEnum::USERNAME:
                        !ValidationHelper::validateUsername($value)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Username is not valid')
                        )));
                        break;
                    case ValidationEnum::NOT_EMPTY_NULLABLE:
                        !ValidationHelper::validateNotEmptyNullable($value)
                        && (array_push($errors, array(
                            'code'    => $validationKey,
                            'message' => ($message = $this->getValidationMessage($name, $validationKey)) ? $message :
                                __('Field should be null or not empty')
                        )));
                        break;
                }
            }
        }

        return $errors;
    }

    public function validate ($data)
    {
        $errors = array();
        foreach ($data as $key => $value) {
            $errors[$key] = $this->validateField($key, $value, $data);
        }
        return array_filter($errors);
    }

    public function isValid ()
    {
        return count($this->validate($this->toArray())) === 0;
    }

    public function isFieldValid ($name, $value, &$data = array())
    {
        return count($this->validateField($name, $value, $data)) === 0;
    }

    public function getErrors ()
    {
        return $this->errors;
    }

    public function hasErrors ()
    {
        return count($this->errors) > 0;
    }

    public function toMeta ()
    {
        $fields = static::$fields;
        $metaProp = self::METAFIELD_PROP;
        $fieldsArray = $this->toArray();
        $keys = array_keys($fieldsArray);
        $metafields = array();
        $metakeys = array_filter($keys, function ($key) use (&$fields, $metaProp) {
            return $fields[$key] && isset($fields[$key][$metaProp]);
        });
        foreach ($metakeys as $metakey) {
            $metafields[$this->mapMetaFieldKey($metakey)] = $fieldsArray[$metakey];
        }
        return $metafields;
    }

    public function toArray ()
    {
        return $this->fieldValues;
    }

    public function toObject ()
    {
        return (object) $this->toArray();
    }

    protected function isMetaField ($key)
    {
        return isset(static::$fields[$key][self::METAFIELD_PROP]);
    }

    protected function mapMetaFieldKey ($key)
    {
        if (empty(static::$fields[$key][self::METAFIELD_PROP])) {
            return $key;
        }
        $prefix = isset(static::$fields[$key][self::METAFIELD_PROP]['prefix'])
            ? static::$fields[$key][self::METAFIELD_PROP]['prefix']
            : '';
        $field = isset(static::$fields[$key][self::METAFIELD_PROP]['field'])
            ? static::$fields[$key][self::METAFIELD_PROP]['field']
            : $key;
        return $prefix . $field;
    }

    protected function isFieldExist ($key) {
        return array_key_exists($key, static::$fields);
    }

    protected abstract function isFieldValidationSkipped (&$fieldConf, $value);
}
