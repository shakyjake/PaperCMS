<?php

// Global variables
if(!defined('__ROOT__')){
	define('__ROOT__', dirname(__DIR__, 1));
}

$current_page = null;
$current_user = null;

require_once 'methods/sanitise.php';
require_once 'methods/regex.php';
require_once 'methods/misc.php';
require_once 'methods/polyfill.php';
require_once 'methods/string.php';
require_once 'methods/cookies.php';
require_once 'methods/date.php';
require_once 'methods/form.php';
require_once 'methods/http.php';
require_once 'methods/ip.php';
require_once 'methods/session.php';
require_once 'methods/config.php';
require_once 'methods/option.php';
require_once 'methods/template.php';
require_once 'methods/error.php';
require_once 'methods/db.php';
require_once 'methods/media.php';
require_once 'methods/zip.php';
require_once 'methods/plugin.php';
require_once 'classes/action.php';
require_once 'classes/pagination.php';
require_once 'classes/ajax.php';
require_once 'methods/ajax.php';
require_once 'classes/rapidstring.php';
require_once 'classes/cache.php';
require_once 'classes/recordset.php';
require_once 'classes/css.php';
require_once 'classes/js.php';
require_once 'classes/csp.php';
require_once 'classes/media.php';
require_once 'classes/menu.php';
require_once 'classes/meta.php';
require_once 'classes/component.php';
require_once 'classes/form.php';
require_once 'classes/admin.php';
require_once 'classes/user.php';
require_once 'classes/table.php';
require_once 'classes/thing.php';
require_once 'classes/page.php';

?>