<?php

require_once XOOPS_ROOT_PATH . '/kernel/object.php';
require_once XOOPS_ROOT_PATH . '/kernel/group.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsuser.php';

// LDAP Class for NFORGE support

if (!defined('nxoops_LDAP_INCLUDED')) {
    define('nxoops_LDAP_INCLUDED', 1);
}

class nxoopsLDAPCVSServer
{
    public $dn;

    public $dnsName;

    public $projCount;

    public function __construct()
    {
        $this->dn = '';

        $this->dnsName = '';

        $this->projCount = 0;
    }
}

class nxoopsLDAPUser
{
    public $dn;

    public $uid;

    public $cn;

    public $email;

    public $groups;

    public $gecos;

    public $extid;

    public function __construct()
    {
        $this->dn = null;

        $this->uid = null;

        $this->cn = null;

        $this->email = null;

        $this->groups = null;

        $gecos = null;
    }
}

class nxoopsLDAP
{
    public $conn;

    public $bound;

    public $error;

    public function __construct()
    {
        $this->error = null;

        $this->conn = null;

        $this->bound = false;
    }

    public function cleanUp()
    {
        if ($this->conn) {
            ldap_unbind($this->conn);

            unset($this->conn);

            $this->conn = null;
        }
    }

    // Return the error string from the last operation

    public function lastError()
    {
        return $this->error;
    }

    // Connect to the LDAP server specified in the configuration

    public function connect()
    {
        global $xoopsConfig;

        $this->error = null;

        $server = (389 == $xoopsConfig['ldapserverport']) ? 'ldap' : 'ldaps';

        $server .= '://' . $xoopsConfig['ldapserver'];

        $this->conn = ldap_connect($server, $xoopsConfig['ldapserverport']);

        if (!$this->conn) {
            $this->conn = null;

            $this->error = 'Failed connection to ' . $server;

            return false;
        }

        return true;
    }

    // Set the error member of this object with a given string prefix

    // and the current LDAP error string

    public function returnLDAPFailure($prefString)
    {
        $this->error = $prefString . ' LDAP Error: ' . ldap_err2str(ldap_errno($this->conn));

        return false;
    }

    // Private

    // Authenticate a user specified by a given DN

    // using a given password.

    public function doBind($userDN, $password)
    {
        global $xoopsConfig;

        $this->bound = false;

        $this->error = null;

        if (!$this->conn) {
            if (!$this->connect()) {
                return false;
            }
        }

        ldap_bind($this->conn, $userDN, $password);

        if (0 != ldap_errno($this->conn)) {
            $userDN .= ' LDAP Server: ' . $xoopsConfig['ldapserver'];

            return $this->returnLDAPFailure('Bind DN: ' . $userDN);
        }

        $this->bound = true;

        return true;
    }

    // Do authentication for the admin

    public function bindAdmin()
    {
        global $xoopsConfig;

        return $this->doBind($xoopsConfig['ldapadmin'], $xoopsConfig['ldapadminpass']);
    }

    // Get an LDAP object associated with a given DN

    public function findLDAPObject($dn, $attributes = null, $filter = null)
    {
        $this->error = null;

        if (!$filter) {
            $filter = '(objectclass=*)';
        }

        $sr = ldap_search($this->conn, $dn, $filter, $attributes);

        if (!$sr) {
            return $this->returnLDAPFailure("Search for CVS object: $dn");
        }

        if (ldap_count_entries($this->conn, $sr) < 1) {
            $this->error = "No entries found for $dn";

            return false;
        }

        $info = ldap_get_entries($this->conn, $sr);

        return $info;
    }

    // Get user information from LDAP

    public function getUser($userName, $includeGroups = false)
    {
        global $xoopsConfig;

        $this->error = null;

        $userObj = null;

        $entryDN = $xoopsConfig['ldapusercont'];

        $filter = '(cn=' . $userName . ')';

        $justthese = ['uidnumber', 'cn', 'mail', 'gecos', 'webidsynchid'];

        if ($includeGroups) {
            $justthese[] = 'groupmembership';
        }

        $info = $this->findLDAPObject($entryDN, $justthese, $filter);

        if (!$info) {
            $this->error = "User $userName not found";

            unset($justthese);

            return null;
        }

        $userObj = new nxoopsLDAPUser();

        $userObj->dn = $info[0]['dn'];

        $userObj->cn = $info[0]['cn'][0];

        $userObj->email = $info[0]['mail'][0];

        $userObj->extid = $info[0]['webidsynchid'][0];

        if (isset($info[0]['uidnumber'][0])) {
            $userObj->uid = $info[0]['uidnumber'][0];

            $userObj->gecos = $info[0]['gecos'][0];
        }

        if ($info[0]['groupmembership']['count'] > 0) {
            $userObj->groups = [];

            for ($i = 0; $i < $info[0]['groupmembership']['count']; $i++) {
                $userObj->groups[] = $info[0]['groupmembership'][$i];
            }
        }

        unset($info);

        return $userObj;
    }

