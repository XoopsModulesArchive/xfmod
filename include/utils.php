<?php
/**
 * utils.php - Misc utils common to all aspects of the site
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @param mixed $string
 * @return string
 * @return string
 * @version   $Id: utils.php,v 1.10 2004/01/14 19:29:49 devsupaul Exp $
 */

//require_once XOOPS_ROOT_PATH."/modules/xfmod/include/phpmailer/class.phpmailer.php";

function util_unconvert_htmlspecialchars($string)
{
    if (mb_strlen($string) < 1) {
        return '';
    }  

    $trans = get_html_translation_table(HTMLENTITIES, ENT_QUOTES);

    $trans = array_flip($trans);

    $str = strtr($string, $trans);

    return $str;
}

/**
 * util_result_column_to_array() - Takes a result set and turns the optional column into an array
 *
 * @param mixed $result
 * @param mixed $col
 * @return array
 * @resturns An array
 */
function util_result_column_to_array($result, $col = 0)
{
    global $xoopsDB;

    /*
        Takes a result set and turns the optional column into
        an array
    */

    $rows = $xoopsDB->getRowsNum($result);

    if ($rows > 0) {
        $arr = [];

        for ($i = 0; $i < $rows; $i++) {
            $arr[$i] = unofficial_getDBResult($result, $i, $col);
        }
    } else {
        $arr = [];
    }

    return $arr;
}

/**
 * util_result_columns_to_assoc() - Takes a result set and turns the column pair into an associative array
 *
 * @param mixed $result
 * @param mixed $col_key
 * @param mixed $col_val
 * @return array
 */
function util_result_columns_to_assoc($result, $col_key = 0, $col_val = 1)
{
    global $xoopsDB;

    $rows = $xoopsDB->getRowsNum($result);

    if ($rows > 0) {
        $arr = [];

        for ($i = 0; $i < $rows; $i++) {
            $arr[unofficial_getDBResult($result, $i, $col_key)] = unofficial_getDBResult($result, $i, $col_val);
        }
    } else {
        $arr = [];
    }

    return $arr;
}

/**
 * show_priority_colors_key() - Show the priority colors legend
 */
function get_priority_colors_key()
{
    $content = '<P><B>' . _XF_PRIORITYCOLORS . ':</B><BR>' . "<TABLE BORDER='0'><TR>";

    for ($i = 1; $i < 10; $i++) {
        $content .= "<TD BGCOLOR='" . get_priority_color($i) . "'>" . $i . '</TD>';
    }

    $content .= '</tr></table>';

    return $content;
}

function show_priority_colors_key()
{
    return get_priority_colors_key();
}

/*
 * Validates an email adress
 * (Code taken from newsportal)
 *
 * $address: a string containing the email-address to be validated
 *
 * returns true if the address passes the tests, false otherwise.
 */
function validate_email($address)
{
    global $xoopsForge;

    if (!isset($xoopsForge['validate_email'])) {
        $xoopsForge['validate_email'] = 1;
    }

    $return = true;

    if (($xoopsForge['validate_email'] >= 1) && (true === $return)) {
        $return = (preg_match(
            '^[-!#$%&\'*+\\./0-9=?A-Z^_A-z{|}~]+' . '@' . '[-!#$%&\'*+\\/0-9=?A-Z^_A-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_A-z{|}~]+$',
            $address
        ));
    }

    if (($xoopsForge['validate_email'] >= 2) && (true === $return)) {
        $addressarray = address_decode($address, 'garantiertungueltig');

        $return = checkdnsrr($addressarray[0]['host'], 'MX');

        if (!$return) {
            $return = checkdnsrr($addressarray[0]['host'], 'A');
        }
    }

    return ($return);
}

/*
 * Split an internet-address string into its parts. An address string could
 * be for example:
 * - user@host.domain (Realname)
 * - "Realname" <user@host.domain>
 * - user@host.domain
 *
 * The address will be split into user, host (incl. domain) and realname
 *
 * $adrstring: The string containing the address in internet format
 * $defaulthost: The name of the host which should be returned if the
 *               address-string doesn't contain a hostname.
 *
 * returns an hash containing the fields "mailbox", "host" and "personal"
 */
