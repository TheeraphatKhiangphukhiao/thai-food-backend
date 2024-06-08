<?php

/* เรียกใช้การ Response */
use Psr\Http\Message\ResponseInterface as Response;
/* เรียกใช้การ Request */
use Psr\Http\Message\ServerRequestInterface as Request;


/* แสดง order ทั้งหมด*/
$app->get('/iorder', function(Request $request, Response $response) {
    /* เชื่อต่อ databast -> GLOBALS จะไปค้นห้ตัวแปลที่ชื่อ connect*/
    $conn = $GLOBALS['connect'];

    /* คำสั่ง sql แสดง รายการ oder ทั้งหมด*/
    $sql = 'SELECT	iorder.oid, iorder.customer_name as customer_name, iorder.address as customer_address, iorder.status, iorder.phone as customer_phone
            FROM	iorder';

    /* เตรียมพร้อมเอาคำสั่ง sql ไปทำงาน */
    $stmt = $conn->prepare($sql);

    /* ส่งคำสั่ง sql ไปทำงานที่ databast*/
    $stmt->execute();

    /* จะได้ข้อมูลอกกมา จากการส่งคำสั่งไป */
    $result = $stmt->get_result();

    /* สร้าง array เพื่อเก็บข้อมูลที่ได้ จาก databast */
    $data = array();

    /* วนรอบตามจำนวนข้อมูลที่มา เพื่อเอาไปเก็บลง array */
    foreach ($result as $row) {
        /* เอาข้อมูลของแต่ละแถว เข้าไปใน array */
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่ได้(jsonString) ให้เป็น json ทั่วไป เพื่อให้ web เรียกใช้งานข้อมูล json นี้ได้*/
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* return ตัวแปล $response เพราะ เมื่อเรียกใช้งาน api(get)(/iorder) นี้ ก็จะได้ข้อมูลชุดนี้ไป*/
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);//แสดงข้อความ และสถานะ ออกทางหน้าจอ
});


/* แสดง order ตามสถานะต่างๆ*/
$app->get('/iorder/status/{text}', function(Request $request, Response $response, $argc) {
    /* เชื่อต่อ databast -> GLOBALS จะไปค้นห้ตัวแปลที่ชื่อ connect*/
    $conn = $GLOBALS['connect'];

    /* คำสั่ง sql แสดง รายการ oder ทั้งหมด*/
    $sql = 'SELECT	iorder.oid, iorder.customer_name as customer_name, iorder.address as customer_address, iorder.status, iorder.phone as customer_phone
            FROM	iorder
            WHERE   status = ?';

    /* เตรียมพร้อมเอาคำสั่ง sql ไปทำงาน */
    $stmt = $conn->prepare($sql);

    /* กำหนดตัวแปล */
    $stmt->bind_param('s', $argc['text']);

    /* ส่งคำสั่ง sql ไปทำงานที่ databast*/
    $stmt->execute();

    /* จะได้ข้อมูลอกกมา จากการส่งคำสั่งไป */
    $result = $stmt->get_result();

    /* สร้าง array เพื่อเก็บข้อมูลที่ได้ จาก databast */
    $data = array();

    /* วนรอบตามจำนวนข้อมูลที่มา เพื่อเอาไปเก็บลง array */
    foreach ($result as $row) {
        /* เอาข้อมูลของแต่ละแถว เข้าไปใน array */
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่ได้(jsonString) ให้เป็น json ทั่วไป เพื่อให้ web เรียกใช้งานข้อมูล json นี้ได้*/
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* return ตัวแปล $response เพราะ เมื่อเรียกใช้งาน api(get)(/iorder) นี้ ก็จะได้ข้อมูลชุดนี้ไป*/
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);//แสดงข้อความ และสถานะ ออกทางหน้าจอ
});


/* แสดงรายการ order ของาลูกค้าคนนั้น */
$app->get('/iorder/customer/{cid}', function (Request $request, Response $response, $argc) {

    /* ใช้ตัวแปลเดียวกันกับที่เชื่อม databast ไว้แล้ว */
    $conn = $GLOBALS['connect'];

    /* คำสั่ง sql แสเงรายการ order ของ ลูกค้าคนนั้นๆ */
    $sql = 'SELECT	iorder.oid, iorder.customer_name as customer_name, iorder.address as customer_address, iorder.status, iorder.phone as customer_phone
            FROM	iorder, customer
            WHERE	iorder.cid = customer.cid
            AND     iorder.cid = ?';

    /* เตรียมพร้อมเอาคำสั่ง sql ไปทำงาน */
    $stmt = $conn->prepare($sql);

    /* ส่งตัว oid ที่รับเข้ามาไป */
    $stmt->bind_param('i', $argc['cid']);

    /* ส่งคำสั่ง sql ไปทำงานที่ databast*/
    $stmt->execute();

    /* จะได้ข้อมูลอกกมา จากการส่งคำสั่งไป */
    $result = $stmt->get_result();

    /* สร้าง array เพื่อเก็บข้อมูลที่ได้ จาก databast */
    $data = array();

    /* วนรอบตามจำนวนข้อมูลที่มา เพื่อเอาไปเก็บลง array */
    foreach ($result as $row) {
        /* เอาข้อมูลของแต่ละแถว เข้าไปใน array */
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่ได้(jsonString) ให้เป็น json ทั่วไป เพื่อให้ web เรียกใช้งานข้อมูล json นี้ได้*/
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* return ตัวแปล $response เพราะ เมื่อเรียกใช้งาน api(get)(/iorder) นี้ ก็จะได้ข้อมูลชุดนี้ไป*/
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);//แสดงข้อความ และสถานะ ออกทางหน้าจอ

});


