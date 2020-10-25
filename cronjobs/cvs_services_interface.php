<?php

/**
 * This is the facade and interface for the cvs management services.
 * In PHP 4, there are no interfaces, thus an abstract class is used. Since PHP 5 supports interfaces, this can be changed over time.
 *
 * @author  Wannes Simons <wannes.simons@cec.eu.int>
 * @version $Id: cvs_services_interface.php,v 1.3 2006/02/08 18:10:14 schalmn Exp $
 */
class CvsServicesInterface
{
    public function __construct()
    {
        trigger_error('Interface CvsServicesInterface may not be instantiated.', E_USER_NOTICE);
    }

    // add a user to a project

    public function addUser($project = null, $userLogin = null, $userFullName = null)
    {
        trigger_error('Missing implementation of CvsServicesInterface::addUser(project, login, fullName)', E_USER_NOTICE);
    }

    // add a project administrator

    public function addAdministrator($project = null, $userLogin = null, $userFullName = null)
    {
        trigger_error('Missing implementation of CvsServicesInterface::addAdministrator(project, login, fullName)', E_USER_NOTICE);
    }

    // remove a user from a project

    public function removeUser($project = null, $userLogin = null)
    {
        trigger_error('Missing implementation of CvsServicesInterface::removeUser(project, login)', E_USER_NOTICE);
    }

    // remove a project administrator

    public function removeAdministrator($project = null, $userLogin = null)
    {
        trigger_error('Missing implementation of CvsServicesInterface::removeAdministrator(project, login)', E_USER_NOTICE);
    }

    // add a new project

    public function addProject($project = null, $leaderLogin = null, $leaderFullName = null)
    {
        trigger_error('Missing implementation of CvsServicesInterface::addProject(project, login, fullName)', E_USER_NOTICE);
    }
}
