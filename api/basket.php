<?php

/* เรียกใช้การ Response */
use Psr\Http\Message\ResponseInterface as Response;
/* เรียกใช้การ Request */
use Psr\Http\Message\ServerRequestInterface as Request;

/* แสดงสินค้าที่อยู่ในตะกร้า ของ customer นั้นๆ ตาม cid */
$app->get('/basket/{cid}', function (Request $request, Response $response, $argc) {
    /* ใช้ตัวแปลที่เชื่อ databast แล้ว */
    $conn = $GLOBALS['connect'];

    /* คำสั่ง sql แสดง รายการสินค้าในตะกร้า ของลูกค้าคนนั้น*/
    $sql = 'SELECT	goods.gid, goods.url, goods.name, goods.price, basket.amount, SUM(goods.price*basket.amount) as total
            FROM	goods, customer, basket
            WHERE	goods.gid = basket.gid
            AND		basket.cid = customer.cid
            AND		basket.cid = ?
            GROUP BY goods.gid, goods.url, goods.name, goods.price, basket.amount';

    /* เตรียมพร้อมคำสั่ง sql */
    $stmt = $conn->prepare($sql);

    /* กำหนดตัวแปลในคำสั่ง sql */
    $stmt->bind_param('i', $argc['cid']);

    /* ส่งคำสั่ง sql ไปทำงาน */
    $stmt->execute();

    /* รับข้อมูลกลับมาจาก databast */
    $result = $stmt->get_result();

    /* สร้าง array เพื่อเก็บข้อมูลที่ได้มา */
    $data = array();

    /* วนรอบเอาข้อมูลไปเก็บเข้า array */
    foreach ($result as $row) {
        array_push($data, $row);
    }

    /* แปลงข้อมูลที่อยู่ใน array data ให้เป็น json */
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

    /* ส่งกลับ ข้อมูลที่แปลงเป็น json แล้วแสดงบนหน้าทดสอบ*/
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});

