<?php

/**
Copyright (year) Bytedance Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 */

namespace app\controller;

use app\BaseController;
use think\Http;
use think\response\Json;

use ProductListV2Request;
use ProductListV2Param;
use GlobalConfig;
use AccessTokenBuilder;
use CreateTokenRequest;
use CreateTokenParam;


require __DIR__ . '/../../lib/sdk-php/src/autoload.php';
include __DIR__ . '/../../lib/sdk-php/src/open/api/product_listV2/ProductListV2Request.php';
include __DIR__ . '/../../lib/sdk-php/src/open/api/product_listV2/param/ProductListV2Param.php';
include __DIR__ . '/../../lib/sdk-php/src/open/core/GlobalConfig.php';
include __DIR__ . '/../../lib/sdk-php/src/open/core/AccessTokenBuilder.php';

class Index extends BaseController
{

    public function getOpenID(): Json
    {
        $target = $this->request->header('X-Tt-OPENID');
        if (is_null($target)) {
            return self::getResponse(-1, 'invalid params', '');
        }
        return self::getResponse(0, 'success', $target);
    }

    public function textAntidirt(): Json
    {
        $content = $this->request->post('content');

        $data = array(
            'tasks' =>
            array(array('content' => $content))
        );
        $data_string = json_encode($data);
        $url = 'http://developer.toutiao.com/api/v2/tags/text/antidirt';

        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_POST, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $request,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        $response = curl_exec($request);

        curl_close($request);
        $res = json_decode($response, true);
        return self::getResponse('0', 'success', $res);
    }



    public function getTest(): Json
    {
        //设置appKey和appSecret，全局设置一次
        GlobalConfig::getGlobalConfig()->appKey = "tt881efea3172f470a01";
        GlobalConfig::getGlobalConfig()->appSecret = "da8988eb6456b47452d1e11c875e7c70d9dcbab6";
        $request = new CreateTokenRequest();
        $param = new CreateTokenParam();
        $param->shop_id = 1327835398542126;
        $param->grant_type = "authorization_self";
        $param->code = "";
        $request->setParam($param);
        $resp = $request->execute(null);
        return self::getResponse('0', 'fail', $resp);
        //创建Access Token
        $accessToken = AccessTokenBuilder::build(1327835398542126, ACCESS_TOKEN_SHOP_ID);
        if (!$accessToken->isSuccess()) {
            $out = array(
                'err_no'    => $accessToken,
                'err_msg'   => '没有accessToken',
            );
            return self::getResponse('0', 'fail', $out);
        }
        $request = new ProductListV2Request();
        $param = new ProductListV2Param();
        $request->setParam($param);
        $param->status = 0;
        $param->check_status = 3;
        $param->product_type = 0;
        $param->start_time = 1619161933;
        $param->end_time = 1619162000;
        $param->update_start_time = 1619161933;
        $param->update_end_time = 1619161933;
        $param->page = 1;
        $param->size = 10;
        $param->store_id = 1327835398542126;
        $param->name = "标题";
        $param->product_id = 3600137140018749665;
        $param->use_cursor = true;
        // $param->cursor_id = "WzE2ODI1Nzc4MjksMTc2NDMxMDczMDU3MDg0M10=";
        $param->can_combine_product = true;
        $response = $request->execute($accessToken);
        return self::getResponse('0', 'success', $response);
    }



    public function getResponse($err_no, $err_msg, $data): Json
    {
        $out = array(
            'err_no'    => $err_no,
            'err_msg'   => $err_msg,
        );
        if (!is_null($data)) {
            $out['data'] = $data;
        }
        return json($out);
    }
}
