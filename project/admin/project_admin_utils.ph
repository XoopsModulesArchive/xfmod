<?php
/**
*
* Project Admin: Module of common functions
*
* SourceForge: Breaking Down the Barriers to Open Source Development
* Copyright 1999-2001 (c) VA Linux Systems
* http://sourceforge.net
*
* @version $Id: project_admin_utils.php,v 1.1 2004/04/25 09:22:33 praedator Exp $
*
*/

require_once(XOOPS_ROOT_PATH."/class/xoopsuser.php");
/*

Standard header to be used on all /project/admin/* pages

*/

function get_project_admin_header($group_id, $is_admin, $is_project=1) {
global $feedback;
$content = "";
$prjhistory_caption="";
if ( $is_project )
{
$prjhistory_caption=_XF_PRJ_PROJECTHISTORY;
}
else
{
$prjhistory_caption=_XF_COMM_COMMHISTORY;
}

$content .= "<P><B>"
."<A HREF='".XOOPS_URL."/modules/xfmod/";
if ( $is_project )
{
$content .= "project";
}
else
{
$content .= "community";
}
$content .= "/admin/?group_id=".$group_id."'>"._XF_G_ADMIN."</A> | ";

// We should show most of the admin menu only to the admins
if ($is_admin) {
$content .= "<A HREF='".XOOPS_URL."/modules/xfmod/project/admin/userperms.php?group_id=".$group_id."'>"._XF_PRJ_USERPERMISSIONS."</A> | "
."<A HREF='".XOOPS_URL."/modules/xfmod/project/admin/editgroupinfo.php?group_id=".$group_id."'>"._XF_PRJ_EDITPUBLICINFO."</A> | "
."<A HREF='".XOOPS_URL."/modules/xfmod/project/admin/history.php?group_id=".$group_id."'>".$prjhistory_caption."</A> "
."<BR />";
}

if ($is_project)
{
$content .= "<A HREF='".XOOPS_URL."/modules/xfmod/project/admin/editpackages.php?group_id=".$group_id."'>"._XF_PRJ_EDITRELEASEFILES."</A>";
}

// We should show most of the admin menu only to the admins
if ($is_admin) {
if ( $is_project )
{
$content .= " | ";
}
$content .= "<A HREF='".XOOPS_URL."/modules/xfjobs/createjob.php?group_id=".$group_id."'>"._XF_PRJ_POSTJOBS."</A> | "
."<A HREF='".XOOPS_URL."/modules/xfjobs/?group_id=".$group_id."'>"._XF_PRJ_EDITJOBS."</A>"
."</B><BR /><BR />";
}
return $content;
}

function project_admin_header($group_id, $is_admin, $is_project=1) {
$content=get_project_admin_header($group_id, $is_admin, $is_project);
return $content;
}

/*


The following functions are for the FRS (File Release System)


*/


/*

pop-up box of supported frs statuses

*/

function frs_show_status_popup ($name='status_id', $checked_val="xzxz") {
/*
return a pop-up select box of statuses
*/
global $FRS_STATUS_RES, $xoopsDB;
if (!isset($FRS_STATUS_RES)) {
$FRS_STATUS_RES = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("xf_frs_status"));
}
return html_build_select_box ($FRS_STATUS_RES,$name,$checked_val,false);
}

/*

pop-up box of supported frs filetypes

*/

function frs_show_filetype_popup ($name='type_id', $checked_val="xzxz") {
/*
return a pop-up select box of the available filetypes
*/
global $FRS_FILETYPE_RES, $xoopsDB;
if (!isset($FRS_FILETYPE_RES)) {
$FRS_FILETYPE_RES = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("xf_frs_filetype"));
}
return html_build_select_box ($FRS_FILETYPE_RES,$name,$checked_val,true,'Must Choose One');
}

/*

pop-up box of supported frs processor options

*/

function frs_show_processor_popup ($name='processor_id', $checked_val="xzxz") {
/*
return a pop-up select box of the available processors 
*/
global $FRS_PROCESSOR_RES, $xoopsDB;
if (!isset($FRS_PROCESSOR_RES)) {
$FRS_PROCESSOR_RES = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("xf_frs_processor"));
}
return html_build_select_box ($FRS_PROCESSOR_RES,$name,$checked_val,true,'Must Choose One');
}

/*

pop-up box of packages:releases for this group

*/


