<?php
    $servername = '202.28.34.197';//************************************** server name: 202.28.34.197 หรือ localhost
    $username = 'web65_64011212108';//************************************** user name: web65_64011212108 หรือ loot
    $password = '64011212108@csmsu';//************************************** password: 64011212108@csmsu หรือ loot
    $dbname = 'web65_64011212108';//************************************** database name: ชื่อฐานข้อมูล web65_64011212108 หรือ ถ้าเป็นอื่น ก็ใช้ชื่ออื่น

    $connect = new mysqli($servername, $username, $password, $dbname);// เชื่อมต่อ database แล้วเก็บไว้ในตัวแปล
    $connect->set_charset("utf8");

