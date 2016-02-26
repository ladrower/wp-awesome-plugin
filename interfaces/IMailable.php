<?php namespace WPAwesomePlugin;

interface IMailable {
    public function setRecipient ($to);
    public function getRecipient ();

    public function setSubject ($subject);
    public function getSubject ();

    public function setMessage ($message);
    public function getMessage ();

    public function setHeaders ($headers);
    public function getHeaders ();

    public function setAttachments ($attachments);
    public function getAttachments ();

    /**
     * @return bool
     */
    public function send();
}
