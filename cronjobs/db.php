<?php
/**
 * MySQL database connection/querying layer
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: db.php,v 1.1 2005/11/18 13:17:21 schalmn Exp $
 */

/**
 * System-wide database type
 *
 * @var    constant $sys_database_type
 */
if (!extension_loaded('mysql')) {
    if (!dl('mysql.so')) {
        exit;
    }
}

//schalmn: using Xoops-named environment settings from e.g. sysconfig.inc.php.
//  Placing such file outside the Xoops site but on the PHP include path
//  is a clean way to separate environment settings from the site itself.
/*
$db = 'your_db';
$host = 'localhost';
$user = 'your_user';
$password = 'your_password';
$dbprefix='xoops';
*/
$sys_database_type = XOOPS_DB_TYPE;
$dbprefix = XOOPS_DB_PREFIX;
$host = XOOPS_DB_HOST;
$user = XOOPS_DB_USER;
$password = XOOPS_DB_PASS;
$db = XOOPS_DB_NAME;
//end

$conn = '';

/**
 *  db_connect() -  Connect to the database
 *
 *  Notice the global vars that must be set up
 *  Sets up a global $conn variable which is used
 *  in other functions in this library
 */
function db_connect()
{
    global $conn, $db, $host, $user, $password;

    $conn = @mysql_pconnect($host, $user, $password);
}

/**
 *  db_query() - Query the database
 *
 * @param mixed $qstring
 * @param mixed $limit
 * @param mixed $offset
 * @return resource
 */
function db_query($qstring, $limit = '-1', $offset = 0)
{
    global $conn, $db, $host, $user, $password;

    if ($limit > 0) {
        if (!$offset || $offset < 0) {
            $offset = 0;
        }

        $qstring .= " LIMIT $offset,$limit";
    }

    return @mysql_db_query($db, $qstring, $conn);
}

/**
 *  db_numrows() - Returns the number of rows in this result set
 *
 * @param mixed $qhandle
 * @return false|int
 */
function db_numrows($qhandle)
{
    // return only if qhandle exists, otherwise 0

    if ($qhandle) {
        return @mysql_numrows($qhandle);
    }
  

    return 0;
}

/**
 *  db_free_result() - Frees a database result properly
 *
 * @param mixed $qhandle
 */
function db_free_result($qhandle)
{
    return @$GLOBALS['xoopsDB']->freeRecordSet($qhandle);
}

/**
 *  db_reset_result() - Reset a result set.
 *
 *  Reset is useful for db_fetch_array sometimes you need to start over
 *
 * @param mixed $qhandle
 * @param mixed $row
 * @return bool
 */
function db_reset_result($qhandle, $row = 0)
{
    return mysql_data_seek($qhandle, $row);
}

/**
 *  db_result() - Returns a field from a result set
 *
 * @param mixed $qhandle
 * @param mixed $row
 * @param mixed $field
 * @return string
 */
function db_result($qhandle, $row, $field)
{
    return @mysql_result($qhandle, $row, $field);
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 * @param mixed $lhandle
 */
function db_numfields($lhandle)
{
    return @mysql_numfields($lhandle);
}

/**
 *  db_fieldname() - Returns the number of rows changed in the last query
 *
 * @param mixed $lhandle
 * @param mixed $fnumber
 */
function db_fieldname($lhandle, $fnumber)
{
    return @mysql_fieldname($lhandle, $fnumber);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 * @param mixed $qhandle
 */
function db_affected_rows($qhandle)
{
    return @$GLOBALS['xoopsDB']->getAffectedRows();
}

/**
 *  db_fetch_array() - Fetch an array
 *
 *  Returns an associative array from
 *  the current row of this database result
 *  Use db_reset_result to seek a particular row
 *
 * @param mixed $qhandle
 */
function db_fetch_array($qhandle)
{
    return @$GLOBALS['xoopsDB']->fetchBoth($qhandle);
}

/**
 *  db_insertid() - Returns the last primary key from an insert
 *
 * @param mixed $qhandle
 * @param mixed $table_name
 * @param mixed $pkey_field_name
 */
function db_insertid($qhandle, $table_name, $pkey_field_name)
{
    return @$GLOBALS['xoopsDB']->getInsertId();
}

/**
 *  db_error() - Returns the last error from the database
 */
function db_error()
{
    return @$GLOBALS['xoopsDB']->error();
}
