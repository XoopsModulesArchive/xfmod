<?php
/**
*
* ArtifactResolution.class - Class to artifact resolutions
*
* SourceForge: Breaking Down the Barriers to Open Source Development
* Copyright 1999-2001 (c) VA Linux Systems
* http://sourceforge.net
*
* @version $Id: ArtifactResolution.class,v 1.1.1.1 2004/08/01 19:13:48 devsupaul Exp $
*
*/
require_once(XOOPS_ROOT_PATH."/modules/xfmod/include/Error.class");
class ArtifactResolution extends Error {
var $db;
/** 
* The artifact type object
*
* @var object $ArtifactType
*/
var $ArtifactType; //object
/**
* Array of artifact data
*
* @var array $data_array
*/
var $data_array;
/**
* ArtifactResolution() - constructor
*
* Use this constructor if you are modifying an existing artifact
*
* @param object Artifact type object
* @param array (all fields from artifact_group) OR id from database
* @return true/false
*/
function ArtifactResolution(&$ArtifactType, $data=false) {
global $xoopsDB;
$this->db = $xoopsDB;
$this->Error(); 
//was ArtifactType legit?
if (!$ArtifactType || !is_object($ArtifactType)) {
$this->setError('ArtifactResolution: No Valid ArtifactType');
return false;
}
//did ArtifactType have an error?
if ($ArtifactType->isError()) {
$this->setError('ArtifactResolution: '.$Artifact->getErrorMessage());
return false;
}
$this->ArtifactType =& $ArtifactType;
if ($data) {
if (is_array($data)) {
$this->data_array =& $data;
return true;
} else {
if (!$this->fetchData($data)) {
return false;
} else {
return true;
}
}
}
}
/**
* fetchData() - re-fetch the data for this ArtifactResolution from the database
*
* @param int Data ID
* @return true/false
*/
function fetchData($id) {
$res = $this->db->query("SELECT * FROM ".$this->db->prefix("xf_artifact_resolution")." WHERE id='$id'");
if (!$res || $this->db->getRowsNum($res) < 1) {
$this->setError('ArtifactResolution: Invalid ArtifactResolution ID');
return false;
}
$this->data_array =& $this->db->fetchArray($res);
return true;
}
/**
* getArtifactType() - get the ArtifactType Object this ArtifactResolution is associated with
*
* @return ArtifactType
*/
function &getArtifactType() {
return $this->ArtifactType;
}
/**
* getID() - get this ArtifactResolution's ID
*
* @return the id #
*/
function getID() {
return $this->data_array['id'];
}
/**
* getName() - get the name
*
* @return text name
*/
function getName() {
return $this->data_array['resolution_name'];
}
}
?>