<?php
/*  Newsportal NNTP<->HTTP Gateway
 *  Version: 0.25
 *
 *  Copyright (C) 2002 Florian Amrhein <florian.amrhein@web.de>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

/*
 * the name and the description of a newsgroup
 */

class newsgroupType
{
    public $name;

    public $description;

    public $count;

    public $text;
}

/*
 * Stores a complete article:
 * - The parsed Header as an headerType
 * - The bodies and attachments as an array of array of lines
 */

class messageType
{
    public $header;

    public $body;
}

/*
 * Stores the Header of an article
 */

class headerType
{
    public $number; // the Number of an article inside a group
    public $id;     // Message-ID
    public $from;   // eMail of the author
    public $name;   // Name of the author
    public $subject; // the subject
    public $newsgroups;  // the Newsgroups where the article belongs to
    public $followup;

    public $date;

    public $organization;

    public $xnoarchive;

    public $references;

    public $content_transfer_encoding;

    public $mime_version;

    public $content_type;   // array, Content-Type of the Body (Index=0) and the
    // Attachments (Index>0)
    public $content_type_charset;  // like content_type
    public $content_type_name;     // array of the names of the attachments
    public $content_type_boundary; // The boundary of an multipart-article.
    public $answers;

    public $isAnswer;

    public $username;

    public $user_agent;

    public $isReply;
}

/*
 * opens the connection to the NNTP-Server
 *
 * $server: adress of the NNTP-Server
 * $port: port of the server
 */
function OpenNNTPconnection($nserver = 0, $nport = 0)
{
    global $text_error, $server_auth_user, $server_auth_pass, $readonly;

    global $server, $port, $feedback;

    $authorize = ((isset($server_auth_user)) && (isset($server_auth_pass))
                  && ('' != $server_auth_user));

    if (0 == $nserver) {
        $nserver = $server;
    }

    if (0 == $nport) {
        $nport = $port;
    }

    $ns = fsockopen($nserver, $nport);

    $weg = lieszeile($ns);  // kill the first line

    if ('20' != mb_substr($weg, 0, 2)) {
        $feedback = '<p>' . $text_error['error:'] . $weg . '</p>';
    } else {
        if (false !== $ns) {
            fwrite($ns, "mode reader\r\n");

            $weg = lieszeile($ns);  // and once more

            if (('20' != mb_substr($weg, 0, 2))
                && ((!$authorize) || (('480' != mb_substr($weg, 0, 3)) && ($authorize)))) {
                $feedback = '<p>' . $text_error['error:'] . $weg . '</p>';
            }
        }

        if ((isset($server_auth_user)) && (isset($server_auth_pass))
            && ('' != $server_auth_user)) {
            fwrite($ns, "authinfo user $server_auth_user\r\n");

            $weg = lieszeile($ns);

            fwrite($ns, "authinfo pass $server_auth_pass\r\n");

            $weg = lieszeile($ns);

            if ('281' != mb_substr($weg, 0, 3)) {
                $feedback .= '<p>' . $text_error['error:'] . '</p>';

                $feedback .= '<p>' . $text_error['auth_error'] . '</p>';
            }
        }
    }

    if (false === $ns) {
        $feedback .= '<p>' . $text_error['connection_failed'] . '</p>';
    }

    return $ns;
}

/*
 * Close a NNTP connection
 *
 * $ns: the handle of the connection
 */
function closeNNTPconnection($ns)
{
    if (false !== $ns) {
        fwrite($ns, "quit\r\n");

        fclose($ns);
    }
}

/*
 * decodes a block of 7bit-data in uuencoded format to it's original
 * 8bit format.
 * The headerline containing filename and permissions doesn't have to
 * be included.
 *
 * $data: The uuencoded data as a string
 *
 * returns the 8bit data as a string
 *
 * Note: this function is very slow and doesn't recognize incorrect code.
 */
function uudecode_line($line)
{
    $data = mb_substr($line, 1);

    $length = ord($line[0]) - 32;

    $decoded = '';

    for ($i = 0; $i < (mb_strlen($data) >> 2); $i++) {
        $pack = mb_substr($data, $i << 2, 4);

        $upack = '';

        $bitmaske = 0;

        for ($o = 0; $o < 4; $o++) {
            $g = ((ord($pack[3 - $o]) - 32));

            if (64 == $g) {
                $g = 0;
            }

            $bitmaske |= ($g << (6 * $o));
        }

        $schablone = 255;

        for ($o = 0; $o < 3; $o++) {
            $c = ($bitmaske & $schablone) >> ($o << 3);

            $schablone <<= 8;

            $upack = chr($c) . $upack;
        }

        $decoded .= $upack;
    }

    $decoded = mb_substr($decoded, 0, $length);

    return $decoded;
}

/*
 * decodes uuencoded Attachments.
 *
 * $data: the encoded data
 *
 * returns the decoded data
 */
function uudecode($data)
{
    $d = explode("\n", $data);

    $u = '';

    for ($i = 0; $i < count($d) - 1; $i++) {
        $u .= uudecode_line($d[$i]);
    }

    return $u;
}

/*
 * returns the mimetype of an filename
 *
 * $name: the complete filename of a file
 *
 * returns a string containing the mimetype
 */
function get_mimetype_by_filename($name)
{
    $ending = mb_strtolower(mb_strrchr($name, '.'));

    switch ($ending) {
        case '.jpg':
            $type = 'image/jpeg';
            break;
        case '.gif':
            $type = 'image/gif';
            break;
        default:
            $type = 'text/plain';
    }

    return $type;
}

/*
 * Shows the little menu on the thread.php where you can select the
 * different pages with the articles on it
 */
function showPageSelectMenu($group, $article_count, $first)
{
    echo getPageSelectMenu($group, $article_count, $first);
}

function getPageSelectMenu($group, $article_count, $first)
{
    global $articles_per_page, $file_thread, $file_framethread, $name, $num_page_tabs, $group_id;

    /*  if (isset($file_framethread)) {
    $thread=$file_framethread;
  } else {
    $thread=$file_thread;
  }
*/

    //	$if($articles_per_page == 0) return false;

    $return = '';

    $pages = floor($article_count / $articles_per_page);

    if ($pages < $num_page_tabs) {
        $num_page_tabs = $pages;
    }

    $current_tab = ($first - 1) / $articles_per_page;

    $start_tab = $current_tab - floor($num_page_tabs / 2);

    if ($start_tab < 0) {
        $start_tab = 0;
    }

    if ($pages - $start_tab + 1 < $num_page_tabs) {
        $start_tab = $pages - $num_page_tabs + 1;
    }

    if ($article_count > $articles_per_page) {
        if (0 != $start_tab) {
            $return .= '<a href="' . $file_thread . '?group_id=' . $group_id . '&group=' . $group . '">1</a> ';
        }

        if ($start_tab > 1) {
            $return .= '.. ';
        }

        for ($i = $start_tab; $i < $num_page_tabs + $start_tab; $i++) {
            //echo '[';
            if ($i != $current_tab) {//($first != $i*$articles_per_page+1)
                $return .= '<a href="' . $file_thread . '?group_id=' . $group_id . '&group=' . $group . '&amp;first=' . ($i * $articles_per_page + 1) . '&amp;last=' . ($i + 1) * $articles_per_page . '">';
            } else {
                $return .= '<b>';
            }

            //echo ($i*$articles_per_page+1).'-';

            //if ($i == $pages) {

            //  echo $article_count;

            //} else {

            //  echo ($i+1)*$articles_per_page;

            //}

            $return .= ($i + 1) . ' ';

            if ($i != $current_tab) {//($first != $i*$articles_per_page+1)
                $return .= '</a>';
            } else {
                $return .= '</b>';
            }

            //echo '] ';
        }

        $return .= ' of ' . $pages;
    }

    return $return;
}

/*
 * Test, if the access to a group is allowed. This is true, if $testgroup is
 * false or the groupname is in groups.txt
 *
 * $groupname: name of the group to be checked
 *
 * returns true, if access is allowed
 */
function testGroup($groupname)
{
    global $testgroup;

    if ($testgroup) {
        $gf = fopen('groups.txt', 'rb');

        while (!feof($gf)) {
            $read = trim(lieszeile($gf));

            $pos = mb_strpos($read, ' ');

            if (false !== $pos) {
                if (mb_substr($read, 0, $pos) == trim($groupname)) {
                    return true;
                }
            } else {
                if ($read == trim($groupname)) {
                    return true;
                }
            }
        }

        fclose($gf);

        return false;
    }
  

    return true;
}

function testGroups($newsgroups)
{
    $groups = explode(',', $newsgroups);

    $count = count($groups);

    $return = '';

    $o = 0;

    for ($i = 0; $i < $count; $i++) {
        if (testGroup($groups[$i])) {
            if ($o > 0) {
                $return .= ',';
            }

            $o++;

            $return .= $groups[$i];
        }
    }

    return ($return);
}

/*
 * read one line from the NNTP-server
 */
function lieszeile($ns)
{
    if (false !== $ns) {
        $t = str_replace("\n", '', str_replace("\r", '', fgets($ns, 1200)));

        return $t;
    }
}

