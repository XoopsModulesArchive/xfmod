<?php
////////////////////////////////////////////////////
// phpmailer - PHP email class
//
// Version 1.50, Created 11/08/2001
//
// Class for sending email using either
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Author: Brent R. Matzelle <bmatzelle@yahoo.com>
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * phpmailer - PHP email transport class
 * @author Brent R. Matzelle
 */
//require_once "../../../../mainfile.php";

class phpmailerxf
{
    /////////////////////////////////////////////////

    // PUBLIC VARIABLES

    /////////////////////////////////////////////////

    /**
     * Email priority (1 = High, 3 = Normal, 5 = low). Default value is 3.
     * @public
     * @type int
     */

    public $Priority = 3;

    /**
     * Sets the CharSet of the message. Default value is "iso-8859-1".
     * @public
     * @type string
     */

    public $CharSet = 'iso-8859-1';

    /**
     * Sets the Content-type of the message. Default value is "text/plain".
     * @public
     * @type string
     */

    public $ContentType = 'text/plain';

    /**
     * Sets the Encoding of the message. Options for this are "8bit" (default),
     * "7bit", "binary", "base64", and "quoted-printable".
     * @public
     * @type string
     */

    public $Encoding = '8bit';

    /**
     * Holds the most recent mailer error message. Default value is "".
     * @public
     * @type string
     */

    public $ErrorInfo = '';

    /**
     * Sets the From email address for the message. Default value is "root@localhost".
     * @public
     * @type string
     */

    public $From = 'root@localhost';

    /**
     * Sets the From name of the message. Default value is "Root User".
     * @public
     * @type string
     */

    public $FromName = 'Root User';

    /**
     * Sets the Sender email of the message. If not empty, will be sent via -f to sendmail
     * or as 'MAIL FROM' in smtp mode. Default value is "".
     * @public
     * @type string
     */

    public $Sender = '';

    /**
     * Sets the Subject of the message. Default value is "".
     * @public
     * @type string
     */

    public $Subject = '';

    /**
     * Sets the Body of the message.  This can be either an HTML or text body.
     * If HTML then run IsHTML(true). Default value is "".
     * @public
     * @type string
     */

    public $Body = '';

    /**
     * Sets the text-only body of the message.  This automatically sets the
     * email to multipart/alternative.  This body can be read by mail
     * clients that do not have HTML email capability such as mutt. Clients
     * that can read HTML will view the normal Body.
     * Default value is "".
     * @public
     * @type string
     */

    public $AltBody = '';

    /**
     * Sets word wrapping on the message. Default value is 0 (off).
     * @public
     * @type int
     */

    public $WordWrap = 0;

    /**
     * Method to send mail: ("mail", "sendmail", or "smtp").
     * Default value is "mail".
     * @public
     * @type string
     */

    public $Mailer = 'mail';

    /**
     * Sets the path of the sendmail program. Default value is
     * "/usr/sbin/sendmail".
     * @public
     * @type string
     */

    public $Sendmail = '/usr/sbin/sendmail';

    /**
     *  Turns Microsoft mail client headers on and off.  Useful mostly
     *  for older clients. Default value is false (off).
     * @public
     * @type bool
     */

    public $UseMSMailHeaders = false;

    /**
     *  Holds phpmailer version.
     * @public
     * @type string
     */

    public $Version = '1.50';

    /////////////////////////////////////////////////

    // SMTP VARIABLES

    /////////////////////////////////////////////////

    /**
     *  Sets the SMTP hosts.  All hosts must be separated by a
     *  semicolon.  You can also specify a different port
     *  for each host by using this format: [hostname:port]
     *  (e.g. "smtp1.domain.com:25;smtp2.domain.com").
     *  Hosts will be tried in order.
     *  Default value is "localhost".
     * @public
     * @type string
     */

    public $Host = 'localhost';

    /**
     *  Sets the default SMTP server port. Default value is 25.
     * @public
     * @type int
     */

    public $Port = 25;

    /**
     *  Sets the SMTP HELO of the message.
     *  Default value is "localhost.localdomain".
     * @public
     * @type string
     */

    public $Helo = 'localhost.localdomain';

    /**
     *  Sets SMTP authentication. Utilizes the Username and Password variables.
     *  Default value is false (off).
     * @public
     * @type bool
     */

    public $SMTPAuth = false;

    /**
     *  Sets SMTP username. Default value is "".
     * @public
     * @type string
     */

    public $Username = '';

    /**
     *  Sets SMTP password. Default value is "".
     * @public
     * @type string
     */

