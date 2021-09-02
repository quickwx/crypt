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
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;

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
        $array = $pc->encrypt($replyMsg, $this->component_appid);
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
        $result = $pc->decrypt($encrypt, $this->component_appid);

        if ($result[0] != 0) {
            return $result[0];
        }
        $msg = $result[1];
        return ErrorCode::$OK;
    }



    /**
     * 启动ticket推送
     */
    public function start_push_ticket()
    {

        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_start_push_ticket';
        $post_data =  [
            'component_appid'=>$this->component_appid,
            'component_secret'=>$this->component_secret
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ]
        ]);

        return (string)$response->getBody();

    }


    /**
     * 获取令牌
     * @param $component_verify_ticket  微信后台推送的
     */
    public function component_token($component_verify_ticket)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $post_data =  [
            'component_appid'=>$this->component_appid,
            'component_appsecret'=>$this->component_secret,
            'component_verify_ticket'=>$component_verify_ticket
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ]
        ]);
        return (string)$response->getBody();
    }

    /**
     * 创建一个预授权码
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     */
    public function create_preauthcode($component_access_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';
        $post_data =  [
            'component_appid'=>$this->component_appid,
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>['component_access_token'=>$component_access_token]
        ]);
        return (string)$response->getBody();
    }


    /**
     * 使用授权码获取授权信息
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     * @param $authorization_code  授权码, 会在授权成功时返回给第三方平台
     */
    public function query_auth($component_access_token,$authorization_code)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';
        $post_data =  [
            'component_appid'=>$this->component_appid,
            'authorization_code'=>$authorization_code
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>['component_access_token'=>$component_access_token]
        ]);
        return (string)$response->getBody();
    }


    /**
     * 获取/刷新接口调用令牌
     * @param $component_access_token
     * @param array $authorizer_appid
     * @param array $authorizer_refresh_token
     * @return array
     */

    public function get_authorizer_token($component_access_token,$authorizer_appid = [],$authorizer_refresh_token = [])
    {

        $promises = [];

        $requests = function ($component_access_token,$authorizer_appid,$authorizer_refresh_token)  {

            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$component_access_token;

            foreach ($authorizer_appid as $k => $v){
                $post_data =  [
                    'component_appid'=>$this->component_appid,
                    'authorizer_appid'=>$v,
                    'authorizer_refresh_token'=>$authorizer_refresh_token[$k]
                ];
                $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

                $headers = [
                    'Content-Type' => 'application/json',
                    'Content-Length'     => strlen($post_str),
                ];

                yield new Request('POST', $url,$headers,$post_str);
            }
        };

        $responses = [];
        $pool = new Pool($this->client, $requests($component_access_token,$authorizer_appid,$authorizer_refresh_token), [
            'concurrency' => 500,
            'fulfilled' => function ($response, $index) use ($authorizer_appid) {
                $responses[$authorizer_appid[$index]] = (string)$response->getBody();
            },
            'rejected' => function ($reason, $index) {
                $responses[$authorizer_appid[$index]] = null;

            },
        ]);

        $promise = $pool->promise();

        $promise->wait();

        return $responses;

    }

    /**
     * 获取授权方的帐号基本信息
     * @param $component_access_token 第三方平台component_access_token，不是authorizer_access_token
     * @param $authorizer_appid  授权方 appid
     */
    public function get_authorizer_info($component_access_token,$authorizer_appid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';
        $post_data =  [
            'component_appid'=>$this->component_appid,
            'authorizer_appid'=>$authorizer_appid
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);
        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>['component_access_token'=>$component_access_token]
        ]);
        return (string)$response->getBody();
    }



    /**
     * 获取授权方选项信息
     * @param $component_access_token 第三方平台component_access_token，不是authorizer_access_token
     * @param $authorizer_appid  授权方 appid
     * @param $option_name  选项名称
     */
    public function get_authorizer_option($component_access_token,$authorizer_appid,$option_name)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';
        $post_data =  [
            'component_appid'=>$this->component_appid,
            'authorizer_appid'=>$authorizer_appid,
            'option_name'=>$option_name
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);
        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>['component_access_token'=>$component_access_token]
        ]);
        return (string)$response->getBody();
    }


    /**
     * 设置授权方选项信息
     * @param $component_access_token
     * @param $authorizer_appid
     * @param $option_name
     * @param $option_value
     */
    public function api_set_authorizer_option($component_access_token,$authorizer_appid,$option_name,$option_value)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option';

        $post_data =  [
            'component_appid'=>$this->component_appid,
            'authorizer_appid'=>$authorizer_appid,
            'option_name'=>$option_name,
            'option_value'=>$option_value
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>['component_access_token'=>$component_access_token]
        ]);
        return (string)$response->getBody();
    }


    /**
     * 拉取所有已授权的帐号信息
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     * @param $component_appid  第三方平台 appid
     * @param int $offset 偏移位置/起始位置
     * @param int $count 拉取数量，最大为 500
     */
    public function get_authorizer_list($component_access_token,$offset = 0,$count = 500)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_list';
        $post_data =  [
            'component_appid'=>$this->component_appid,
            'offset'=>$offset,
            'count'=>$count
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>[
                'component_access_token'=>$component_access_token,
            ]
        ]);
        return (string)$response->getBody();
    }

    /**
     * 创建一个微信小程序
     * @param $component_access_token  第三方平台component_access_token，不是authorizer_access_token
     * @param $name 企业名
     * @param $code 企业代码
     * @param $code_type  企业代码类型（1：统一社会信用代码， 2：组织机构代码，3：营业执照注册号）
     * @param $legal_persona_wechat 法人微信
     * @param $legal_persona_name 法人姓名
     * @param $component_phone 第三方联系电话
     */
    public function fastregisterweapp($component_access_token,$name,$code,$code_type,$legal_persona_wechat,$legal_persona_name,$component_phone)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp';
        $post_data =  [
            'name'=>$name,
            'code'=>$code,
            'code_type'=>$code_type,
            'legal_persona_wechat'=>$legal_persona_wechat,
            'legal_persona_name'=>$legal_persona_name,
            'component_phone'=>$component_phone
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>[
                'component_access_token'=>$component_access_token,
                'action'=>'create'
            ]
        ]);
        return (string)$response->getBody();
    }

    /**
     * 查询创建小程序任务状态
     */
    public function fastregisterweapp_status($component_access_token,$name,$legal_persona_wechat,$legal_persona_name)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp';
        $post_data =  [
            'name'=>$name,
            'legal_persona_wechat'=>$legal_persona_wechat,
            'legal_persona_name'=>$legal_persona_name,
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length'     => strlen($post_str),
            ],
            'query'=>[
                'component_access_token'=>$component_access_token,
                'action'=>'search'
            ]
        ]);
        return (string)$response->getBody();
    }


    /**
     * 创建试用小程序
     * @param $component_access_token
     * @param $name
     * @param $openid
     * @return string
     */
    public function fastregisterbetaweapp($component_access_token,$name,$openid){
        $url = 'https://api.weixin.qq.com/wxa/component/fastregisterbetaweapp';
        $post_data =  [
            'name'=>$name,
            'openid'=>$openid
        ];
        $post_str = json_encode($post_data,JSON_UNESCAPED_UNICODE);

        $response =  $this->client->request('POST',$url,[
            'body'=>$post_str,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($post_str),
            ],
            'query'=>['access_token'=>$component_access_token]
        ]);
        return (string)$response->getBody();
    }


}
