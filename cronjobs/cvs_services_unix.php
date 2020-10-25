<?php

require_once 'cvs_services_interface.php';
include 'mail_function.php';
//schalmn: removing the dependancy on common.php
//include "../../../mainfile.php";
include '../language/english/mailmessages.php';
//schalmn: removing hardcoded environment settings
//require_once "db.php";

/**
 * This is the *nix impelementation for the cvs management services.
 *
 * @author  Wannes Simons <wannes.simons@cec.eu.int>
 * @version $Id: cvs_services_unix.php,v 1.10 2006/02/16 12:44:16 schalmn Exp $
 */
class CvsServicesProcessorImplementation extends CvsServicesInterface
{
    public function __construct()
    {
    }

    // add a user to a project

    public function addUser($project = null, $userLogin = null, $userFullName = '')
    {
        if (null != $project && null != $userLogin) {
            //schalmn: usernames in /etc/passwd should not include capital letters

            $userLogin = mb_strtolower($userLogin);

            $project_admin = 0;

            // encrypt the password

            $mySalt = mb_substr(preg_replace('[^a-zA-Z0-9./]', '', crypt(mt_rand(10000000, 99999999), mt_rand(10, 99))), 2, 2);

            $cryptpassword = crypt((string)$userLogin, $mySalt);

            $password = $userLogin;

            // launch back-end process (send error messages to both stdout and stderr)

            $lastLine = exec(
                "/usr/local/bin/addCVSUser.sh $project $userLogin $cryptpassword \"$userFullName\" " . "$project_admin 2>&1 >/dev/null | tee /dev/stderr",
                $scriptOutput,
                $returnStatus
            );

            // record timestamp

            $time_processed = time();

            // send email

            $message = $this->newProjectMessage('new_user_message', $project, $userLogin, $userFullName, $password);

            $mail = sendMail($message);

            // return result details as an array

            return [
                'time_processed' => $time_processed,
                'return_status' => $returnStatus,
                'error_output' => implode("\n", $scriptOutput),
            ];
        }
  

        return null;
    }

    // add a project administrator

    public function addAdministrator($project = null, $userLogin = null, $userFullName = '')
    {
        if (null != $project && null != $userLogin) {
            //schalmn: usernames in /etc/passwd should not include capital letters

            $userLogin = mb_strtolower($userLogin);

            $project_admin = 1;

            // encrypt the password

            $mySalt = mb_substr(preg_replace('[^a-zA-Z0-9./]', '', crypt(mt_rand(10000000, 99999999), mt_rand(10, 99))), 2, 2);

            $cryptpassword = crypt((string)$userLogin, $mySalt);

            // launch back-end process (send error messages to both stdout and stderr)

            $lastLine = exec(
                "/usr/local/bin/addCVSUser.sh $project $userLogin $cryptpassword \"$userFullName\" " . "$project_admin 2>&1 >/dev/null | tee /dev/stderr",
                $scriptOutput,
                $returnStatus
            );

            // record timestamp

            $time_processed = time();

            // return result details as an array

            return [
                'time_processed' => $time_processed,
                'return_status' => $returnStatus,
                'error_output' => implode("\n", $scriptOutput),
            ];
        }
  

        return null;
    }

    // remove a user from a project

    public function removeUser($project = null, $userLogin = null)
    {
        if (null != $project && null != $userLogin) {
            $project_admin = 0;

            // launch back-end process (send error messages to both stdout and stderr)

            $lastLine = exec(
                "/usr/local/bin/removeCVSUser.sh $project $userLogin $project_admin 2>&1 >/dev/null | tee /dev/stderr",
                $scriptOutput,
                $returnStatus
            );

            // record timestamp

            $time_processed = time();

            // send email - TODO

            // return result details as an array

            return [
                'time_processed' => $time_processed,
                'return_status' => $returnStatus,
                'error_output' => implode("\n", $scriptOutput),
            ];
        }
  

        return null;
    }

    // remove a project administrator

    public function removeAdministrator($project = null, $userLogin = null)
    {
        if (null != $project && null != $userLogin) {
            $project_admin = 1;

            // launch back-end process (send error messages to both stdout and stderr)

            $lastLine = exec(
                "/usr/local/bin/removeCVSUser.sh $project $userLogin $project_admin 2>&1 >/dev/null | tee /dev/stderr",
                $scriptOutput,
                $returnStatus
            );

            // record timestamp

            $time_processed = time();

            // return result details as an array

            return [
                'time_processed' => $time_processed,
                'return_status' => $returnStatus,
                'error_output' => implode("\n", $scriptOutput),
            ];
        }
  

        return null;
    }

    // add a new project

    public function addProject($project = null, $leaderLogin = null, $leaderFullName = '', $isPublic = '1', $anonCvs = '1')
    {
        if (null != $project && null != $leaderLogin) {
            $mySalt = mb_substr(preg_replace('[^a-zA-Z0-9./]', '', crypt(mt_rand(10000000, 99999999), mt_rand(10, 99))), 2, 2);

            $cryptpassword = crypt((string)$leaderLogin, $mySalt);

            // launch back-end process (send error messages to both stdout and stderr)

            $lastLine = exec(
                "/usr/local/bin/newCVSProject.sh $project $leaderLogin \"$leaderFullName\" " . "$cryptpassword $isPublic $anonCvs 2>&1 >/dev/null | tee /dev/stderr",
                $scriptOutput,
                $returnStatus
            );

            // record timestamp

            $time_processed = time();

            // send email

            $message = $this->newProjectMessage('new_project_message', $project, $leaderLogin, $leaderFullName);

            $mail = sendMail($message);

            // return result details as an array

            return [
                'time_processed' => $time_processed,
                'return_status' => $returnStatus,
                'error_output' => implode("\n", $scriptOutput),
            ];
        }
  

        return null;
    }

    public function newProjectMessage($messagename, $project, $login, $fullName, $password = '')
    {
        //schalmn: removing hardcoded environment settings

        //global $siteName;

        $query = "SELECT group_id FROM xoops_xf_groups where unix_group_name='$project'";

        $result = db_query($query);

        echo db_error();

        $row = db_fetch_array($result);

        $projectID = $row['group_id'];

        $query = "SELECT * FROM xoops_xf_config where name='$messagename'";

        $result = db_query($query);

        echo db_error();

        $row = db_fetch_array($result);

        $body = $row['value'];

        $query = "SELECT email FROM xoops_users where uname='$login'";

        $result = db_query($query);

        echo db_error();

        $row = db_fetch_array($result);

        $toemail = $row['email'];

        //schalmn: removing the dependancy on common.php

        /*
        if ("new_project_message"==$messagename){
            $subject = "[".$xoopsConfig['sitename']."] - Your Project {PROJECT} has been approved";
        }else {
            $subject = "[".$xoopsConfig['sitename']."] - User {LOGIN} added to {PROJECT} CVS Repository";
        }
        */

        if ('new_project_message' == $messagename) {
            $subject = '[' . XOOPS_SITE_NAME . '] - Your Project {PROJECT} has been approved';
        } else {
            $subject = '[' . XOOPS_SITE_NAME . '] - User {LOGIN} added to {PROJECT} CVS Repository';
        }

        //end

        $search = ['{PROJECTID}', '{LOGIN}', '{PROJECT}', '{FULLNAME}', '{PASSWORD}'];

        $replace = [$projectID, $login, $project, $fullName, $password];

        $message = [];

        $message['subject'] = str_replace($search, $replace, $subject);

        $message['body'] = str_replace($search, $replace, $body);

        $message['toemail'] = $toemail;

        db_free_result($result);

        return $message;
    }
}
