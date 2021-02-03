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

    private $component_appid;

    private $encodingAesKey;

    private $appId;

    public function __construct($component_appid,$token,$encodingAesKey)
    {
        $this->component_appid = $component_appid;

        $this->encodingAesKey = $encodingAesKey;

        $this->component_appid = $component_appid;
    }
}
