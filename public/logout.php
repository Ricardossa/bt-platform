<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/autoload.php';

use BT\App\Auth\Auth;
use BT\App\Auth\Session;

Auth::logout();

Session::destroy();

header('Location: /login.php');

exit;
