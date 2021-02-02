<?php
/**
 * Created by Crypt
 * @Desc
 * @Author liulei email 369968620@qq.com
 * Date 2021/2/2 10:56 上午
 */
namespace quickwx;
use quickwx\sdk\OpenApi;

class WxTool
{
    use OpenApi;

    private $token;

    private $encodingAesKey;

    private $appId;

    public function __construct($token, $encodingAesKey, $appId)
    {
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->appId = $appId;
    }
}
