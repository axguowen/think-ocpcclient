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

namespace think;

use think\helper\Arr;
use think\exception\InvalidArgumentException;

/**
 * OCPC客户端
 */
class OcpcClient extends Manager
{
    /**
     * 驱动的命名空间
     * @var string
     */
	protected $namespace = '\\think\\ocpcclient\\driver\\';

	/**
     * 默认驱动
     * @access public
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

	/**
     * 获取客户端配置
     * @access public
     * @param null|string $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfig($name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('ocpcclient.' . $name, $default);
        }

        return $this->app->config->get('ocpcclient');
    }

	/**
     * 获取平台配置
     * @param string $platform 平台名称
     * @param null|string $name 配置名称
     * @param null|string $default 默认值
     * @return array
     */
    public function getPlatformConfig($platform, $name = null, $default = null)
    {
		// 读取驱动配置文件
        if ($config = $this->getConfig('platforms.' . $platform)) {
            return Arr::get($config, $name, $default);
        }
		// 驱动不存在
        throw new \InvalidArgumentException('平台 [' . $platform . '] 配置不存在.');
    }

    /**
     * 当前平台的驱动配置
     * @param string $name 驱动名称
     * @return mixed
     */
    protected function resolveType($name)
    {
        return $this->getPlatformConfig($name, 'type', 'baidu');
    }

	/**
     * 获取驱动配置
     * @param string $name 驱动名称
     * @return mixed
     */
    protected function resolveConfig($name)
    {
        return $this->getPlatformConfig($name);
    }

	/**
     * 选择或者切换平台
     * @access public
     * @param string $name 平台的配置名
     * @param array $options 平台配置
     * @return \think\ocpcclient\Platform
     */
    public function platform($name = null, array $options = [])
    {
        // 如果指定了自定义配置
        if(!empty($options)){
            // 创建驱动实例并设置参数
            return $this->createDriver($name)->setConfig($options);
        }
        // 返回已有驱动实例
        return $this->driver($name);
    }

	/**
     * 普通转化回传
     * @access public
     * @param array $options 转化数据
     * @return array
     */
    public function convertGenerally($options = [])
    {
        return $this->platform(null, $options)->convertGenerally();
    }

	/**
     * 深度转化回传
     * @access public
     * @param array $options 转化数据
     * @return array
     */
    public function convertDeeply($options = [])
    {
        return $this->platform(null, $options)->convertDeeply();
    }

	/**
     * 标记普通转化无效
     * @access public
     * @param array $options 转化数据
     * @return array
     */
    public function invalidGenerally($options = [])
    {
        return $this->platform(null, $options)->invalidGenerally();
    }

	/**
     * 标记深度转化无效
     * @access public
     * @param array $options 转化数据
     * @return array
     */
    public function invalidDeeply($options = [])
    {
        return $this->platform(null, $options)->invalidDeeply();
    }
}