function readGroups($server, $port)
{
    $ns = OpenNNTPconnection($server, $port);

    if (false === $ns) {
        return false;
    }

    $gf = fopen('groups.txt', 'rb');

    while (!feof($gf)) {
        $gruppe = new newsgroupType();

        $tmp = trim(lieszeile($gf));

        if (':' == mb_substr($tmp, 0, 1)) {
            $gruppe->text = mb_substr($tmp, 1);

            $newsgroups[] = $gruppe;
        } else {
            $pos = mb_strpos($tmp, ' ');

            if (false !== $pos) {
                $gruppe->name = mb_substr($tmp, 0, $pos);

                $desc = mb_substr($tmp, $pos);
            } else {
                $gruppe->name = $tmp;

                fwrite($ns, "xgtitle $gruppe->name\r\n");

                $response = lieszeile($ns);

                if (0 == strcmp(mb_substr($response, 0, 3), '282')) {
                    $neu = lieszeile($ns);

                    do {
                        $response = $neu;

                        if ('.' != $neu) {
                            $neu = lieszeile($ns);
                        }
                    } while ('.' != $neu);

                    $desc = mb_strrchr($response, "\t");

                    if (0 == strcmp($response, '.')) {
                        $desc = '-';
                    }
                } else {
                    $desc = $response;
                }

                if (0 == strcmp(mb_substr($response, 0, 3), '500')) {
                    $desc = '-';
                }
            }

            if (0 == strcmp($desc, '')) {
                $desc = '-';
            }

            $gruppe->description = $desc;

            fwrite($ns, 'group ' . $gruppe->name . "\r\n");

            $response = lieszeile($ns);

            $t = strstr($response, ' ');

            $t = mb_substr($t, 1, mb_strlen($t) - 1);

            $gruppe->count = mb_substr($t, 0, mb_strpos($t, ' '));

            if ((0 != strcmp(trim($gruppe->name), ''))
                && ('#' != mb_substr($gruppe->name, 0, 1))) {
                $newsgroups[] = $gruppe;
            }
        }
    }

    fclose($gf);

    closeNNTPconnection($ns);

    return $newsgroups;
}

/*
 * print the group names from an array to the webpage
 */
function showgroups($gruppen)
{
    if (false === $gruppen) {
        return;
    }

    global $file_thread, $text_groups, $group_id;

    $c = count($gruppen);

    echo "<table>\n";

    echo '<tr><td>#</td><td>' . $text_groups['newsgroup'] . '</td><td>' . $text_groups['description'] . "</td></tr>\n";

    for ($i = 0; $i < $c; $i++) {
        $g = $gruppen[$i];

        echo '<tr>';

        if (isset($g->text)) {
            echo '<td colspan="3">' . $g->text . '</td>';
        } else {
            echo '<td>';

            echo "$g->count</td><td>";

            echo '<a ';

            if ((isset($frame_threadframeset)) && ('' != $frame_threadframeset)) {
                echo 'target="' . $frame_threadframeset . '" ';
            }

            echo 'href="' . $file_thread . '?group_id=' . $group_id . '&group=' . urlencode($g->name) . '">' . $g->name . "</a></td>\n";

            echo "<td>$g->description</td>";
        }

        echo "</tr>\n";

        flush();
    }

    echo "</table>\n";
}

/*
 * gets a list of aviable articles in the group $groupname
 */
function getArticleList($von, $groupname)
{
    fwrite($von, "listgroup $groupname \r\n");

    $zeile = lieszeile($von);

    $zeile = lieszeile($von);

    while (0 != strcmp($zeile, '.')) {
        $articleList[] = trim($zeile);

        $zeile = lieszeile($von);
    }

    if (!isset($articleList)) {
        $articleList = '-';
    }

    return $articleList;
}

/*
 * Decode quoted-printable or base64 encoded headerlines
 *
 * $value: The to be decoded line
 *
 * returns the decoded line
 */
function headerDecode($value)
{
    if (eregi('=\?.*\?.\?.*\?=', $value)) { // is there anything encoded?
        if (eregi('=\?.*\?Q\?.*\?=', $value)) {  // quoted-printable decoding
            $result1 = eregi_replace('(.*)=\?.*\?Q\?(.*)\?=(.*)', '\1', $value);

            $result2 = eregi_replace('(.*)=\?.*\?Q\?(.*)\?=(.*)', '\2', $value);

            $result3 = eregi_replace('(.*)=\?.*\?Q\?(.*)\?=(.*)', '\3', $value);

            $result2 = str_replace('_', ' ', quoted_printable_decode($result2));

            $newvalue = $result1 . $result2 . $result3;
        }

        if (eregi('=\?.*\?B\?.*\?=', $value)) {  // base64 decoding
            $result1 = eregi_replace('(.*)=\?.*\?B\?(.*)\?=(.*)', '\1', $value);

            $result2 = eregi_replace('(.*)=\?.*\?B\?(.*)\?=(.*)', '\2', $value);

            $result3 = eregi_replace('(.*)=\?.*\?B\?(.*)\?=(.*)', '\3', $value);

            $result2 = base64_decode($result2, true);

            $newvalue = $result1 . $result2 . $result3;
        }

        if (!isset($newvalue)) { // nothing of the above, must be an unknown encoding...
            $newvalue = $value;
        } else {
            $newvalue = headerDecode($newvalue);
        }  // maybe there are more encoded
        return ($newvalue);                    // parts
    }     // there wasn't anything encoded, return the original string
        return ($value);
}

/*
 * calculates an Unix timestamp out of a Date-Header in an article
 *
 * $value: Value of the Date: header
 *
 * returns an Unix timestamp
 */
function getTimestamp($value)
{
    $months = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12];

    $value = str_replace('  ', ' ', $value);

    $d = preg_split(' ', $value, 5);

    if (0 == strcmp(mb_substr($d[0], mb_strlen($d[0]) - 1, 1), ',')) {
        $date[0] = $d[1];  // day
        $date[1] = $d[2];  // month
        $date[2] = $d[3];  // year
        $date[3] = $d[4];  // hours:minutes:seconds
    } else {
        $date[0] = $d[0];  // day
        $date[1] = $d[1];  // month
        $date[2] = $d[2];  // year
        $date[3] = $d[3];  // hours:minutes:seconds
    }

    $time = preg_split(':', $date[3]);

    $timestamp = mktime($time[0], $time[1], $time[2], $months[$date[1]], $date[0], $date[2]);

    return $timestamp;
}

function parse_header($hdr, $number = '')
{
    for ($i = count($hdr) - 1; $i > 0; $i--) {
        if (preg_match("/^(\x09|\x20)/", $hdr[$i])) {
            $hdr[$i - 1] .= ' ' . ltrim($hdr[$i]);
        }
    }

    $header = new headerType();

    $header->isAnswer = false;

    for ($count = 0, $countMax = count($hdr); $count < $countMax; $count++) {
        $variable = mb_substr($hdr[$count], 0, mb_strpos($hdr[$count], ' '));

        $value = trim(mb_substr($hdr[$count], mb_strpos($hdr[$count], ' ') + 1));

        switch (mb_strtolower($variable)) {
            case 'from:':
                $fromline = address_decode(headerDecode($value), 'nirgendwo');
                if (!isset($fromline[0]['host'])) {
                    $fromline[0]['host'] = '';
                }
                $header->from = $fromline[0]['mailbox'] . '@' . $fromline[0]['host'];
                $header->username = $fromline[0]['mailbox'];
                if (!isset($fromline[0]['personal'])) {
                    $header->name = '';
                } else {
                    $header->name = $fromline[0]['personal'];
                }
                break;
            case 'message-id:':
                $header->id = $value;
                break;
            case 'subject:':
                $header->subject = headerDecode($value);
                break;
            case 'newsgroups:':
                $header->newsgroups = $value;
                break;
            case 'organization:':
                $header->organization = $value;
                break;
            case 'content-transfer-encoding:':
                $header->content_transfer_encoding = trim(mb_strtolower($value));
                break;
            case 'content-type:':
                $header->content_type = [];
                $subheader = preg_split(';', $value);
                $header->content_type[0] = mb_strtolower(trim($subheader[0]));
                for ($i = 1, $iMax = count($subheader); $i < $iMax; $i++) {
                    $gleichpos = mb_strpos($subheader[$i], '=');

                    if ($gleichpos) {
                        $subvariable = trim(mb_substr($subheader[$i], 0, $gleichpos));

                        $subvalue = trim(mb_substr($subheader[$i], $gleichpos + 1));

                        if (('"' == $subvalue[0])
                            && ('"' == $subvalue[mb_strlen($subvalue) - 1])) {
                            $subvalue = mb_substr($subvalue, 1, -1);
                        }

                        switch ($subvariable) {
                            case 'charset':
                                $header->content_type_charset = [mb_strtolower($subvalue)];
                                break;
                            case 'name':
                                $header->content_type_name = [$subvalue];
                                break;
                            case 'boundary':
                                $header->content_type_boundary = $subvalue;
                        }
                    }
                }
                break;
            case 'references:':
                $ref = trim($value);
                while (false !== mb_strpos($ref, '> <')) {
                    $header->references[] = mb_substr($ref, 0, mb_strpos($ref, ' '));

                    $ref = mb_substr($ref, mb_strpos($ref, '> <') + 2);
                }
                $header->references[] = trim($ref);
                break;
            case 'date:':
                $header->date = getTimestamp(trim($value));
                break;
            case 'followup-to:':
                $header->followup = trim($value);
                break;
            case 'x-newsreader:':
            case 'x-mailer:':
            case 'user-agent:':
                $header->user_agent = trim($value);
                break;
            case 'x-face:': // not ready
                //          echo "<p>-".base64_decode($value)."-</p>";
                break;
            case 'x-no-archive:':
                $header->xnoarchive = mb_strtolower(trim($value));
        }
    }

    if (!isset($header->content_type[0])) {
        $header->content_type[0] = 'text/plain';
    }

    if (!isset($header->content_transfer_encoding)) {
        $header->content_transfer_encoding = '8bit';
    }

    if ('' != $number) {
        $header->number = $number;
    }

    return $header;
}

