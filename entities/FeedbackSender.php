<?php namespace WPAwesomePlugin;


class FeedbackSender extends EntityAbstract {

    protected static $fields = array(
        'username'         => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true
            )
        ),
        'email'         => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true,
                ValidationEnum::EMAIL => true
            )
        ),
        'country'      => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true
            )
        ),
        'feedback'     => array(
            self::VALIDATION_PROP => array(
                ValidationEnum::REQUIRED => true
            ),
            self::VALIDATION_MESSAGES_PROP => array(
                ValidationEnum::REQUIRED => 'Please add your comments'
            )
        )
    );

    public function getSubject()
    {
        return 'Feedback from ' . $this->username . ' [' . $this->email . ']';
    }

    public function getMessage()
    {
        $feedbackSender = $this;

        ob_start();

        include MailHelper::getTemplatePath('feedback', 1);

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    public function send()
    {
        return MailHelper::sendAdminNotification($this->getSubject(), $this->getMessage());
    }

    protected function isFieldValidationSkipped (&$fieldConf, $value) {
        return false;
    }
}
