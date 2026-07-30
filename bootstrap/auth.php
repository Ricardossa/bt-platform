<?php

declare(strict_types=1);

use BT\App\Auth\Auth;

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}