function decode_body($body, $encoding)
{
    $bodyzeile = '';

    switch ($encoding) {
        case 'base64':
            $body = base64_decode($body, true);
            break;
        case 'quoted-printable':
            $body = quoted_printable_decode($body);
            $body = str_replace("=\n", '', $body);
        //    default:
        //      $body=str_replace("\n..\n","\n.\n",$body);
    }

    return $body;
}

function parse_message($rawmessage)
{
    global $attachment_delete_alternative, $attachment_uudecode;

    // Read the header of the message:

    $count_rawmessage = count($rawmessage);

    $message = new messageType();

    $rawheader = [];

    $i = 0;

    while ('' != $rawmessage[$i]) {
        $rawheader[] = $rawmessage[$i];

        $i++;
    }

    // Parse the Header:

    $message->header = parse_header($rawheader);

    // Now we know if the message is a mime-multipart message:

    $content_type = preg_split('/', $message->header->content_type[0]);

    if ('multipart' == $content_type[0]) {
        $message->header->content_type = [];

        // We have multible bodies, so we split the message into its parts

        $boundary = '--' . $message->header->content_type_boundary;

        // lets find the first part

        while ($rawmessage[$i] != $boundary) {
            $i++;
        }

        $i++;

        $part = [];

        while ($i <= $count_rawmessage) {
            if (($rawmessage[$i] == $boundary) || ($i == $count_rawmessage - 1)
                || ($rawmessage[$i] == $boundary . '--')) {
                $partmessage = parse_message($part);

                // merge the content-types of the message with those of the part

                for ($o = 0, $oMax = count($partmessage->header->content_type); $o < $oMax; $o++) {
                    $message->header->content_type[] = $partmessage->header->content_type[$o];

                    $message->header->content_type_charset[] = $partmessage->header->content_type_charset[$o];

                    $message->header->content_type_name[] = $partmessage->header->content_type_name[$o];

                    $message->body[] = $partmessage->body[$o];
                }

                $part = [];
            } else {
                if ($i < $count_rawmessage) {
                    $part[] = $rawmessage[$i];
                }
            }

            if ($rawmessage[$i] == $boundary . '--') {
                break;
            }

            $i++;
        }

        // Is this a multipart/alternative multipart-message? Do we have to

        // delete all non plain/text parts?

        if (($attachment_delete_alternative)
            && ('alternative' == $content_type[1])) {
            $plaintext = false;

            for ($o = 0, $oMax = count($message->header->content_type); $o < $oMax; $o++) {
                if ('text/plain' == $message->header->content_type[$o]) {
                    $plaintext = true;
                } // we found at least one text/plain
            }

            if ($plaintext) {    // now we can delete the other parts
                for ($o = 0, $oMax = count($message->header->content_type); $o < $oMax; $o++) {
                    if ('text/plain' != $message->header->content_type[$o]) {
                        unset($message->header->content_type[$o]);

                        unset($message->header->content_type_name[$o]);

                        unset($message->header->content_type_charset[$o]);

                        unset($message->body[$o]);
                    }
                }
            }
        }
    } else {
        // No mime-attachments in the message:

        $body = '';

        $uueatt = 0; // as default we have no uuencoded attachments

        for ($i++; $i < $count_rawmessage; $i++) {
            // do we have an inlay uuencoded file?

            if (('begin' != mb_strtolower(mb_substr($rawmessage[$i], 0, 5)))
                || (false === $attachment_uudecode)) {
                $body .= $rawmessage[$i] . "\n";

            // yes, it seems, we have!
            } else {
                $old_i = $i;

                $uue_infoline_raw = $rawmessage[$i];

                $uue_infoline = explode(' ', $uue_infoline_raw);

                $uue_data = '';

                $i++;

                while ('end' != $rawmessage[$i]) {
                    if (mb_strlen(trim($rawmessage[$i])) > 2) {
                        $uue_data .= $rawmessage[$i] . "\n";
                    }

                    $i++;
                }

                // now write the data in an attachment

                $uueatt++;

                $message->body[$uueatt] = uudecode($uue_data);

                $message->header->content_type_name[$uueatt] = '';

                for ($o = 2, $oMax = count($uue_infoline); $o < $oMax; $o++) {
                    $message->header->content_type_name[$uueatt] .= $uue_infoline[$o];
                }

                $message->header->content_type[$uueatt] = get_mimetype_by_filename($message->header->content_type_name[$uueatt]);
            }
        }

        if ('text/plain' == $message->header->content_type[0]) {
            $body = trim($body);

            if ('' == $body) {
                $body = ' ';
            }
        }

        $body = decode_body($body, $message->header->content_transfer_encoding);

        $message->body[0] = $body;
    }

    if (!isset($message->header->content_type_charset)) {
        $message->header->content_type_charset = ['ISO-8859-1'];
    }

    if (!isset($message->header->content_type_name)) {
        $message->header->content_type_name = ['unnamed'];
    }

    for ($o = 0, $oMax = count($message->body); $o < $oMax; $o++) {
        if (!isset($message->header->content_type_charset[$o])) {
            $message->header->content_type_charset[$o] = 'ISO-8859-1';
        }

        if (!isset($message->header->content_type_name[$o])) {
            $message->header->content_type_name[$o] = 'unnamed';
        }
    }

    return $message;
}

/*
 * remove an article from the overview-file
 * is needed, when article has been canceled, the article is still
 * in the thread spool on disc and someone wants to read this article.
 * the read_message function can now call this function to remove
 * the article now.
 */
function removeArticlefromOverview($group, $id)
{
    $thread = loadThreadData($group);

    if (!$thread) {
        return false;
    }

    $changed = false;

    foreach ($thread as $value) {
        if (($value->number == $id) || ($value->id == $id)) {
            // found to be deleted article

            // now lets rebuild the tree...

            if (isset($value->answers)) {
                foreach ($value->answers as $key => $answer) {
                    $thread[$answer]->isAnswer = false;
                }
            }

            if (isset($value->references)) {
                foreach ($value->references as $reference) {
                    if (isset($thread[$reference]->answers)) {
                        $search = array_search($value->id, $thread[$reference]->answers, true);

                        if (!(false === $search)) {
                            unset($thread[$reference]->answers[$search]);
                        }
                    }
                }
            }

            unset($thread[$value->id]);

            $changed = true;

            break;
        }
    }

    if ($changed) {
        saveThreadData($thread, $group);
    }
}

/*
 * read an article from the newsserver or the spool-directory
 *
 * $id: the Message-ID of an article
 * $bodynum: the number of the attachment:
 *          -1: return only the header without any bodies or attachments.
 *           0: the body
 *           1: the first attachment...
 *
 * The function returns an article as an messageType or false if the article
 * doesn't exists on the newsserver or doesn't contain the given
 * attachment.
 */
function read_message($id, $bodynum = 0, $group = '')
{
    global $cache_articles, $spooldir, $text_error, $ns;

    if (!testGroup($group)) {
        echo $text_error['read_access_denied'];

        return;
    }

    $message = new messageType();

    if ((isset($cache_articles)) && (true === $cache_articles)) {
        // Try to load a cached article

        if ((preg_match('^[0-9]+$', $id)) && ('' != $group)) {
            $filename = $group . '_' . $id;
        } else {
            $filename = base64_encode($id);
        }

        $cachefilename_header = $spooldir . '/' . $filename . '.header';

        $cachefilename_body = $spooldir . '/' . $filename . '.body';

        if (file_exists($cachefilename_header)) {
            $cachefile = fopen($cachefilename_header, 'rb');

            $message->header = unserialize(fread($cachefile, filesize($cachefilename_header)));

            fclose($cachefile);
        } else {
            unset($message->header);
        }

        // Is a non-existing attachment of an article requested?

        if ((isset($message->header))
            && (-1 != $bodynum)
            && (!isset($message->header->content_type[$bodynum]))) {
            return false;
        }

        if ((file_exists($cachefilename_body . $bodynum))
            && (-1 != $bodynum)) {
            $cachefile = fopen($cachefilename_body . $bodynum, 'rb');

            $message->body[$bodynum] = fread($cachefile, filesize($cachefilename_body . $bodynum));

            fclose($cachefile);
        }
    }

    if ((!isset($message->header))
        || ((!isset($message->body[$bodynum]))
            && (-1 != $bodynum))) {
        if (!isset($ns)) {
            $ns = OpenNNTPconnection();
        }

        if ('' != $group) {
            fwrite($ns, 'group ' . $group . "\r\n");

            $zeile = lieszeile($ns);
        }

        fwrite($ns, 'article ' . $id . "\r\n");

        $zeile = lieszeile($ns);

        if ('220' != mb_substr($zeile, 0, 3)) {
            // requested article doesn't exist on the newsserver. Now we

            // should check, if the thread stored in the spool-directory

            // also doesnt't contain that article...

            removeArticlefromOverview($group, $id);

            return false;
        }

        $rawmessage = [];

        $line = lieszeile($ns);

        while (0 != strcmp($line, '.')) {
            $rawmessage[] = $line;

            $line = lieszeile($ns);
        }

        $message = parse_message($rawmessage);

        if (preg_match('^[0-9]+$', $id)) {
            $message->header->number = $id;
        }

        // write header, body and attachments to the cache

        if ((isset($cache_articles)) && (true === $cache_articles)) {
            $cachefile = fopen($cachefilename_header, 'wb');

            if ($cachefile) {
                fwrite($cachefile, serialize($message->header));
            }

            fclose($cachefile);

            for ($i = 0, $iMax = count($message->header->content_type); $i < $iMax; $i++) {
                if (isset($message->body[$i])) {
                    $cachefile = fopen($cachefilename_body . $i, 'wb');

                    fwrite($cachefile, $message->body[$i]);

                    fclose($cachefile);
                }
            }
        }
    }

    return $message;
}

