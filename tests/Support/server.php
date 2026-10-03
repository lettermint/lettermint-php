<?php

// Router for `php -S`, used by tests/NetworkTest.php. The first path segment
// selects the behaviour; the rest is the API path (`/<mode>/v1/send`).

$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$mode = explode('/', trim($path, '/'))[0] ?? '';
$capture = getenv('LETTERMINT_TEST_CAPTURE') ?: sys_get_temp_dir().'/lettermint-capture.json';

switch ($mode) {
    case 'ok':
        header('Content-Type: application/json');
        http_response_code(202);
        echo json_encode(['message_id' => 'msg_ok', 'status' => 'pending', 'path' => $path, 'query' => $_SERVER['QUERY_STRING'] ?? '']);
        break;
    case 'redirect':
        http_response_code(307);
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/capture'.substr($path, strlen('/redirect')));
        header('Content-Type: application/json');
        echo '{"message":"Moved"}';
        break;
    case 'capture':
        file_put_contents($capture, json_encode(getallheaders()));
        http_response_code(202);
        header('Content-Type: application/json');
        echo '{"message_id":"captured","status":"pending"}';
        break;
    case 'slow':
        sleep(3);
        http_response_code(202);
        header('Content-Type: application/json');
        echo '{"message_id":"slow","status":"pending"}';
        break;
    case 'slowbody':
        http_response_code(202);
        header('Content-Type: application/json');
        echo '{"message_id":';
        @ob_flush();
        flush();
        sleep(3);
        echo '"slow","status":"pending"}';
        break;
    default:
        http_response_code(404);
}
