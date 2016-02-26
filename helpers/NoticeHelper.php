<?php namespace WPAwesomePlugin;

require_once( ABSPATH . '/wp-admin/includes/template.php' );

class NoticeHelper {

    static function addSuccessNotice ($namespace, $code, $message, $persist = true)
    {
        add_settings_error(__NAMESPACE__ . $namespace, $code, $message, 'updated' );
        $persist && set_transient(__CLASS__, get_settings_errors(__NAMESPACE__ . $namespace), 0);
    }

    static function addErrorNotice ($namespace, $code, $message, $persist = true)
    {

        add_settings_error(__NAMESPACE__ . $namespace, $code, $message );
        $persist && set_transient(__CLASS__, get_settings_errors(__NAMESPACE__ . $namespace), 0);
    }

    static function outputNotices ($namespace)
    {
        $notices = get_transient(__CLASS__);
        if ($notices) {
            global $wp_settings_errors;
            $wp_settings_errors = array_merge( (array) $wp_settings_errors, $notices );
            delete_transient(__CLASS__);
        }
        settings_errors(__NAMESPACE__ . $namespace);
    }


}