function textwrap($text, $wrap = 80, $break = "\n")
{
    $len = mb_strlen($text);

    if ($len > $wrap) {
        $h = '';        // massaged text
        $lastWhite = 0; // position of last whitespace char
        $lastChar = 0;  // position of last char
        $lastBreak = 0; // position of last break
        // while there is text to process
        while ($lastChar < $len) {
            $char = mb_substr($text, $lastChar, 1); // get the next character

            // if we are beyond the wrap boundry and there is a place to break

            if (($lastChar - $lastBreak > $wrap) && ($lastWhite > $lastBreak)) {
                $h .= mb_substr($text, $lastBreak, ($lastWhite - $lastBreak)) . $break;

                $lastChar = $lastWhite + 1;

                $lastBreak = $lastChar;
            }

            // You may wish to include other characters as valid whitespace...

            if (' ' == $char || $char == chr(13) || $char == chr(10)) {
                $lastWhite = $lastChar; // note the position of the last whitespace
            }

            $lastChar += 1; // advance the last character position by one
        }

        $h .= mb_substr($text, $lastBreak); // build line
    } else {
        $h = $text; // in this case everything can fit on one line
    }

    return $h;
}

/*
 * makes URLs clickable
 *
 * $comment: A text-line probably containing links.
 *
 * the function returns the text-line with HTML-Links to the links or
 * email-adresses.
 */
function html_parse($comment)
{
    global $frame_externallink;

    if ((isset($frame_externallink)) && ('' != $frame_externallink)) {
        $target = ' TARGET="' . $frame_externallink . '" ';
    } else {
        $target = ' ';
    }

    $ncomment = eregi_replace('http://([-a-z0-9_./~@?=%#&;]+)', '<a' . $target . 'href="http://\1">http://\1</a>', $comment);

    if ($ncomment == $comment) {
        $ncomment = eregi_replace('(www\.[-a-z]+\.(de|int|eu|dk|org|net|at|ch|com))', '<a' . $target . 'href="http://\1">\1</a>', $comment);
    }

    $comment = $ncomment;

    $comment = eregi_replace('https://([-a-z0-9_./~@?=%#&;\n]+)', '<a' . $target . 'href="https://\1">https://\1</a>', $comment);

    $comment = eregi_replace('gopher://([-a-z0-9_./~@?=%\n]+)', '<a' . $target . 'href="gopher://\1">gopher://\1</a>', $comment);

    $comment = eregi_replace('news://([-a-z0-9_./~@?=%\n]+)', '<a' . $target . 'href="news://\1">news://\1</a>', $comment);

    $comment = eregi_replace('ftp://([-a-z0-9_./~@?=%\n]+)', '<a' . $target . 'href="ftp://\1">ftp://\1</a>', $comment);

    $comment = eregi_replace('([-a-z0-9_./n]+)@([-a-z0-9_.]+)', '<a href="mailto:\1@\2">\1@\2</a>', $comment);

    return ($comment);
}

/*
 * read the header of an article in plaintext into an array
 * $articleNumber can be the number of an article or its message-id.
 */
function readPlainHeader($von, $group, $articleNumber)
{
    fwrite($von, "group $group\r\n");

    $zeile = lieszeile($von);

    fwrite($von, "head $articleNumber\r\n");

    $zeile = lieszeile($von);

    if ('221' != mb_substr($zeile, 0, 3)) {
        echo $text_error['article_not_found'];

        $header = false;
    } else {
        $zeile = lieszeile($von);

        $body = '';

        while (0 != strcmp(trim($zeile), '.')) {
            $body .= $zeile . "\n";

            $zeile = lieszeile($von);
        }

        return preg_split("\n", str_replace("\r\n", "\n", $body));
    }
}

function readArticles(&$von, $groupname, $articleList)
{
    for ($i = 0; $i <= count($articleList) - 1; $i++) {
        $temp = read_header($von, $articleList[$i]);

        $articles[$temp->id] = $temp;
    }

    return $articles;
}

/*
 * Remove re:, aw: etc. from a subject.
 *
 * $subject: a string containing the complete Subject
 *
 * The function removes the re:, aw: etc. from $subject end returns true
 * if it removed anything, and false if not.
 */
function splitSubject(&$subject)
{
    $s = eregi_replace('^(aw:|re:|re\[2\]:| )+', '', $subject);

    $return = ($s != $subject);

    $subject = $s;

    return $return;
}

function interpretOverviewLine($zeile, $overviewformat, $groupname)
{
    $return = '';

    $overviewfmt = explode("\t", $overviewformat);

    echo ' ';                // keep the connection to the webbrowser alive
    flush();                 // while generating the message-tree
    $over = preg_split("\t", $zeile, count($overviewfmt) - 1);

    $article = new headerType();

    for ($i = 0; $i < count($overviewfmt) - 1; $i++) {
        if ('Subject:' == $overviewfmt[$i]) {
            $subject = headerDecode($over[$i + 1]);

            $article->isReply = splitSubject($subject);

            $article->subject = $subject;
        }

        if ('Date:' == $overviewfmt[$i]) {
            $article->date = getTimestamp($over[$i + 1]);
        }

        if ('From:' == $overviewfmt[$i]) {
            $fromline = address_decode(headerDecode($over[$i + 1]), 'nirgendwo');

            $article->from = $fromline[0]['mailbox'] . '@' . $fromline[0]['host'];

            $article->username = $fromline[0]['mailbox'];

            if (!isset($fromline[0]['personal'])) {
                $article->name = $fromline[0]['mailbox'];

                if (mb_strpos($article->name, '%')) {
                    $article->name = mb_substr($article->name, 0, mb_strpos($article->name, '%'));
                }

                $article->name = strtr($article->name, '_', ' ');
            } else {
                $article->name = $fromline[0]['personal'];
            }
        }

        if ('Message-ID:' == $overviewfmt[$i]) {
            $article->id = $over[$i + 1];
        }

        if (('References:' == $overviewfmt[$i]) && ('' != $over[$i + 1])) {
            $article->references = explode(' ', $over[$i + 1]);
        }
    }

    $article->number = $over[0];

    $article->isAnswer = false;

    return ($article);
}

function getOldThreads($von, $groupname, $poll, $first, $last)
{
    $first -= 30;

    $last += 30;

    fwrite($von, "list overview.fmt\r\n");  // find out the format of the
    $tmp = lieszeile($von);                 // xover-command
    $zeile = lieszeile($von);

    while (0 != strcmp($zeile, '.')) {
        $overviewfmt[] = $zeile;

        $zeile = lieszeile($von);
    }

    $overviewformat = implode("\t", $overviewfmt);

    fwrite($von, "group $groupname\r\n");   // select a group

    $groupinfo = explode(' ', lieszeile($von));

    if (2 != mb_substr($groupinfo[0], 0, 1)) {
        echo '<p>' . $text_error['error:'] . '</p>';

        echo '<p>' . $text_thread['no_such_group'] . '</p>';

        flush();
    } else {
        $lastarticle = $groupinfo[3] - $first + 1; //get a few extra articles on each side of what is really
        $firstarticle = $groupinfo[3] - $last + 1; //desired incase the thread crosses pages
        fwrite($von, 'xover ' . $firstarticle . '-' . $lastarticle . "\r\n");  // and read the overview
        $tmp = lieszeile($von);

        if ('224' == mb_substr($tmp, 0, 3)) {
            $zeile = lieszeile($von);

            while ('.' != $zeile) {
                $article = interpretOverviewLine($zeile, $overviewformat, $groupname);

                $headers[$article->id] = $article;

                if ($poll) {
                    echo $article->number . ', ';

                    flush();

                    read_message($article->number, 0, $groupname);
                }

                $zeile = lieszeile($von);
            }
        }

        if ((isset($headers)) && (count($headers) > 0)) {
            foreach ($headers as $c) {
                if ((false === $c->isAnswer) && (isset($c->references))) {   // is the article an answer to an
                    // other article?

                    // try to find a matching article to one of the references

                    $refmatch = false;

                    foreach ($c->references as $reference) {
                        if (isset($headers[$reference])) {
                            $refmatch = $reference;
                        }
                    }

                    if (false !== $refmatch) {
                        // the article itself is a answer to another article

                        $c->isAnswer = true;

                        $headers[$c->id] = $c;

                        // the referenced article get the ID af this article as in

                        // his answers-array

                        $headers[$refmatch]->answers[] = $c->id;
                    }
                }
            }

            reset($headers);
        }

        return ($headers ?? false);
    }
}

/*
 * Rebuild the Overview-File
 */
