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
 * 阿里汇川
 * @document https://yingxiao.uc.cn/article_14.html?id=208
 * @pdf https://yingxiao.uc.cn/yingxiao/common/file/read?m=292C65C0948146680E06629969021CB2_966693274.pdf
 */
class HuiChuan extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://huichuan.uc.cn/callback/webapi';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 包含追踪参数的完整页面地址
        'link' => '',
        // 转化类型
        'event_type' => '',
        // 深度转化类型
        'deep_type' => '',
        // 转化时间
        'event_time' => '',
        // 数据来源
        'source' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     * @link https://yingxiao.uc.cn/yingxiao/common/file/read?m=8F6C89A223A543EC7A6A7287D6648D8F_3762051905.pdf
     */
	public function convertGenerally()
	{
        // 页面地址参数错误
        if(empty($this->options['link'])){
            return [null, new \Exception('未指定参数link', 400)];
        }

        // 如果转化类型为空
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未指定超级汇川转化事件类型', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // 页面地址
            'link' => $this->options['link'],
            // 转化类型
            'event_type' => $this->options['event_type'],
            // 转化时间
            'event_time' => $this->options['event_time'] * 1000,
            // 数据来源
            'source' => $this->options['source'],
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData);
	}

    /**
     * 深度转化回传
     * @access public
     * @return array
     * @link https://yingxiao.uc.cn/yingxiao/common/file/read?m=C1793DB12460F5DCC1EA252DC4AB4155_2531938010.pdf
     */
	public function convertDeeply()
	{
        // 页面地址参数错误
        if(empty($this->options['link'])){
            return [null, new \Exception('未指定参数link', 400)];
        }

        // 如果深度转化类型为空
        if(empty($this->options['deep_type'])){
            return [null, new \Exception('版权资质未指定超级汇川深度转化事件类型', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // 页面地址
            'link' => $this->options['link'],
            // 转化类型
            'event_type' => $this->options['deep_type'],
            // 转化时间
            'event_time' => $this->options['event_time'] * 1000,
            // 数据来源
            'source' => $this->options['source'],
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
        // 构建query数据
        $requestQuery = http_build_query($data);

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path, $requestQuery, [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['status'] == 0){
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['message'], 400)];
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