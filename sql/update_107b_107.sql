# Table xoops_xf_cvs_services_queue
#-----------------------------------

# add new columns for the CVS services queue

ALTER TABLE xoops_xf_cvs_services_queue
    ADD COLUMN options           VARCHAR(200) DEFAULT NULL,
    ADD COLUMN time_processed    DATETIME     DEFAULT NULL,
    ADD COLUMN job_return_status INT(11)      DEFAULT NULL,
    ADD COLUMN job_error_output  TEXT         DEFAULT NULL;