function rebuildOverview($von, $groupname, $poll)
{
    global $spooldir, $maxarticles, $maxfetch, $initialfetch, $maxarticles_extra;

    global $text_error, $text_thread, $compress_spoolfiles, $server;

    $idstring = '0.22,' . $server . ',' . $compress_spoolfiles . ',' . $maxarticles . ',' . $maxarticles_extra . ',' . $maxfetch . ',' . $initialfetch;

    fwrite($von, "list overview.fmt\r\n");  // find out the format of the
    $tmp = lieszeile($von);                 // xover-command
    $zeile = lieszeile($von);

    while (0 != strcmp($zeile, '.')) {
        $overviewfmt[] = $zeile;

        $zeile = lieszeile($von);
    }

    $overviewformat = implode("\t", $overviewfmt);

    $spoolfilename = $spooldir . '/' . $groupname . '-data.dat';

    fwrite($von, "group $groupname\r\n");   // select a group

    $groupinfo = explode(' ', lieszeile($von));

    if (2 != mb_substr($groupinfo[0], 0, 1)) {
        echo '<p>' . $text_error['error:'] . '</p>';

        echo '<p>' . $text_thread['no_such_group'] . '</p>';

        flush();
    } else {
        $infofilename = $spooldir . '/' . $groupname . '-info.txt';

        $spoolopenmodus = 'n';

        if (!((file_exists($infofilename)) && (file_exists($spoolfilename)))) {
            $spoolopenmodus = 'w';
        } else {
            $infofile = fopen($infofilename, 'rb');

            $oldid = fgets($infofile, 100);

            if (trim($oldid) != $idstring) {
                echo "<!-- Database Error, rebuilding Database...-->\n";

                $spoolopenmodus = 'w';
            }

            $oldgroupinfo = explode(' ', trim(fgets($infofile, 200)));

            fclose($infofile);

            if ($groupinfo[3] < $oldgroupinfo[1]) {
                $spoolopenmodus = 'w';
            }

            if (0 == $maxarticles) {
                if ($groupinfo[2] != $oldgroupinfo[0]) {
                    $spoolopenmodus = 'w';
                }
            } else {
                if ($groupinfo[2] > $oldgroupinfo[0]) {
                    $spoolopenmodus = 'w';
                }
            }

            if (('n' == $spoolopenmodus) && ($groupinfo[3] > $oldgroupinfo[1])) {
                $spoolopenmodus = 'a';
            }
        }

        if ('a' == $spoolopenmodus) {
            $firstarticle = $oldgroupinfo[1] + 1;

            $lastarticle = $groupinfo[3];
        }

        if ('w' == $spoolopenmodus) {
            $firstarticle = $groupinfo[2];

            $lastarticle = $groupinfo[3];
        }

        if ('n' != $spoolopenmodus) {
            if (0 != $maxarticles) {
                if ('w' == $spoolopenmodus) {
                    $firstarticle = $lastarticle - $maxarticles + 1;

                    if ($firstarticle < $groupinfo[2]) {
                        $firstarticle = $groupinfo[2];
                    }
                } else {
                    if ($lastarticle - $oldgroupinfo[0] + 1 > $maxarticles + $maxarticles_extra) {
                        $firstarticle = $lastarticle - $maxarticles + 1;

                        $spoolopenmodus = 'w';
                    }
                }
            }

            if ((0 != $maxfetch) && (($lastarticle - $firstarticle + 1) > $maxfetch)) {
                if ('w' == $spoolopenmodus) {
                    $tofetch = (0 != $initialfetch) ? $initialfetch : $maxfetch;

                    $lastarticle = $firstarticle + $tofetch - 1;
                } else {
                    $lastarticle = $firstarticle + $maxfetch - 1;
                }
            }
        }

        echo '<!--openmodus: ' . $spoolopenmodus . "-->\n";

        if ('w' != $spoolopenmodus) {
            $headers = loadThreadData($groupname);
        }

        if ('n' != $spoolopenmodus) {
            fwrite($von, 'xover ' . $firstarticle . '-' . $lastarticle . "\r\n");  // and read the overview

            $tmp = lieszeile($von);

            if ('224' == mb_substr($tmp, 0, 3)) {
                $zeile = lieszeile($von);

                while ('.' != $zeile) {
                    $article = interpretOverviewLine($zeile, $overviewformat, $groupname);

                    $headers[$article->id] = $article;

                    if ($poll) {
                        echo $article->number . ', ';

                        flush();

                        read_message($article->number, 0, $groupname);
                    }

                    $zeile = lieszeile($von);
                }
            }

            if (file_exists($spoolfilename)) {
                unlink($spoolfilename);
            }

            if ((isset($headers)) && (count($headers) > 0)) {
                $infofile = fopen($infofilename, 'wb');

                if ('a' == $spoolopenmodus) {
                    $firstarticle = $oldgroupinfo[0];
                }

                fwrite($infofile, $idstring . "\n");

                fwrite($infofile, $firstarticle . ' ' . $lastarticle . "\r\n");

                fclose($infofile);

                foreach ($headers as $c) {
                    if ((false === $c->isAnswer)
                        && (isset($c->references))) {   // is the article an answer to an
                        // other article?

                        // try to find a matching article to one of the references

                        $refmatch = false;

                        foreach ($c->references as $reference) {
                            if (isset($headers[$reference])) {
                                $refmatch = $reference;
                            }
                        }

                        if (false !== $refmatch) {
                            // the article itself is a answer to another article

                            $c->isAnswer = true;

                            $headers[$c->id] = $c;

                            // the referenced article get the ID af this article as in

                            // his answers-array

                            $headers[$refmatch]->answers[] = $c->id;
                        }
                    }
                }

                reset($headers);

                saveThreadData($headers, $groupname);
            }

            // remove cached articles, that are not in this thread

            $dirhandle = opendir($spooldir);

            while ($cachefile = readdir($dirhandle)) {
                if (mb_substr($cachefile, 0, mb_strlen($groupname) + 1) == $groupname . '_') {
                    $num = eregi_replace('^(.*)_(.*)\.(.*)$', '\2', $cachefile);

                    if (($num < $firstarticle) || ($num > $lastarticle)) {
                        unlink($spooldir . '/' . $cachefile);
                    }
                }
            }
        }

        return ($headers ?? false);
    }
}

/*
 * Read the Overview.
 * Format of the overview-file:
 *    message-id
 *    date
 *    subject
 *    author
 *    email
 *    references
 */
function mycompare($a, $b)
{
    global $thread_sorting;

    if ($a->date == $b->date) {
        $r = 0;
    }

    $r = ($a->date < $b->date) ? -1 : 1;

    return $r * $thread_sorting;
}

function readOverview($von, $groupname, $readmode = 1, $poll = false, $first = 0, $last = 0)
{
    global $text_error, $maxarticles;

    global $spooldir, $thread_sorting;

    if (!testGroup($groupname)) {
        echo $text_error['read_access_denied'];

        return;
    }

    if (false === $von) {
        return false;
    }

    if ((false !== $von) && ($readmode > 0)) {
        if (0 != $first && 0 != $last) {
            $articles = getOldThreads($von, $groupname, $poll, $first, $last);
        } else {
            $articles = rebuildOverview($von, $groupname, $poll);
        }
    }

    if ((isset($articles)) && ($articles)) {
        if ((0 != $thread_sorting) && (count($articles) > 0)) {
            uasort($articles, 'mycompare');
        }

        return $articles;
    }
  

    return false;
}

function str_change($str, $pos, $char)
{
    return (mb_substr($str, 0, $pos) . $char . mb_substr($str, $pos + 1, mb_strlen($str) - $pos));
}

function getIcon($c)
{
    global $age_count, $age_time, $age_color, $age_img, $imgdir;

    $return = '';

    $currentTime = time();

    $color = '';

    if ($age_count > 0) {
        for ($t = $age_count; $t >= 1; $t--) {
            if ($currentTime - $c->date < $age_time[$t]) {
                $color = $age_color[$t];

                $image = $age_img[$t];
            }
        }
    }

    if ('' != $image) {
        $return .= "<img src='$imgdir/$image' width='25' height='13' alt='new' title='new'>&nbsp;";
    } elseif ('' != $color) {
        $return .= '<font color="' . $color . '">* </font>';
    }

    return $return;
}

function formatDate($c)
{
    global $thread_date_format;

    return date($thread_date_format, $c->date);                // format the date
}

function calculateTree($newtree, $depth, $num, $liste, $c)
{
    if ((isset($c->answers[0])) && (count($c->answers) > 0)) {
        $newtree .= '*';
    } else {
        if (1 == $depth) {
            $newtree .= 'o';
        } else {
            $newtree .= '-';
        }
    }

    if (($num == count($liste) - 1) && ($depth > 1)) {
        $newtree = str_change($newtree, $depth - 2, '`');
    }

    return ($newtree);
}

/*
 * Format the message-tree
 * Zeichen im Baum:
 *  o : leerer Kasten            k1.gif
 *  * : Kasten mit Zeichen drin  k2.gif
 *  i : vertikale Linie          I.gif
 *  - : horizontale Linie        s.gif
 *  + : T-Stueck                 T.gif
 *  ` : Winkel                   L.gif
 */
