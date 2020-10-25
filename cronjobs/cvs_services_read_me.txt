Some notes on using the CVS service pages (by Wannes Simons).

Introduction
------------

The CVS service system works with a queue.
Commands are inserted into the queue table 'xoops_xf_cvs_services_queue' in MySQL.

This queueing behaviour aimed to create automatically CVS repository is triggered in xfmod when the parameter
"Use the automatic CVS repository creation" is set to YES on the configuration page
("/modules/xfmod/admin.php?fct=config")
parameter name is : '$usecvs'

- In a certain PHP, include the cvs_services.php page and construct a CvsServices object.Then use its methods to add commands to the queue.
(e.g. /modules/xfmod/admin/groups/groups.php line 995)

- The page that processes the commands on the queue needs root privileges or administrator's rights and should be scheduled using cron or scheduled tasks. This page is called 'cvs_services_process_queue.php'.
  The processing of the queue is decoupled from the implementation. In the file 'cvs_services_processor.php', there is an include statement to the processor implementation.

crontab entry example :

* * * * * /usr/bin/php4 -q /usr/share/xoops/modules/xfmod/cronjobs/cvs_services_process_queue.php 2> /tmp/cvsservice.log


UNIX/LINUX environnement:
------------------------
the shell scripts corresponding to your OS contained in this directory should be copied to the following path :
(be sure to remove debian_ or redHat_)
/usr/local/bin/addCVSUser.sh
/usr/local/bin/newCVSProject.sh
note those path are hardcoded in the script "cvs_services_unix.php".
If you want to use a different path, be sure to modify it in the script too.

- DB connection
The cronjobs configured above wil have to connect to the db :
be sure to tweak "db.php" to allow the connection to your db.

- "Use the automatic CVS repository creation" in administration is set to "yes" (see admin->xfmod->configuration) : 
a new project will generated a mail explaining the situation to the 
project manager : 

__________________________________________
Dear {FULLNAME},
Your Project {PROJECT} CVS repository as been created on Xoops.

The CVS sevrer has been updated with your credentials :
CVS server : cvsserver

Your {PROJECT} repository : cvsserver/cvsroot/{PROJECT}
is accessible through ssh connection with the your login : {LOGIN}
Your Password will be sent to you in another mail.

To change your password,execute the command "passwd" in a SHH console
on the CVS server.
__________________________________________
(values between {} are replaced with data coming from xoops)

Same situation when a user is added to the project, user receive a mail explaining him how to reach his cvs repository :
__________________________________________
Dear {FULLNAME},
Your CITnet user has been added to the Project {PROJECT} as been created on CITnet.

The CVS sevrer has been updated with your credentials :
CVS server : citnet.cec.eu.int

Your {PROJECT} repository : citnet.cec.eu.int/cvsroot/{PROJECT}
is accessible through ssh connection with the your login : {LOGIN}/{PASSWORD}

Your Password will be sent to you in another mail.

To change your password,execute the command "passwd" in a SHH console 
on the CVS server.
__________________________________________

Those mail content are kept on the db : you can modify them through the admin->xfmod->configuration




______________________________________________________
new files :
xfmod/cronjobs/class.smtp.php
xfmod/cronjobs/class.phpmailer.php
xfmod/cronjobs/mail_function.php     // !!!do not forget to configure it with your mail server.
xfmod/sql/update_104_to_107.sql     // tobe executed to install new features

Files to update :
xfmod/cronjobs/cvs_services_unix.php //new mailing functionalities
xfmod/cronjobs/cvs_services.php    //checks the croncvs parameter in table xoops_xf_config
xfmod/admin/config/config.php  //new fields in the configuration form