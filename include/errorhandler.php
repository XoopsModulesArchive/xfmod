<?php

class XoopsForgeErrorHandler
{
    public $messages;

    public $errors;

    public $system_error;

    public function __construct()
    {
        $messages = [];

        $errors = [];

        $system_error = '';
    }

    public function addMessage($info)
    {
        $this->messages[] = $info;
    }

    public function addError($info)
    {
        $this->errors[] = $info;
    }

    public function setSystemError($info)
    {
        if (!headers_sent()) {
            require XOOPS_ROOT_PATH . '/header.php';

            //OpenTable();
        }

        $content = "<p style='font-weight:bold; color:#FF0000; font-size:16pt'>" . 'System Error: ' . $info . '</p>';

        if (count($this->getFeedback()) > 0) {
            $content .= 'The following errors were found preceeding the system error and may have led to the problem:<BR>';

            $this->displayFeedback();
        }

        $content .= "<p>[ <a href='javascript:history.go(-1)'>Go Back</a> ]</p>";

        //CloseTable();

        require XOOPS_ROOT_PATH . '/footer.php';

        exit();
    }

    public function getMessage()
    {
        $feedback = $this->messages;

        $this->messages = [];

        return $feedback;
    }

    public function getError()
    {
        $feedback = $this->errors;

        $this->errors = [];

        return $feedback;
    }

    public function getFeedback()
    {
        $feedback = array_merge($this->messages, $this->errors);

        $this->messages = [];

        $this->errors = [];

        return $feedback;
    }

    public function getDisplayFeedback()
    {
        $content = '';

        if (count($this->messages) > 0) {
            $content .= "<div style='font-weight:bold;color:#0000DD'>" . implode('<br>', $this->messages) . '</div>';

            $this->messages = [];
        }

        if (count($this->errors) > 0) {
            $content .= "<div style='font-weight:bold;color:#FF0000'>" . implode('<br>', $this->errors) . '</div>';

            $this->errors = [];
        }

        return $content;
    }

    public function displayFeedback()
    {
        echo $this->getDisplayFeedback();
    }
}

$xoopsForgeErrorHandler = new XoopsForgeErrorHandler();