    public $Password = '';

    /**
     *  Sets the SMTP server timeout in seconds. Does not function at this time
     *  because PHP for win32 does not support it. Default value is 10.
     * @public
     * @type int
     */

    public $Timeout = 10;

    /**
     *  Sets SMTP class debugging on or off. Default value is false (off).
     * @public
     * @type bool
     */

    public $SMTPDebug = false;

    /////////////////////////////////////////////////

    // PRIVATE VARIABLES

    /////////////////////////////////////////////////

    /**
     *  Holds all "To" addresses.
     * @type array
     */

    public $to = [];

    /**
     *  Holds all "CC" addresses.
     * @type array
     */

    public $cc = [];

    /**
     *  Holds all "BCC" addresses.
     * @type array
     */

    public $bcc = [];

    /**
     *  Holds all "Reply-To" addresses.
     * @type array
     */

    public $ReplyTo = [];

    /**
     *  Holds all string and binary attachments.
     * @type array
     */

    public $attachment = [];

    /**
     *  Holds all custom headers.
     * @type array
     */

    public $CustomHeader = [];

    /**
     *  Holds the message boundary. Default is false.
     * @type string
     */

    public $boundary = false;

    /**
     *  Holds the message boundary. This is used specifically
     *  when multipart/alternative messages are sent. Default is false.
     * @type string
     */

    public $subboundary = false;

    /////////////////////////////////////////////////

    // VARIABLE METHODS

    /////////////////////////////////////////////////

    /**
     * Sets message type to HTML.  Returns void.
     * @public
     * @returns void
     * @param mixed $bool
     */

    public function IsHTML($bool)
    {
        if (true === $bool) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }

    /**
     * Sets Mailer to send message using SMTP.
     * Returns void.
     * @public
     * @returns void
     */

    public function IsSMTP()
    {
        $this->Mailer = 'smtp';
    }

    /**
     * Sets Mailer to send message using PHP mail() function.
     * Returns void.
     * @public
     * @returns void
     */

    public function IsMail()
    {
        $this->Mailer = 'mail';
    }

    /**
     * Sets Mailer to send message using the $Sendmail program.
     * Returns void.
     * @public
     * @returns void
     */

    public function IsSendmail()
    {
        $this->Mailer = 'sendmail';
    }

    /**
     * Sets Mailer to send message using the qmail MTA.  Returns void.
     * @public
     * @returns void
     */

    public function IsQmail()
    {
        //$this->Sendmail = "/var/qmail/bin/qmail-inject";

        //$this->Sendmail = "/var/qmail/bin/sendmail";

        $this->Mailer = 'sendmail';
    }

    /////////////////////////////////////////////////

    // STATIC METHODS

    /////////////////////////////////////////////////

    public function getMailAgents()
    {
        $mailers = [];

        $mailers[0] = 'SMTP';

        $mailers[1] = 'Mail';

        $mailers[2] = 'Sendmail';

        $mailers[3] = 'QMail';

        return $mailers;
    }

    /////////////////////////////////////////////////

    // RECIPIENT METHODS

    /////////////////////////////////////////////////

    /**
     * Adds a "To" address.  Returns void.
     * @public
     * @returns void
     * @param mixed $address
     * @param mixed $name
     */

    public function AddAddress($address, $name = '')
    {
        $cur = count($this->to);

        $this->to[$cur][0] = trim($address);

        $this->to[$cur][1] = $name;
    }

    /**
     * Adds a "Cc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.  This is a PHP bug that has been submitted
     * on http://bugs.php.net. The *NIX version of PHP
     * functions correctly. Returns void.
     * @public
     * @returns void
     * @param mixed $address
     * @param mixed $name
     */

    public function AddCC($address, $name = '')
    {
        $cur = count($this->cc);

        $this->cc[$cur][0] = trim($address);

        $this->cc[$cur][1] = $name;
    }

    /**
     * Adds a "Bcc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.  This is a PHP bug that has been submitted
     * on http://bugs.php.net. The *NIX version of PHP
     * functions correctly.
     * Returns void.
     * @public
     * @returns void
     * @param mixed $address
     * @param mixed $name
     */

    public function AddBCC($address, $name = '')
    {
        $cur = count($this->bcc);

        $this->bcc[$cur][0] = trim($address);

        $this->bcc[$cur][1] = $name;
    }

    /**
     * Adds a "Reply-to" address.  Returns void.
     * @public
     * @returns void
     * @param mixed $address
     * @param mixed $name
     */