//================================================================================================================เพิ่มสินค้าเข้าตะกร้า
/* เพิ่มสินค้าเข้าตะกร้าของ cusotmer คนนั้นๆ */
$app->post('/basket', function (Request $request, Response $response) {
    /* ใช้ตัวแปลที่เชื่อ databast แล้ว */
    $conn = $GLOBALS['connect'];

    /* ดึงข้อมูลออกมาจาก body เป็น json */
    $json = $request->getBody();

    /* แปลง json ให้อยู่ในรูป associatice array ที่ php ใช้งานได้ */
    $jsonData = json_decode($json, true);

    /* คำสั่ง sql ตรวจสอบว่า customer คนนี้ มีสินค้าชิ้นนี้ อยู่ในตะกร้าหรือไม่ */
    $sql = 'SELECT * 
            from    basket
            where   cid = ?
            and     gid = ?';

    /* เตรียมพร้อมคำสั่ง sql */
    $stmt = $conn->prepare($sql);

    /* กำหนดตัวแปล ให้กับคำสั่ง sql */
    $stmt->bind_param('ii', $jsonData['cid'], $jsonData['gid']);

    /* ส่งคำสั่ง sql ไปทำงาน */
    $stmt->execute();

    /* row ที่ได้รับผลกระทบ */
    $result = $stmt->get_result();

    /* ถ้าลูกค้าคนนั้น มีสินค้าชิ้นนั้น อยู่แล้ว ก็จะเพิ่มจำนวนของสินค้าชิ้นนั้น ของลูกค้าคนนั้น*/
    if ($result->num_rows > 0) {
        
        /* คำสั่ง update สินค้าชิ้นนั้น ข้อมลูกค้าคนนั้น */
        $sql = 'UPDATE  basket 
                SET     amount = amount + (?) 
                WHERE   basket.cid = ? 
                AND     basket.gid = ?';

        /* เตรียมพร้อมคำสั่ง sql */
        $stmt = $conn->prepare($sql);

        /* กำหนดตัวแปล ให้กับคำสั่ง sql */
        $stmt->bind_param('iii', $jsonData['amount'], $jsonData['cid'], $jsonData['gid']);

        /* สั่งให้ทำงาน */
        $stmt->execute();

        /* จำวน colum ที่ได้รับผลกระทบ - จำนวนข้อมูลที่ได้เข้าไปใน databast */
        $affected = $stmt->affected_rows;

        /* ถ้ามี row ที่ถูก update */
        if ($affected > 0) {
            /* สร้าง array data เพื่อแสดงข้อมูล */
            $data = ["affected_rows" => $affected, "เพิ่ม-ลด จำนวนของสินค้า gid" => $jsonData['gid'], "ในตะกร้าของ customer cid" => $jsonData['cid']];
            /* แปลง array data เป็น jaon แล้วเอาไปเก็บไว้ใน response */
            $response->getBody()->write(json_encode($data));
            /* ส่ง response กลับออกไป */
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
    /* ถ้าลูกค้าคนนั้น ไม่เคยมีสินค้าชิ้นนั้นอยู่ในตะกร้าเลย จะทำการเพิ่มสิ้นค้นชิ้นนั้น ของลูกค้าคนนั้นเข้าไปในตะกร้า */
    else {

        /* คำสั่ง sql insert ข้อมูลใหม่*/
        $sql = 'INSERT into basket (cid, gid, amount) values (?, ?, ?)';

        /* เตรียมพร้อมคำสั่ง sql */
        $stmt = $conn->prepare($sql);

        /* กำหนดตัวแปล ให้กับคำสั่ง sql */
        $stmt->bind_param('iii', $jsonData['cid'], $jsonData['gid'], $jsonData['amount']);

        /* สั่งให้ทำงาน */
        $stmt->execute();

        /* จำวน colum ที่ได้รับผลกระทบ - จำนวนข้อมูลที่ได้เข้าไปใน databast */
        $affected = $stmt->affected_rows;

        /* ถ้ามี row ที่ถูก เพิ่มข้อมูล */
        if ($affected > 0) {
            /* สร้าง array data เพื่อแสดงข้อมูล */
            $data = ["affected_rows" => $affected, "เพิ่งสินค้า gid" => $jsonData['gid'], "เข้าไปในตะกร้าของ customer cid" => $jsonData['cid']];
            /* แปลง array data เป็น jaon แล้วเอาไปเก็บไว้ใน response */
            $response->getBody()->write(json_encode($data));
            /* ส่ง response กลับออกไป */
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
});


/* ลบสินค้าชิ้นนั้นๆ ของลูกค้าคนๆนั้น ออกจากตะกร้า */
$app->post('/basket/delete', function (Request $request, Response $response) {
    /* ใช้ตัวแปลตัวเดัยวกัน ที่เชื่อ databast ไว้แล้ว */
    $conn = $GLOBALS['connect'];

    /* ดึงข้อมูลออกมาจาก body ที่เป็น json */
    $json = $request->getBody();

    /* แปลง json ที่ได้มาจากการ request ไปเป็น associative array สำหรับ php */
    $jsonData = json_decode($json, true); // true คือ ทำให้เป็น associative array

    /* คำสั่ง sql ลบสินค้าชิ้นนั้นๆ ของคนๆนั้น ออกจากตะกร้า */
    $sql = 'DELETE from basket 
            where   cid = ?
            and     gid = ?';
    
    /* เตรียมพร้อมคำสั่ง sql */
    $stmt = $conn->prepare($sql);

    /* กำหนดตัวแปลให้กับคำสั่ง sql */
    $stmt->bind_param('ii', $jsonData['cid'], $jsonData['gid']);

    /* สั่งให้คำสั่ง sql ทำงาน */
    $stmt->execute();

    /* จำนวน colum ที่ได้รับผลกระทบจากคำสั่ง sql */
    $affected = $stmt->affected_rows;

    /* ถ้ามี row ที่ถูก ลบข้อมูล */
    if ($affected > 0) {
        /* สร้าง array data เพื่อแสดงข้อมูล */
        $data = ["affected_rows" => $affected, "ลบสินค้า gid" => $jsonData['gid'], "ในตะกร้าของ customer cid" => $jsonData['cid']];
        /* แปลง array data เป็น jaon แล้วเอาไปเก็บไว้ใน response */
        $response->getBody()->write(json_encode($data));
        /* ส่ง response กลับออกไป */
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

});


//============================================================================================================================ยืนยันคำสั่งซื้อ==================================================
/* ลูกค้าใส่ ชื่อ เบอร์มือถือ และที่อยู่ ที่ต้องการให้จัดส่งของ order นั้นๆของลูกค้า */

/* ลูกค้า ยืนยันคำสั่งซื้อ ของที่อยู่ในตะกร้า ของลูกค้าคนนั้นๆ */
$app->post('/basket/order/confirmation', function (Request $request, Response $response) {

    /* ใช้ตัวแปลที่ connect เข้ากับ databast */
    $conn = $GLOBALS['connect'];

    /* ดึงข้อมูล ออกมาจาก json ที่ส่งมา */
    $json = $request->getBody();

    /* แปลงข้อมูล json ที่ได้ ให้เป็น associative array */
    $jsonData = json_decode($json, true);// true คือ ให้เป็น associative array

    /* คำสั่ง sql get สินค้า ที่อยู่ในตะกร้าของลูกค้าคนนั้น */
    $sql = 'SELECT  gid, amount 
            from    basket
            where   cid = ?';

    /* เตรียมพร้อม คำสั่ง sql */
    $stmt = $conn->prepare($sql);

    /* ดำหนดตัวแปล ? ให้กับคำสั่ง sql */
    $stmt->bind_param('i', $jsonData['cid']);

    /* ส่งคำสั่งไปทำงาน */
    $stmt->execute();

    /* รับข้อมูลที่ได้มาจาก databast -- สินค้าทั้งหมดที่อยู่ในตะกร้าของลูกค้าคนๆนี้ */
    $goodss = $stmt->get_result();

    /* ก่อนที่จะกดยืนยันคำสั่งซื้อ ลูกค้าต้องมีสินค้าในตะกร้าเสียนก่อน */
    /* ถ้ามีสินค้าอยู่ในตะกร้า */
    if ($goodss->num_rows > 0) {

        /* คำสั่ง sql insert iorder ของลูกค้าคนนี้ */
        $sql = 'INSERT INTO iorder (oid, status, cid, customer_name, phone, address) VALUES (NULL, "ยังไม่ส่ง", ?, ?, ?, ?)';

        /* เตรียมพร้อม คำสั่ง sql */
        $stmt = $conn->prepare($sql);

        /* ดำหนดตัวแปล ? ให้กับคำสั่ง sql */
        $stmt->bind_param('isss', $jsonData['cid'], $jsonData['customer_name'], $jsonData['customer_phone'], $jsonData['customer_address']);

        /* ส่งคำสั่งไปทำงาน */
        $stmt->execute();

        /* จำนวน row ได้รับผลกระทบ จากคำสั่ง insert iorder */
        $affected = $stmt->affected_rows;

        /* ถ้ามี row ที่ได้รับผลกระทบ จากคำสั่ง insert iorder */
        if ($affected == 1) {
            
            /* oid ล่าสุด ที่เพิ่งถูก insert เข้าไป ของลูกค้าคนนี้ */
            $last_oid = $conn->insert_id;

            /* วนรอบเอา รหัสสินค้า และ จำนวนที่ซื้อ ของ สินค้าแต่ละอัน ที่เป็น iorder ล่าสุด เข้าไปเก็บที่ teble consist_of */
            foreach ($goodss as $goods) {

                 /* คำสั่ง insert รหัสสินค้า และ จำนวนที่ซื้อ ของ iorder ล่าสุดของลูกค้าคนนี้ */
                $sql = 'INSERT INTO consist_of (gid, oid, amount) VALUES (?, ?, ?)';

                /* เตรียมพร้อม คำสั่ง sql */
                $stmt = $conn->prepare($sql);
                
                /* ดำหนดตัวแปล ? ให้กับคำสั่ง sql */
                $stmt->bind_param('iii', $goods['gid'], $last_oid, $goods['amount']);

                /* ส่งคำสั่งไปทำงาน */
                $stmt->execute();
            }

            /* กลังจาก ยืนยันคำสั่งซื้อแล้ว สินค้าที่อยู่ในตะกร้าของลูกค้าคนนี้ ก็จะถูกลบออกไป */
            /* คำสั่งลบสินค้าทั้งหมดที่อยู่ในตะกร้าของลูกค้าคนนี้ */
            $sql = 'DELETE FROM basket WHERE cid = ?';

            /* เตรียมพร้อม คำสั่ง sql */
            $stmt = $conn->prepare($sql);

            /* ดำหนดตัวแปล ? ให้กับคำสั่ง sql */
            $stmt->bind_param('i', $jsonData['cid']);

            /* ส่งคำสั่งไปทำงาน */
            $stmt->execute();

            /* แปลงข้อมูล ให้เป็น json */
            $response->getBody()->write(json_encode("สั่งซื้อเสร็จสิ้น"));

            /* ส่ง response กลับออกไป */
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }

    }
    /* ถ้าไม่มีสินค้าอยู่ในตะกร้า */
    else {

        /* แปลงข้อมูล ให้เป็น json */
        $response->getBody()->write(json_encode("ไม่มีสินค้าอยู่ในตะกร้า --> กดสั่งซื้อไม่ได้"));

        /* ส่ง response กลับออกไป */
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    }
});

?>