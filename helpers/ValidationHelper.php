<?php namespace WPAwesomePlugin;

class ValidationHelper {

    static function hasErrors (&$errors, $field)
    {
        return isset($errors[$field]) && $errors[$field];
    }

    static function getFirstErrorMessage (&$errors, $field)
    {
        return static::hasErrors($errors, $field) ? $errors[$field][0]['message'] : '';
    }

    static function toErrorsString (&$errors, $field)
    {
        return static::hasErrors($errors, $field) ? implode(', ', array_map(function ($val) {
            return $val['message'];
        }, $errors[$field])) : '';
    }

    static function validateCanDeleteAccount ($userId)
    {
        $errors = array();
        if (is_super_admin($userId)) {
            array_push($errors, array(
                'code'    => 'delete_account',
                'message' => __("Administrators can delete their accounts only via dashboard")
            ));
        }
        return $errors;
    }

    static function validateAnyoneCanRegister ($redirectTo = false)
    {
        $can = get_option('users_can_register');
        if (!$can && is_string($redirectTo)) {
            wp_redirect(site_url($redirectTo));exit;
        }
        return $can;
    }

    static function validateFormNonce ($formData, $entityName, $die = true)
    {
        $nonceField = $entityName . '_form_nonce';
        if (
            ! isset( $formData[$nonceField] )
            || ! wp_verify_nonce( $formData[$nonceField], $entityName . '_form_submit' )
        ) {
            if ($die) {
                print 'Sorry, your nonce did not verify.';
                exit;
            }
            return false;
        }
        return true;
    }

    static function checkLoggedInOrRedirect ($redirectTo = null, $loggedOutPage = 'login')
    {
        if (!is_user_logged_in()) {
            $loggedOutUrl = site_url($loggedOutPage);
            $redirectTo = is_string($redirectTo) ? $redirectTo : remove_query_arg('loggedout', $_SERVER['REQUEST_URI']);
            $redirectTo = urlencode(site_url($redirectTo));
            $redirectTo = add_query_arg( 'redirect_to', $redirectTo, $loggedOutUrl);
            wp_redirect(/*$redirectTo*/ $loggedOutUrl);
            exit;
        }
    }

    static function checkSafeDomain ($val)
    {
        $domain = OutputHelper::getHost($val);
        return $domain && $domain === OutputHelper::getHost(site_url());
    }

    static function assertDataValue (&$data, $key, $val)
    {
        return isset($data[$key]) && $data[$key] === $val;
    }

    static function validateEmail ($val)
    {
        return is_email($val);
    }

    static function validateRequired ($val)
    {
        return !empty($val);
    }

    static function validateEmailUnique ($val, $userId)
    {
        $id = email_exists($val);
        return $id === false || $id === $userId;
    }

    static function validateUsernameUnique ($val, $userId)
    {
        $id = username_exists($val);
        return $id === null || $id === $userId;
    }

    static function validateUsername ($val)
    {
        return validate_username($val);
    }

    static function validateMinlength ($val, $min)
    {
        return strlen($val) >= $min;
    }

    static function validateMaxlength ($val, $max)
    {
        return strlen($val) <= $max;
    }

    static function validateEquals ($val1, $val2, $strict = true)
    {
        return $strict ? $val1 === $val2 : $val1 == $val2;
    }

    static function validateIntNullable ($val)
    {
        return is_null($val) ? true : is_int($val);
    }

    static function validateStringNullable ($val)
    {
        return is_null($val) ? true : is_string($val);
    }

    static function validateImmutable ($val, $previousVal)
    {
        return is_null($previousVal) || static::validateEquals($val, $previousVal);
    }

    static function validatePatternOrEmpty ($val, $pattern)
    {
        return empty($val) || static::validatePattern($val, $pattern);
    }

    static function validatePattern ($val, $pattern)
    {
        return preg_match($pattern, $val) === 1;
    }

    static function validateNotEmptyNullable ($val)
    {
        return is_null($val) || !empty($val);
    }


}
