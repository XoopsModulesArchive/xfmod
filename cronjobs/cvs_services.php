<?php

require_once 'cvs_services_interface.php';

/**
 * This is the class that queues the user services requests in the mySQL database.
 */
class CvsServices extends CvsServicesInterface
{
    public function __construct()
    {
    }

    // add a user to a project

    public function addUser($project = null, $userLogin = null, $userFullName = '')
    {
        $leaderFullName = addslashes($userFullName);

        $project = addslashes($project);

        $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

        $query = 'SELECT * FROM ' . $xoopsDB->prefix('xf_config') . " WHERE name = 'croncvs'";

        $result = $xoopsDB->query($query);

        $row = $xoopsDB->fetchArray($result);

        $croncvs = $row['value'];

        if ('1' == $croncvs && null != $project && null != $userLogin) {
            $query = "INSERT INTO xoops_xf_cvs_services_queue (command, login, user_full_name, project) VALUES ('add user', '$userLogin', '$userFullName', '$project')";

            $result = $xoopsDB->query($query);
        }
    }

    // add a project administrator

    public function addAdministrator($project = null, $userLogin = null, $userFullName = null)
    {
        if (null === $userFullName) {
            $userFullName = $userLogin;
        }

        $leaderFullName = addslashes($userFullName);

        $project = addslashes($project);

        $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

        if (null != $project && null != $userLogin && null != $userFullName) {
            $query = "INSERT INTO xoops_xf_cvs_services_queue (command, login, user_full_name, project) VALUES ('add administrator', '$userLogin', '$userFullName', '$project')";

            $result = $xoopsDB->query($query);
        }
    }

    // remove a user from a project

    public function removeUser($project = null, $userLogin = null)
    {
        $project = addslashes($project);

        $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

        $query = 'SELECT * FROM ' . $xoopsDB->prefix('xf_config') . " WHERE name = 'croncvs'";

        $result = $xoopsDB->query($query);

        $row = $xoopsDB->fetchArray($result);

        $croncvs = $row['value'];

        if ('1' == $croncvs && null != $project && null != $userLogin) {
            $query = "INSERT INTO xoops_xf_cvs_services_queue (command, login, project) VALUES ('remove user', '$userLogin', '$project')";

            $result = $xoopsDB->query($query);
        }
    }

    // remove a project administrator

    public function removeAdministrator($project = null, $userLogin = null)
    {
        $project = addslashes($project);

        $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

        if (null != $project && null != $userLogin) {
            $query = "INSERT INTO xoops_xf_cvs_services_queue (command, login, project) VALUES ('remove administrator', '$userLogin', '$project')";

            $result = $xoopsDB->query($query);
        }
    }

    // add a new project

    public function addProject($project = null, $leaderLogin = null, $leaderFullName = null, $isPublic = null, $anonCvs = null)
    {
        $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

        $query = 'SELECT * FROM ' . $xoopsDB->prefix('xf_config') . " WHERE name = 'croncvs'";

        $result = $xoopsDB->query($query);

        $row = $xoopsDB->fetchArray($result);

        $croncvs = $row['value'];

        if ('1' == $croncvs && null != $project && null != $leaderLogin) {
            $leaderFullName = addslashes($leaderFullName);

            $project = addslashes($project);

            // an array of options to be serialized and stored in the db

            $options = ['is_public' => $isPublic, 'anon_cvs' => $anonCvs];

            $options = serialize($options);

            $query = "INSERT INTO xoops_xf_cvs_services_queue (command, login, user_full_name, project, options) VALUES ('add project', '$leaderLogin', '$leaderFullName', '$project', '$options')";

            $result = $xoopsDB->query($query);
        }
    }
}
