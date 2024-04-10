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
        $queryParam = $this->request->param();
        $appKey = $this->request->param('appKey');
        $appSecret = $this->request->param('appSecret');
        $shopId = $this->request->param('shopId');
        $status = $this->request->param('status');
        $check_status = $this->request->param('check_status');
        $product_type = $this->request->param('product_type');
        $start_time = $this->request->param('start_time');
        $end_time = $this->request->param('end_time');
        $update_start_time = $this->request->param('update_start_time');
        $update_end_time = $this->request->param('update_end_time');
        $page = $this->request->param('page');
        $size = $this->request->param('size');
        $store_id = $this->request->param('store_id');
        $name = $this->request->param('name');
        $product_id = $this->request->param('product_id');
        $use_cursor = $this->request->param('use_cursor');
        $can_combine_product = $this->request->param('can_combine_product');
        //设置appKey和appSecret，全局设置一次
        GlobalConfig::getGlobalConfig()->appKey = $appKey;
        GlobalConfig::getGlobalConfig()->appSecret = $appSecret;
        //创建Access Token
        $accessToken = AccessTokenBuilder::build($shopId, ACCESS_TOKEN_SHOP_ID);
        if (!$accessToken->isSuccess()) {
            $out = array(
                'err_no'    => $queryParam,
                'err_msg'   => '没有accessToken',
            );
            return self::getResponse('0', 'fail', $out);
        }
        $request = new ProductListV2Request();
        $param = new ProductListV2Param();
        $param->status = $status; //0
        $param->check_status = $check_status; // || 3;
        $param->product_type = $product_type; // ||  0;
        $param->start_time = $start_time; // 1619161933;
        $param->end_time = $end_time; // 1619162000;
        $param->update_start_time =  $update_start_time; // 1619161933;
        $param->update_end_time = $update_end_time; //  1619161933;
        $param->page =   $page; //1;
        $param->size =  $size; // 10;
        $param->store_id =  $store_id; // 1327835398542126;
        $param->name = $name; //  "标题";
        $param->product_id = $product_id; // 3600137140018749665;
        $param->use_cursor = $use_cursor; // true;
        // $param->cursor_id = "WzE2ODI1Nzc4MjksMTc2NDMxMDczMDU3MDg0M10=";
        $param->can_combine_product = $can_combine_product; // true;
        $request->setParam($param);
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