function address_decode($adrstring, $defaulthost)
{
    $parsestring = trim($adrstring);

    $len = mb_strlen($parsestring);

    $at_pos = mb_strpos($parsestring, '@');     // find @
    $ka_pos = mb_strpos($parsestring, '(');     // find (
    $kz_pos = mb_strpos($parsestring, ')');     // find )
    $ha_pos = mb_strpos($parsestring, '<');     // find <
    $hz_pos = mb_strpos($parsestring, '>');     // find >
    $space_pos = mb_strpos($parsestring, ')');  // find ' '
    $email = '';

    $mailbox = '';

    $host = '';

    $personal = '';

    if (false !== $space_pos) {
        if ((false !== $ka_pos) && (false !== $kz_pos)) {
            $personal = mb_substr($parsestring, $ka_pos + 1, $kz_pos - $ka_pos - 1);

            $email = trim(mb_substr($parsestring, 0, $ka_pos - 1));
        }
    } else {
        $email = $adrstring;
    }

    if ((false !== $ha_pos) && (false !== $hz_pos)) {
        $email = trim(mb_substr($parsestring, $ha_pos + 1, $hz_pos - $ha_pos - 1));

        $personal = mb_substr($parsestring, 0, $ha_pos - 1);
    }

    if (false !== $at_pos) {
        $mailbox = mb_substr($email, 0, mb_strpos($email, '@'));

        $host = mb_substr($email, mb_strpos($email, '@') + 1);
    } else {
        $mailbox = $email;

        $host = $defaulthost;
    }

    $personal = trim($personal);

    if ('"' == mb_substr($personal, 0, 1)) {
        $personal = mb_substr($personal, 1);
    }

    if ('"' == mb_substr($personal, mb_strlen($personal) - 1, 1)) {
        $personal = mb_substr($personal, 0, -1);
    }

    $result['mailbox'] = trim($mailbox);

    $result['host'] = trim($host);

    if ('' != $personal) {
        $result['personal'] = $personal;
    }

    $complete[] = $result;

    return ($complete);
}

function checkXFAdminPermissions($error_on_failure = 'Access Denied')
{
    global $xoopsConfig, $xoopsUser;

    require_once $xoopsConfig['root_path'] . 'class/xoopsmodule.php';

    require_once $xoopsConfig['root_path'] . 'kernel/group.php';

    $xoopsModule = XoopsModule::getByDirname('xfmod');

    if (!$xoopsModule) {
        redirect_header($xoopsConfig['xoops_url'] . '/', 2, "SOME SERIOUS ERROR OCCURED!! Where is the 'xfmod' module?!?!?!");

        exit();
    }

    if ($xoopsUser) {
        if (!XoopsGroup::hasAccessRight($xoopsModule->mid(), $xoopsUser->groups())) {
            redirect_header($xoopsConfig['xoops_url'] . '/', 2, $error_on_failure);

            exit();
        }
    } else {
        if (!XoopsGroup::hasAccessRight($xoopsModule->mid(), 0)) {
            redirect_header($xoopsConfig['xoops_url'] . '/', 2, $error_on_failure);

            exit();
        }
    }
}

function xoopsForgeMail($from, $fromname, $subject, $body, $to_arr, $bcc_arr = false, $cc_arr = false)
{
    $xoopsMailer = getMailer();

    $xoopsMailer->setToEmails($to_arr);

    if ($bcc_arr) {
        $xoopsMailer->setToEmails($bcc_arr);
    }

    if ($cc_arr) {
        $xoopsMailer->setToEmails($cc_arr);
    }

    $xoopsMailer->setFromName($fromname);

    $xoopsMailer->setFromEmail($from);

    $xoopsMailer->setSubject($subject);

    $xoopsMailer->setBody($body);

    $xoopsMailer->useMail();

    return $xoopsMailer->send();
}

