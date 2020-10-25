<?php

// xf/include/tracker/Artifact.class
define('_XF_TRK_A_ONLYMEMBERSCANVIEW', 'Nur Gruppenmitglieder können sich private Artefakt-Typen anzeigen lassen');
define('_XF_TRK_A_ONLYADMINSCANMODIFY', 'Nur Artefakt-Administratoren können private Artefakt-Typen verändern');
define('_XF_TRK_A_ANONSUBMISSIONSNOTALLOWED', 'Dieser Artefakt-Typ erlaubt keine anonymen Einsendungen. Bitte einloggen.');
define('_XF_TRK_A_SUMMARYREQUIRED', 'Nachrichtenzusammenfassung ist erforderlich');
define('_XF_TRK_A_BODYREQUIRED', 'Nachrichtentext ist erforderlich');
define('_XF_TRK_A_ATTEMPTEDDOUBLESUBMIT', 'Du hast versucht dieses Objekt mehrfach zu verschicken. Bitte vermeide Doppelklicken.');
define('_XF_TRK_A_VALIDEMAILREQUIRED', 'Gültige E-Mail-Adresse ist erforderlich');
define('_XF_TRK_A_NOWMONITORING', 'Wird jetzt beobachtet');
define('_XF_TRK_A_MONITORINGDEACTIVATED', 'Beobachtung deaktiviert');
define('_XF_TRK_A_MISSINGPARAMETERS', 'Fehlende Parameter');
define('_XF_TRK_A_MISSINGMAILADDRESS', 'Fehlende E-Mail-Adresse');
define('_XF_TRK_A_UPDATEPERMISSIONDENIED', 'Berechtigung zur Aktualisierung verweigert');

define('_XF_TRK_A_ITEMSMALL', 'Objekt');
define('_XF_TRK_A_WASOPENEDAT', 'wurde eröffnet am %s'); // %s = date
define('_XF_TRK_A_YOUCANRESPOND', 'Hier kannst du antworten:');
define('_XF_TRK_A_INITIALCOMMENT', 'Anfangskommentar');
define('_XF_TRK_A_COMMENTBY', 'Kommentar von');

// xf/include/tracker/ArtifactCanned.class
define('_XF_TRK_AC_NAMEASSIGNEEREQUIRED', 'Name und Bevollmächtigter sind erforderlich');

// xf/include/tracker/ArtifactFile.class
define('_XF_TRK_AF_FILEADDED', 'Datei hinzufügt');
define('_XF_TRK_AF_FILEDELETED', 'Datei gelöscht');

// xf/include/tracker/ArtifactGroup.class
define('_XF_TRK_AG_NAMEREQUIRED', 'Name wird benötigt');

// xf/include/tracker/ArtifactType.class
define('_XF_TRK_AT_NAMEDESCDUEREQUIRED', 'Name, Beschreibung und Ablaufzeitraum sind erforderlich');
define('_XF_TRK_AT_STATUSNAMENOTFOUND', 'Statusname nicht gefunden');
define('_XF_TRK_AT_NAMEDESCDUESTATUSREQUIRED', 'Name, Beschreibung, Ablaufdatum und Status-Timeout sind erforderlich');

// xf/include/pm/pm_data.php
define('_XF_PM_NOTFOUND', 'nicht gefunden');
define('_XF_PM_TASKBEENUPDATED', 'Aufgabe #%s wurde aktualisiert.'); // %s = number of task
define('_XF_PM_SUBPROJECT', 'Unterprojekt');
define('_XF_PM_COMPLETE', 'Komplett');
define('_XF_PM_AUTHORITY', 'Befugnis');
define('_XF_PM_FORMOREINFO', 'Für mehr Informationen besuche:');
define('_XF_PM_TASK', 'Aufgabe');
define('_XF_PM_TASKUPDATESENT', 'Aufgabenaktualisierung gesendet');
define('_XF_PM_TASKUPDATENOTSENT', 'Konnte Aufgabenaktualisierung nicht senden');

