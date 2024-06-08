<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/* แสดง customer ทั้งหมด */
$app->get('/customer', function (Request $request, Response $response) {
    /* ใช้ตัวแปลเดียวกันจากไฟล์ dbconnect.php */
    $conn = $GLOBALS['connect'];
    /* คำสั่ง sql */
    $sql = 'SELECT * from customer';
    /* ส่งคำสั่งไปที่ sql แล้วจะได้ข้อมูลมา */
    $stmt = $conn->prepare($sql);
    /* สั่งให้ส่งไปทำงาน */
    $stmt->execute();
    /* ดึงค่าที่ได้จาก sql มา */
    $result = $stmt->get_result();
    
    /* สร้างตัวแปล array */
    $data = array();

    /* วนรอบเพื่อเอาข้อมูลที่ได้ ไปเก็บไว้ใน array */
    foreach ($result as $row) {
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่ได้มาจากการร้องขอไปยัง server(JSON) ให้เป็น Object */
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* เสร็จสิ้น */
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});

/* แสดง customer จากการค้นหาด้วย cid */
$app->get('/customer/{cid}', function (Request $request, Response $response, $argc) {// ตัวแปล argc คือ Array ของ ค่าที่รับมาจาก http ในที่นี้ คือ cid
    /* ใช้ตัวแปลเดียวกันจากไฟล์ dbconnect.php */
    $conn = $GLOBALS['connect'];
    /* คำสั่ง sql */
    $sql = 'SELECT * from customer where cid = ?';
    /* ส่งคำสั่งไปที่ sql แล้วจะได้ข้อมูลมา */
    $stmt = $conn->prepare($sql);

    /* ส่งตัวแปลที่รับมา ไปให้ sql */
    $stmt->bind_param('i', $argc['cid']);// i คือ int

    /* สั่งให้ทำงาน */
    $stmt->execute();
    /* ดึงข้อมูลออกมา */
    $result = $stmt->get_result();

    /* สร้าง array */
    $data = array();

    /* วนรอบ เอาข้อมูลที่ได้จากการ request ไปที่ server มาเก็บลง array*/
    foreach ($result as $row) {
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่ได้มาจากการร้องขอไปยัง server(JSON) ให้เป็น Object */
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* เสร็จสิ้น */
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
    
});

/* แสดง customer จากการค้นหาด้วย ชื่อ */
$app->get('/customer/name/{name}', function (Request $request, Response $response, $args) {
    /* รูปแบบการค้นหา string ใน sql */
    $name = '%'.$args['name'].'%';

    $conn = $GLOBALS['connect'];

    $sql = 'SELECT * from customer where name like ?';

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('s', $name);// s คือ string

    $stmt->execute();

    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {
         array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);

});
//===================================================================================================================================login

/* login ตรวจสอบ email - password ของ customer ถ้ามีใน databast จะส่ง ข้อมูลของ customer คนนั้นไป*/
$app->post('/customer/login' , function (Request $request, Response $response) {
    /* ใช้ตัวแปล ที่เชื่อ databast แล้ว */
    $conn = $GLOBALS['connect'];

    /* ดึงข้อมูลจาก from ที่ให้กรอกข้อมูล */
    $json = $request->getBody();

    /* แปลงข้อมูลที่ได้จากการกรอก from เข้ามา(json) ให้เป็น object ของภาษา php */
    $jsonData = json_decode($json, true);//true คือ ให้เป็น associative array คือ array ที่อ่างอิงข้อมูลด้วยชื่อได้

    /* คำสั่ง sql */
    $sql = 'SELECT * from customer where email = ?';

    /* เตรียมพร้อมคำสั่ง sql*/
    $stmt = $conn->prepare($sql);

    /* ส่ง email ที่รับเข้ามาไปที่ sql */
    $stmt->bind_param("s", $jsonData['email']);

    /* สั่งให้ทำงาน */
    $stmt->execute();

    /* จำนวนของ row ที่ได้รับผลกระทบ ($result คือ array)*/
    $result = $stmt->get_result();

    // /* สร้าง array เพื่อรอรับข้อมูล */
    $data = array();

    /* ถ้ามี row เดียว */
    if ($result->num_rows == 1) {

        /* แปลง เป็น associative array */
        $row = $result->fetch_assoc();

        /* เอา password ของ email นั้น ออมา */
        $passwordInDB_hash = $row["password"];

        /* เปรียบเทียบ password ที่รับเข้ามา กับ password(password_hash) ที่เอามาจาก database ของ email นั้น */
        if (password_verify($jsonData['password'], $passwordInDB_hash)) {
            
            /* คำสั่ง sql */
            $sql = 'SELECT * from customer where email = ?';

            /* เตรียมพร้อมคำสั่ง sql*/
            $stmt = $conn->prepare($sql);

            /* ส่ง email ที่รับเข้ามาไปที่ sql */
            $stmt->bind_param("s", $jsonData['email']);

            /* สั่งให้ทำงาน */
            $stmt->execute();

            /* จำนวนของ row ที่ได้รับผลกระทบ ($result คือ array)*/
            $result = $stmt->get_result();

            /* วนรอบเข้าไปใน array $result เพื่อเอาข้อมูลของคนที่ login ออกมา (มีแค่คนเดียว) */
            foreach ($result as $row) {
                 array_push($data, $row);

                /* แปลงข้อมูลที่ได้มาจากการร้องขอไปยัง server(JSON) ให้เป็น Object แล้วเขียนลงใน body ของ $response*/
                $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)); //การ login จะได้ข้อมูลของ คนที่มา login เพียงแค่คนเดียวเท่านั้น ดังนั้นจึงส่ง $row กลับออกไป เพราะมีแค่คนเดียว

                /* เสร็จสิ้น จึง return $response กลับออกไป */
                return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
            }

        }
        /* else นี้ คือ มี Email นั้นอยู่ใน databast แต่ใส่ Password ไม่ถูกต้อง */
        else {
            $response->getBody()->write(json_encode("Password ไม่ถูกต้อง"));
            return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
        }
    }
    /* else นี้ คือ ไม่มี Email นั้น อยู่ใน databast */
    else {
        $response->getBody()->write(json_encode("ไม่มี Email นี้ อยู่ใน ฐานข้อมูล คุณต้อง สมัคนสมาชิกเสียก่อน"));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
    }

});
//========================================================================================================================================

