<?php

$type = $group->isProject() ? 'project' : 'community';
$utype = $group->isProject() ? 'Project' : 'Community';

?>
<B>HINWEIS:</B>

<dl>
    <dt><B><?php echo $utype; ?> Administratoren (fett)</B></dt>
    <dd>haben Zugriff auf diese Seite und andere <?php echo $type; ?> Administrationsseiten</dd>

    <?php
    if ($group->isProject()) {
        ?>
        <dt><B>Release-Techniker</B></dt>
        <dd>können Datei-Releases erstellen (jeder <?php echo $type; ?> Admin ist auch ein Release-Techniker)</dd>

        <dt><B>Tool-Techniker (T)</B></dt>
        <dd>können Bugs/Aufgaben/Patches zugewiesen werden</dd>

        <dt><B>Tool-Administratoren (A)</B></dt>
        <dd>können Änderungen sowohl an den Bugs/Aufgaben/Patches als auch an den /toolname/admin/-Seiten vornehmen</dd>

        <dt><B>Tool Keine Berechtigung (N/A)</B></dt>
        <dd>Der Entwickler hat keine spezifischen Berechtigungen (entspricht aktuell '-')</dd>
        <?php
    }
    ?>

    <dt><B>Moderatoren</B> (Foren)</dt>
    <dd>können Beiträge von dem <?php echo $type; ?> Forum löschen</dd>

    <dt><B>Editoren</B> (Dokumenten-Manager)</dt>
    <dd>können von <?php echo $type; ?> Dokumentationen aktualisieren/bearbeiten/entfernen.</dd>
</dl>
