<?php
function require_auth($loginPath)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['member_id'])) {
        header('Location: ' . $loginPath);
        exit();
    }
}