// xf/include/canned_responses.php
define('_XF_CRSELECTRESPONSE', 'Wähle Antwort');

// xf/include/frs.class
define('_XF_FRS_ADDCHANGELOGFAILED', 'FRS Hinzufügen Change Log nicht erfolgreich');
define('_XF_FRS_ADDFILEFAILED', 'FRS Datei hinzufügen nicht erfolgreich');
define('_XF_FRS_ADDRELEASEFAILED', 'FRS Release hinzufügen nicht erfolgreich');
define('_XF_FRS_ADDNOTESFAILED', 'FRS Notizen hinzufügen nicht erfolgreich');
define('_XF_FRS_CHANGEPACKAGENAMEFAILED', 'FRS Paketname ändern nicht erfolgreich');
define('_XF_FRS_CHANGEFILEFAILED', 'FRS Datei ändern nicht erfolgreich');
define('_XF_FRS_CHANGEFILERELEASEFAILED', 'FRS Datei-Release ändern nicht erfolgreich');
define('_XF_FRS_CHANGERELEASEFAILED', 'FRS Release ändern nicht erfolgreich');
define('_XF_FRS_GETRELEASELISTFAILED', 'FRS Release-Liste beziehen nicht erfolgreich');
define('_XF_FRS_GETRELEASEFAILED', 'FRS Release beziehen nicht erfolgreich');
define('_XF_FRS_GETRELEASEFILESFAILED', 'FRS Release-Dateien beziehen nicht erfolgreich');
define('_XF_FRS_VERIFYFILEOWNERSHIPFAILED', 'FRS Überprüfung Datei-Eigentumsrecht nicht erfolgreich');
define('_XF_FRS_VERIFYRELEASEFAILED', 'FRS Überprüfung Release nicht erfolgreich');
define('_XF_FRS_VERIFYPACKAGEFAILED', 'FRS Überprüfung Paket nicht erfolgreich');
define('_XF_FRS_VERIFYPACKAGENAMEFAILED', 'FRS Überprüfung Paketname nicht erfolgreich');
define('_XF_FRS_VERIFYFILEFAILED', 'FRS Überprüfung Datei nicht erfolgreich');
define('_XF_FRS_VERIFYFILERELEASEFAILED', 'FRS Überprüfung Datei-Release nicht erfolgreich');
define('_XF_FRS_FILEALREADYEXISTS', 'Datei bereits vorhanden');
define('_XF_FRS_VERIFYPROJECTFAILED', 'FRS Überprüfung Projekt nicht erfolgreich');
define('_XF_FRS_VERIFYRELEASEDATEFAILED', 'FRS Überprüfung Release Datum nicht erfolgreich: ungültiges Datumsformat');
define('_XF_FRS_CREATEPACKAGEFAILED', 'FRS Paketerstellung nicht erfolgreich');
define('_XF_FRS_SENDNOTICEFAILED', 'FRS Notizen schicken nicht erfolgreich');
define('_XF_FRS_RESOLVERELEASEFAILED', 'FRS Release auflösen nicht erfolgreich');

// xf/language/%language%/mailmessages.php
define('_XF_FRS_RELEASE', 'Release');