/* แสดงรายการสินค้าที่อยู่ใน order นั้นๆ */
$app->get('/iorder/{oid}', function(Request $request, Response $response, $argc) {
    /* เชื่อต่อ databast -> GLOBALS จะไปค้นห้ตัวแปลที่ชื่อ connect*/
    $conn = $GLOBALS['connect'];

    /* คำสั่ง sql แสดง รายการ oder ทั้งหมดที่ลูกค้าได้สั่งไว้ */
    $sql = 'SELECT goods.gid, goods.url, goods.name, goods.price, consist_of.amount, SUM(goods.price*consist_of.amount) as total
            FROM	goods, iorder, consist_of
            WHERE	goods.gid = consist_of.gid
            AND		consist_of.oid = iorder.oid
            AND		iorder.oid = ?
            GROUP BY goods.gid, goods.url, goods.name, iorder.oid, goods.price, consist_of.amount';

    /* เตรียมพร้อมเอาคำสั่ง sql ไปทำงาน */
    $stmt = $conn->prepare($sql);

    /* ส่งตัว oid ที่รับเข้ามาไป */
    $stmt->bind_param('i', $argc['oid']);

    /* ส่งคำสั่ง sql ไปทำงานที่ databast*/
    $stmt->execute();

    /* จะได้ข้อมูลอกกมา จากการส่งคำสั่งไป */
    $result = $stmt->get_result();

    /* สร้าง array เพื่อเก็บข้อมูลที่ได้ จาก databast */
    $data = array();

    /* วนรอบตามจำนวนข้อมูลที่มา เพื่อเอาไปเก็บลง array */
    foreach ($result as $row) {
        /* เอาข้อมูลของแต่ละแถว เข้าไปใน array */
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่ได้(jsonString) ให้เป็น json ทั่วไป เพื่อให้ web เรียกใช้งานข้อมูล json นี้ได้*/
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* return ตัวแปล $response เพราะ เมื่อเรียกใช้งาน api(get)(/iorder) นี้ ก็จะได้ข้อมูลชุดนี้ไป*/
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);//แสดงข้อความ และสถานะ ออกทางหน้าจอ
});


/* แก้ไขสถานะของ order นั้นๆ */
$app->put('/iorder/{oid}', function (Request $request, Response $response, $argc) {
    /* ใช้ตัวแปลเดียวกันกับ ตัวที่เชื่อม databast */
    $conn = $GLOBALS['connect'];

    /* get ข้อมูล ออกมาจาก from ที่กรอกข้อมูลเอาไว้ */
    $json = $request->getBody();

    /* แปลงข้อมูลเป็บแบบ Associative Array -> key และ value*/
    $jsonData = json_decode($json, true);

    /* คำสั่ง sql แปลี่ยน status ของ order นั้นๆ*/
    $sql = 'UPDATE iorder 
            SET status = ? 
            WHERE iorder.oid = ?';

    /* คำสั่ง sql เตรียม พร้อมทำงาน*/
    $stmt = $conn->prepare($sql);

    /* ส่งค่าให้กับตัวแปล ที่อยู่ใน sql */
    $stmt->bind_param('si', $jsonData['status'], $argc['oid']);

    /* ส่งคำสั่งไปทำงานที่ sql */
    $stmt->execute();

    /* จำนวน แถวที่ได้รับผลกระทบจากการที่ได้ส่งคำสั่งไปทำงานแล้ว */
    $affected = $stmt->affected_rows;

    /* ถ้ามี row ที่ได้ผลกระทบ ให้ตอบกลับไปว่าเสร็จสิ้น */
    if ($affected > 0) {
        
        /* จำวน row ที่ได้รับผลกระทบ */
        $data = ["affected_rows" => $affected];

        /* เขียนผลรับใน $response ในส่วนของ body โดยแปลงเป็น json ธรรมดา*/
        $response->getBody()->write(json_encode($data));

        /* ส่งกลับค่าสถานะการเสร็จสิ้น */
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

});

