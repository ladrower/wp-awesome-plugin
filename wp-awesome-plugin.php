<?php namespace WPAwesomePlugin;

/*
Plugin Name: WPAwesomePlugin
Version: 1.0
*/

define('WP_AWESOME_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

spl_autoload_register(WPAwesomePlugin::CLASS_NAME . '::autoloadHelpers');
spl_autoload_register(WPAwesomePlugin::CLASS_NAME . '::autoloadEntities');
spl_autoload_register(WPAwesomePlugin::CLASS_NAME . '::autoloadEnums');
spl_autoload_register(WPAwesomePlugin::CLASS_NAME . '::autoloadInterfaces');

class WPAwesomePlugin {
    const CLASS_NAME = __CLASS__;

    protected static $initialized = false;

    public static function init ()
    {
        if (static::$initialized) {
            return;
        }
        static::$initialized = true;
        MailHelper::init();
    }

    public static function autoload ($folder, $class_name)
    {
        if ( class_exists( $class_name, false ) )
            return;

        // for PHP versions < 5.3
        $dir = dirname( __FILE__ );

        $short_name = substr($class_name, strrpos($class_name, '\\') + 1);

        $file = "$dir/$folder/$short_name.php";

        if ( file_exists( $file ) )
            @include( $file );
    }

    public static function autoloadHelpers ($class_name)
    {
        static::autoload('helpers', $class_name);
    }

    public static function autoloadEntities ($class_name)
    {
        static::autoload('entities', $class_name);
    }

    public static function autoloadEnums ($class_name)
    {
        static::autoload('enums', $class_name);
    }

    public static function autoloadInterfaces ($class_name)
    {
        static::autoload('interfaces', $class_name);
    }
}

add_action( 'wp_loaded', array(WPAwesomePlugin::CLASS_NAME, 'init'));