function formatTreeGraphic($newtree)
{
    global $imgdir;

    $return = '';

    for ($o = 0, $oMax = mb_strlen($newtree); $o < $oMax; $o++) {
        $return .= '<img src="' . $imgdir . '/';

        $k = mb_substr($newtree, $o, 1);

        $alt = $k;

        switch ($k) {
            case 'o':
                $return .= 'k1.gif';
                break;
            case '*':
                $return .= 'k2.gif';
                break;
            case 'i':
                $return .= 'I.gif';
                $alt = '|';
                break;
            case '-':
                $return .= 's.gif';
                break;
            case '+':
                $return .= 'T.gif';
                break;
            case '`':
                $return .= 'L.gif';
                break;
            case '.':
                $return .= 'e.gif';
                $alt = '&nbsp;';
                break;
        }

        $return .= '" alt="' . $alt . '"';

        if (0 == strcmp($k, '.')) {
            $return .= (' width="12" height="9"');
        }

        $return .= '>';
    }

    return ($return);
}

function formatTreeText($tree)
{
    $tree = str_replace('i', '|', $tree);

    $tree = str_replace('.', '&nbsp;', $tree);

    return ($tree);
}

function formatSubject($c, $group, $highlight = false)
{
    global $file_article, $thread_maxSubject, $frame_article, $group_id;

    if ($c->isReply) {
        $re = 'Re: ';
    } else {
        $re = '';
    }

    if ($highlight == $c->id) {
        $return = '<b>';
    } else {
        $return = '<a ';

        if ((isset($frame_article)) && ('' != $frame_article)) {
            $return .= 'target="' . $frame_article . '" ';
        }

        $return .= 'href="' . $file_article . '?group_id=' . $group_id . '&msg_id=' . urlencode($c->number) . '&group=' . urlencode($group) . '">';
    }

    $return .= $re . htmlspecialchars(mb_substr(trim($c->subject), 0, $thread_maxSubject), ENT_QUOTES | ENT_HTML5);

    if ($highlight == $c->id) {
        $return .= '</b>';
    } else {
        $return .= '</a>';
    }

    return ($return);
}

function formatAuthor($c)
{
    $return = '<a href="mailto:' . trim($c->from) . '">';

    if ('' != trim($c->name)) {
        $return .= htmlspecialchars(trim($c->name), ENT_QUOTES | ENT_HTML5);
    } else {
        if (isset($c->username)) {
            $s = mb_strpos($c->username, '%');

            if (false !== $s) {
                $return .= htmlspecialchars(mb_substr($c->username, 0, $s), ENT_QUOTES | ENT_HTML5);
            } else {
                $return .= htmlspecialchars($c->username, ENT_QUOTES | ENT_HTML5);
            }
        }
    }

    $return .= '</a>';

    return ($return);
}

function showThread(&$headers, $liste, $depth, $tree, $group, $article_first, $article_last, &$article_count, $highlight = false)
{
    global $thread_treestyle;

    global $thread_showDate, $thread_showSubject;

    global $thread_showAuthor, $imgdir;

    global $file_article, $thread_maxSubject;

    global $age_count, $age_time, $age_color;

    global $frame_article, $thread_fontPre, $thread_fontPost;

    if (3 == $thread_treestyle) {
        echo "\n<UL>\n";
    }

    for ($i = 0, $iMax = count($liste); $i < $iMax; $i++) {
        $c = $headers[$liste[$i]];    // read the first article

        $article_count++;

        switch ($thread_treestyle) {
            case 4:  // thread
            case 5:  // thread, graphic
            case 6:  // thread, table
            case 7:  // thread, table, graphic
                $newtree = calculateTree($tree, $depth, $i, $liste, $c);
        }

        if ((0 == $article_first)
            || (($article_count >= $article_first)
                && ($article_count <= $article_last))) {
            switch ($thread_treestyle) {
                case 0: // simple list
                    echo $thread_fontPre;
                    if ($thread_showDate) {
                        echo formatDate($c) . ' ';
                    }
                    if ($thread_showSubject) {
                        echo formatSubject($c, $group) . ' ';
                    }
                    if ($thread_showAuthor) {
                        echo '(' . formatAuthor($c) . ')';
                    }
                    echo $thread_fontPost;
                    echo "<br>\n";
                    break;
                case 1: // html-auflistung, kein baum
                    echo '<li><nobr>' . $thread_fontPre;
                    if ($thread_showDate) {
                        echo formatDate($c) . ' ';
                    }
                    if ($thread_showSubject) {
                        echo formatSubject($c, $group, $highlight) . ' ';
                    }
                    if ($thread_showAuthor) {
                        echo '<i>(' . formatAuthor($c) . ')</i>';
                    }
                    echo $thread_fontPost . '</nobr></li>';
                    break;
                case 2:   // table
                    echo '<tr>';
                    if ($thread_showDate) {
                        echo '<td>' . $thread_fontPre . formatDate($c) . ' ' . $thread_fontPost . '</td>';
                    }
                    if ($thread_showSubject) {
                        echo '<td nowrap="nowrap">' . $thread_fontPre . formatSubject($c, $group, $highlight);

                        echo $thread_fontPost . '</td>';
                    }
                    if ($thread_showAuthor) {
                        echo '<td></td>';

                        echo '<td nowrap="nowrap">' . $thread_fontPre . formatAuthor($c);

                        echo $thread_fontPost . '</td>';
                    }
                    echo "</tr>\n";
                    break;
                case 3: // html-tree
                    echo '<li><nobr>' . $thread_fontPre;
                    if ($thread_showDate) {
                        echo formatDate($c) . ' ';
                    }
                    if ($thread_showSubject) {
                        echo formatSubject($c, $group, $highlight) . ' ';
                    }
                    if ($thread_showAuthor) {
                        echo '<i>(' . formatAuthor($c) . ')</i>';
                    }
                    echo $thread_fontPost . "</nobr>\n";
                    break;
                case 4:  // thread
                    echo '<nobr><tt>' . $thread_fontPre;
                    if ($thread_showDate) {
                        echo formatDate($c) . ' ';
                    }
                    echo formatTreeText($newtree) . ' ';
                    if ($thread_showSubject) {
                        echo formatSubject($c, $group, $highlight) . ' ';
                    }
                    if ($thread_showAuthor) {
                        echo '<i>(' . formatAuthor($c) . ')</i>';
                    }
                    echo $thread_fontPost . '</tt></nobr><br>';
                    break;
                case 5:  // thread, graphic
                    echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr>\n";
                    if ($thread_showDate) {
                        echo '<td nowrap="nowrap">' . $thread_fontPre . formatDate($c) . ' ' . $thread_fontPost . '</td>';
                    }
                    echo '<td>' . $thread_fontPre . formatTreeGraphic($newtree) . $thread_fontPost . '</td>';
                    if ($thread_showSubject) {
                        echo '<td nowrap="nowrap">' . $thread_fontPre . '&nbsp;' . formatSubject($c, $group, $highlight) . ' ';
                    }
                    if ($thread_showAuthor) {
                        echo '(' . formatAuthor($c) . ')' . $thread_fontPost . '</td>';
                    }
                    echo '</tr></table>';
                    break;
                case 6:  // thread, table
                    echo '<tr>';
                    if ($thread_showDate) {
                        echo '<td nowrap="nowrap"><tt>' . $thread_fontPre . formatDate($c) . ' ' . $thread_fontPost . '</tt></td>';
                    }
                    echo '<td nowrap="nowrap"><tt>' . $thread_fontPre . formatTreeText($newtree) . ' ';
                    if ($thread_showSubject) {
                        echo formatSubject($c, $group, $highlight) . $thread_fontPost . '</tt></td>';

                        echo '<td></td>';
                    }
                    if ($thread_showAuthor) {
                        echo '<td nowrap="nowrap"><tt>' . $thread_fontPre . formatAuthor($c) . $thread_fontPost . '</tt></td>';
                    }
                    echo '</tr>';
                    break;
                case 7:  // thread, table, graphic
                    echo '<tr>';
                    echo '<td>' . getIcon($c) . '</td>';
                    echo "<td><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
                    echo "<td nowrap='nowrap'>" . formatTreeGraphic($newtree) . '</td>';
                    if ($thread_showSubject) {
                        echo '<td nowrap="nowrap">' . $thread_fontPre . '&nbsp;' . formatSubject($c, $group, $highlight) . $thread_fontPost . '</td>';
                    }
                    echo '</table></td>';
                    if ($thread_showDate) {
                        echo '<td></td><td nowrap="nowrap">' . $thread_fontPre . formatDate($c) . ' ' . $thread_fontPost . '</td>';
                    }
                    if ($thread_showAuthor) {
                        echo '<td></td><td nowrap="nowrap">' . $thread_fontPre . formatAuthor($c) . $thread_fontPost . '</td>';
                    }
                    echo '</tr>';
                    break;
            }
        }

        if ((isset($c->answers[0])) && (count($c->answers) > 0)
            && ($article_count <= $article_last)) {
            if ($thread_treestyle >= 4) {
                if ('+' == mb_substr($newtree, $depth - 2, 1)) {
                    $newtree = str_change($newtree, $depth - 2, 'i');
                }

                $newtree = str_change($newtree, $depth - 1, '+');

                $newtree = strtr($newtree, '`', '.');
            }

            if (!isset($newtree)) {
                $newtree = '';
            }

            showThread(
                $headers,
                $c->answers,
                $depth + 1,
                $newtree . '',
                $group,
                $article_first,
                $article_last,
                $article_count,
                $highlight
            );
        }

        flush();
    }

    if (3 == $thread_treestyle) {
        echo '</UL>';
    }
}

