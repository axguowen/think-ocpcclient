<?php
// +----------------------------------------------------------------------
// | ThinkPHP OcpcClient [Simple OCPC Client For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP OcpcClient客户端
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

namespace think\ocpcclient\driver;

use think\ocpcclient\Platform;
use axguowen\HttpClient;

/**
 * OPPO营销推广平台
 * @document https://e.oppomobile.com/markets/toolbox/ActionTrack/ClueDetail
 * @document https://adsfs.heytapimage.com/res/v2/default/market/doc/H5%E6%95%B0%E6%8D%AE%E5%9B%9E%E4%BC%A0%E8%BD%AC%E5%8C%96Api%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A33_0_3.pdf
 */
class Oppo extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://sapi.ads.oppomobile.com/v1/clue/sendData';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 广告主ID
        'owner_id' => '',
        // APIID
        'api_id' => '',
        // APIKEY
        'api_key' => '',
        // 落地页ID
        'page_id' => '',
        // traceId
        'tid' => '',
        // 流量号
        'lbid' => '',
        // 转化类型
        'transform_type' => '',
        // 用户IP
        'ip' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 如果广告主ID为空
        if(empty($this->options['owner_id'])){
            return [null, new \Exception('未指定广告主ID', 400)];
        }

        // api_id参数错误
        if(empty($this->options['api_id'])){
            return [null, new \Exception('未指定参数api_id', 400)];
        }

        // api_key参数错误
        if(empty($this->options['api_key'])){
            return [null, new \Exception('未指定参数api_key', 400)];
        }

        // page_id参数错误
        if(empty($this->options['page_id'])){
            return [null, new \Exception('未指定参数page_id', 400)];
        }

        // tid参数错误
        if(empty($this->options['tid'])){
            return [null, new \Exception('未指定参数tid', 400)];
        }

        // lbid参数错误
        if(empty($this->options['lbid'])){
            return [null, new \Exception('未指定参数lbid', 400)];
        }

        // 未指定转化类型
        if(empty($this->options['transform_type'])){
            return [null, new \Exception('版权资质未指定OPPO转化事件类型', 400)];
        }

        // 未指定IP
        if(empty($this->options['ip'])){
            return [null, new \Exception('未指定IP地址', 400)];
        }

        // 转化数据
        $requestData = [
			'ownerId' => $this->options['owner_id'],
            'pageId' => $this->options['page_id'],
			'tid' => $this->options['tid'],
			'lbid' => $this->options['lbid'],
			'transformType' => $this->options['transform_type'],
			'ip' => $this->options['ip'],
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData);
	}

    /**
     * 发送请求
     * @access protected
     * @param array $data 转化数据
     * @param string $path URL
     * @return array
     */
	protected function sendRequest(array $data, $path = '')
	{
        // 获取当前时间构造签名
        $time_stamp = time();
        // 计算签名
        $sign = sha1($this->options['api_id'] . $this->options['api_key'] . $time_stamp);
        // 生成token
        $token = base64_encode($this->options['owner_id'] . ',' . $this->options['api_id'] . ',' . $time_stamp . ',' . $sign);

        // json序列化后的数据
        $requestJson = json_encode($data);

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path, $requestJson, [
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => 'Bearer ' . $token
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['code'] == 0){
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['msg'], 400)];
        }
        // 异常捕获
        catch (\Exception $e) {
            // 如果开启调试模式
            if(\think\facade\App::isDebug()){
                // 手动抛出异常
                throw $e;
            }
            return [null, $e];
        }
    }

}