// xf/include/Group.class
define('_XF_GRP_GROUPOBJECTALREADYEXISTS', 'Gruppenobjekt existiert bereits');
define('_XF_GRP_COULDNOTCREATEGROUP', 'Kann keine Gruppe anlegen');
define('_XF_GRP_COULDNOTADDADMIN', 'Konnte keinen Administrator zur neu angelegten Gruppe hinzufügen');
define('_XF_GRP_COULDNOTGETPERMISSION', 'Konnte keine Berechtigung bekommen');
define('_XF_GRP_COULDNOTCHANGEGROUP', 'Gruppeneigenschaften konnten nicht geändert werden');
define('_XF_GRP_INVALIDGROUPNAME', 'Ungültiger Gruppenname');
define('_XF_GRP_ERRORUPDATINGPROJECT', 'Fehler beim Aktualisieren der Projekt-Information');
define('_XF_GRP_CHANGEDPUBLICINFO', 'Öffentliche Information ändern');
define('_XF_GRP_INVALIDSTATUSCHANGE', 'Ungültige Statusänderung');
define('_XF_GRP_COULDNOTCHANGEGROUPSTATUS', 'Konnte den Gruppenstatus nicht ändern');
define('_XF_GRP_NOTADMINTHISGROUP', 'Du bist kein Administrator dieser Gruppe');
define('_XF_GRP_USERNOTACTIVE', 'Der User ist nicht aktiv. Nur aktive User können hinzugefügt werden');
define('_XF_GRP_COULDNOTADDUSERTOGROUP', 'Konnte den User der Gruppe nicht hinzufügen');
define('_XF_GRP_ADDEDUSER', 'Hinzugefügte User');
define('_XF_GRP_REMOVEDUSER', 'Gelöschte User');
define('_XF_GRP_APPROVEDPROJECT', 'Freigegeben');
define('_XF_GRP_CANNOTREMOVEADMIN', 'Kann Administrator nicht löschen');
define('_XF_GRP_USERNOTREMOVED', 'User nicht gelöscht');
define('_XF_GRP_COULDNOTCHANGEDMEMBER', 'Kann Mitgliederberechtigungen nicht ändern');
define('_XF_GRP_GROUPALREADYACTIVE', 'Gruppe bereits aktiv');
define('_XF_GRP_GROUPHASNOADMINS', 'Die Gruppe hat keine Administratoren');

define('_XF_FND_COULDNOTINSERTFOUNDRY', 'Konnte foundry_data-Reihe nicht einfügen');
define('_XF_TSK_TASKADDRESSINVALID', 'Aufgabenadresse schien ungültig zu sein');
define('_XF_ART_ERRORCREATINGARTIFACTTYPES', 'Fehler beim Anlegen eines Artefakt-Typ Objekts');

// xf/include/Project.class
define('_XF_PRJ_NOTADMINTHISPROJECT', 'Du bist nicht der Administrator dieses Projekts');
define('_XF_PRJ_NOTMEMBEROFPROJECT', 'Du bist kein Mitglied dieses Projekts');

// xf/include/Permission.class
define('_XF_PER_NOVALIDGROUPOBJECT', 'Kein gültiges Gruppenobjekt');
define('_XF_PER_NOVALIDUSEROBJECT', 'Kein gültiges Userobjekt');
define('_XF_PER_USERNOTFOUND', 'User nicht gefunden');

// xf/include/trove.php
define('_XF_TRV_NONESELECTED', 'Keins ausgewählt');
define('_XF_TRV_NONYETCATEGORIZED', 'Diese Projekt ist noch nicht kategorisiert');
define('_XF_TRV_NOWFILTERING', 'Jetzt filtern');
define('_XF_TRV_FILTER', 'Filter');

define('_XF_TRV_NONYETCATEGORIZEDCOMM', 'Diese Community ist noch nicht kategorisiert ');

// xf/forum/forum_utils.php
define('_XF_FRM_FORUMADDED', 'Forum hinzugefügt');

// xf/news/news_utils.php
define('_XF_NWS_NONEWSITEMSFOUND', 'Keine Artikel gefunden');
define('_XF_NWS_COMMENT', 'Kommentar');
define('_XF_NWS_COMMENTS', 'Kommentare');
define('_XF_NWS_READMORECOMMENT', 'Lese mehr/Kommentar');
define('_XF_NWS_NEWSARCHIVE', 'Artikelarchiv');
define('_XF_NWS_SUBMITNEWS', 'Artikel erstellen');

// xf/include/vote_function.php
define('_XF_LOW', 'Niedrig');
define('_XF_HIGH', 'Hoch');
define('_XF_SURVEYPRIVACY', 'Private Umfrage');
define('_XF_SURVEYNOTFOUND', 'Umfrage nicht gefunden');

