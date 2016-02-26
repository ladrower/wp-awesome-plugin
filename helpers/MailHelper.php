<?php namespace WPAwesomePlugin;


class MailHelper implements IMailable {

    protected $recipient;
    protected $subject;
    protected $message;
    protected $headers;
    protected static $initialized = false;
    const CLASS_NAME = __CLASS__;

    private function __construct ()
    {
        $this->headers  = 'MIME-Version: 1.0' . "\r\n";
        $this->headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    }

    public function setRecipient($to)
    {
        $this->recipient = $to;
        return $this;
    }

    public function getRecipient()
    {
        return $this->recipient;
    }

    public static function init ()
    {
        if (static::$initialized) {
            return;
        }
        static::$initialized = true;
        static::addMessageFilter();
    }

    protected static function addMessageFilter ()
    {
        if ( ! has_filter( 'wp_mail', array(__CLASS__, 'messageFilter') ) ) {
            add_filter( 'wp_mail', array(__CLASS__, 'messageFilter') );
        }
    }

    protected static function removeMessageFilter ()
    {
        remove_filter( 'wp_mail', array(__CLASS__, 'messageFilter') );
    }

    public static function messageFilter ($atts)
    {
        if ( isset( $atts['message'] ) ) {
            $atts['message'] = static::getEmailBody(nl2br(OutputHelper::tagUrls($atts['message'])));
        }
        return $atts;
    }

    public static function sendUserNotification ($userId, $subject, $message, $templateVersion = 1)
    {
        $user = new \WP_User($userId);
        return static::sendNotification($user->user_email, $subject, $message, $templateVersion);
    }

    public static function sendAdminNotification ($subject, $message, $templateVersion = 1)
    {
        return static::sendNotification(get_option('admin_email'), $subject, $message, $templateVersion);
    }

    protected static function sendNotification ($recipient, $subject, $message, $templateVersion)
    {
        $mail = new self;
        $mail->setRecipient($recipient);
        $mail->setSubject($subject);
        $mail->setMessage(static::getEmailBody($message, $templateVersion));

        return $mail->send();
    }


    public static function getEmailBody ($message, $version = 1)
    {
        ob_start();

        include static::getHeaderTemplatePath($version);

        echo $message;

        include static::getFooterTemplatePath($version);

        $body = ob_get_contents();

        ob_end_clean();

        return $body;
    }

    protected static function getHeaderTemplatePath ($version)
    {
        return static::getTemplatePath('header', $version);
    }

    protected static function getFooterTemplatePath ($version)
    {
        return static::getTemplatePath('footer', $version);
    }

    public static function getTemplatePath ($part, $version)
    {
        $themeFile = get_template_directory() . "/parts/email-{$part}-v{$version}.php";
        if (file_exists($themeFile)) {
            return $themeFile;
        }
        return WP_AWESOME_PLUGIN_PATH .  "templates/email-{$part}-v1.php";
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setAttachments($attachments)
    {
        // TODO: Implement setAttachments() method.
    }

    public function getAttachments()
    {
        // TODO: Implement getAttachments() method.
    }

    /**
     * @return bool
     */
    public function send()
    {
        $result = false;
        try {
            static::removeMessageFilter();
            $result = @wp_mail($this->getRecipient(), $this->getSubject(), $this->getMessage(), $this->getHeaders());
            static::addMessageFilter();
        } catch (\Exception $e) {};
        return $result;
    }
}
