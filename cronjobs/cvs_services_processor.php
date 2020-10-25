<?php
/**
 * This is the interface cvs services processor that is meant to be scheduled
 * and will call cvs management scripts with root privileges or system administrator rights.
 *
 * @author  Wannes Simons <wannes.simons@cec.eu.int>
 * @version $Id: cvs_services_processor.php,v 1.11 2006/02/08 18:10:14 schalmn Exp $
 */

//schalmn: removing hardcoded environment settings
//require_once "db.php";
require_once 'header.php';
require_once 'cvs_services_unix.php';

/**
 * The processor queries the mySQL database for new cvs services requests and processes them.
 */
class CvsServicesProcessor
{
    public $cvsServicesProcessor;

    public function __construct()
    {
        $this->cvsServicesProcessor = new CvsServicesProcessorImplementation();
    }

    public function run()
    {
        $query = "SELECT * FROM xoops_xf_cvs_services_queue WHERE is_processed = 'N' ORDER BY queue_id ASC";

        $result = db_query($query);

        echo db_error();

        $this->process($result);

        db_free_result($result);
    }

    public function process($resultset)
    {
        while (false !== ($row = db_fetch_array($resultset))) {
            $command = $row['command'];

            $project = $row['project'];

            $login = $row['login'];

            $userFullName = $row['user_full_name'];

            // add a user to a project

            if ('add user' == $command) {
                if (null != $project && null != $login) {
                    $result_arr = $this->cvsServicesProcessor->addUser(
                        $project,
                        $login,
                        $userFullName
                    );

                    $this->setAsProcessed($row['queue_id'], $result_arr);
                }
            } // add a project administrator

            elseif ('add administrator' == $command) {
                if (null != $project && null != $login) {
                    $result_arr = $this->cvsServicesProcessor->addAdministrator(
                        $project,
                        $login,
                        $userFullName
                    );

                    $this->setAsProcessed($row['queue_id'], $result_arr);
                }
            } // remove a user from a project

            elseif ('remove user' == $command) {
                if (null != $project && null != $login) {
                    $result_arr = $this->cvsServicesProcessor->removeUser(
                        $project,
                        $login
                    );

                    $this->setAsProcessed($row['queue_id'], $result_arr);
                }
            } // remove a project administrator

            elseif ('remove administrator' == $command) {
                if (null != $project && null != $login) {
                    $result_arr = $this->cvsServicesProcessor->removeAdministrator(
                        $project,
                        $login
                    );

                    $this->setAsProcessed($row['queue_id'], $result_arr);
                }
            } // add a new project

            elseif ('add project' == $command) {
                if (null != $project && null != $login) {
                    $options = unserialize($row['options']);

                    $result_arr = $this->cvsServicesProcessor->addProject(
                        $project,
                        $login,
                        $userFullName,
                        $options['is_public'],
                        $options['anon_cvs']
                    );

                    $this->setAsProcessed($row['queue_id'], $result_arr);
                }
            }
        }
    }

    public function setAsProcessed($queueId, $result_arr)
    {
        if (!is_array($result_arr)) {
            die('The function requires an array argument.');
        }

        $query = 'UPDATE xoops_xf_cvs_services_queue '
                 . " SET is_processed = 'Y', "
                 . ' time_processed = FROM_UNIXTIME('
                 . $result_arr['time_processed']
                 . '), '
                 . ' job_return_status = '
                 . $result_arr['return_status']
                 . ', '
                 . " job_error_output = '"
                 . addslashes($result_arr['error_output'])
                 . "' "
                 . " WHERE queue_id = $queueId";

        $result = db_query($query);

        echo db_error();

        db_free_result($result);
    }
}