// Default names for forums,documentation,trackers and stuff like that
define('_XF_FRM_OPENDISCUSSION', 'Offene Diskussionen');
define('_XF_FRM_OPENDISCUSSIONDESC', 'Allgemeine Diskussionen');
define('_XF_FRM_HELP', 'Hilfe');
define('_XF_FRM_HELPDESC', 'Hilfe bekommen');
define('_XF_FRM_DEVELOPERS', 'Entwickler');
define('_XF_FRM_DEVELOPERSDESC', 'Projektentwickler-Diskussionen');
define('_XF_DOC_UNCATEGORIZEDSUBS', 'Nicht kategorisierte Einsendungen');
define('_XF_TRK_BUGS', 'Bugs');
define('_XF_TRK_BUGSDESC', 'Bug Tracking System');
define('_XF_TRK_SUPPORTREQUESTS', 'Supportanfrage');
define('_XF_TRK_SUPPORTREQUESTSDESC', 'Tech Support Tracking System');
define('_XF_TRK_PATCHES', 'Patches');
define('_XF_TRK_PATCHESDESC', 'Patch Tracking System');
define('_XF_TRK_FEATUREREQUESTS', 'Feature-Anfrage');
define('_XF_TRK_FEATUREREQUESTSDESC', 'Feature-Anfrage Tracking System');

// xf/include/html.php
define('_XF_HTM_LOWEST', 'Niedrig');
define('_XF_HTM_MEDIUM', 'Mittel');
define('_XF_HTM_HIGHEST', 'Hoch');

// xf/include/utils.php
define('_XF_PRIORITYCOLORS', 'Prioritätsfarben');

// xf/include/pm/pm_data.php
define('_XF_PM_MISSINGREQPARAMETERS', 'Fehlende erforderliche Parameter');
define('_XF_PM_ENDDATEMUSTBEGREATER', 'Enddatum muss nach dem Startdatum liegen');
define('_XF_PM_TASKDOESNOTEXIST', 'Aufgabe existiert nicht in diesem Unterprojekt');
define('_XF_PM_CANNOTPUTTASKINOTHERGROUP', 'Du kannst diese Aufgabe nicht in ein Unterprojekt einer anderen Gruppe legen.');
define('_XF_PM_MODIFIEDTASK', 'Aufgabe erfolgreich verändert');

// xf/tracker/include/ArtifactFileHtml.class
define('_XF_TRK_AFHINVALIDFILENAME', 'Artefakt-Datei: Ungültiger Dateiname');
define('_XF_TRK_AFHFILESIZEINCORRECT', 'Artefakt-Datei: Datei muss eine Größe zwischen 20 und 256000 Bytes haben');

// xf/tracker/include/ArtifactTypeHtml.class
define('_XF_TRK_ATHSUBMITNEW', 'Einschicken');
define('_XF_TRK_ATHADMINFUNCTIONS', 'Administrator-Funktionen');
define('_XF_TRK_ATHADDBROWSETYPES', 'Artefakt-Typen hinzufügen/durchsuchen');
define('_XF_TRK_ATHEDITUPDATEOPTIONS', 'Optionen bearbeiten/aktualisieren in: %s'); // %s = name of ArtifactType
define('_XF_TRK_ATHREQID', 'Req. ID');
define('_XF_TRK_ATHRESOLUTION', 'Resolution');
define('_XF_TRK_ATHIFAPPLYTOALL', "Möchtest du das die Änderungen für die oben ausgewählten Objekte gelten, benutze diese Kontrollen um deren Eigenschaften zu ändern und klicke einmal 'Massen-Aktualisierung'.");
define('_XF_TRK_ATHCATEGORY', 'Kategorie');
define('_XF_TRK_ATHGROUP', 'Gruppe');
define('_XF_TRK_ATHSTATUS', 'Status');
define('_XF_TRK_ATHSELECTALL', 'Wähle alle Objekte aus oder mache alle Auswahlen rückgängig');
define('_XF_TRK_ATHCANNEDRESP', 'Geschlossene Antwort');
define('_XF_TRK_ATHMASSUPDATE', 'Massen-Aktualisierung');

