<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;

require_once 'Gapi.php';
require_once 'Setting.php';
require_once 'GaraponTVConnectionInfo.php';

class Garapon
{
    const GARAPON_WEB_AUTH_URL = 'http://garagw.garapon.info/getgtvaddress';

    const API_PATH_AUTH     = '/gapi/v3/auth';
    const API_PATH_SEARCH   = '/gapi/v3/search';
    const API_PATH_FAVORITE = '/gapi/v3/favorite';
    const API_PATH_CHANNEL  = '/gapi/v3/channel';

    const API_URL_TYPE_AUTH     = 'auth';
    const API_URL_TYPE_SEARCH   = 'search';
    const API_URL_TYPE_FAVORITE = 'favorite';
    const API_URL_TYPE_CHANNEL  = 'channel';

    public $loginid;
    public $password;
    public $md5passwd;
    public $dev_id;

    public $gtvsession;
    public $version = 'v3';

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
    private $_session;

    public function __construct($isConnect = true)
    {
        $this->_setting = new Setting();
        if ($isConnect) {
            $this->getConnectionInfo();
            $this->_gapi = new Gapi();
            $this->url($this->connection_info->ip);
        }
    }

    public function getConnectionInfo()
    {
        if ($this->connection_info != null)
        {
            return $this->connection_info;
        }
        $data = array(
            'user'      => $this->_setting->user_id,
            'md5passwd' => $this->_setting->password,
            'dev_id'    => $this->_setting->developer_id,
        );
        $gapi = new Gapi();
        $gapi->url = self::GARAPON_WEB_AUTH_URL;
        $result = $gapi->post('', $data);
        $this->connection_info = new GaraponTVConnectionInfo($result);
        return $this->connection_info;
    }

    public function login()
    {
        $method = 'auth';
        $data = array(
            'type' => 'login',
            'loginid' => $this->_setting->user_id,
            'md5pswd' => $this->_setting->password,
        );
        $query = array(
            'dev_id' => $this->_setting->developer_id,
        );
        if (empty($this->_gapi))
        {
            throw new \Exception("No connection yet");
        }
        $result = $this->_gapi->post($method, $query, $data);
        switch ($result) {
            case '0':
                throw new \Exception("No response");
                break;
            case '100':
            case '200':
                throw new \Exception("Login failed");
                break;
        }
        return $result == '1';
    }

    public function url($host, $version = null)
    {
        $version = $version ? : $this->version;
        $url = "http://$host/gapi/$version/";
        $this->_gapi->url = $url;
    }
}