function ShowResultSet($result, $title = 'Untitled', $linkify = false)
{
    global $group_id, $xoopsDB;

    if ($result) {
        $rows = $xoopsDB->getRowsNum($result);

        $cols = unofficial_getNumFields($result);

        $content = "<table border='0' width='100%'>" . "<tr class='bg2'><td colspan='" . $cols . "'><B style='font-size:14px;text-align:left;'>" . $title . '</B></td></tr>';

        /*  Create  the  headers  */

        $content .= "<tr class='bg2'>";

        for ($i = 0; $i < $cols; $i++) {
            $content .= '<td><B>' . unofficial_getFieldName($result, $i) . '</B></TD>';
        }

        $content .= '</tr>';

        /*  Create the rows  */

        for ($j = 0; $j < $rows; $j++) {
            $content .= "<TR class='" . ($j % 2 > 0 ? 'bg1' : 'bg3') . "'>";

            for ($i = 0; $i < $cols; $i++) {
                if ($linkify && 0 == $i) {
                    $link = '<A HREF="' . $_SERVER['PHP_SELF'] . '?';

                    $linkend = '</A>';

                    if ('bug_cat' == $linkify) {
                        $link .= 'group_id=' . $group_id . '&bug_cat_mod=y&bug_cat_id=' . unofficial_getDBResult($result, $j, 'bug_category_id') . '">';
                    } elseif ('bug_group' == $linkify) {
                        $link .= 'group_id=' . $group_id . '&bug_group_mod=y&bug_group_id=' . unofficial_getDBResult($result, $j, 'bug_group_id') . '">';
                    } elseif ('patch_cat' == $linkify) {
                        $link .= 'group_id=' . $group_id . '&patch_cat_mod=y&patch_cat_id=' . unofficial_getDBResult($result, $j, 'patch_category_id') . '">';
                    } elseif ('support_cat' == $linkify) {
                        $link .= 'group_id=' . $group_id . '&support_cat_mod=y&support_cat_id=' . unofficial_getDBResult($result, $j, 'support_category_id') . '">';
                    } elseif ('pm_project' == $linkify) {
                        $link .= 'group_id=' . $group_id . '&project_cat_mod=y&project_cat_id=' . unofficial_getDBResult($result, $j, 'group_project_id') . '">';
                    } else {
                        $link = $linkend = '';
                    }
                } else {
                    $link = $linkend = '';
                }

                $content .= '<td>' . $link . unofficial_getDBResult($result, $j, $i) . $linkend . '</td>';
            }

            $content .= '</tr>';
        }

        $content .= '</table>';
    } else {
        $content .= $xoopsDB->error();
    }

    return $content;
}

/**
 * util_check_fileupload() - determines if a filename is appropriate for upload
 *
 * @param mixed $filename
 * @return string
 */
function util_check_fileupload($filename)
{
    /* Empty file is a valid file.
       This is because this function should be called
       unconditionally at the top of submit action processing
       and many forms have optional file upload. */

    if ('none' == $filename || '' == $filename) {
        return 'OK';
    }

    /* This should be enough... */

    if (!is_uploaded_file($filename)) {
        return '!is_uploaded_file';
    }

    /* ... but we'd rather be paranoic */

    if (mb_strstr($filename, '..')) {
        return "strstr(filename, '..')";
    }

    if (!is_file($filename)) {
        return '!is_file';
    }

    if (!file_exists($filename)) {
        return '!file_exists';
    }

    return 'OK';
}

/**
 * GraphResult() - Takes a database result set and builds a graph.
 * The first column should be the name, and the second column should be the values
 * Be sure to include HTL_Graphs.php before using this function
 *
 * @param mixed $result
 * @param mixed $title
 *
 * @author Tim Perdue tperdue@valinux.com
 */
function GraphResult($result, $title)
{
    global $xoopsDB;

    $rows = $xoopsDB->getRowsNum($result);

    if ((!$result) || ($rows < 1)) {
        echo 'None Found.';
    } else {
        $names = [];

        $values = [];

        for ($j = 0; $j < $xoopsDB->getRowsNum($result); $j++) {
            if ('' != unofficial_getDBResult($result, $j, 0) && '' != unofficial_getDBResult($result, $j, 1)) {
                $names[$j] = unofficial_getDBResult($result, $j, 0);

                $values[$j] = unofficial_getDBResult($result, $j, 1);
            }
        }

        /*
            This is another function detailed below
        */

        GraphIt($names, $values, $title);
    }
}