    // Add PosixAccount Extension to user object

    public function addPosixToUser($userName, $userDN, $uidNumber)
    {
        global $xoopsConfig;

        $this->error = null;

        $entry = [];

        $entry['objectclass'][0] = 'posixaccount';

        $entry['gidnumber'] = 1000;

        $entry['uidnumber'] = $uidNumber;

        $entry['homedirectory'] = '/home/nforgeusers';

        $entry['gecos'] = $userName;

        $entry['loginshell'] = '/bin/cvssh';

        $res = ldap_mod_add($this->conn, $userDN, $entry);

        if (!$res) {
            return $this->returnLDAPFailure(
                'Adding posixAccount to user: ' . $userDN
            );
        }

        return true;
    }

    // Ensure the user has the posixAccount information

    // attached

    public function ensurePosixUser($userName)
    {
        global $xoopsConfig;

        $userName = mb_strtolower($userName);

        $userObj = $this->getUser($userName);

        if (!$userObj) {
            return false;
        }

        if (mb_strlen($userObj->uid) > 0) {
            // uidnumber is already there, so lets' assume

            // the object already has the posix extension

            return true;
        }

        if (mb_strlen($userObj->extid) < 1) {
            // We don't have an ID to use for the uidNumber

            $this->error = 'Error: No webidsynchid returned for ' . $userObj->dn;

            return false;
        }

        return $this->addPosixToUser($userName, $userObj->dn, $userObj->extid);
    }

    // Add or remove a group/user relationship

    public function modUserGroupRelation($userObj, $grpName, $doDel = false)
    {
        global $xoopsConfig;

        $this->error = null;

        $userName = $userObj->getVar('uname', 'S');

        $ldapUser = $this->getUser($userName);

        if (!$ldapUser) {
            return false;
        }

        $userDN = $ldapUser->dn;

        $projBase = 'ou=projects,ou=nforge,' . $xoopsConfig['ldaproot'];

        $grpDN = "cn=$grpName,$projBase";

        $entry['groupmembership'] = $grpDN;

        $entry['securityequals'] = $grpDN;

        $res = false;

        $lop = 'Add';

        if ($doDel) {
            $lop = 'Remove';

            $res = ldap_mod_del($this->conn, $userDN, $entry);
        } else {
            $res = ldap_mod_add($this->conn, $userDN, $entry);
        }

        if (!$res) {
            $this->returnLDAPFailure($lop . ' Group User DN: ' . $userDN);

            return false;
        }

        unset($entry);

        $entry['member'] = $userDN;

        $entry['equivalentToMe'] = $userDN;

        if ($doDel) {
            $lop = 'Remove user ' . $userDN . ' from grp ' . $grpDN;

            $res = ldap_mod_del($this->conn, $grpDN, $entry);
        } else {
            $lop = 'Adding user ' . $userDN . ' to grp ' . $grpDN;

            $res = ldap_mod_add($this->conn, $grpDN, $entry);
        }

        if (!$res) {
            $this->returnLDAPFailure($lop);

            return false;
        }

        return true;
    }

    // Add a user to a group

    public function addUserToGroup($userObj, $grpName)
    {
        return $this->modUserGroupRelation($userObj, $grpName, false);
    }

    // Remove a user from a group

    public function removeUserFromGroup($userObj, $grpName)
    {
        return $this->modUserGroupRelation($userObj, $grpName, true);
    }

    // Enumerate the cvs server in the ou=cvsservers,ou=nforge container.

    // Find the one the has the lowest project count and return it.

