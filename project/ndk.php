<?php

class NDK
{
    public $shortname;

    public $name;

    public $downloads;

    public $description;

    public $whatsnew;

    public $whatsnew_archive;

    public $dependencies;

    public $support_status;

    public $attribute;

    public function __construct($shortname)
    {
        $this->shortname = $shortname;

        $this->name = '';

        $this->description = '';

        $this->whatsnew = '';

        $this->whatsnew_archive = '';

        $this->dependencies = '';

        $this->support_status = '';

        $this->attribute = '';
    }

    public function getShortName()
    {
        return $this->shortname;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDownloads()
    {
        return $this->downloads;
    }

    public function getDoc()
    {
        return $this->doc;
    }

    public function getSample()
    {
        return $this->samplecode;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getWhatsNew()
    {
        return $this->whatsnew;
    }

    public function getWhatsNewArchive()
    {
        return $this->whatsnew_archive;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function getSupportStatus()
    {
        return $this->support_status;
    }

    public function setShortName($shortname)
    {
        $this->shortname .= $shortname;
    }

    public function setName($name)
    {
        $this->name .= $name;
    }

    public function setDownloads($download)
    {
        $this->downloads[] = $download;
    }

    public function setDoc($doc)
    {
        $this->doc .= $doc;
    }

    public function setSample($sample)
    {
        $this->samplecode .= $sample;
    }

    public function setDescription($description)
    {
        $this->description .= $description;
    }

    public function setWhatsNew($whatsnew)
    {
        $this->whatsnew .= $whatsnew;
    }

    public function setWhatsNewArchive($whatsnew_archive)
    {
        $this->whatsnew_archive .= $whatsnew_archive;
    }

    public function setDependencies($dependencies)
    {
        $this->dependencies .= $dependencies;
    }

    public function setSupportStatus($support_status)
    {
        $this->support_status .= $support_status;
    }

    public function destroy()
    {
    }
}

class DOWNLOAD
{
    public $name;

    public $type;

    public $size;

    public $modified;

    public $update;

    public $path;

    public function __construct($name)
    {
        $this->name = $name;

        $this->type = '';

        $this->size = '';

        $this->modified = '';

        $this->update = '';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getModified()
    {
        return $this->modified;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    public function setUpdate($update)
    {
        $this->update = $update;
    }

    public function destroy()
    {
    }
}