/*
 * Displays a Thread. Is used in article.php
 *
 * $id:    Message-ID (not number!) of an article in the thread
 * $group: name of the newsgroup
 */
function message_thread($id, $group, $thread)
{
    $current = $id;

    flush();

    echo '<hr>';

    while (isset($thread[$id]->references)) {
        foreach ($thread[$id]->references as $reference) {
            if (('' != trim($reference)) && (isset($thread[$reference]))) {
                $id = $reference;

                continue 2;
            }
        }

        break;
    }

    $liste = [];

    $liste[] = $id;

    $tmp = 0;

    showHeaders_head();

    showThread($thread, $liste, 1, '', $group, 0, 100, $tmp, $current);

    showHeaders_tail();
}

/*
 * Load a thread from disk
 *
 * $group: name of the newsgroup, is needed to create the filename
 *
 * the function returns an array of headerType containing the
 * overview-data of the thread.
 */
function loadThreadData($group, $id = 0)
{
    global $spooldir, $compress_spoolfiles;

    if (0 != $id) {
        $infofilename = $spooldir . '/' . $group . '-info.txt';

        $infofile = fopen($infofilename, 'rb');

        fgets($infofile, 100); //we only need the second line in the file so drop this one

        $groupinfo = explode(' ', trim(fgets($infofile, 200)));

        if ($id < $groupinfo[0] || $id > $groupinfo[1]) {
            global $server, $port;

            $ns = OpenNNTPconnection($server, $port);

            flush();

            if (false !== $ns) {
                $headers = getOldThreads($ns, $group, false, $groupinfo[1] - $id - 25, $groupinfo[1] - $id + 25);

                closeNNTPconnection($ns);

                return $headers;
            }
        }
    }

    $filename = $spooldir . '/' . $group . '-data.dat';

    if (!file_exists($filename)) {
        return false;
    }

    if ($compress_spoolfiles) {
        $file = gzopen("$spooldir/$group-data.dat", 'r');

        $headers = unserialize(gzread($file, 1000000));

        gzclose($file);
    } else {
        $file = fopen($filename, 'rb');

        $headers = unserialize(fread($file, filesize($filename)));

        fclose($file);
    }

    return ($headers);
}

/*
 * Save the thread to disk
 *
 * $header: is an array of headerType containing all overview-information
 *          of a newsgroup
 * $group: name of the newsgroup, is needed to create the filename
 */
function saveThreadData($headers, $group)
{
    global $spooldir, $compress_spoolfiles;

    if ($compress_spoolfiles) {
        $file = gzopen("$spooldir/$group-data.dat", 'w');

        gzputs($file, serialize($headers));

        gzclose($file);
    } else {
        $file = fopen("$spooldir/$group-data.dat", 'wb');

        fwrite($file, serialize($headers));

        fclose($file);
    }
}

/*
 * Displays the Head (table tags, headlines etc.) of a thread
 */
function showHeaders_head()
{
    global $thread_showDate, $thread_showTable;

    global $thread_showAuthor, $thread_showSubject;

    global $text_thread, $thread_treestyle;

    if ((2 == $thread_treestyle) || (6 == $thread_treestyle)
        || (7 == $thread_treestyle)) {
        echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";

        echo "<tr>\n";

        if ($thread_showSubject) {
            echo '<td></td><td>' . $text_thread['subject'] . '</td>';
        }

        if ($thread_showDate) {
            echo '<td>&nbsp;&nbsp;</td><td>' . $text_thread['date'] . '&nbsp;</td>';
        }

        if ($thread_showAuthor) {
            echo '<td>&nbsp;&nbsp;</td><td>' . $text_thread['author'] . "</td>\n";
        }

        echo "</tr>\n";
    } else {
        if (1 == $thread_treestyle) {
            echo "<ul>\n";
        }
    }
}

/*
 * Displays the tail (closing table tags, headlines etc.) of a thread
 */
function showHeaders_tail()
{
    global $thread_showDate, $thread_showTable;

    global $thread_showAuthor, $thread_showSubject;

    global $text_thread, $thread_treestyle;

    if ((2 == $thread_treestyle) || (6 == $thread_treestyle)
        || (7 == $thread_treestyle)) {
        echo "</table>\n";
    } else {
        if (1 == $thread_treestyle) {
            echo "</ul>\n";
        }
    }
}

/*
 * Shows a complete thread
 *
 * $headers: The thread to be displayed
 * $group:   name of the newsgroup
 * $article_first: Number of the first article to be displayed
 * $article_last: last article
 */
function showHeaders(&$headers, $group, $article_first = 0, $article_last = 0)
{
    $article_count = 0;

    if (false === $headers) {
        echo $text_thread['no_articles'];
    } else {
        reset($headers);

        $c = current($headers);

        for ($i = 0; $i <= count($headers) - 1; $i++) {  // create the array $liste
            if (false === $c->isAnswer) {             // where are all the articles
                $liste[] = $c->id;                       // in that don't have
            }                                        // references
            $c = next($headers);
        }

        reset($liste);

        if (count($liste) > 0) {
            showHeaders_head();

            showThread(
                $headers,
                $liste,
                1,
                '',
                $group,
                $article_first,
                $article_last,
                $article_count
            );

            showHeaders_tail();
        }
    }
}

/*
 * Print the header of a message to the webpage
 *
 * $head: the header of the message as an headerType
 * $group: the name of the newsgroup, is needed for the links to post.php3
 *         and the header.
 */
function show_header($head, $group)
{
    global $article_show, $text_header, $file_article, $attachment_show, $group_id;

    global $file_attachment;

    if ($article_show['Subject']) {
        echo $text_header['subject'] . htmlspecialchars($head->subject, ENT_QUOTES | ENT_HTML5) . '<br>';
    }

    if ($article_show['From']) {
        echo $text_header['from'] . '<a href="mailto:' . htmlspecialchars($head->from, ENT_QUOTES | ENT_HTML5) . '">' . htmlspecialchars($head->from, ENT_QUOTES | ENT_HTML5) . '</a> ';

        if ('' != $head->name) {
            echo '(' . htmlspecialchars($head->name, ENT_QUOTES | ENT_HTML5) . ')';
        }

        echo '<br>';
    }

    if ($article_show['Newsgroups']) {
        echo $text_header['newsgroups'] . htmlspecialchars(str_replace(',', ', ', $head->newsgroups), ENT_QUOTES | ENT_HTML5) . "<br>\n";
    }

    if (isset($head->followup) && ($article_show['Followup']) && ('' != $head->followup)) {
        echo $text_header['followup'] . htmlspecialchars($head->followup, ENT_QUOTES | ENT_HTML5) . "<br>\n";
    }

    if ((isset($head->organization)) && ($article_show['Organization'])
        && ('' != $head->organization)) {
        echo $text_header['organization'] . html_parse(htmlspecialchars($head->organization, ENT_QUOTES | ENT_HTML5)) . "<br>\n";
    }

    if ($article_show['Date']) {
        echo $text_header['date'] . date($text_header['date_format'], $head->date) . "<br>\n";
    }

    if ($article_show['Message-ID']) {
        echo $text_header['message-id'] . htmlspecialchars($head->id, ENT_QUOTES | ENT_HTML5) . "<br>\n";
    }

    if (($article_show['References']) && (isset($head->references[0]))) {
        echo $text_header['references'];

        for ($i = 0; $i <= count($head->references) - 1; $i++) {
            $ref = $head->references[$i];

            echo ' ' . '<a href="' . $file_article . '?group=' . urlencode($group) . '&group_id=' . $group_id . '&msg_id=' . urlencode($ref) . '">' . ($i + 1) . '</a>';
        }

        echo '<br>';
    }

    if (isset($head->user_agent)) {
        if ((isset($article_show['User-Agent']))
            && ($article_show['User-Agent'])) {
            echo $text_header['user-agent'] . htmlspecialchars($head->user_agent, ENT_QUOTES | ENT_HTML5) . "<br>\n";
        } else {
            echo '<!-- User-Agent: ' . htmlspecialchars($head->user_agent, ENT_QUOTES | ENT_HTML5) . " -->\n";
        }
    }

    if ((isset($attachment_show)) && (true === $attachment_show)
        && (isset($head->content_type[1]))) {
        echo $text_header['attachments'];

        for ($i = 1, $iMax = count($head->content_type); $i < $iMax; $i++) {
            echo ' <a href="' . $file_attachment . '/' . urlencode($group) . '/' . urlencode($head->number) . '/' . $i . '/' . urlencode($head->content_type_name[$i]) . '">' . $head->content_type_name[$i] . '</a> (' . $head->content_type[$i] . ')';

            if ($i < count($head->content_type) - 1) {
                echo ', ';
            }
        }
    }
}

/*
 * print an article to the webpage
 *
 * $group: The name of the newsgroup
 * $id: the ID of the article inside the group or the message-id
 * $attachment: The number of the attachment of the article.
 *              0 means the normal textbody.
 */