    public function AddReplyTo($address, $name = '')
    {
        $cur = count($this->ReplyTo);

        $this->ReplyTo[$cur][0] = trim($address);

        $this->ReplyTo[$cur][1] = $name;
    }

    /////////////////////////////////////////////////

    // MAIL SENDING METHODS

    /////////////////////////////////////////////////

    /**
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false.  Use the ErrorInfo
     * variable to view description of the error.  Returns bool.
     * @public
     * @returns bool
     */

    public function Send()
    {
        if (count($this->to) < 1) {
            $this->errorHandler('You must provide at least one recipient email address');

            return false;
        }

        // Set whether the message is multipart/alternative

        if (!empty($this->AltBody)) {
            $this->ContentType = 'multipart/alternative';
        }

        $header = $this->create_header();

        if (!$body = $this->create_body()) {
            return false;
        }

        //echo "<pre>".$header . $body . "</pre>"; // debugging

        // Choose the mailer

        if ('sendmail' == $this->Mailer) {
            if (!$this->sendmail_send($header, $body)) {
                return false;
            }
        } elseif ('mail' == $this->Mailer) {
            if (!$this->mail_send($header, $body)) {
                return false;
            }
        } elseif ('smtp' == $this->Mailer) {
            if (!$this->smtp_send($header, $body)) {
                return false;
            }
        } else {
            $this->errorHandler(sprintf('%s mailer is not supported', $this->Mailer));

            return false;
        }

        return true;
    }

    /**
     * Sends mail using the $Sendmail program.  Returns bool.
     * @private
     * @returns bool
     * @param mixed $header
     * @param mixed $body
     * @return bool
     * @return bool
     */

    public function sendmail_send($header, $body)
    {
        if ('' != $this->Sender) {
            $sendmail = sprintf('%s -oi -f %s -t', $this->Sendmail, $this->Sender);
        } else {
            $sendmail = sprintf('%s -oi -t', $this->Sendmail);
        }

        if (!@$mail = popen($sendmail, 'w')) {
            $this->errorHandler(sprintf('Could not execute %s', $this->Sendmail));

            return false;
        }

        fwrite($mail, $header);

        fwrite($mail, $body);

        pclose($mail);

        return true;
    }

    /**
     * Sends mail using the PHP mail() function.  Returns bool.
     * @private
     * @returns bool
     * @param mixed $header
     * @param mixed $body
     * @return bool
     * @return bool
     */

    public function mail_send($header, $body)
    {
        //$to = substr($this->addr_append("To", $this->to), 4, -2);

        // Cannot add Bcc's to the $to
        $to = $this->to[0][0]; // no extra comma
        for ($i = 1, $iMax = count($this->to); $i < $iMax; $i++) {
            $to .= sprintf(',%s', $this->to[$i][0]);
        }

        if ('' != $this->Sender && PHP_VERSION >= '4.0') {
            $old_from = ini_get('sendmail_from');

            ini_set('sendmail_from', $this->Sender);
        }

        if ('' != $this->Sender && PHP_VERSION >= '4.0.5') {
            // The fifth parameter to mail is only available in PHP >= 4.0.5

            $params = sprintf('-oi -f %s', $this->Sender);

            $rt = @mail($to, $this->Subject, $body, $header, $params);
        } else {
            $rt = @mail($to, $this->Subject, $body, $header);
        }

        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }

        if (!$rt) {
            $this->errorHandler('Could not instantiate mail()');

            return false;
        }

