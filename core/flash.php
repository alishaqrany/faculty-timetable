<?php
function flash_set($message, $type = 'success')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function flash_consume()
{
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
    $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';

    unset($_SESSION['message'], $_SESSION['message_type']);

    return array($message, $type);
}

function flash_redirect($location, $message, $type = 'success')
{
    flash_set($message, $type);
    header('Location: ' . $location);
    exit();
}
