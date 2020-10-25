###################################################
# BEWARE : change xoops prefix to your DB prefix
###################################################


#
# Alteration of  table `xoops_xf_config`
#
ALTER TABLE `xoops_xf_config`
    CHANGE `value` `value` TEXT NOT NULL

#
# Insertion of new config data `xoops_xf_config`
#

INSERT INTO `xoops_xf_config` (`name`, `value`)
VALUES ('new_project_message', 'Dear {FULLNAME},
Your Project {PROJECT} CVS repository as been created on CITnet.

The CVS sevrer has been updated with your credentials :
CVS server : citnet.cec.eu.int

Your {PROJECT} repository : citnet.cec.eu.int/cvsroot/{PROJECT}
is accessible through ssh connection with the your login : {LOGIN}
Your Password will be sent to you in another mail.

To change your password,execute the command "passwd" in a SHH console 
on the CVS server.
');

INSERT INTO `xoops_xf_config` (`name`, `value`)
VALUES ('new_user_message', 'Dear {FULLNAME},
Your CITnet user has been added to the Project {PROJECT} as been created on CITnet.

The CVS sevrer has been updated with your credentials :
CVS server : citnet.cec.eu.int

Your {PROJECT} repository : citnet.cec.eu.int/cvsroot/{PROJECT}
is accessible through ssh connection with the your login : {LOGIN}/{PASSWORD}

Your Password will be sent to you in another mail.

To change your password,execute the command "passwd" in a SHH console 
on the CVS server.');

INSERT INTO `xoops_xf_config` (`name`, `value`)
VALUES ('croncvs', '0');

#
# Table structure for table `xoops_xf_cvs_services_queue`
#

CREATE TABLE xoops_xf_cvs_services_queue (
    queue_id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    is_processed      CHAR(1)      NOT NULL DEFAULT 'N',
    command           VARCHAR(100) NOT NULL DEFAULT '',
    login             VARCHAR(100)          DEFAULT NULL,
    user_full_name    VARCHAR(100)          DEFAULT NULL,
    project           VARCHAR(100)          DEFAULT NULL,
    options           VARCHAR(200)          DEFAULT NULL,
    time_processed    DATETIME              DEFAULT NULL,
    job_return_status INT(11)               DEFAULT NULL,
    job_error_output  TEXT                  DEFAULT NULL,
    PRIMARY KEY (queue_id)
)
    ENGINE = ISAM;


