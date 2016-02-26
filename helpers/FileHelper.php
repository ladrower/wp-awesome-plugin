<?php namespace WPAwesomePlugin;

require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );

define('WP_AWESOME_PLUGIN_UPLOADS_DIR', WP_PLUGIN_DIR . '/WPAwesomePlugin/uploads/');

class FileHelper {

    protected static function checkEntityType ($type) {
        if (!in_array($type, EntityTypeEnum::getConstants(), true)) {
            throw new \Exception("Unknown entity type");
        }
    }

    static function getEntityFolder ($type, $id) {
        static::checkEntityType($type);
        $paths = str_split(substr(md5($id), 0, 2));
        return WP_AWESOME_PLUGIN_UPLOADS_DIR . DIRECTORY_SEPARATOR .
            strtolower($type) . DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, $paths) . DIRECTORY_SEPARATOR .
            $id . DIRECTORY_SEPARATOR;
    }

    /**
     * @param $fileField
     * @return int
     * @throws \Exception
     */
    static function uploadFile ($fileField)
    {
        $attachment_id = media_handle_upload( $fileField, 0 );

        if ( is_wp_error( $attachment_id ) ) {
            throw new \Exception($attachment_id->get_error_message());
        }

        return $attachment_id;
    }

    static function uploadFiles ($fileFields)
    {
        $uploadedFiles = array();

        foreach ($fileFields as $fileField) {
            try {
                array_push($uploadedFiles, array(
                    'field' => $fileField,
                    'attachment_id' => static::uploadFile($fileField)
                ));
            } catch (\Exception $e) {}
        }

        return $uploadedFiles;
    }

}
