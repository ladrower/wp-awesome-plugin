<?php namespace WPAwesomePlugin;
require_once('EntityAbstract.php');

/**
 * Class Profile
 * @package WPAwesomePlugin
 */
class Profile extends EntityAbstract implements IPersistable {

    const TYPE = EntityTypeEnum::PROFILE;

    const VALIDATION_SKIPPED_IF_UPDATE_AND_EMPTY = 'skip_validation_if_not_updated';

    protected static $fields = array(
        'userid'        => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::INT_NULLABLE => true,
                ValidationEnum::IMMUTABLE => true
            )
        ),
        'firstname'     => array(),
        'lastname'      => array(),
        'username'      => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true,
                ValidationEnum::MINLENGTH => 2,
                ValidationEnum::MAXLENGTH => 20,
                ValidationEnum::USERNAME => true,
                ValidationEnum::USERNAME_UNIQUE => 'userid'
            ),
            self::VALIDATION_MESSAGES_PROP => array(
                ValidationEnum::MINLENGTH => 'Username should be at least 2 characters',
                ValidationEnum::MAXLENGTH => 'Username should be 2 to 20 characters'
            )
        ),
        'email'         => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true,
                ValidationEnum::EMAIL => true,
                ValidationEnum::EMAIL_UNIQUE => 'userid'
            )
        ),
        'password'      => array(
            self::VALIDATION_SKIPPED_IF_UPDATE_AND_EMPTY => true,
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true,
                ValidationEnum::MINLENGTH => 8,
                ValidationEnum::MAXLENGTH => 12,
                ValidationEnum::PATTERN => '/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/',
                ValidationEnum::MATCH_FIELD => 'password2'
            ),
            self::VALIDATION_MESSAGES_PROP => array(
                ValidationEnum::MINLENGTH => 'Password should be 8 to 12 characters',
                ValidationEnum::MAXLENGTH => 'Password should be 8 to 12 characters',
                ValidationEnum::MATCH_FIELD => 'Passwords do not match',
                ValidationEnum::PATTERN => 'Password should contain at least one numeric, one upper and one lower case character'
            )
        ),
        'password2'     => array(
            self::VALIDATION_SKIPPED_IF_UPDATE_AND_EMPTY=> true,
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true
            )
        ),
        'address'       => array(
            self::METAFIELD_PROP => array(
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'city'       => array(
            self::METAFIELD_PROP => array(
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'country'     => array(
            self::METAFIELD_PROP => array(
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'postcode'      => array(
            self::METAFIELD_PROP => array(
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'mobile'        => array(
            self::METAFIELD_PROP => array(
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'avatarimagefile'    => array(
            self::METAFIELD_PROP => array(
                'field' => 'avatar',
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'coverimagefile'    => array(
            self::METAFIELD_PROP => array(
                'field' => 'cover',
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'birth'         => array(
            self::METAFIELD_PROP => array(
                'field' => 'date_of_birth',
                'prefix' => self::METABOX_PREFIX
            )
        ),
        'gender'        => array(
            self::METAFIELD_PROP => array(
                'prefix' => self::METABOX_PREFIX
            )
        )
    );

    public static $optionsMap = array(
        "gender" => array(
            "m" => "Male",
            "f" => "Female"
        ),
        "country" => array(
            "USA" => "United States",
            "AUSTRALIA" => "Australia",
            "CANADA" => "Canada",
            "UNITED KINGDOM" => "United Kingdom",
            "EUROPE" => "Europe",
            "JAPAN" => "Japan",
            "KOREA" => "Korea",
            "CHINA" => "China",
            "HONG KONG" => "Hong Kong",
            "INDIA" => "India",
            "NEW ZEALAND" => "New Zealand",
            "SOUTH EAST ASIA" => "South East Asia",
            "OTHER" => "Other"
        )
    );

    public function __set($name, $value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        parent::__set($name, $value);
    }

    public function getOptionValue ($option)
    {
        return isset(static::$optionsMap[$option][$this->$option])
            ? static::$optionsMap[$option][$this->$option]
            : '';
    }

    public function getId ($exceptionIfNotDefined = false)
    {
        if ($exceptionIfNotDefined && !$this->hasId()) {
            throw new \Exception('Primary key is not set');
        }
        return $this->userid;
    }

    public function hasId ()
    {
        return $this->getId() > 0;
    }

    public function load ()
    {
        $userId = $this->getId(true);
        $userData = get_userdata($userId);
        if (!$userData) {
            return $this;
        }
        $this->setUserdata($userData);

        foreach (static::$fields as $key => $params) {
            if ($this->isMetaField($key)) {
                $this->$key = get_user_meta( $userId, $this->mapMetaFieldKey($key), 1 );
            }
        }

        return $this;
    }

    public function loadMetaField ($key)
    {
        if ($this->isMetaField($key)) {
            $this->$key = get_user_meta( $this->getId(true), $this->mapMetaFieldKey($key), 1 );
        }
        return $this;
    }

    public function saveMetaField ($key)
    {
        if ($this->isMetaField($key)) {
            update_user_meta($this->getId(true), $this->mapMetaFieldKey($key), $this->$key);
        }
        return $this;
    }

    public function setId ($userId)
    {
        $this->userid = $userId;
    }

    /**
     * @param bool $notify
     * @return int
     * @throws \Exception
     */
    public function create ($notify = true)
    {
        $result = wp_insert_user(array(
            'user_login'  =>  $this->username,
            'user_pass'   =>  $this->password,
            'user_email'  =>  $this->email,
            'first_name'  =>  $this->firstname,
            'last_name'   =>  $this->lastname
        ));

        if ( is_wp_error( $result ) ) {
            throw new \Exception($result->get_error_message());
        } else {
            $this->setId($result);
            $this->updateMeta();
        }

        $notify && do_action( __NAMESPACE__ . '_profile_created', $this );

        return $result;
    }

    /**
     * @param bool $notify
     * @return int
     * @throws \Exception
     */
    public function update ($notify = true)
    {
        $result = wp_update_user(array(
            'ID'          =>  $this->getId(),
            'user_email'  =>  $this->email,
            'first_name'  =>  $this->firstname,
            'last_name'   =>  $this->lastname
        ));

        if ( is_wp_error( $result ) ) {
            throw new \Exception($result->get_error_message());
        } else {
            $this->updateMeta();
            $this->updateUserLogin();
            $this->updatePassword();
        }

        $notify && do_action( __NAMESPACE__ . '_profile_updated', $this );

        return $result;
    }

    public function saveFiles ()
    {
        foreach (FileHelper::uploadFiles(array('avatarimagefile', 'coverimagefile')) as $data) {
            update_user_meta($this->getId(), $this->mapMetaFieldKey($data['field']), $data['attachment_id']);
        }
    }

    public function updateMetaSet ($metaKey, $metaValue, $uniqueness = SORT_NUMERIC)
    {
        $this->loadMetaSet($metaKey);
        array_unshift($this->fieldValues[$metaKey], $metaValue);
        $this->$metaKey = array_slice(array_unique($this->$metaKey, $uniqueness), 0, 10);
        $this->saveMetaField($metaKey);
    }

    public function loadMetaSet ($metaKey)
    {
        $this->loadMetaField($metaKey);
        if (!is_array($this->$metaKey)) {
            $this->$metaKey = array();
        }
        return $this->$metaKey;
    }

    protected function updateUserLogin ()
    {
        global $wpdb;
        $currentLogin = wp_get_current_user()->user_login;
        if ($this->username !== $currentLogin && $this->isFieldValid('username', $this->username, $this->toArray())) {
            wp_cache_delete($currentLogin, 'userlogins');
            $wpdb->update($wpdb->users, array('user_login' => $this->username), array('ID' => $this->getId(true)));
        }
    }

    protected function updatePassword ()
    {
        if (!empty($this->fieldValues['password']) && $this->isFieldValid('password', $this->password, $this->toArray())) {
            wp_set_password( $this->password, $this->getId(true) );
            wp_signon(array('user_login' => $this->username, 'user_password' => $this->password));
        }
    }

    protected function setUserdata (\WP_User $user)
    {
        $this->userid          = $user->ID;
        $this->username        = $user->user_login;
        $this->email           = $user->user_email;
        $this->firstname       = $user->first_name;
        $this->lastname        = $user->last_name;
    }

    protected function updateMeta ()
    {
        $id = $this->getId(true);
        foreach ($this->toMeta() as $key => $value) {
            update_user_meta($id, $key, $value);
        }
    }

    protected function isFieldValidationSkipped (&$fieldConf, $value) {
        return array_key_exists(static::VALIDATION_SKIPPED_IF_UPDATE_AND_EMPTY, $fieldConf)
        && $this->hasId()
        && empty($value);
    }
}