/* เพิ่มข้อมูล customer */
$app->post('/customer', function (Request $request, Response $response) {

    /* ใช้ตัวแปลเดียวกันกับตัวที่เชื่อม databast ไว้แล้ว */
    $conn = $GLOBALS['connect'];

    /* ดึงข้อมูลจาก from ที่ให้กรอกข้อมูล */
    $json = $request->getBody();

    /* แปลงข้อมูลที่ได้จากการกรอก from เข้ามา(json) ให้เป็น object ของภาษา php */
    $jsonData = json_decode($json, true);//true คือ ให้เป็น associative array คือ array ที่อ่างอิงข้อมูลด้วยชื่อได้

    /* คำสั่ง sql ดึง email ของ customer ทุกคนออกมา */
    $sql = 'SELECT email FROM customer WHERE email = ?';

    /* เตรียมพร้อมคำสั่ง */
    $stmt = $conn->prepare($sql);

    /* กำหนดตัวแปลให้คำสั่ง sql */
    $stmt->bind_param('s', $jsonData['email']);

    /* ส่งคำสั่งไปทำงาน */
    $stmt->execute();

    /* ได้รับ email กลับมา หลังจากส่งคำสั่งไป */
    $result = $stmt->get_result();

    /* ถ้ามี email นี้อยู่ใน databast แล้ว จะไม่สามารถ ใช้ email นี้ได้อีก */
    if ($result->num_rows > 0) {

        /* ส่งข้อความว่า มี email นี้ใน databast แล้ว */
        $response->getBody()->write(json_encode("Email นี้ถูกใช้งานแล้ว"));

    }

    /* ถ้าไม่เคยมี Email นี้ใน databast เลย ก็จะทำการเพิ่มผู้ใช้ใหม่ */
    else {

        /* แปลง password ที่ customer ป้อนเข้ามา ให้อ่านไม่ออก เพื่อเอาไปเก็บไว้ใน databast*/
        $hash_password = password_hash($jsonData['password'], PASSWORD_DEFAULT);

        $sql = 'INSERT into customer (name, phone, address, email, password) values (?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($sql);

        $stmt->bind_param('sssss', $jsonData['name'], $jsonData['phone'], $jsonData['address'], $jsonData['email'], $hash_password);

        $stmt->execute();
        /* จำนวน colum ที่ได้รับผลกระทบ - จำนวนข้อมูลที่ได้เข้าไป */
        $affected = $stmt->affected_rows;

        /* ถ้าเพิ่มข้อมูลได้ */
        if ($affected > 0) {
            /* ให้แสดงว่า เสร็จสิ้น และ cid ที่ถูกเพิ่ม */
            $data = ["affected_rows" => $affected, "last_cid" => $conn->insert_id];

            $response->getBody()->write(json_encode($data));
        }
    }

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

/* แก้ไขข้อมูล customer */
$app->put('/customer/{cid}', function (Request $request, Response $response, $args) {
    /* ดึงข้อมูลมาจาก from ที่ให้กรอก */
    $json = $request->getBody();

    /* แปลงข้อมูลเป็นแบบ associatiอe array คือ array ที่อ่างอิงข้อมูลด้วยชื่อได้ key และ value*/
    $jsonData = json_decode($json, true);

    /* แปลง password ที่ customer ป้อนเข้ามา ให้อ่านไม่ออก เพื่อเอาไปเก็บไว้ใน databast*/
    $hash_password = password_hash($jsonData['password'], PASSWORD_DEFAULT);

    $cid = $args['cid'];
    /* เชื่อมต่อฐานข้อมูล จากตัวแปลที่อยู่ในไฟล์ dbconnect.php GLOBALS จะไปวิ่งหาชื่อตัวแปล connect */
    $conn = $GLOBALS['connect'];

    $sql = 'UPDATE customer 
            set name=?, phone=?, address=?, email=?, password=? 
            where cid = ?';

    $stmt = $conn->prepare($sql);
    /* ส่งตัวแปลไปให้ sql */
    $stmt->bind_param('sssssi', $jsonData['name'], $jsonData['phone'], $jsonData['address'], $jsonData['email'], $hash_password, $cid);

    $stmt->execute();

    $affected = $stmt->affected_rows;

    if ($affected > 0) {
        
        $data = ["affected_rows" => $affected];
        
        $response->getBody()->write(json_encode($data));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});

/* ลบข้อมูล customer */
$app->delete('/customer/{cid}', function (Request $request, Response $response, $argc) {
    /* เก็บ cid ที่ได้จาก url */
    $cid = $argc['cid'];

    $conn = $GLOBALS['connect'];

    $sql = 'DELETE from customer 
            where cid = ?';

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('i', $cid);

    $stmt->execute();

    $affected = $stmt->affected_rows;

    if ($affected > 0) {

        $data = ["affected_rows" => $affected];

        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
});