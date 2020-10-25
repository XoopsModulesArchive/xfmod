<?php
/*
 * File: smtp.php
 *
 * Description: Define an SMTP class that can be used to connect
 *              and communicate with any SMTP server. It implements
 *              all the SMTP functions defined in RFC821 except TURN.
 *
 * Creator: Chris Ryan <chris@greatbridge.com>
 * Created: 03/26/2001
 *
 * TODO:
 *     - Move all the duplicate code to a utility function
 *           Most of the functions have the first lines of
 *           code do the same processing. If this can be moved
 *           into a utility function then it would reduce the
 *           overall size of the code significantly.
 */

/*
 * STMP is rfc 821 compliant and implements all the rfc 821 SMTP
 * commands except TURN which will always return a not implemented
 * error. SMTP also provides some utility methods for sending mail
 * to an SMTP server.
 */

class SMTP
{
    public $SMTP_PORT = 25; # the default SMTP PORT
    public $CRLF = "\r\n";  # CRLF pair
    public $smtp_conn;      # the socket to the server
    public $error;          # error if any on the last call
    public $helo_rply;      # the reply the server sent to us for HELO
    public $do_debug;       # the level of debug to perform

    /*
     * SMTP()
     *
     * Initialize the class so that the data is in a known state.
     */

    public function __construct()
    {
        $this->smtp_conn = 0;

        $this->error = null;

        $this->helo_rply = null;

        $this->do_debug = 0;
    }

    /************************************************************
     *                    CONNECTION FUNCTIONS                  *
     **********************************************************
     * @param     $host
     * @param int $port
     * @param int $tval
     * @return bool
     */

    /*
     * Connect($host, $port=0, $tval=30)
     *
     * Connect to the server specified on the port specified.
     * If the port is not specified use the default SMTP_PORT.
     * If tval is specified then a connection will try and be
     * established with the server for that number of seconds.
     * If tval is not specified the default is 30 seconds to
     * try on the connection.
     *
     * SMTP CODE SUCCESS: 220
     * SMTP CODE FAILURE: 421
     */

    public function Connect($host, $port = 0, $tval = 30)
    {
        # set the error val to null so there is no confusion

        $this->error = null;

        # make sure we are __not__ connected

        if ($this->Connected()) {
            # ok we are connected! what should we do?

            # for now we will just give an error saying we

            # are already connected

            $this->error = ['error' => 'Already connected to a server'];

            return false;
        }

        if (empty($port)) {
            $port = $this->SMTP_PORT;
        }

        #connect to the smtp server

        $this->smtp_conn = fsockopen(
            $host,    # the host of the server
            $port,    # the port to use
            $errno,   # error number if any
            $errstr,  # error message if any
            $tval
        );   # give up after ? secs

        # verify we connected properly

        if (empty($this->smtp_conn)) {
            $this->error = [
                'error' => 'Failed to connect to server',
                'errno' => $errno,
                'errstr' => $errstr,
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ": $errstr ($errno)" . $this->CRLF;
            }

            return false;
        }

        # sometimes the SMTP server takes a little longer to respond

        # so we will give it a longer timeout for the first read

        // Commented b/c of win32 warning messages

        //if(function_exists("socket_set_timeout"))

        //   socket_set_timeout($this->smtp_conn, 1, 0);

        # get any announcement stuff

        $announce = $this->get_lines();

        # set the timeout  of any socket functions at 1/10 of a second

        //if(function_exists("socket_set_timeout"))

        //   socket_set_timeout($this->smtp_conn, 0, 100000);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $announce;
        }

