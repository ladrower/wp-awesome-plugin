<?php namespace WPAwesomePlugin;

class OutputHelper {
    static function outputValue (&$data, $key) {
        echo isset($data[$key]) ? static::toSafeHtml($data[$key]) : '';
    }

    static function toSafeHtml ($value) {
        return esc_attr($value);
    }

    static function generateNonceField ($entityName) {
        wp_nonce_field( $entityName . '_form_submit', $entityName . '_form_nonce' );
    }

    static function getDefaultAvatarSrc ()
    {
        return get_template_directory_uri() . '/img/empty-avatar.png';
    }

    static function getHost ($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    static function tagUrls ($text)
    {
        return mb_substr(preg_replace('@([^(:=\')(:=")])(https?://([-\w\.]+)+(:\d+)?(/([-\w/_\.#]*(\?\S+)?)?)?)@', '$1<a href="$2">$2</a>', ' ' . $text), 1);
    }

}