        return true;
    }

    /**
     * Sends mail via SMTP using PhpSMTP (Author:
     * Chris Ryan).  Returns bool.  Returns false if there is a
     * bad MAIL FROM, RCPT, or DATA input.
     * @private
     * @returns bool
     * @param mixed $header
     * @param mixed $body
     * @return bool
     * @return bool
     */

    public function smtp_send($header, $body)
    {
        global $xoopsConfig;

        // Include SMTP class code, but not twice

        require_once XOOPS_ROOT_PATH . 'modules/xfmod/include/phpmailer/class.smtp.php';

        $smtp = new SMTP();

        $smtp->do_debug = $this->SMTPDebug;

        // Try to connect to all SMTP servers

        $hosts = explode(';', $this->Host);

        $index = 0;

        $connection = false;

        $smtp_from = '';

        $bad_rcpt = [];

        $e = '';

        // Retry while there is no connection

        while ($index < count($hosts) && false === $connection) {
            if (mb_strstr($hosts[$index], ':')) {
                [$host, $port] = explode(':', $hosts[$index]);
            } else {
                $host = $hosts[$index];

                $port = $this->Port;
            }

            if ($smtp->Connect($host, $port, $this->Timeout)) {
                $connection = true;
            }

            //printf("%s host could not connect<br>", $hosts[$index]); //debug only

            $index++;
        }

        if (!$connection) {
            $this->errorHandler('SMTP Error: could not connect to SMTP host server(s)');

            return false;
        }

        // Must perform HELO before authentication

        $smtp->Hello($this->Helo);

        // If user requests SMTP authentication

        if ($this->SMTPAuth) {
            if (!$smtp->Authenticate($this->Username, $this->Password)) {
                $this->errorHandler('SMTP Error: Could not authenticate');

                return false;
            }
        }

        if ('' == $this->Sender) {
            $smtp_from = $this->From;
        } else {
            $smtp_from = $this->Sender;
        }

        if (!$smtp->Mail(sprintf('<%s>', $smtp_from))) {
            $e = sprintf('SMTP Error: From address [%s] failed', $smtp_from);

            $this->errorHandler($e);

            return false;
        }

        // Attempt to send attach all recipients

        for ($i = 0, $iMax = count($this->to); $i < $iMax; $i++) {
            if (!$smtp->Recipient(sprintf('<%s>', $this->to[$i][0]))) {
                $bad_rcpt[] = $this->to[$i][0];
            }
        }

        for ($i = 0, $iMax = count($this->cc); $i < $iMax; $i++) {
            if (!$smtp->Recipient(sprintf('<%s>', $this->cc[$i][0]))) {
                $bad_rcpt[] = $this->cc[$i][0];
            }
        }

        for ($i = 0, $iMax = count($this->bcc); $i < $iMax; $i++) {
            if (!$smtp->Recipient(sprintf('<%s>', $this->bcc[$i][0]))) {
                $bad_rcpt[] = $this->bcc[$i][0];
            }
        }

        // Create error message

        if (count($bad_rcpt) > 0) {
            for ($i = 0, $iMax = count($bad_rcpt); $i < $iMax; $i++) {
                if (0 != $i) {
                    $e .= ', ';
                }

                $e .= $bad_rcpt[$i];
            }

            $e = sprintf('SMTP Error: The following recipients failed [%s]', $e);

            $this->errorHandler($e);

            return false;
        }

        if (!$smtp->Data(sprintf('%s%s', $header, $body))) {
            $this->errorHandler('SMTP Error: Data not accepted');

            return false;
        }

        $smtp->Quit();

        return true;
    }

    /////////////////////////////////////////////////

    // MESSAGE CREATION METHODS

    /////////////////////////////////////////////////

    /**
     * Creates recipient headers.  Returns string.
     * @private
     * @returns string
     * @param mixed $type
     * @param mixed $addr
     * @return string
     * @return string
     */

    public function addr_append($type, $addr)
    {
        $addr_str = '';

        $addr_str .= sprintf('%s: "%s" <%s>', $type, addslashes($addr[0][1]), $addr[0][0]);

        if (count($addr) > 1) {
            for ($i = 1, $iMax = count($addr); $i < $iMax; $i++) {
                $addr_str .= sprintf(', "%s" <%s>', addslashes($addr[$i][1]), $addr[$i][0]);
            }

            $addr_str .= "\r\n";
        } else {
            $addr_str .= "\r\n";
        }

        return ($addr_str);
    }

    /**
     * Wraps message for use with mailers that do not
     * automatically perform wrapping and for quoted-printable.
     * Original written by philippe.  Returns string.
     * @private
     * @returns string
     * @param mixed $message
     * @param mixed $length
     * @param mixed $qp_mode
     * @return string
     * @return string
     */

    public function wordwrap($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = " =\r\n";
        } else {
            $soft_break = "\r\n";
        }

        $message = $this->fix_eol($message);

        if ("\r\n" == mb_substr($message, -1)) {
            $message = mb_substr($message, 0, -1);
        }

        $line = explode("\r\n", $message);

        $message = '';

        for ($i = 0, $iMax = count($line); $i < $iMax; $i++) {
            $line_part = explode(' ', $line[$i]);

            $buf = '';

            for ($e = 0, $eMax = count($line_part); $e < $eMax; $e++) {
                $word = $line_part[$e];

                if ($qp_mode and (mb_strlen($word) > $length)) {
                    $space_left = $length - mb_strlen($buf) - 1;

                    if (0 != $e) {
                        if ($space_left > 20) {
                            $len = $space_left;

                            if ('=' == mb_substr($word, $len - 1, 1)) {
                                $len--;
                            } elseif ('=' == mb_substr($word, $len - 2, 1)) {
                                $len -= 2;
                            }

                            $part = mb_substr($word, 0, $len);

                            $word = mb_substr($word, $len);

                            $buf .= ' ' . $part;

                            $message .= $buf . "=\r\n";
                        } else {
                            $message .= $buf . $soft_break;
                        }

                        $buf = '';
                    }

                    while (mb_strlen($word) > 0) {
                        $len = $length;

                        if ('=' == mb_substr($word, $len - 1, 1)) {
                            $len--;
                        } elseif ('=' == mb_substr($word, $len - 2, 1)) {
                            $len -= 2;
                        }

                        $part = mb_substr($word, 0, $len);

                        $word = mb_substr($word, $len);

                        if (mb_strlen($word) > 0) {
                            $message .= $part . "=\r\n";
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;

                    if (0 == $e) {
                        $buf .= $word;
                    } else {
                        $buf .= ' ' . $word;
                    }

                    if (mb_strlen($buf) > $length and '' != $buf_o) {
                        $message .= $buf_o . $soft_break;

                        $buf = $word;
                    }
                }
            }

            $message .= $buf . "\r\n";
        }

        return ($message);
    }

    /**
     * Assembles message header.  Returns a string if successful
     * or false if unsuccessful.
     * @private
     * @returns string
     */

    public function create_header()
    {
        $header = [];

        $header[] = $this->received();

        $header[] = sprintf("Date: %s\r\n", $this->rfc_date());

        // To be created automatically by mail()

        if ('mail' != $this->Mailer) {
            $header[] = $this->addr_append('To', $this->to);
        }

        $header[] = sprintf("From: \"%s\" <%s>\r\n", addslashes($this->FromName), trim($this->From));

        if (count($this->cc) > 0) {
            $header[] = $this->addr_append('Cc', $this->cc);
        }

        // sendmail and mail() extract Bcc from the header before sending

        if ((('sendmail' == $this->Mailer) || ('mail' == $this->Mailer)) && (count($this->bcc) > 0)) {
            $header[] = $this->addr_append('Bcc', $this->bcc);
        }

        if (count($this->ReplyTo) > 0) {
            $header[] = $this->addr_append('Reply-to', $this->ReplyTo);
        }

        // mail() sets the subject itself

        if ('mail' != $this->Mailer) {
            $header[] = sprintf("Subject: %s\r\n", trim($this->Subject));
        }

        $header[] = sprintf("X-Priority: %d\r\n", $this->Priority);

        $header[] = sprintf("X-Mailer: phpmailer [version %s]\r\n", $this->Version);

        $header[] = sprintf("Return-Path: %s\r\n", trim($this->From));

        // Add custom headers

        for ($index = 0, $indexMax = count($this->CustomHeader); $index < $indexMax; $index++) {
            $header[] = sprintf("%s\r\n", $this->CustomHeader[$index]);
        }

        if ($this->UseMSMailHeaders) {
            $header[] = $this->AddMSMailHeaders();
        }

        $header[] = "MIME-Version: 1.0\r\n";

        // Add all attachments

        if (count($this->attachment) > 0 || !empty($this->AltBody)) {
            // Set message boundary

            $this->boundary = '_b' . md5(uniqid(time()));

            // Set message subboundary for multipart/alternative

            $this->subboundary = '_sb' . md5(uniqid(time()));

            $header[] = "Content-Type: Multipart/Mixed;\r\n";

            $header[] = sprintf("\tboundary=\"Boundary-=%s\"\r\n\r\n", $this->boundary);
        } else {
            $header[] = sprintf("Content-Transfer-Encoding: %s\r\n", $this->Encoding);

            $header[] = sprintf(
                'Content-Type: %s; charset = "%s"',
                $this->ContentType,
                $this->CharSet
            );

            // No additional lines when using mail() function

            if ('mail' != $this->Mailer) {
                $header[] = "\r\n\r\n";
            }
        }

        return (implode('', $header));
    }

    /**
     * Assembles the message body.  Returns a string if successful
     * or false if unsuccessful.
     * @private
     * @returns string
     */

    public function create_body()
    {
        // wordwrap the message body if set

        if ($this->WordWrap) {
            $this->Body = $this->wordwrap($this->Body, $this->WordWrap);
        }

        // If content type is multipart/alternative set body like this:

        if ((!empty($this->AltBody)) && (count($this->attachment) < 1)) {
            // Return text of body

            $mime = [];

            $mime[] = "This is a MIME message. If you are reading this text, you\r\n";

            $mime[] = "might want to consider changing to a mail reader that\r\n";

            $mime[] = "understands how to properly display MIME multipart messages.\r\n\r\n";

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->boundary);

            // Insert body. If multipart/alternative, insert both html and plain

            $mime[] = sprintf(
                "Content-Type: %s; charset = \"%s\";\r\n" . "\tboundary=\"Boundary-=%s\";\r\n\r\n",
                $this->ContentType,
                $this->CharSet,
                $this->subboundary
            );

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->subboundary);

            $mime[] = sprintf("Content-Type: text/plain; charset = \"%s\";\r\n", $this->CharSet);

            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);

            $mime[] = sprintf("%s\r\n\r\n", $this->AltBody);

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->subboundary);

            $mime[] = sprintf("Content-Type: text/html; charset = \"%s\";\r\n", $this->CharSet);

            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);

            $mime[] = sprintf("%s\r\n\r\n", $this->Body);

            $mime[] = sprintf("\r\n--Boundary-=%s--\r\n\r\n", $this->subboundary);

            $mime[] = sprintf("\r\n--Boundary-=%s--\r\n", $this->boundary);

            $this->Body = $this->encode_string(implode('', $mime), $this->Encoding);
        } else {
            $this->Body = $this->encode_string($this->Body, $this->Encoding);
        }

        if (count($this->attachment) > 0) {
            if (!$body = $this->attach_all()) {
                return false;
            }
        } else {
            $body = $this->Body;
        }

        return ($body);
    }

    /////////////////////////////////////////////////

    // ATTACHMENT METHODS

    /////////////////////////////////////////////////

    /**
     * Adds an attachment from a path on the filesystem.
     * Checks if attachment is valid and then adds
     * the attachment to the list.
     * Returns false if the file could not be found
     * or accessed.
     * @public
     * @returns bool
     * @param mixed $path
     * @param mixed $name
     * @param mixed $encoding
     * @param mixed $type
     * @return bool
     * @return bool
     */

    public function AddAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        if (!@is_file($path)) {
            $this->errorHandler(sprintf('Could not access [%s] file', $path));

            return false;
        }

        $filename = basename($path);

        if ('' == $name) {
            $name = $filename;
        }

        // Append to $attachment array

        $cur = count($this->attachment);

        $this->attachment[$cur][0] = $path;

        $this->attachment[$cur][1] = $filename;

        $this->attachment[$cur][2] = $name;

        $this->attachment[$cur][3] = $encoding;

        $this->attachment[$cur][4] = $type;

        $this->attachment[$cur][5] = false; // isStringAttachment

        return true;
    }

    /**
     * Attaches all fs, string, and binary attachments to the message.
     * Returns a string if successful or false if unsuccessful.
     * @private
     * @returns string
     */

    public function attach_all()
    {
        // Return text of body

        $mime = [];

        $mime[] = "This is a MIME message. If you are reading this text, you\r\n";

        $mime[] = "might want to consider changing to a mail reader that\r\n";

        $mime[] = "understands how to properly display MIME multipart messages.\r\n\r\n";

        $mime[] = sprintf("--Boundary-=%s\r\n", $this->boundary);

        // Insert body. If multipart/alternative, insert both html and plain.

        if (!empty($this->AltBody)) {
            $mime[] = sprintf(
                "Content-Type: %s; charset = \"%s\";\r\n" . "\tboundary=\"Boundary-=%s\";\r\n\r\n",
                $this->ContentType,
                $this->CharSet,
                $this->subboundary
            );

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->subboundary);

            $mime[] = sprintf("Content-Type: text/plain; charset = \"%s\";\r\n", $this->CharSet);

            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);

            $mime[] = sprintf("%s\r\n\r\n", $this->AltBody);

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->subboundary);

            $mime[] = sprintf("Content-Type: text/html; charset = \"%s\";\r\n", $this->CharSet);

            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);

            $mime[] = sprintf("%s\r\n\r\n", $this->Body);

            $mime[] = sprintf("\r\n--Boundary-=%s--\r\n\r\n", $this->subboundary);
        } else {
            $mime[] = sprintf("Content-Type: %s; charset = \"%s\";\r\n", $this->ContentType, $this->CharSet);

            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);

            $mime[] = sprintf("%s\r\n", $this->Body);
        }

        // Add all attachments

        for ($i = 0, $iMax = count($this->attachment); $i < $iMax; $i++) {
            // Check for string attachment

            $isString = $this->attachment[$i][5];

            if ($isString) {
                $string = $this->attachment[$i][0];
            } else {
                $path = $this->attachment[$i][0];
            }

            $filename = $this->attachment[$i][1];

            $name = $this->attachment[$i][2];

            $encoding = $this->attachment[$i][3];

            $type = $this->attachment[$i][4];

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->boundary);

            $mime[] = sprintf('Content-Type: %s; ', $type);

            $mime[] = sprintf("name=\"%s\"\r\n", $name);

            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n", $encoding);

            $mime[] = sprintf("Content-Disposition: attachment; filename=\"%s\"\r\n\r\n", $name);

            // Encode as string attachment

            if ($isString) {
                if (!$mime[] = sprintf("%s\r\n\r\n", $this->encode_string($string, $encoding))) {
                    return false;
                }
            } else {
                if (!$mime[] = sprintf("%s\r\n\r\n", $this->encode_file($path, $encoding))) {
                    return false;
                }
            }
        }

        $mime[] = sprintf("\r\n--Boundary-=%s--\r\n", $this->boundary);

        return (implode('', $mime));
    }

    /**
     * Encodes attachment in requested format.  Returns a
     * string if successful or false if unsuccessful.
     * @private
     * @returns string
     * @param mixed $path
     * @param mixed $encoding
     * @return false|mixed|string|string[]
     * @return false|mixed|string|string[]
     */

    public function encode_file($path, $encoding = 'base64')
    {
        if (!@$fd = fopen($path, 'rb')) {
            $this->errorHandler(sprintf('File Error: Could not open file %s', $path));

            return false;
        }

        $file = fread($fd, filesize($path));

        $encoded = $this->encode_string($file, $encoding);

        fclose($fd);

        return ($encoded);
    }

    /**
     * Encodes string to requested format. Returns a
     * string if successful or false if unsuccessful.
     * @private
     * @returns string
     * @param mixed $str
     * @param mixed $encoding
     * @return false|mixed|string|string[]
     * @return false|mixed|string|string[]
     */

    public function encode_string($str, $encoding = 'base64')
    {
        switch (mb_strtolower($encoding)) {
            case 'base64':
                // chunk_split is found in PHP >= 3.0.6
                $encoded = chunk_preg_split(base64_encode($str));
                break;
            case '7bit':
            case '8bit':
                $encoded = $this->fix_eol($str);
                if ("\r\n" != mb_substr($encoded, -2)) {
                    $encoded .= "\r\n";
                }
                break;
            case 'binary':
                $encoded = $str;
                break;
            case 'quoted-printable':
                $encoded = $this->encode_qp($str);
                break;
            default:
                $this->errorHandler(sprintf('Unknown encoding: %s', $encoding));

                return false;
        }

        return ($encoded);
    }

    /**
     * Encode string to quoted-printable.  Returns a string.
     * @private
     * @returns string
     * @param mixed $str
     * @return string
     * @return string
     */

    public function encode_qp($str)
    {
        $encoded = $this->fix_eol($str);

        if ("\r\n" != mb_substr($encoded, -2)) {
            $encoded .= "\r\n";
        }

        // Replace every high ascii, control and = characters

        $encoded = preg_replace(
            "/([\001-\010\013\014\016-\037\075\177-\377])/e",
            "'='.sprintf('%02X', ord('\\1'))",
            $encoded
        );

        // Replace every spaces and tabs when it's the last character on a line

        $encoded = preg_replace(
            "/([\011\040])\r\n/e",
            "'='.sprintf('%02X', ord('\\1')).'\r\n'",
            $encoded
        );

        // Maximum line length of 76 characters before CRLF (74 + space + '=')

        $encoded = $this->wordwrap($encoded, 74, true);

        return $encoded;
    }

    /**
     * Adds a string or binary attachment (non-filesystem) to the list.
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     * @public
     * @returns void
     * @param mixed $string
     * @param mixed $filename
     * @param mixed $encoding
     * @param mixed $type
     */

    public function AddStringAttachment($string, $filename, $encoding = 'base64', $type = 'application/octet-stream')
    {
        // Append to $attachment array

        $cur = count($this->attachment);

        $this->attachment[$cur][0] = $string;

        $this->attachment[$cur][1] = $filename;

        $this->attachment[$cur][2] = $filename;

        $this->attachment[$cur][3] = $encoding;

        $this->attachment[$cur][4] = $type;

        $this->attachment[$cur][5] = true; // isString
    }

    /////////////////////////////////////////////////

    // MESSAGE RESET METHODS

    /////////////////////////////////////////////////

    /**
     * Clears all recipients assigned in the TO array.  Returns void.
     * @public
     * @returns void
     */

    public function ClearAddresses()
    {
        $this->to = [];
    }

    /**
     * Clears all recipients assigned in the CC array.  Returns void.
     * @public
     * @returns void
     */

    public function ClearCCs()
    {
        $this->cc = [];
    }

    /**
     * Clears all recipients assigned in the BCC array.  Returns void.
     * @public
     * @returns void
     */

    public function ClearBCCs()
    {
        $this->bcc = [];
    }

    /**
     * Clears all recipients assigned in the ReplyTo array.  Returns void.
     * @public
     * @returns void
     */

    public function ClearReplyTos()
    {
        $this->ReplyTo = [];
    }

    /**
     * Clears all recipients assigned in the TO, CC and BCC
     * array.  Returns void.
     * @public
     * @returns void
     */

    public function ClearAllRecipients()
    {
        $this->to = [];

        $this->cc = [];

        $this->bcc = [];
    }

    /**
     * Clears all previously set filesystem, string, and binary
     * attachments.  Returns void.
     * @public
     * @returns void
     */

    public function ClearAttachments()
    {
        $this->attachment = [];
    }

    /**
     * Clears all custom headers.  Returns void.
     * @public
     * @returns void
     */

    public function ClearCustomHeaders()
    {
        $this->CustomHeader = [];
    }

    /////////////////////////////////////////////////

    // MISCELLANEOUS METHODS

    /////////////////////////////////////////////////

    /**
     * Adds the error message to the error container.
     * Returns void.
     * @private
     * @returns void
     * @param mixed $msg
     */

    public function errorHandler($msg)
    {
        $this->ErrorInfo = $msg;
    }

    /**
     * Returns the proper RFC 822 formatted date. Returns string.
     * @private
     * @returns string
     */

    public function rfc_date()
    {
        $tz = date('Z');

        $tzs = ($tz < 0) ? '-' : '+';

        $tz = abs($tz);

        $tz = ($tz / 3600) * 100 + ($tz % 3600) / 60;

        $date = sprintf('%s %s%04d', date('D, j M Y H:i:s'), $tzs, $tz);

        return $date;
    }

    /**
     * Returns received header for message tracing. Returns string.
     * @private
     * @returns string
     */

    public function received()
    {
        global $HTTP_SERVER_VARS;

        global $HTTP_ENV_VARS;

        // IIS & Apache use different global variables

        if ('' == $HTTP_SERVER_VARS['REMOTE_ADDR']) {
            $http_vars = $HTTP_ENV_VARS;
        } // Apache found

        else {
            $http_vars = $HTTP_SERVER_VARS;
        } // IIS found

        $str = sprintf(
            'Received: from phpmailer ([%s]) by %s ' . "with HTTP (%s);\r\n\t %s\r\n",
            $http_vars['REMOTE_ADDR'],
            $http_vars['SERVER_NAME'],
            $http_vars['SERVER_SOFTWARE'],
            $this->rfc_date()
        );

        return $str;
    }

    /**
     * Changes every end of line from CR or LF to CRLF.  Returns string.
     * @private
     * @returns string
     * @param mixed $str
     * @return string|string[]
     * @return string|string[]
     */

    public function fix_eol($str)
    {
        $str = str_replace("\r\n", "\n", $str);

        $str = str_replace("\r", "\n", $str);

        $str = str_replace("\n", "\r\n", $str);

        return $str;
    }

    /**
     * Adds a custom header.  Returns void.
     * @public
     * @returns void
     * @param mixed $custom_header
     */

    public function AddCustomHeader($custom_header)
    {
        $this->CustomHeader[] = $custom_header;
    }

    /**
     * Adds all the Microsoft message headers.  Returns string.
     * @private
     * @returns string
     */

    public function AddMSMailHeaders()
    {
        $MSHeader = '';

        if (1 == $this->Priority) {
            $MSPriority = 'High';
        } elseif (5 == $this->Priority) {
            $MSPriority = 'Low';
        } else {
            $MSPriority = 'Medium';
        }

        $MSHeader .= sprintf("X-MSMail-Priority: %s\r\n", $MSPriority);

        $MSHeader .= sprintf("Importance: %s\r\n", $MSPriority);

        return ($MSHeader);
    }
}

// End of class