// xf/project/admin/project_admin_utils.php
define('_XF_PRJ_USERPERMISSIONS', 'Benutzerzugriffsrechte');
define('_XF_PRJ_EDITRELEASEFILES', 'Bearbeite/veröffentliche Dateien');
define('_XF_PRJ_EDITPUBLICINFO', 'Öffentliche Informationen bearbeiten');
define('_XF_PRJ_POSTJOBS', 'Jobs veröffentlichen');
define('_XF_PRJ_EDITJOBS', 'Jobs bearbeiten');
define('_XF_PRJ_NOCHANGESMADETHISGROUP', 'Es wurden an dieser Gruppe keine Veränderungen vorgenommen');
define('_XF_PRJ_PROJECTHISTORY', 'Projektverlauf');

// global
define('_XF_G_SEARCH', 'Suchen');
define('_XF_G_NONE', 'Keine(r)');
define('_XF_G_UNDEFINED', 'Undefiniert');
define('_XF_G_NOCHANGE', 'Nicht geändert');
define('_XF_G_PERMISSIONDENIED', 'Zugriff verweigert');
// Months
define('_XF_G_JANUARY', 'Januar');
define('_XF_G_FEBRUARY', 'Februar');
define('_XF_G_MARCH', 'März');
define('_XF_G_APRIL', 'April');
define('_XF_G_MAY', 'Mai');
define('_XF_G_JUNE', 'Juni');
define('_XF_G_JULY', 'Juli');
define('_XF_G_AUGUST', 'August');
define('_XF_G_SEPTEMBER', 'September');
define('_XF_G_OCTOBER', 'Oktober');
define('_XF_G_NOVEMBER', 'November');
define('_XF_G_DECEMBER', 'Dezember');
// Menu Item
define('_XF_G_SUMMARY', 'Übersicht');
define('_XF_G_ADMIN', 'Administrator');
define('_XF_G_SITEADMIN', 'Site-Admin Homepage');
define('_XF_G_HOMEPAGE', 'Homepage');
define('_XF_G_FORUMS', 'Foren');
define('_XF_G_TRACKERS', 'Tracker');
define('_XF_G_BUGS', 'Bugs');
define('_XF_G_SUPPORT', 'Support');
define('_XF_G_PATCHES', 'Patches');
define('_XF_G_TASKS', 'Aufgaben');
define('_XF_G_DOCS', 'Dokumentationen');
define('_XF_G_SURVEYS', 'Umfragen');
define('_XF_G_LISTS', 'Mailingliste');
define('_XF_G_SAMPLE', 'Beispielcode');
define('_XF_G_NEWS', 'Artikel');
define('_XF_G_FILES', 'Dateien');
define('_XF_G_COMM', 'Community');
define('_XF_G_CVS', 'CVS');
// navigation
define('_XF_G_PREVIOUS', 'Vorheriges');
define('_XF_G_NEXT', 'Nächstes');
define('_XF_G_BACK', 'Zurück');
define('_XF_G_REPLY', 'Antworten');
define('_XF_G_SUBMIT', 'Absenden');
define('_XF_G_BROWSE', 'Durchsuche');
define('_XF_G_CANCEL', 'Abbrechen');
define('_XF_G_ADD', 'Hinzufügen');
define('_XF_G_EDIT', 'Editieren');
define('_XF_G_DELETE', 'Löschen');
define('_XF_G_REMOVE', 'Entfernen');
define('_XF_G_DELETED', 'Gelöscht');
define('_XF_G_ANY', 'Alle');
define('_XF_G_CHANGE', 'Ändern');
define('_XF_G_UPDATE', 'Update');
define('_XF_G_ISPUBLIC', 'Ist öffentlich?');