function show_article($group, $id, $attachment = 0, $article_data = false)
{
    global $file_article;

    global $text_header, $article_showthread;

    global $block_xnoarchive;

    if (false === $article_data) {
        $article_data = read_message($id, $attachment, $group);
    }

    $head = $article_data->header;

    $body = $article_data->body[$attachment];

    if ($head) {
        if (($block_xnoarchive) && (isset($head->xnoarchive))
            && ('yes' == $head->xnoarchive)) {
            echo 'Der Autor dieses Artikels w?nscht keine Ver?ffentlichung ' . 'in Archiven. Auch wenn dieses System kein Archiv ist, ' . 'verwehrt es den Zugriff.';
        } elseif (('text/plain' == $head->content_type[$attachment])
                  && (0 == $attachment)) {
            show_header($head, $group);

            echo "<pre>\n";

            $body = preg_split("\n", $body);

            for ($i = 0; $i <= count($body) - 1; $i++) {
                $b = textwrap($body[$i], 80, "\n");

                if ((false !== mb_strpos(mb_substr($b, 0, mb_strpos($b, ' ')), '>'))
                    || (0 == strcmp(mb_substr($b, 0, 1), '>'))
                    || (0 == strcmp(mb_substr($b, 0, 1), ':'))) {
                    echo '<i>' . html_parse(htmlspecialchars($b, ENT_QUOTES | ENT_HTML5)) . "</i>\n";
                } else {
                    echo html_parse(htmlspecialchars($b, ENT_QUOTES | ENT_HTML5) . "\n");
                }
            }

            echo "</pre>\n";
        } else {
            echo $body;
        }
    }

    if ($article_showthread > 0) {
    }
}

/*
 * Encode lines with 8bit-characters to quote-printable
 *
 * $line: the to be encoded line
 *
 * the function returns a sting containing the quoted-printable encoded
 * $line
 */
function quoted_printable_encode($line)
{
    $qp_table = [
        '=00',
        '=01',
        '=02',
        '=03',
        '=04',
        '=05',
        '=06',
        '=07',
        '=08',
        '=09',
        '=0A',
        '=0B',
        '=0C',
        '=0D',
        '=0E',
        '=0F',
        '=10',
        '=11',
        '=12',
        '=13',
        '=14',
        '=15',
        '=16',
        '=17',
        '=18',
        '=19',
        '=1A',
        '=1B',
        '=1C',
        '=1D',
        '=1E',
        '=1F',
        '_',
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        "'",
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        ':',
        ';',
        '<',
        '=3D',
        '>',
        '=3F',
        '@',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        '[',
        '\\',
        ']',
        '^',
        '=5F',
        '',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
        '{',
        '|',
        '}',
        '~',
        '=7F',
        '=80',
        '=81',
        '=82',
        '=83',
        '=84',
        '=85',
        '=86',
        '=87',
        '=88',
        '=89',
        '=8A',
        '=8B',
        '=8C',
        '=8D',
        '=8E',
        '=8F',
        '=90',
        '=91',
        '=92',
        '=93',
        '=94',
        '=95',
        '=96',
        '=97',
        '=98',
        '=99',
        '=9A',
        '=9B',
        '=9C',
        '=9D',
        '=9E',
        '=9F',
        '=A0',
        '=A1',
        '=A2',
        '=A3',
        '=A4',
        '=A5',
        '=A6',
        '=A7',
        '=A8',
        '=A9',
        '=AA',
        '=AB',
        '=AC',
        '=AD',
        '=AE',
        '=AF',
        '=B0',
        '=B1',
        '=B2',
        '=B3',
        '=B4',
        '=B5',
        '=B6',
        '=B7',
        '=B8',
        '=B9',
        '=BA',
        '=BB',
        '=BC',
        '=BD',
        '=BE',
        '=BF',
        '=C0',
        '=C1',
        '=C2',
        '=C3',
        '=C4',
        '=C5',
        '=C6',
        '=C7',
        '=C8',
        '=C9',
        '=CA',
        '=CB',
        '=CC',
        '=CD',
        '=CE',
        '=CF',
        '=D0',
        '=D1',
        '=D2',
        '=D3',
        '=D4',
        '=D5',
        '=D6',
        '=D7',
        '=D8',
        '=D9',
        '=DA',
        '=DB',
        '=DC',
        '=DD',
        '=DE',
        '=DF',
        '=E0',
        '=E1',
        '=E2',
        '=E3',
        '=E4',
        '=E5',
        '=E6',
        '=E7',
        '=E8',
        '=E9',
        '=EA',
        '=EB',
        '=EC',
        '=ED',
        '=EE',
        '=EF',
        '=F0',
        '=F1',
        '=F2',
        '=F3',
        '=F4',
        '=F5',
        '=F6',
        '=F7',
        '=F8',
        '=F9',
        '=FA',
        '=FB',
        '=FC',
        '=FD',
        '=FE',
        '=FF',
    ];

    // are there "forbidden" characters in the string?

    for ($i = 0; $i < mb_strlen($line) && ord($line[$i]) <= 127; $i++) {
    }

    if ($i < mb_strlen($line)) { // yes, there are. So lets encode them!
        $from = $i;

        for ($to = mb_strlen($line) - 1; ord($line[$to]) <= 127; $to--) {
        }

        // lets scan for the start and the end of the to be encoded _words_

        for (; $from > 0 && ' ' != $line[$from]; $from--) {
        }

        if ($from > 0) {
            $from++;
        }

        for (; $to < mb_strlen($line) && ' ' != $line[$to]; $to++) {
        }

        // split the string into the to be encoded middle and the rest

        $begin = mb_substr($line, 0, $from);

        $middle = mb_substr($line, $from, $to - $from);

        $end = mb_substr($line, $to);

        // ok, now lets encode $middle...

        $newmiddle = '';

        for ($i = 0, $iMax = mb_strlen($middle); $i < $iMax; $i++) {
            $newmiddle .= $qp_table[ord($middle[$i])];
        }

        // now we glue the parts together...

        $line = $begin . '=?ISO-8859-15?Q?' . $newmiddle . '?=' . $end;
    }

    return $line;
}

/*
 * Post an article to a newsgroup
 *
 * $subject: The Subject of the article
 * $from: The authors name and email of the article
 * $newsgroups: The groups to post to
 * $ref: The references of the article
 * $body: The article itself
 */
function verschicken($subject, $from, $newsgroups, $ref, $body)
{
    global $server, $port, $send_poster_host, $organization, $text_error;

    global $file_footer;

    flush();

    $ns = OpenNNTPconnection($server, $port);

    if (false !== $ns) {
        fwrite($ns, "post\r\n");

        $weg = lieszeile($ns);

        fwrite($ns, 'Subject: ' . quoted_printable_encode($subject) . "\r\n");

        fwrite($ns, 'From: ' . $from . "\r\n");

        fwrite($ns, 'Newsgroups: ' . $newsgroups . "\r\n");

        fwrite($ns, "Mime-Version: 1.0\r\n");

        fwrite($ns, "Content-Type: text/plain; charset=ISO-8859-15\r\n");

        fwrite($ns, "Content-Transfer-Encoding: 8bit\r\n");

        fwrite($ns, "User-Agent: NewsPortal/0.25 (http://florian-amrhein.de/newsportal/)\r\n");

        if ($send_poster_host) {
            fwrite($ns, 'X-HTTP-Posting-Host: ' . gethostbyaddr(getenv('REMOTE_ADDR')) . "\r\n");
        }

        if (false !== $ref) {
            fwrite($ns, 'References: ' . $ref . "\r\n");
        }

        if (isset($organization)) {
            fwrite($ns, 'Organization: ' . quoted_printable_encode($organization) . "\r\n");
        }

        if ((isset($file_footer)) && ('' != $file_footer)) {
            $footerfile = fopen($file_footer, 'rb');

            $body .= "\n" . fread($footerfile, filesize($file_footer));

            fclose($footerfile);
        }

        $body = str_replace("\n.\r", "\n..\r", $body);

        $body = str_replace("\r", '', $body);

        $b = preg_split("\n", $body);

        $body = '';

        for ($i = 0, $iMax = count($b); $i < $iMax; $i++) {
            if ((false !== mb_strpos(mb_substr($b[$i], 0, mb_strpos($b[$i], ' ')), '>')) | (0 == strcmp(mb_substr($b[$i], 0, 1), '>'))) {
                $body .= textwrap(stripslashes($b[$i]), 78, "\r\n") . "\r\n";
            } else {
                $body .= textwrap(stripslashes($b[$i]), 74, "\r\n") . "\r\n";
            }
        }

        fwrite($ns, "\r\n" . $body . "\r\n.\r\n");

        $message = lieszeile($ns);

        closeNNTPconnection($ns);
    } else {
        $message = $text_error['post_failed'];
    }

    return $message;
}

/*
 * Returns the total number of articles for a specified group
 *
 * $von: The handler of the NNTP-Connection
 * $groupname: The full name of the neewsgroup to be acted upon
 */
function getNumArticles($von, $groupname)
{
    global $feedback;

    fwrite($von, "group $groupname\r\n");   // select a group

    $groupinfo = explode(' ', lieszeile($von));

    if (2 != mb_substr($groupinfo[0], 0, 1)) {
        $feedback = '<p>' . $text_error['error:'] . '</p>';

        $feedback .= '<p>' . $text_thread['no_such_group'] . '</p>';

        flush();

        return false;
    }
  

    return $groupinfo[3] - $groupinfo[2] + 1;
}

function getLastArticle($von, $groupname)
{
    fwrite($von, "group $groupname\r\n");   // select a group

    $groupinfo = explode(' ', lieszeile($von));

    if (2 != mb_substr($groupinfo[0], 0, 1)) {
        return false;
    }
  

    return $groupinfo[3];
}
