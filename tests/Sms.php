<?php
namespace Tests;

use Bce\Sms;

require_once __DIR__ . '/../vendor/autoload.php';

$to = [
    "signatureId" => "sms-sign-ZzfnPv11512", //如果在类中已经设定好，则此处无需再写
    "template"    => "sms-tmpl-XtxkFX14567", //如果在类中已经设定好，则此处无需再写
    "mobile"      => "***",
    "contentVar"  => [
        "content" => str_shuffle(rand(100000, 999999)), //模板里面的变量
    ],
];
$res = Sms::config([
    'accessKey'       => '***',
    'secretAccessKey' => '***',
])->send($to);

var_dump($res); //打印返回结果