define('_XF_G_DATE', 'Datum');
define('_XF_G_POSTEDBY', 'Geschrieben von');
define('_XF_G_SUBMITTEDBY', 'Erstellt von');
define('_XF_G_SENDER', 'Sender');
define('_XF_G_ASSIGNEDTO', 'Zugewiesen an');
define('_XF_G_UNASSIGNED', 'nicht zugewiesen');
define('_XF_G_FOLLOWUPS', 'Folgebeiträge');
define('_XF_G_NOFOLLOWUPS', 'Es wurden keine Folgebeiträge veröffentlicht');
define('_XF_G_BY', 'Von');
define('_XF_G_FOR', 'Für');
define('_XF_G_IN', 'In');
define('_XF_G_DESCRIPTION', 'Beschreibung');
define('_XF_G_SUBJECT', 'Betreff');
define('_XF_G_COMMENT', 'Kommentar');
define('_XF_G_NOCOMMENTSADDED', 'Es wurden keine Kommentare hinzugefügt');
define('_XF_G_MESSAGE', 'Mitteilung');
define('_XF_G_PROJECT', 'Projekt');
define('_XF_G_PRIORITY', 'Priorität');

// History
define('_XF_G_FIELD', 'Feld');
define('_XF_G_PROPERTY', 'Besitzer');
define('_XF_G_OLDVALUE', 'Alter Wert');
define('_XF_G_VALUE', 'Wert');
define('_XF_G_NOCHANGES', 'Zu diesem Punkt gibt es keine Änderungen');

