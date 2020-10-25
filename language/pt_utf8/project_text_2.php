<?php

$type = $group->isProject() ? 'project' : 'community';
$utype = $group->isProject() ? 'Project' : 'Community';
?>
<B>NOTE:</B>
<dl>
    <dt><B><?php echo $utype; ?> Admins (bold)</B></dt>
    <dd>can access this page and other <?php echo $type; ?> administration pages</dd>
    <?php
    if ($group->isProject()) {
        ?>
        <dt><B>Release Technicians</B></dt>
        <dd>can make the file releases (any <?php echo $type; ?> admin also a release technician)</dd>
        <dt><B>Tool Technicians (T)</B></dt>
        <dd>can be assigned Bugs/Tasks/Patches</dd>
        <dt><B>Tool Admins (A)</B></dt>
        <dd>can make changes to Bugs/Tasks/Patches as well as use the /toolname/admin/ pages</dd>
        <dt><B>Tool No Permission (N/A)</B></dt>
        <dd>Developer doesn't have specific permission (currently
            equivalent to '-')
        </dd>
        <?php
    }
    ?>
    <dt><B>Moderators</B> (forums)</dt>
    <dd>can delete messages from the <?php echo $type; ?> forums</dd>
    <dt><B>Editors</B> (doc. manager)</dt>
    <dd>can update/edit/remove documentation from the <?php echo $type; ?>.</dd>
</dl>
