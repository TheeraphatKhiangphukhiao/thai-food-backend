<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/* ค้นหาสินค้าทั้งหมด */
$app->get('/goods', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT goods.gid, goods.name, goods.price, goods.url, type.name as type 
            from goods 
            inner join type on goods.tid = type.tid';

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

/* ค้นหาสินค้าด้วยชื่อ */
$app->get('/goods/name/{name}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT goods.gid, goods.name, goods.price, goods.url, type.name as type
            from    goods, type
            where   goods.tid = type.tid
            and     goods.name like ?';
    
    $stmt = $conn->prepare($sql);

    $name = '%' . $args['name'] . '%';

    $stmt->bind_param('s', $name);

    $stmt->execute();

    $result = $stmt->get_result();

    $data = array();

    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);

});

/* ค้นหาสินค้า ด้วย ชื่อ ประเภท */
$app->get('/goods/type/{name}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT goods.gid, goods.name, goods.price, goods.url, type.name as type 
            from goods 
            inner join type on goods.tid = type.tid 
                where type.name like ?';

    $stmt = $conn->prepare($sql);
    $name = '%' . $args['name'] . '%';
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

/* ค้นหาสินค้าด้วย gid */
$app->get('/goods/{gid}', function (Request $request, Response $response, $args) {
    $gid = $args['gid'];
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT goods.gid, goods.name, goods.price, goods.url, type.name as type 
            from goods 
            inner join type on goods.tid = type.tid 
                where goods.gid = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $gid);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});

/* เพิ่มสินค้า */
$app->post('/goods', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);

    $conn = $GLOBALS['connect'];

    $sql = 'INSERT into goods (name, price, url, tid) values (?, ?, ?, ?)';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sisi', $jsonData['name'], $jsonData['price'], $jsonData['url'], $jsonData['tid']);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {

        $data = ["affected_rows" => $affected, "last_gid" => $conn->insert_id];//จำนวนแถวที่ได้รับผลกระทบ และ id สุดท้ายที่ถูกเพิ่ม
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});

/* แก้ไขสินค้า */
$app->put('/goods/{gid}', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $gid = $args['gid'];
    $conn = $GLOBALS['connect'];

    $sql = 'UPDATE goods 
            set name=?, price=?, url=?, tid=? 
            where gid = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sisii', $jsonData['name'], $jsonData['price'], $jsonData['url'], $jsonData['tid'], $gid);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});

/* ลบสินค้า */
$app->delete('/goods/{gid}', function (Request $request, Response $response, $args) {
    $gid = $args['gid'];
    $conn = $GLOBALS['connect'];

    $sql = 'DELETE from goods 
            where gid = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $gid);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});