// group items
define('_XF_GR_DISPLAY', 'Anzeige von Gruppen beginnend mit: ');
define('_XF_GR_GROUPSW', 'Gruppen mit');
define('_XF_GR_PENDING', '<B>P</B> (wartend) Status');
define('_XF_GR_NEWPR', 'Neue Projektfreigabe');
define('_XF_GR_MANUALDISABLED', 'Manuelle Projektfreigabe ist deaktiviert');
define('_XF_GR_INCOMPLETE', '<B>I</B> (nicht komplett) Status');
define('_XF_GR_DELETED', '<B>D</B> (gelöscht) Status');
define('_XF_GR_PRIVGROUPS', 'Private Gruppen');
define('_XF_GR_PREDEFRESPONSES', 'Verwalte vordefinierte Antworten');
define('_XF_GR_ADMINSEARCHRES', 'Admin Suchergebnisse');
define('_XF_GR_REFUSEDISPLAYDB', '<H4>Anzeige der kompletten Datenbank verweigert</H4>Das würde die komplette Datenbank anzeigen.');
define('_XF_GR_GROUPSEARCHCRIT', 'Gruppensuche mit Kriterien');
define('_XF_GR_MATCHES', 'stimmt überein mit');
define('_XF_GR_UNIXNAME', 'Unix-Name');
define('_XF_GR_FULLNAME', 'Langname');
define('_XF_GR_REGISTERED', 'Registriert');
define('_XF_GR_STATUS', 'Status');
define('_XF_GR_ERRORREJECT', 'Fehler bei der Ablehnung der Gruppe');
define('_XF_GR_NONEFOUND', 'Keine gefunden');
define('_XF_GR_NOPENDING', 'Keine wartenden Projekte freizugeben');
define('_XF_GR_PENDINGPR', 'Wartende Projekte');
define('_XF_GR_EDITPROJECT', 'Projektdetails bearbeiten');
define('_XF_GR_ADMINPROJECT', 'Projektadministrator');
define('_XF_GR_VIEWPROJECTMEMBERS', 'Projektmitglieder anzeigen/bearbeiten');
define('_XF_GR_APPROVE', 'Freigeben');
define('_XF_GR_CANNEDRESP', 'Geschlossene Antworten');
define('_XF_GR_MANAGERESP', 'Antworten verwalten');
define('_XF_GR_CUSTOMRESP', 'Benutzerdefinierter Antworttitel und -Text');
define('_XF_GR_ADDCANNED', 'Diese benutzerdefinierte Antwort den Geschlossenen Antworten hinzufügen');
define('_XF_GR_REJECT', 'Ablehnen');
define('_XF_GR_LICENSE', 'Lizenz');
define('_XF_GR_OTHERINFO', 'Andere Information');
define('_XF_GR_UNIXGROUPNAME', 'Unix Gruppenname');
define('_XF_GR_SUBMDESCRIPTION', 'Eingeschickte Beschreibung');
define('_XF_GR_LICENSEOTHER', 'Andere lizensieren');
define('_XF_GR_PENDINGREASON', 'Wartender Grund');
define('_XF_GR_ERRORCREATOBJECT', 'Fehler beim Anlegen des Gruppenobjekts');
define('_XF_GR_INSTRMAILSENT', 'E-Mail mit Anweisungen geschickt');
define('_XF_GR_GROUPTYPE', 'Gruppentyp');
define('_XF_GR_SELINCOMPLETE', 'Nicht komplett (I)');
define('_XF_GR_SELACTIVE', 'Aktiv (A)');
define('_XF_GR_SELPENDING', 'Wartend (P)');
define('_XF_GR_SELHOLDING', 'Haltend (H)');
define('_XF_GR_SELDELETED', 'Gelöscht (D)');
define('_XF_GR_NO', 'Nein');
define('_XF_GR_YES', 'Ja');
define('_XF_GR_PUBLIC', 'Öffentlich');
define('_XF_GR_NOTAVAILABLE', 'N/V');
define('_XF_GR_OTHER', 'Andere');
define('_XF_GR_HTTPDOMAIN', 'HTTP-Domain');
define('_XF_GR_REGAPP', 'Registrations-Anwendung');
define('_XF_GR_UPDATE', 'Aktualisieren');
define('_XF_GR_RESENDINSTRUCTION', 'Neue-Projektanweisungs-E-Mail erneut senden');
define('_XF_GR_USERADDED', 'User zur Gruppe hinzugefügt');
define('_XF_GR_USERLIST', 'Userliste für Gruppe');
define('_XF_GR_VIEWPROFILE', 'Zeige Profile');
define('_XF_GR_ADDUSER', 'Füge Benutzer der Gruppe hinzu');
define('_XF_GR_SUBMIT', 'Absenden');
define('_XF_GR_MANAGECANNED', 'Verwalte Geschlossene Antworten');
define('_XF_GR_EDIT', 'Bearbeiten');
define('_XF_GR_DELETE', 'Löschen');
define('_XF_GR_YESSURE', 'Ja, ich bin sicher');
define('_XF_GR_EDITEDRESPONSE', 'Bearbeite Antwort');
define('_XF_GR_GO', 'Los');
define('_XF_GR_GET', 'Beziehe');
define('_DELETEDRESPONSE', 'Lösche Antwort');
define('_XF_GR_SUREDELETE1', "Wenn du doch nicht sicher bist, warum hast du dann auf 'Löschen' geklickt?");
define('_XF_GR_SUREDELETE2', '<i>By the way, ich habe nichts gelöscht ... für alle Fälle ...</i>');
define('_XF_GR_ADDEDRESPONSE', 'Hinzugefügte Antwort');
define('_XF_GR_CREATENEWRESP', 'Neue Antwort erstellen');
define('_XF_GR_RTITLE', 'Antworttitel');
define('_XF_GR_RTEXT', 'Antworttext');
define('_XF_GR_CREATE', 'Erstellen');
define('_XF_GR_YOUCANT', 'Das funktioniert nicht');
define('_XF_GR_NONE', 'Keine');
define('_XF_GR_APPROVING', 'Gruppe freigeben');
define('_XF_GR_UPDATED', 'Aktualisiert');
define('_XF_GR_DENIED', 'Zugriff verweigert');
