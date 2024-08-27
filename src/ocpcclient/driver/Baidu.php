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
 * 百度推广OCPC
 * @document https://dev2.baidu.com/content?sceneType=0&pageId=101211&nodeId=658&subhead=
 */
class Baidu extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://ocpc.baidu.com/ocpcapi/api';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 令牌
        'token' => '',
        // 页面url
        'logidUrl' => '',
        // 转化类型
        'newType' => '',
        // 深度转化类型
        'deepType' => '',
        // 转化时间戳, 单位秒
        'convertTime' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 如果token为空
        if(empty($this->options['token'])){
            return [null, new \Exception('版权资质百度OCPC未填写token, 请填写', 400)];
        }

        // 页面URL为空
        if(empty($this->options['logidUrl'])){
            return [null, new \Exception('页面链接错误', 400)];
        }

        // 未设置转化类型
        if(empty($this->options['newType'])){
            return [null, new \Exception('版权资质未指定百度OCPC转化事件类型', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['convertTime'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // token
            'token' => $this->options['token'],
            // 转化数据
            'conversionTypes' => [
                [
                    // 页面URL
                    'logidUrl' => $this->options['logidUrl'],
                    // 转化类型
                    'newType' => $this->options['newType'],
                    // 转化时间
                    'convertTime' => $this->options['convertTime'],
                ]
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadConvertData');
	}

    /**
     * 深度转化回传
     * @access public
     * @return array
     */
	public function convertDeeply()
	{
        // 如果token为空
        if(empty($this->options['token'])){
            return [null, new \Exception('未指定token', 400)];
        }

        // 页面URL为空
        if(empty($this->options['logidUrl'])){
            return [null, new \Exception('页面链接错误', 400)];
        }

        // 未设置转化类型
        if(empty($this->options['deepType'])){
            return [null, new \Exception('版权资质未指定百度OCPC深度转化事件类型', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['convertTime'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // token
            'token' => $this->options['token'],
            // 转化数据
            'conversionTypes' => [
                [
                    // 页面URL
                    'logidUrl' => $this->options['logidUrl'],
                    // 转化类型
                    'newType' => $this->options['deepType'],
                    // 转化时间
                    'convertTime' => $this->options['convertTime'],
                ]
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadConvertData');
	}

    /**
     * 无效转化回传
     * @access public
     * @return array
     */
	public function invalidGenerally()
	{
        // 如果token为空
        if(empty($this->options['token'])){
            return [null, new \Exception('未指定token', 400)];
        }

        // 页面URL为空
        if(empty($this->options['logidUrl'])){
            return [null, new \Exception('页面链接错误', 400)];
        }

        // 未设置转化类型
        if(empty($this->options['newType'])){
            return [null, new \Exception('版权资质未指定百度OCPC转化事件类型', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['convertTime'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // token
            'token' => $this->options['token'],
            // 无效转化数据
            'invalidConversionTypes' => [
                [
                    // 页面URL
                    'logidUrl' => $this->options['logidUrl'],
                    // 转化类型
                    'convertType' => $this->options['newType'],
                    // 转化时间
                    'convertTime' => $this->options['convertTime'],
                    // 置信度
                    'confidence' => 0,
                ]
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadInvalidConvertData');
	}

    /**
     * 无效转化回传
     * @access public
     * @return array
     */
	public function invalidDeeply()
	{
        // 如果token为空
        if(empty($this->options['token'])){
            return [null, new \Exception('未指定token', 400)];
        }

        // 页面URL为空
        if(empty($this->options['logidUrl'])){
            return [null, new \Exception('页面链接错误', 400)];
        }

        // 未设置转化类型
        if(empty($this->options['newType'])){
            return [null, new \Exception('未指定转化类型', 400)];
        }

        // 未设置转化类型
        if(empty($this->options['deepType'])){
            return [null, new \Exception('未指定转化类型', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['convertTime'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }
        // 转化数据
        $requestData = [
            // token
            'token' => $this->options['token'],
            // 无效转化数据
            'invalidConversionTypes' => [
                [
                    // 页面URL
                    'logidUrl' => $this->options['logidUrl'],
                    // 转化类型
                    'convertType' => $this->options['newType'],
                    // 转化时间
                    'convertTime' => $this->options['convertTime'],
                    // 置信度
                    'confidence' => 0,
                ],
                [
                    // 页面URL
                    'logidUrl' => $this->options['logidUrl'],
                    // 转化类型
                    'convertType' => $this->options['deepType'],
                    // 转化时间
                    'convertTime' => $this->options['convertTime'],
                    // 置信度
                    'confidence' => 0,
                ]
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadInvalidConvertData');
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
        // json序列化后的数据
        $requestJson = json_encode($data);

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path, $requestJson, [
                'Content-Type' => 'application/json;charset=utf-8',
                'Content-Length' => strlen($requestJson)
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['header']['status'] == 0){
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['header']['desc'], 400)];
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