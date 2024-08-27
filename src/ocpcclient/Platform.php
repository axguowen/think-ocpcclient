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

namespace think\ocpcclient;

/**
 * 平台抽象类
 */
abstract class Platform
{
	/**
     * 平台配置参数
     * @var array
     */
	protected $options = [];

	/**
     * 架构函数
     * @access public
     * @param array $options 配置参数
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
    }

	/**
     * 动态设置平台配置参数
     * @access public
     * @param array $options 平台配置
     * @return $this
     */
    public function setConfig(array $options)
    {
        // 合并配置
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        // 返回
        return $this;
    }

    /**
     * 普通转化回传
     * @access public
     * @return array
     */
    abstract public function convertGenerally();

    /**
     * 深度转化回传
     * @access public
     * @return array
     */
    public function convertDeeply()
	{
        return [null, new \Exception('当前平台不支持深度转化回传', 400)];
    }

    /**
     * 标记普通转化无效
     * @access public
     * @return array
     */
    public function invalidGenerally()
	{
        return [null, new \Exception('当前平台不支持标记普通转化无效', 400)];
    }

    /**
     * 标记深度转化无效
     * @access public
     * @return array
     */
    public function invalidDeeply()
	{
        return [null, new \Exception('当前平台不支持标记深度转化无效', 400)];
    }
}
