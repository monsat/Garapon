<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;

require_once 'Request.php';
require_once 'Response.php';

class Garapon
{
    const GARAPON_WEB_AUTH_URL = 'http://garagw.garapon.info/getgtvaddress';

    const API_VERSION = 'v3';
    const API_DIR = 'gapi';

    /**
     * @var array $_map
     */
    private $_map = array(
        '0' => 'status',
        'ipaddr' => 'ip',
        'gipaddr' => 'global_ip',
        'pipaddr' => 'private_ip',
        'port' => 'port',
        'port2' => 'ts_port',
        'gtvver' => 'version',
    );

    /**
     * @var Request $request
     */
    public $request;
    /**
     * @var Response $response
     */
    public $response;

    /**
     * @var Gapi
     */
    private $_gapi;
    /**
     * @var Setting
     */
    private $_setting;

    /**
     * @var GaraponTVConnectionInfo
     */
    public $connection_info;

    /**
     * @var
     */
    public $url;

    /**
     * @var
     */
    public $settings = array();

    public function __construct($configFilePath = null)
    {
        $this->request = new Request();
        $this->response = $this->request->response;
        $this->settings($configFilePath);
        return $this;
    }

    public function settings($path = null)
    {
        $defaultPath = 'developer_info.json';
        if (!$path) {
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $defaultPath;
        }
        $settings = $this->_settings($path);
        $this->settings = $settings;
        $this->request->connection += $settings;
        return $this;
    }

    protected function _settings($path)
    {
        if (!file_exists($path))
        {
            throw new \Exception("File not found: $path");
        }
        $json = file_get_contents($path);
        $settings = json_decode($json, true);
        if ($settings == null || !is_array($settings))
        {
            throw new \Exception("Cannot Decode JSON file: $path");
        }
        $defaults = array(
            'user_id' => null,
            'password' => null,
            'developer_id' => null,
            'api_version' => self::API_VERSION,
            'api_dir' => self::API_DIR,
        );
        $settings += $defaults;
        if (empty($settings['user_id']) || empty($settings['password']) || empty($settings['developer_id']))
        {
            throw new \Exception('Invalid config file');
        }
        return $settings;
    }

    public function getConnection($force = false)
    {
        if (!$force && ($this->isLoggedIn() || $this->isGetConnected()))
        {
            return $this;
        }
        $data = array(
            'user'      => $this->settings['user_id'],
            'md5passwd' => $this->settings['password'],
            'dev_id'    => $this->settings['developer_id'],
        );
        $request = new Request(self::GARAPON_WEB_AUTH_URL);
        $results = $request->webRequest($data);
        $this->_getConnection($results);
        return $this;
    }

    protected function _getConnection($results)
    {
        if (!empty($results['1']))
        {
            throw new \Exception('ERROR: ' . $results['1']);
        }
        $keys = array_keys($this->_map);
        $_results = $results;
        foreach ($results as $key => $value)
        {
            if (in_array($key, $keys))
            {
                unset($_results[$key]);
                $_results[$this->_map[$key]] = $value;
            }
        }
        unset($_results['0']);
        $this->request->connection += $_results;
        $settings = $this->settings;
        unset($settings['password']);
        $this->request->connection += $settings;
    }

    public function isGetConnected()
    {
        return !empty($this->request->connection['gtvver']);
    }

    public function isLoggedIn()
    {
        return !empty($this->request->connection['gtvsession']);
    }

    public function login($force = false)
    {
        if (!$force && $this->isLoggedIn())
        {
            return $this;
        }
        $settings = $this->request->connection + $this->settings;
        $data = array(
            'type' => 'login',
            'loginid' => $settings['user_id'],
            'md5pswd' => $settings['password'],
        );
        $query = array(
            'dev_id' => $settings['developer_id'],
        );
        if (!$this->isGetConnected())
        {
            $this->getConnection();
        }
        $results = $this->request->post('auth', $data, compact('query'));
        $this->_checkStatus($results, array(
            '0' => 'Status error or empty parameter',
            '100' => 'Login failed',
            '200' => 'Login failed',
        ));
        $this->request->connection['gtvsession'] = $results['gtvsession'];
        return $this;
    }

    public function url($host, $version = null)
    {
        $version = $version ? : $this->version;
        $url = "http://$host/gapi/$version/";
        $this->_gapi->url = $url;
    }
    protected function _checkStatus($results, $errorMessages, $prefix = '')
    {
        foreach ($results as $code => $value)
        {
            if (array_key_exists($code, $errorMessages))
            {
                throw new \Exception($prefix . $errorMessages[$code]);
            }
        }
    }
}