    public function getAvailCVSServer($allServers = false)
    {
        global $xoopsConfig;

        $this->error = null;

        $justthese = ['nforgednsname', 'nforgeprojectcount'];

        $base = 'ou=cvsservers,ou=nforge,' . $xoopsConfig['ldaproot'];

        $sr = ldap_list(
            $this->conn,
            $base,
            '(objectclass=NFORGECVSServer)',
            $justthese
        );

        if (!$sr) {
            return $this->returnLDAPFailure('Search for CVS Servers');
        }

        if (ldap_count_entries($this->conn, $sr) < 1) {
            $this->error = 'No CVS Servers found';

            unset($justthese);

            unset($sr);

            return false;
        }

        $info = ldap_get_entries($this->conn, $sr);

        if (true === $allServers) {
            $servers = [];

            for ($i = 0; $i < $info['count']; $i++) {
                $cvsServer = new nxoopsLDAPCVSServer();

                $cvsServer->dn = $info[$i]['dn'];

                $cvsServer->dnsName = $info[$i]['nforgednsname'][0];

                $cvsServer->projCount = $info[$i]['nforgeprojectcount'][0];

                $servers[] = $cvsServer;
            }

            unset($justthese);

            unset($sr);

            unset($info);

            return $servers;
        }

        $cvsServer = new nxoopsLDAPCVSServer();

        $cvsServer->projCount = 0x7FFFFFFF;

        for ($i = 0; $i < $info['count']; $i++) {
            $projCount = $info[$i]['nforgeprojectcount'][0];

            if (!$projCount) {
                $projCount = 0;
            }

            if ($cvsServer->projCount > $projCount) {
                $cvsServer->dn = $info[$i]['dn'];

                $cvsServer->dnsName = $info[$i]['nforgednsname'][0];

                $cvsServer->projCount = $projCount;
            }
        }

        unset($justthese);

        unset($sr);

        unset($info);

        return $cvsServer;
    }

    // Get a CVS Server LDAP object based on a given DNS name

    public function getCVSServer($cvsServerDNSName)
    {
        global $xoopsConfig;

        $this->error = null;

        $justthese = ['nforgednsname', 'nforgeprojectcount'];

        $filter = '(&(objectclass=NFORGECVSServer)(NFORGEDNSName=';

        $filter .= $cvsServerDNSName . '))';

        $base = 'ou=cvsservers,ou=nforge,' . $xoopsConfig['ldaproot'];

        $sr = ldap_list($this->conn, $base, $filter, $justthese);

        if (!$sr) {
            return $this->returnLDAPFailure("Didn't find CVS Server: " . $cvsServerDNSName);
        }

        if (ldap_count_entries($this->conn, $sr) < 1) {
            $this->error = "Didn't find CVS Server: " . $cvsServerDNSName;

            unset($justthese);

            unset($sr);

            return false;
        }

        $info = ldap_get_entries($this->conn, $sr);

        $cvsServer = new nxoopsLDAPCVSServer();

        $cvsServer->dn = $info[$i]['dn'];

        $cvsServer->dnsName = $info[$i]['nforgednsname'][0];

        $cvsServer->projCount = $info[$i]['nforgeprojectcount'][0];

        if (!$cvsServer->projCount) {
            $cvsServer->projCount = 0;
        }

        unset($justthese);

        unset($sr);

        unset($info);

        return $cvsServer;
    }

    // Get the CVS Server object associated with a given project name

    public function getProjectCVSServer($projectName)
    {
        global $xoopsConfig;

        $cvsserver = null;

        // Find the LDAP object for the given project

        $entryDN = "cn=$projectName,ou=projects,ou=nforge," . $xoopsConfig['ldaproot'];

        $justthese = ['nforgecvsserverref'];

        $info = $this->findLDAPObject($entryDN, $justthese);

        if (!$info) {
            $this->error = "Project $projectName not found";

            unset($justthese);

            return false;
        }

        $entryDN = $info[0]['nforgecvsserverref'][0];

        if (!$entryDN) {
            $this->error = "Project $projectName has no CVS server";

            return null;
        }

        unset($info);

        // Find the LDAP object for the CVS server of the project.

        $justthese = ['nforgednsname', 'nforgeprojectcount'];

        $info = $this->findLDAPObject($entryDN, $justthese);

        if (!$info) {
            $this->error = "Project $projName has invalid CVS Server DN: ";

            $this->error .= $entryDN;

            return null;
        }

        $cvsserver->dn = $info[0]['dn'];

        $cvsserver->dnsName = $info[0]['nforgednsname'][0];

        $cvsserver->projCount = $info[0]['nforgeprojectcount'][0];

        if (!$cvsserver->projCount) {
            $cvsserver->projCount = 0;
        }

        unset($info);

        unset($justthese);

        return $cvsserver;
    }

    // Add a pending project action to a given CVS Server

