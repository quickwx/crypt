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
     * 解密第三方平台推送的密文
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



    /**
     * 获取最新的ticket
     */
    public function start_push_ticket($component_secret)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_start_push_ticket';




    }


    /**
     * 获取令牌
     * @param $component_appid  第三方平台
     * @param $component_appsecret  第三方平台
     * @param $component_verify_ticket  微信后台推送的
     */
    public function component_token($component_appid,$component_appsecret,$component_verify_ticket)
    {

    }

    /**
     * 创建一个预授权码
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     * @param $component_appid  第三方平台 appid
     */
    public function create_preauthcode($component_access_token,$component_appid)
    {

    }


    /**
     * 获取授权信息
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     * @param $component_appid 第三方平台  appid
     * @param $authorization_code  授权码, 会在授权成功时返回给第三方平台
     */
    public function query_auth($component_access_token,$component_appid,$authorization_code)
    {

    }

    /**
     * @param $component_access_token  第三方平台component_access_token
     * @param $component_appid  第三方平台 appid
     * @param $authorizer_appid  授权方 appid
     * @param $authorizer_refresh_token  	刷新令牌，获取授权信息时得到
     */
    public function authorizer_token($component_access_token,$component_appid,$authorizer_appid,$authorizer_refresh_token)
    {

    }

    /**
     * 获取授权方的帐号基本信息
     * @param $component_access_token 第三方平台component_access_token，不是authorizer_access_token
     * @param $component_appid  第三方平台 appid
     * @param $authorizer_appid  授权方 appid
     */
    public function get_authorizer_info($component_access_token,$component_appid,$authorizer_appid)
    {

    }



    /**
     * 获取授权方的帐号基本信息
     * @param $component_access_token 第三方平台component_access_token，不是authorizer_access_token
     * @param $component_appid  第三方平台 appid
     * @param $authorizer_appid  授权方 appid
     * @param $option_name  选项名称
     */
    public function get_authorizer_option($component_access_token,$component_appid,$authorizer_appid,$option_name)
    {

    }


    /**
     * 拉取所有已授权的帐号信息
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     * @param $component_appid  第三方平台 appid
     * @param int $offset 偏移位置/起始位置
     * @param int $count 拉取数量，最大为 500
     */
    public function get_authorizer_list($component_access_token,$component_appid,$offset = 0,$count = 500)
    {

    }

    /**
     * 创建一个微信小程序
     * @param $component_access_token
     * @param $name
     * @param $code
     * @param $code_type
     * @param $legal_persona_wechat
     * @param $legal_persona_name
     * @param $component_phone
     */
    public function create_program($component_access_token,$name,$code,$code_type,$legal_persona_wechat,$legal_persona_name,$component_phone)
    {

    }







}