/**
 * GraphIt() - Build a graph
 *
 * @param mixed $name_string
 * @param mixed $value_string
 * @param mixed $title
 *
 * @author Tim Perdue tperdue@valinux.com
 */
function GraphIt($name_string, $value_string, $title)
{
    global $bgpri;

    $counter = count($name_string);

    /*
        Can choose any color you wish
    */

    $bars = [];

    for ($i = 0; $i < $counter; $i++) {
        $bars[$i] = $bgpri[5];
    }

    $counter = count($value_string);

    /*
        Figure the max_value passed in, so scale can be determined
    */

    $max_value = 0;

    for ($i = 0; $i < $counter; $i++) {
        if ($value_string[$i] > $max_value) {
            $max_value = $value_string[$i];
        }
    }

    if ($max_value < 1) {
        $max_value = 1;
    }

    /*
        I want my graphs all to be 800 pixels wide, so that is my divisor
    */

    $scale = (400 / $max_value);

    /*
        I create a wrapper table around the graph that holds the title
    */

    echo "<table border='0'>" . "<tr class='bg2'>" . "<td><b>$title</b></td>" . '</tr>';

    echo '<TR><TD>';

    /*
        Create an associate array to pass in. I leave most of it blank
    */

    $vals = [
        'vlabel' => '',
        'hlabel' => '',
        'type' => '',
        'cellpadding' => '',
        'cellspacing' => '0',
        'border' => '',
        'width' => '',
        'background' => '',
        'vfcolor' => '',
        'hfcolor' => '',
        'vbgcolor' => '',
        'hbgcolor' => '',
        'vfstyle' => '',
        'hfstyle' => '',
        'noshowvals' => '',
        'scale' => $scale,
        'namebgcolor' => '',
        'valuebgcolor' => '',
        'namefcolor' => '',
        'valuefcolor' => '',
        'namefstyle' => '',
        'valuefstyle' => '',
        'doublefcolor' => '',
    ];

    /*
        This is the actual call to the HTML_Graphs class
    */

    html_graph($name_string, $value_string, $bars, $vals);

    echo '
		</TD></TR></TABLE>
		<!-- end outer graph table -->';
}

//A return value of false means there were no problems reported
function VirusScan($filename)
{
    global $xoopsForge;

    if (1 != $xoopsForge['virusscan']) {
        return false;
    }

    $results = shell_exec(XOOPS_ROOT_PATH . '/modules/xfmod/bin/cscmdline -c ' . XOOPS_ROOT_PATH . '/modules/xfmod/bin -s prv-teamsite1.provo.novell.com -v ' . $filename);

    preg_match_all("/\w+:\s+(\d)/", $results, $matches);

    // $matches[1][1] - the number of files scanned

    // $matches[1][2] - the number of infected files

    // $matches[1][3] - the number of repaired files

    // $matches[1][4] - the number of errors reported

    if ($matches[1][4] > 0) {
        return _XF_FRS_VIRUSSCANFAILED;
    }

    if ($matches[1][1] < 1) {
        return _XF_FRS_VIRUSSCANFAILEDNOFILE;
    }

    if ($matches[1][2] > 0) {
        return _XF_FRS_VIRUSFOUND;
    }

    return false;
}

function FileExtFilter($filename)
{
    return false;
}

/*
@return value : $_POST[$key] , _GET[$key] or null
*/
function util_http_track_vars($key, $default = 'null')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];
    } elseif (isset($_GET[$key])) {
        $value = $_GET[$key];
    } else {
        $value = $default;
    }

    return $value;
}

function http_get($key, $default = null)
{
    $value = $_GET[$key] ?? $default;

    return $value;
}

function http_post($key, $default = null)
{
    $value = $_POST[$key] ?? $default;

    return $value;
}