    public function addCVSServerAction($cvsServerDN, $identifier, $tranType, $tranbuf = null)
    {
        global $xoopsDB;

        $tranType = mb_strtoupper($tranType);

        $this->error = null;

        switch ($tranType) {
            case 'A':
                $action = 'ADD';
                break;
            case 'M':
                $action = 'MOD';
                break;
            case 'D':
                $action = 'DEL';
                break;
            case 'P':
                $action = 'PUB';
                break;
            case 'E':
                $action = 'EML';
                break;
            default:
                $this->error = "Invalid Argument action = $action";

                return false;
        }

        $actionLine = $identifier . '?' . $action . '?' . microtime();

        if ('P' == $tranType or 'E' == $tranType) {
            $actionLine .= '?' . $tranbuf;
        }

        $entry['nforgependingprojectactions'] = $actionLine;

        ldap_mod_add($this->conn, $cvsServerDN, $entry);

        if (0 != ldap_errno($this->conn)) {
            $emsg = "Add Pending action. cvsserver:$cvsServerDN ";

            $emsg .= "action:$identifier?$action";

            return $this->returnLDAPFailure($emsg);
        }

        $count = unofficial_getDBResult($xoopsDB->query($sql), 0, 'count');

        return true;
    }

    // Increment or decrement a project count on a CVS server object

    public function incDecCVSServerProjCount($cvsServerDN, $doDec = false)
    {
        $this->error = null;

        $justthese = ['nforgeprojectcount'];

        $info = $this->findLDAPObject($cvsServerDN, $justthese);

        if (!$info) {
            $this->error = "Didn't find CVS Server: " . $cvsServerDN;

            unset($justthese);

            return false;
        }

        $projCount = $info[0]['nforgeprojectcount'][0];

        if (!$projCount) {
            $projCount = 0;
        }

        if ($doDec) {
            $projCount--;
        } else {
            $projCount++;
        }

        $cc = true;

        // Does ldap_mod_replace work if attribute doesn't exist?

        $entry['nforgeprojectcount'] = $projCount;

        ldap_mod_replace($this->conn, $cvsServerDN, $entry);

        if (0 != ldap_errno($this->conn)) {
            $cc = false;

            $this->error = $prefString . ' LDAP Error: ' . ldap_err2str(ldap_errno($this->conn));
        }

        unset($justthese);

        unset($entry);

        return $cc;
    }

    // Increment the project count on a CVS server object

    public function incCVSServerProjCount($cvsServerDN)
    {
        return $this->incDecCVSServerProjCount($cvsServerDN, false);
    }

    // decrement the project count on a CVS server object

    public function decCVSServerProjCount($cvsServerDN)
    {
        return $this->incDecCVSServerProjCount($cvsServerDN, true);
    }

    // Set up a project to be add to a CVS server

    public function createCVSProject($cvsServerDN, $projectName)
    {
        return $this->addCVSServerAction($cvsServerDN, $projectName, 'A');
    }

    // Set up a project to be removed from a CVS server

    public function removeCVSProject($cvsServerDN, $projectName)
    {
        return $this->addCVSServerAction($cvsServerDN, $projectName, 'D');
    }

    // Set up a project to be modified (anon access flag) from a CVS server

    public function modifyCVSProject($cvsServerDN, $projectName)
    {
        return $this->addCVSServerAction($cvsServerDN, $projectName, 'M');
    }

    // Create an LDAP project object.

    public function createProject($projectName, $gidNumber, $anonAllowed)
    {
        global $xoopsConfig;

        $this->error = null;

        $projectExists = false;

        // See if the project already exists

        $entryDN = "cn=$projectName,ou=projects,ou=nforge," . $xoopsConfig['ldaproot'];

        $justthese = ['nforgecvsserverref'];

        $info = $this->findLDAPObject($entryDN, $justthese);

        if ($info) {
            unset($justthese);

            $entryDN = $info[0]['nforgecvsserverref'][0];

            if ($entryDN) {
                return $this->modifyCVSProject($entryDN, $projectName);
            }

            // At this point we know the project exists, but it is not

            // associated with a CVS server object.

            $projectExists = true;
        }

        $cvsserver = $this->getAvailCVSServer();

        if (!$cvsserver) {
            return false;
        }

        if (!$this->incCVSServerProjCount($cvsserver->dn)) {
            return false;
        }

        $entryDN = "cn=$projectName,ou=projects,ou=nforge," . $xoopsConfig['ldaproot'];

        $res = true;

        $entry = [];

        if ($projectExists) {
            $entry['nforgecvsserverref'] = $cvsserver->dn;

            $res = ldap_mod_add($this->conn, $entryDN, $entry);
        } else {
            $entry['objectclass'][0] = 'top';

            $entry['objectclass'][1] = 'groupofnames';

            $entry['objectclass'][2] = 'posixgroup';

            $entry['objectclass'][3] = 'nforgeproject';

            $entry['gidnumber'] = $gidNumber;

            $entry['cn'] = $projectName;

            $entry['nforgecvsserverref'] = $cvsserver->dn;

            $entry['nforgeanonymousallowed'] = ($anonAllowed) ? 'TRUE' : 'FALSE';

            $res = ldap_add($this->conn, $entryDN, $entry);
        }

        if (!$res) {
            $error = $prefString . ' LDAP Error: ' . ldap_err2str(ldap_errno());

            $this->decCVSServerProjCount($cvsserver->dn);

            $this->error = $error;

            return false;
        }

        if (!$this->createCVSProject($cvsserver->dn, $projectName)) {
            return false;
        }

        return $cvsserver;
    }