        return true;
    }

    /*
     * Authenticate()
     *
     * Performs SMTP authentication.  Must be run after running the
     * Hello() method.  Returns true if successfully authenticated.
     */

    public function Authenticate($username, $password)
    {
        // Start authentication

        fwrite($this->smtp_conn, 'AUTH LOGIN' . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if (334 != $code) {
            $this->error = [
                'error' => 'AUTH not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        // Send encoded username

        fwrite($this->smtp_conn, base64_encode($username) . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if (334 != $code) {
            $this->error = [
                'error' => 'Username not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        // Send encoded password

        fwrite($this->smtp_conn, base64_encode($password) . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if (235 != $code) {
            $this->error = [
                'error' => 'Password not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Connected()
     *
     * Returns true if connected to a server otherwise false
     */

    public function Connected()
    {
        if (!empty($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);

            if ($sock_status['eof']) {
                # hmm this is an odd situation... the socket is

                # valid but we aren't connected anymore

                if ($this->do_debug >= 1) {
                    echo 'SMTP -> NOTICE:' . $this->CRLF . 'EOF caught while checking if connected';
                }

                $this->Close();

                return false;
            }

            return true; # everything looks good
        }

        return false;
    }

    /*
     * Close()
     *
     * Closes the socket and cleans up the state of the class.
     * It is not considered good to use this function without
     * first trying to use QUIT.
     */

    public function Close()
    {
        $this->error = null; # so there is no confusion

        $this->helo_rply = null;

        if (!empty($this->smtp_conn)) {
            # close the connection and cleanup

            fclose($this->smtp_conn);

            $this->smtp_conn = 0;
        }
    }

    /**************************************************************
     *                        SMTP COMMANDS                       *
     ************************************************************
     * @param $msg_data
     * @return bool
     */

    /*
     * Data($msg_data)
     *
     * Issues a data command and sends the msg_data to the server
     * finializing the mail transaction. $msg_data is the message
     * that is to be send with the headers. Each header needs to be
     * on a single line followed by a <CRLF> with the message headers
     * and the message body being seperated by and additional <CRLF>.
     *
     * Implements rfc 821: DATA <CRLF>
     *
     * SMTP CODE INTERMEDIATE: 354
     *     [data]
     *     <CRLF>.<CRLF>
     *     SMTP CODE SUCCESS: 250
     *     SMTP CODE FAILURE: 552,554,451,452
     * SMTP CODE FAILURE: 451,554
     * SMTP CODE ERROR  : 500,501,503,421
     */

    public function Data($msg_data)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Data() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'DATA' . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (354 != $code) {
            $this->error = [
                'error' => 'DATA command not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        # the server is ready to accept data!

        # according to rfc 821 we should not send more than 1000

        # including the CRLF

        # characters on a single line so we will break the data up

        # into lines by \r and/or \n then if needed we will break

        # each of those into smaller lines to fit within the limit.

        # in addition we will be looking for lines that start with

        # a period '.' and append and additional period '.' to that

        # line. NOTE: this does not count towards are limit.

        # normalize the line breaks so we know the explode works

        $msg_data = str_replace("\r\n", "\n", $msg_data);

        $msg_data = str_replace("\r", "\n", $msg_data);

        $lines = explode("\n", $msg_data);

        # we need to find a good way to determine is headers are

        # in the msg_data or if it is a straight msg body

        # currently I'm assuming rfc 822 definitions of msg headers

        # and if the first field of the first line (':' sperated)

        # does not contain a space then it _should_ be a header

        # and we can process all lines before a blank "" line as

        # headers.

        $field = mb_substr($lines[0], 0, mb_strpos($lines[0], ':'));

        $in_headers = false;

        if (!empty($field) && !mb_strstr($field, ' ')) {
            $in_headers = true;
        }

        $max_line_length = 998; # used below; set here for ease in change

        while (list(, $line) = @each($lines)) {
            $lines_out = null;

            if ('' == $line && $in_headers) {
                $in_headers = false;
            }

            # ok we need to break this line up into several

            # smaller lines

            while (mb_strlen($line) > $max_line_length) {
                $pos = mb_strrpos(mb_substr($line, 0, $max_line_length), ' ');

                $lines_out[] = mb_substr($line, 0, $pos);

                $line = mb_substr($line, $pos + 1);

                # if we are processing headers we need to

                # add a LWSP-char to the front of the new line

                # rfc 822 on long msg headers

                if ($in_headers) {
                    $line = "\t" . $line;
                }
            }

            $lines_out[] = $line;

            # now send the lines to the server

            while (list(, $line_out) = @each($lines_out)) {
                if ('.' == $line_out[0]) {
                    $line_out = '.' . $line_out;
                }

                fwrite($this->smtp_conn, $line_out . $this->CRLF);
            }
        }

        # ok all the message data has been sent so lets get this

        # over with aleady

        fwrite($this->smtp_conn, $this->CRLF . '.' . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'DATA not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Expand($name)
     *
     * Expand takes the name and asks the server to list all the
     * people who are members of the _list_. Expand will return
     * back and array of the result or false if an error occurs.
     * Each value in the array returned has the format of:
     *     [ <full-name> <sp> ] <path>
     * The definition of <path> is defined in rfc 821
     *
     * Implements rfc 821: EXPN <SP> <string> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE FAILURE: 550
     * SMTP CODE ERROR  : 500,501,502,504,421
     */

    public function Expand($name)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Expand() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'EXPN ' . $name . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'EXPN not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        # parse the reply and place in our array to return to user

        $entries = explode($this->CRLF, $rply);

        while (list(, $l) = @each($entries)) {
            $list[] = mb_substr($l, 4);
        }

        return $rval;
    }

    /*
     * Hello($host="")
     *
     * Sends the HELO command to the smtp server.
     * This makes sure that we and the server are in
     * the same known state.
     *
     * Implements from rfc 821: HELO <SP> <domain> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 501, 504, 421
     */

    public function Hello($host = '')
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Hello() without being connected',
            ];

            return false;
        }

        # if a hostname for the HELO wasn't specified determine

        # a suitable one to send

        if (empty($host)) {
            # we need to determine some sort of appopiate default

            # to send to the server

            $host = 'localhost';
        }

        fwrite($this->smtp_conn, 'HELO ' . $host . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER: ' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'HELO not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        $this->helo_rply = $rply;

        return true;
    }

    /*
     * Help($keyword="")
     *
     * Gets help information on the keyword specified. If the keyword
     * is not specified then returns generic help, ussually contianing
     * A list of keywords that help is available on. This function
     * returns the results back to the user. It is up to the user to
     * handle the returned data. If an error occurs then false is
     * returned with $this->error set appropiately.
     *
     * Implements rfc 821: HELP [ <SP> <string> ] <CRLF>
     *
     * SMTP CODE SUCCESS: 211,214
     * SMTP CODE ERROR  : 500,501,502,504,421
     *
     */

    public function Help($keyword = '')
    {
        $this->error = null; # to avoid confusion

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Help() without being connected',
            ];

            return false;
        }

        $extra = '';

        if (!empty($keyword)) {
            $extra = ' ' . $keyword;
        }

        fwrite($this->smtp_conn, 'HELP' . $extra . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (211 != $code && 214 != $code) {
            $this->error = [
                'error' => 'HELP not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return $rply;
    }

    /*
     * Mail($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command.
     *
     * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,421
     */

    public function Mail($from)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Mail() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'MAIL FROM:' . $from . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'MAIL not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Noop()
     *
     * Sends the command NOOP to the SMTP server.
     *
     * Implements from rfc 821: NOOP <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 421
     */

    public function Noop()
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Noop() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'NOOP' . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'NOOP not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Quit($close_on_error=true)
     *
     * Sends the quit command to the server and then closes the socket
     * if there is no error or the $close_on_error argument is true.
     *
     * Implements from rfc 821: QUIT <CRLF>
     *
     * SMTP CODE SUCCESS: 221
     * SMTP CODE ERROR  : 500
     */

    public function Quit($close_on_error = true)
    {
        $this->error = null; # so there is no confusion

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Quit() without being connected',
            ];

            return false;
        }

        # send the quit command to the server

        fwrite($this->smtp_conn, 'quit' . $this->CRLF);

        # get any good-bye messages

        $byemsg = $this->get_lines();

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $byemsg;
        }

        $rval = true;

        $e = null;

        $code = mb_substr($byemsg, 0, 3);

        if (221 != $code) {
            # use e as a tmp var cause Close will overwrite $this->error

            $e = [
                'error' => 'SMTP server rejected quit command',
                'smtp_code' => $code,
                'smtp_rply' => mb_substr($byemsg, 4),
            ];

            $rval = false;

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $e['error'] . ': ' . $byemsg . $this->CRLF;
            }
        }

        if (empty($e) || $close_on_error) {
            $this->Close();
        }

        return $rval;
    }

    /*
     * Recipient($to)
     *
     * Sends the command RCPT to the SMTP server with the TO: argument of $to.
     * Returns true if the recipient was accepted false if it was rejected.
     *
     * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250,251
     * SMTP CODE FAILURE: 550,551,552,553,450,451,452
     * SMTP CODE ERROR  : 500,501,503,421
     */

    public function Recipient($to)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Recipient() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'RCPT TO:' . $to . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code && 251 != $code) {
            $this->error = [
                'error' => 'RCPT not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Reset()
     *
     * Sends the RSET command to abort and transaction that is
     * currently in progress. Returns true if successful false
     * otherwise.
     *
     * Implements rfc 821: RSET <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500,501,504,421
     */

    public function Reset()
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Reset() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'RSET' . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'RSET failed',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Send($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in.
     *
     * Implements rfc 821: SEND <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     */

    public function Send($from)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Send() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'SEND FROM:' . $from . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'SEND not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * SendAndMail($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in and send them an email.
     *
     * Implements rfc 821: SAML <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     */

    public function SendAndMail($from)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called SendAndMail() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'SAML FROM:' . $from . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'SAML not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * SendOrMail($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in or mail it to them if they are not.
     *
     * Implements rfc 821: SOML <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     */

    public function SendOrMail($from)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called SendOrMail() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'SOML FROM:' . $from . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code) {
            $this->error = [
                'error' => 'SOML not accepted from server',
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return true;
    }

    /*
     * Turn()
     *
     * This is an optional command for SMTP that this class does not
     * support. This method is here to make the RFC821 Definition
     * complete for this class and __may__ be implimented in the future
     *
     * Implements from rfc 821: TURN <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE FAILURE: 502
     * SMTP CODE ERROR  : 500, 503
     */

    public function Turn()
    {
        $this->error = [
            'error' => 'This method, TURN, of the SMTP ' . 'is not implemented',
        ];

        if ($this->do_debug >= 1) {
            echo 'SMTP -> NOTICE: ' . $this->error['error'] . $this->CRLF;
        }

        return false;
    }

    /*
     * Verify($name)
     *
     * Verifies that the name is recognized by the server.
     * Returns false if the name could not be verified otherwise
     * the response from the server is returned.
     *
     * Implements rfc 821: VRFY <SP> <string> <CRLF>
     *
     * SMTP CODE SUCCESS: 250,251
     * SMTP CODE FAILURE: 550,551,553
     * SMTP CODE ERROR  : 500,501,502,421
     */

    public function Verify($name)
    {
        $this->error = null; # so no confusion is caused

        if (!$this->Connected()) {
            $this->error = [
                'error' => 'Called Verify() without being connected',
            ];

            return false;
        }

        fwrite($this->smtp_conn, 'VRFY ' . $name . $this->CRLF);

        $rply = $this->get_lines();

        $code = mb_substr($rply, 0, 3);

        if ($this->do_debug >= 2) {
            echo 'SMTP -> FROM SERVER:' . $this->CRLF . $rply;
        }

        if (250 != $code && 251 != $code) {
            $this->error = [
                'error' => "VRFY failed on name '$name'",
                'smtp_code' => $code,
                'smtp_msg' => mb_substr($rply, 4),
            ];

            if ($this->do_debug >= 1) {
                echo 'SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply . $this->CRLF;
            }

            return false;
        }

        return $rply;
    }

    /******************************************************************
     *                       INTERNAL FUNCTIONS                       *
     ******************************************************************/

    /*
     * get_lines()
     *
     * __internal_use_only__: read in as many lines as possible
     * either before eof or socket timeout occurs on the operation.
     * With SMTP we can tell if we have more lines to read if the
     * 4th character is '-' symbol. If it is a space then we don't
     * need to read anything else.
     */

    public function get_lines()
    {
        $data = '';

        while ($str = fgets($this->smtp_conn, 515)) {
            if ($this->do_debug >= 4) {
                echo "SMTP -> get_lines(): \$data was \"$data\"" . $this->CRLF;

                echo "SMTP -> get_lines(): \$str is \"$str\"" . $this->CRLF;
            }

            $data .= $str;

            if ($this->do_debug >= 4) {
                echo "SMTP -> get_lines(): \$data is \"$data\"" . $this->CRLF;
            }

            # if the 4th character is a space then we are done reading

            # so just break the loop

            if (' ' == mb_substr($str, 3, 1)) {
                break;
            }
        }

        return $data;
    }
}