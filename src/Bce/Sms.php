<?php
namespace Bce;

use Bce\Core\HttpUtil;
use Bce\Core\SampleSigner;
use Bce\Core\SignOption;

class Sms
{
    private static $_instance = null;

    protected static $endPoint = 'smsv3.bj.baidubce.com';
    //AK
    protected static $accessKey;
    //SK
    protected static $secretAccessKey;

    /**
     * 私有化默认构造方法，保证外界无法直接实例化
     */
    private function __construct()
    {

    }

    public static function config(array $config)
    {
        SampleSigner::__init();
        HttpUtil::__init();

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        self::$accessKey       = $config['accessKey'];
        self::$secretAccessKey = $config['secretAccessKey'];
        return self::$_instance;
    }

    public function send($message_array, $config = null)
    {

        $init_array = [
            "invokeId"     => "2ZWOI6xV-A8pZ-****",
            "templateCode" => "smsTpl:e7476122a1c24*********",
        ];
        $message_array = array_merge($init_array, $message_array);
        //生成json格式
        $json_data = json_encode($message_array);

        //生成签名
        $signer      = new SampleSigner();
        $credentials = array("ak" => self::$accessKey, "sk" => self::$secretAccessKey);
        $httpMethod  = "POST";
        $path        = "/api/v3/sendSms";
        $params      = array();
        $timestamp   = new \DateTime();
        $timestamp->setTimezone(new \DateTimeZone("GMT"));
        $datetime     = $timestamp->format("Y-m-d\TH:i:s\Z");
        $datetime_gmt = $timestamp->format("D, d M Y H:i:s T");

        $headers                         = array("Host" => self::$endPoint);
        $str_sha256                      = hash('sha256', $json_data);
        $headers['x-bce-content-sha256'] = $str_sha256;
        $headers['Content-Length']       = strlen($json_data);
        $headers['Content-Type']         = "application/json";
        $headers['x-bce-date']           = $datetime;
        $options                         = array(SignOption::TIMESTAMP => $timestamp, SignOption::HEADERS_TO_SIGN => array('host', 'x-bce-content-sha256'));
        $ret                             = $signer->sign($credentials, $httpMethod, $path, $headers, $params, $options);
        $headers_curl                    = [
            'Content-Type:application/json',
            'Host:' . self::$endPoint,
            'x-bce-date:' . $datetime,
            'Content-Length:' . strlen($json_data),
            'x-bce-content-sha256:' . $str_sha256,
            'Authorization:' . $ret,
            "Accept-Encoding: gzip,deflate",
            'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
            'Date:' . $datetime_gmt,
        ];

        $url  = 'http://' . self::$endPoint . $path;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_curl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }
}