    // Delete an LDAP Project object

    public function deleteProject($projectName, $cvsServerDNSName)
    {
        global $xoopsConfig;

        $this->error = null;

        $cvsserver = $this->getCVSServer($cvsServerDNSName);

        if (!$cvsserver) {
            return false;
        }

        if (!$this->decCVSServerProjCount($cvsserver->dn)) {
            return false;
        }

        $this->removeCVSProject($cvsserver->dn, $projectName);

        $entryDN = "cn=$projectName,ou=projects,ou=nforge," . $xoopsConfig['ldaproot'];

        if (!ldap_delete($this->conn, $entryDN)) {
            return $this->returnLDAPFailure('Failed to delete project: ' . $entryDN);
        }

        return true;
    }

    // Set up project so anon access will be modified on the

    // CVS server

    public function modifyProject($projectName, $cvsServerDNSName)
    {
        $this->error = null;

        $cvsserver = $this->getCVSServer($cvsServerDNSName);

        if (!$cvsserver) {
            return false;
        }

        return $this->modifyCVSProject($cvsserver->dn, $projectName);
    }

    // Set the anonymous flag on a project

    public function setAnonAllowed($projectName, $anonFlag = true)
    {
        global $xoopsConfig;

        $entryDN = "cn=$projectName,ou=projects,ou=nforge," . $xoopsConfig['ldaproot'];

        $entry = [];

        $entry['nforgeanonymousallowed'] = ($anonFlag) ? 'TRUE' : 'FALSE';

        // Modify the anon flag on the project

        if (!ldap_mod_replace($this->conn, $entryDN, $entry)) {
            return $this->returnLDAPFailure(
                'Change anonymous flag for ' . " project: $entryDN"
            );
        }

        // Get the cvs server DN from the project

        $justthese = ['nforgecvsserverref'];

        $info = $this->findLDAPObject($entryDN, $justthese);

        if (!$info) {
            $this->error = "Project $projectName not found";

            unset($justthese);

            return false;
        }

        // Get the CVS server DN

        $entryDN = $info[0]['nforgecvsserverref'][0];

        if (!$entryDN) {
            $this->error = "Project $projectName has no CVS server";

            return null;
        }

        unset($info);

        return $this->modifyCVSProject($entryDN, $projectName);
    }

    // Set a user's public keys

    public function setUserPubKeys(
        $userName,    // The user name keys are associated with
        $pubKeys
    )            // Public keys for the given user (array of strings)
    {
        global $xoopsConfig;

        $this->error = null;

        $userName = mb_strtolower($userName);

        $keybuf = '';

        for ($i = 0, $iMax = count($pubKeys); $i < $iMax; $i++) {
            $keybuf .= $pubKeys[$i] . "\n";
        }

        // Get all of the CVS servers for the Forge system.

        $cvsservers = $this->getAvailCVSServer(true);

        for ($i = 0, $iMax = count($cvsservers); $i < $iMax; $i++) {
            // Put a public key transaction in the CVS server's

            // transaction queue.

            $this->addCVSServerAction($cvsservers[$i]->dn, $userName, 'P', $keybuf);
        }

        return true;
    }

    // Set email notification for projects

    public function setProjNotify(
        $projName,    // The name of the project to set email on
        $emailAddrs
    )            // Email addresses for the given project (array of strings)
    {
        global $xoopsConfig;

        $this->error = null;

        $projName = mb_strtolower($projName);

        $tranbuf = '';

        for ($i = 0, $iMax = count($emailAddrs); $i < $iMax; $i++) {
            $tranbuf .= $emailAddrs[$i] . "\n";
        }

        // Get all of the CVS servers for the Forge system.

        $cvsservers = $this->getAvailCVSServer(true);

        for ($i = 0, $iMax = count($cvsservers); $i < $iMax; $i++) {
            // Put a public key transaction in the CVS server's

            // transaction queue.

            $this->addCVSServerAction($cvsservers[$i]->dn, $projName, 'E', $tranbuf);
        }

        return true;
    }
}
