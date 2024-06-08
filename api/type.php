<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/* ประเถทสินค้าทั้งหมด */
$app->get('/type', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT * from type';

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});

/* ค้าหา ประเภทสินค้าด้วย tid */
$app->get('/type/{tid}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT * from type where tid = ?';

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('i', $args['tid']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});

/* ค้นหาประเภทสินค้า ด้วย ชื่อ ประเภท */
$app->get('/type/name/{name}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    $name = '%'.$args['name'].'%';

    $sql = 'SELECT * from type where name like ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});

/* เพิ่ม ประเภทสินค้า */
$app->post('/type', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    /* แปลง json ทั่งไปที่ได้จาก web ให้เป็น jsonString ที่ php ใช้งานได้ */
    $jsonData = json_decode($json, true);

    $conn = $GLOBALS['connect'];

    $sql = 'INSERT into type (name) values (?)';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $jsonData['name']);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {

        $data = ["affected_rows" => $affected, "last_tid" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});

/* แก้ไขประเภทสินค้า */
$app->put('/type/{tid}', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $tid = $args['tid'];
    $conn = $GLOBALS['connect'];

    $sql = 'UPDATE type set name=? where tid = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $jsonData['name'], $tid);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});

/* ลบประเภทสินค้า */
$app->delete('/type/{tid}', function (Request $request, Response $response, $args) {
    $tid = $args['tid'];
    $conn = $GLOBALS['connect'];

    $sql = 'DELETE from type where tid = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $tid);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});
