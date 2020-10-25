<?php
/**
 * MySQL database connection/querying layer
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @param mixed $qhandle
 * @param mixed $row
 * @param mixed $field
 * @return string
 * @return string
 * @version   $Id: database-mysql.php,v 1.2 2003/12/09 15:03:53 devsupaul Exp $
 */
function unofficial_getDBResult($qhandle, $row, $field)
{
    return @mysql_result($qhandle, $row, $field);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 * @param mixed $qhandle
 */
function unofficial_getAffectedRows($qhandle)
{
    return @$GLOBALS['xoopsDB']->getAffectedRows();
}

function unofficial_ResetResult($qhandle, $row = 0)
{
    return mysql_data_seek($qhandle, $row);
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 * @param mixed $lhandle
 */
function unofficial_getNumFields($lhandle)
{
    return @mysql_numfields($lhandle);
}

/**
 *  db_fieldname() - Returns the number of rows changed in the last query
 *
 * @param mixed $lhandle
 * @param mixed $fnumber
 */
function unofficial_getFieldName($lhandle, $fnumber)
{
    return @mysql_fieldname($lhandle, $fnumber);
}