function frs_show_release_popup ($group_id, $name='release_id', $checked_val="xzxz") {
/*
return a pop-up select box of releases for the project
*/
global $FRS_RELEASE_RES, $xoopsDB;
if (!$group_id) {
return 'ERROR - GROUP ID REQUIRED';
} else {
if (!isset($FRS_RELEASE_RES)) {
$FRS_RELEASE_RES = $xoopsDB->query("SELECT r.release_id,r.name "
."FROM ".$xoopsDB->prefix("xf_frs_release")." r,".$xoopsDB->prefix("xf_frs_package")." p "
."WHERE p.group_id='$group_id' "
."AND r.package_id=p.package_id");
echo $xoopsDB->error();
}
return html_build_select_box ($FRS_RELEASE_RES,$name,$checked_val,false);
}
}

/*

pop-up box of packages for this group

*/

function frs_show_package_popup ($group_id, $name='package_id', $checked_val="xzxz") {
/*
return a pop-up select box of packages for this project
*/
global $FRS_PACKAGE_RES, $xoopsDB;
if (!$group_id) {
return 'ERROR - GROUP ID REQUIRED';
} else {
if (!isset($FRS_PACKAGE_RES)) {
$FRS_PACKAGE_RES = $xoopsDB->query("SELECT package_id,name "
."FROM ".$xoopsDB->prefix("xf_frs_package")." "
."WHERE group_id='$group_id'");
echo $xoopsDB->error();
}
return html_build_select_box ($FRS_PACKAGE_RES,$name,$checked_val,false);
}
}

/*

The following three functions are for group
audit trail

When changes like adduser/rmuser/change status
are made to a group, a row is added to audit trail
using group_add_history()

*/

function group_get_history ($group_id=false) {
global $xoopsDB;

$sql = "SELECT gh.field_name,gh.old_value,gh.date,u.uname "
."FROM ".$xoopsDB->prefix("xf_group_history")." gh,".$xoopsDB->prefix("users")." u "
."WHERE gh.mod_by=u.uid "
."AND group_id='$group_id' ORDER BY gh.date DESC";

return $xoopsDB->query($sql);
} 

function group_add_history ($field_name,$old_value,$group_id) {
$group = group_get_object($group_id);
$group->addHistory($field_name,$old_value);
} 

/*

Nicely html-formatted output of this group's audit trail

*/

function show_grouphistory ($group_id,$is_project=1) {
/* 
show the group_history rows that are relevant to 
this group_id
*/
global $sys_datefmt, $xoopsDB;
$result = group_get_history($group_id);
$rows = $xoopsDB->getRowsNum($result);

if ($rows > 0) {

if ( $is_project )
{
echo "<H4>"._XF_PRJ_PROJECTHISTORY."</H4><P>";
}
else
{
echo "<H4>"._XF_COMM_COMMHISTORY."</H4><P>";
}

?>
<table border="0" width="95%" cellpadding="0" cellspacing="0" align="center" valign="top"><tr><td class='bg2'>
<table border="0" cellpadding="4" cellspacing="1" width="100%">
<tr class='bg3' align='left'>
<td align="center"><span class='fg2'><b><?php echo _XF_G_FIELD; ?></b></span></td>
<td align="center"><span class='fg2'><b><?php echo _XF_G_OLDVALUE; ?></b></span></td>
<td align="center"><span class='fg2'><b><?php echo _XF_G_DATE; ?></b></span></td>
<td align="center"><span class='fg2'><b><?php echo _XF_G_BY; ?></b></span></td>
</tr>
<?php

for ($i=0; $i < $rows; $i++) { 
$field = unofficial_getDBResult($result, $i, 'field_name');
echo "<TR class='".($i%2>0?"bg1":"bg3")."'>"
."<TD>".$field."</TD>"
."<TD>";

if ($field == 'removed user') {
echo XoopsUser::getUnameFromId(unofficial_getDBResult($result, $i, 'old_value'));
} else {
echo unofficial_getDBResult($result, $i, 'old_value');
} 
echo "</TD>"
."<TD>".date($sys_datefmt,unofficial_getDBResult($result, $i, 'date'))."</TD>"
."<TD>".unofficial_getDBResult($result, $i, 'uname')."</TD>"
."</TR>";
} 

echo "</table></td></tr></table>";

} else {
echo "<H4>"._XF_PRJ_NOCHANGESMADETHISGROUP."</H4>";
} 
} 

?>