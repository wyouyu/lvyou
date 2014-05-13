<?php
require_once(dirname(__FILE__) . '/app.php');
ob_get_clean();

Utility::CaptchaCreate(4);
