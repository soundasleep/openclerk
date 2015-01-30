<?php

/**
 * Admin: run a job (without displaying automated_key publically)
 */

require_admin();
require(__DIR__ . "/../layout/templates.php");

define('ADMIN_RUN_JOB', true);

page_header("Run Job", "page_run_job");

$_GET['key'] = get_site_config('automated_key');
require(__DIR__ . "/../batch/batch_run.php");

page_footer();
