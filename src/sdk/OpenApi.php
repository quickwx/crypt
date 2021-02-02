<?php
/**
 * Created by crypt
 * @Desc
 * @Author liulei email 369968620@qq.com
 * Date 2021/2/2 3:41 下午
 */
namespace quickwx\sdk;

use quickwx\tool\Prpcrypt;
use quickwx\tool\SHA1;
use quickwx\tool\XMLParse;
use quickwx\tool\ErrorCode;

trait OpenApi
{

    /**
     * @param $replyMsg
     * @param $timeStamp
     * @param $nonce
     * @param $encryptMsg
     * @return int|mixed
     */
    public function encryptMsg($replyMsg, $timeStamp, $nonce, &$encryptMsg)
    {
        $pc = new Prpcrypt($this->encodingAesKey);

        //加密
        $array = $pc->encrypt($replyMsg, $this->appId);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }

        if ($timeStamp == null) {
            $timeStamp = time();
        }
        $encrypt = $array[1];

        //生成安全签名
        $sha1 = new SHA1;
        $array = $sha1->getSHA1($this->token, $timeStamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];

        //生成发送的xml
        $xmlparse = new XMLParse;
        $encryptMsg = $xmlparse->generate($encrypt, $signature, $timeStamp, $nonce);
        return ErrorCode::$OK;
    }


    /**
     * @param $msgSignature
     * @param $timestamp
     * @param $nonce
     * @param $postData
     * @param $msg
     * @return int|mixed
     */
    public function decryptMsg($msgSignature, $timestamp , $nonce, $postData, &$msg)
    {
        if (strlen($this->encodingAesKey) != 43) {
            return ErrorCode::$IllegalAesKey;
        }
        $pc = new Prpcrypt($this->encodingAesKey);

        //提取密文
        $xmlparse = new XMLParse;
        $array = $xmlparse->extract($postData);
        $ret = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        if ($timestamp == null) {
            $timestamp = time();
        }

        $encrypt = $array[1];
        $touser_name = $array[2];

        //验证安全签名
        $sha1 = new SHA1;
        $array = $sha1->getSHA1($this->token, $timestamp, $nonce, $encrypt);
        $ret = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        $signature = $array[1];
        if ($signature != $msgSignature) {
            return ErrorCode::$ValidateSignatureError;
        }
        $result = $pc->decrypt($encrypt, $this->appId);

        if ($result[0] != 0) {
            return $result[0];
        }
        $msg = $result[1];
        return ErrorCode::$OK;
    }
}
