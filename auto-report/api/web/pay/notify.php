<?php
$callback_string = file_get_contents('php://input');
if (!empty($callback_string)) {

    $params = simplexml_load_string($callback_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    header('Location: ' . $_SERVER['SERVER_NAME'] . '/shop/return?return_code=' . $params->return_code . '&attach=' . $params->attach . '&transaction_id=' . $params->transaction_id );
}

