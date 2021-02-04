<?php
/**
 * Created by Crypt
 * @Desc
 * @Author liulei email 369968620@qq.com
 * Date 2021/2/2 10:56 上午
 */
namespace quickwx;

use GuzzleHttp\Client;
use quickwx\sdk\OpenApi;

class WxTool
{
    use OpenApi;

    private $component_appid;

    private $component_secret;

    private $token;

    private $encodingAesKey;

    private $client;




    public function __construct($component_appid,$component_secret,$token,$encodingAesKey)
    {
        $this->component_appid = $component_appid;

        $this->component_secret = $component_secret;

        $this->token = $token;

        $this->encodingAesKey = $encodingAesKey;

        $this->client = new Client();

    }



}
