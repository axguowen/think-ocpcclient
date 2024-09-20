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
 * 360点睛推广平台
 * @document http://pub-shyc2.s3.360.cn/dianjingweb/360%E7%82%B9%E7%9D%9B-%E7%BD%91%E9%A1%B5%E8%BD%AC%E5%8C%96%E6%95%B0%E6%8D%AEAPI%E5%AF%B9%E6%8E%A5%E6%96%87%E6%A1%A3.pdf
 * @document https://pages-juxiao.mediav.com/360%E7%82%B9%E7%9D%9B-%E7%BD%91%E9%A1%B5%E8%BD%AC%E5%8C%96%E6%95%B0%E6%8D%AEAPI%E5%AF%B9%E6%8E%A5%E6%96%87%E6%A1%A3-202204.zip
 */
class Qihu extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://convert.dop.360.cn';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 公钥
        'key' => '',
        // 私钥
        'secret' => '',
        // 展示广告主ID
        'jzqs' => '',
        // 数据应用方, 可选值 移动推广:ocpc_web_convert PC搜索推广:ocpc_ps_convert 移动搜索:ocpc_ms_convert 展示广告:ocpc_zs_convert 
        'data_industry' => 'ocpc_ps_convert',
        // 转化追踪标识
        'qhclickid' => '',
        // 转化类型
        'event' => '',
        // 深度转化类型
        'event_deep' => '',
        // 转化时间戳, 单位秒
        'event_time' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['key'])){
            return [null, new \Exception('未指定参数key', 400)];
        }
        if(empty($this->options['secret'])){
            return [null, new \Exception('未指定参数secret', 400)];
        }
        if(empty($this->options['data_industry'])){
            return [null, new \Exception('未指定参数data_industry', 400)];
        }
        if(empty($this->options['qhclickid'])){
            return [null, new \Exception('未指定参数qhclickid', 400)];
        }
        if(empty($this->options['event'])){
            return [null, new \Exception('版权资质未指定360转化事件类型', 400)];
        }
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定参数event_time', 400)];
        }

        $dataIndustry = $this->options['data_industry'];
        // 转化数据
        $dataDetail = [
            'event' => $this->options['event'],
            'event_time' => $this->options['event_time'],
        ];

        // 如果是信息流
        if($dataIndustry == 'ocpc_web_convert'){
            $dataDetail['impression_id'] = $this->options['qhclickid'];
        }
        // 不是信息流
        else{
            $dataDetail['qhclickid'] = $this->options['qhclickid'];
            $dataDetail['trans_id'] = $this->options['qhclickid'];
            $dataDetail['jzqs'] = $this->options['jzqs'];
        }
    
        // 转化数据
        $requestData = [
            'data' => [
                'request_time' => time(),
                'data_industry' => $dataIndustry,
                'data_detail' => $dataDetail,
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadWebConvert');
	}

    /**
     * 深度转化回传
     * @access public
     * @return array
     */
	public function convertDeeply()
	{
        if(empty($this->options['key'])){
            return [null, new \Exception('未指定参数key', 400)];
        }
        if(empty($this->options['secret'])){
            return [null, new \Exception('未指定参数secret', 400)];
        }
        if(empty($this->options['data_industry'])){
            return [null, new \Exception('未指定参数data_industry', 400)];
        }
        if(empty($this->options['qhclickid'])){
            return [null, new \Exception('未指定参数qhclickid', 400)];
        }
        if(empty($this->options['event_deep'])){
            return [null, new \Exception('版权资质未指定360深度转化事件类型', 400)];
        }
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定参数event_time', 400)];
        }

        $dataIndustry = $this->options['data_industry'];
        // 转化数据
        $dataDetail = [
            'event' => $this->options['event_deep'],
            'event_time' => $this->options['event_time'],
        ];

        // 如果是信息流
        if($dataIndustry == 'ocpc_web_convert'){
            $dataDetail['impression_id'] = $this->options['qhclickid'];
        }
        // 不是信息流
        else{
            $dataDetail['qhclickid'] = $this->options['qhclickid'];
            $dataDetail['trans_id'] = $this->options['qhclickid'];
            $dataDetail['jzqs'] = $this->options['jzqs'];
        }
    
        // 转化数据
        $requestData = [
            'data' => [
                'request_time' => time(),
                'data_industry' => $dataIndustry,
                'data_detail' => $dataDetail,
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadWebConvert');
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
                'App-Key' => $this->options['key'],
                'App-Sign' => hash('md5', $this->options['secret'] . $requestJson),
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['errno'] == 0){
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['error'], 400)];
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