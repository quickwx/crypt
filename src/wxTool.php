<?php
/**
 * Created by Crypt
 * @Desc
 * @Author liulei email liulei@369968620.com
 * Date 2021/2/2 10:56 上午
 */
namespace quickwx;

class wxTool
{

    private $token;

    private $encodingAesKey;

    private $appId;

    public function __construct($token, $encodingAesKey, $appId)
    {
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->appId = $appId;
    }


    public function decryptMsg ($msgSignature, $timestamp = null, $nonce, $postData, &$msg)
    {
        $msg = 1;
        return $msg;
    }
}
