<?php

/**
 * Admin: run a job (without displaying automated_key publically)
 */

require(__DIR__ . "/inc/global.php");
require_admin();
define('ADMIN_RUN_JOB', true);

require_get("job_id");

$_GET['key'] = get_site_config('automated_key');
require(__DIR__ . "/batch_run.php");
