<?php

/**
 * Admin: run a job (without displaying automated_key publically)
 */

require("inc/global.php");
require_admin();
define('ADMIN_RUN_JOB', true);

require_get("job_id");

$_GET['key'] = get_site_config('automated_key');
require("batch_run.php");
