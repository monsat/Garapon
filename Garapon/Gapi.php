<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 11:59
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;


class Gapi
{

    public $isHttps = false;
    public $version = 'v3';
    public $timeout = 10;
    public $url;
    protected $_ch;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function request($method = '', $data = array(), $options = array())
    {
        $options = array() + $this->request->options;
        $this->url .= $method . '?' . http_build_query($query);
        $this->_init($this->url);
        if ($options['method'] == 'post')
        {
            $this->_setOption(CURLOPT_POST, 1);
            if (!empty($data)) {
                $this->_setOption(CURLOPT_POSTFIELDS, $data);
            }
        }
        return $this->_send($options);
    }
    public function post($method = '', $data = array(), $query = array(), $options = array())
    {
        $this->url .= $method . '?' . http_build_query($query);
        $this->_init($this->url);
        $this->_setOption(CURLOPT_POST, 1);
        if (!empty($data)) {
            $this->_setOption(CURLOPT_POSTFIELDS, $data);
        }
        return $this->_send($options);
    }

    public function get($method = '', $query = array(), $options = array())
    {
        $this->url .= $method . '?' . http_build_query($query);
        $this->_init($this->url);
        return $this->_send($options);
    }

    public function url($host, $version = null)
    {
        $url = $this->isHttps ? 'https://' : 'http://';
        $version = $version ? : $this->version;
        $url .= "$host/$version/";
        $this->url = $url;
    }

    public function _send($options = array())
    {
        if (!empty($options)) {
            $this->_setOption($options);
        }
        $result = $this->_exec();
        if ($result) {
            $this->_close();
        }
        $result = $this->_parse($result);
        return $result;
    }

    protected function _init($url)
    {
        $ch = curl_init($url);
        if (!$ch) {
            new \Exception('curl init failed');
        } else {
            $this->_ch = $ch;
            $this->_setOption(CURLOPT_RETURNTRANSFER, true);
            $this->_setOption(CURLOPT_TIMEOUT, $this->timeout);
        }
        return $ch;
    }

    protected function _setOption($option, $value = null)
    {
        if (is_array($option) && !$value) {
            return curl_setopt_array($this->_ch, $option);
        } else {
            return curl_setopt($this->_ch, $option, $value);
        }
    }

    protected function _exec()
    {
        return curl_exec($this->_ch);
    }

    protected function _close()
    {
        $result = curl_close($this->_ch);
        if ($result) {
            $this->_ch = null;
        }
        return $result;
    }

    protected function _parse($result)
    {
        $results = array();
        $result = preg_split("/\n/", $result);
        if (!is_array($result))
        {
            return $result;
        }
        foreach ($result as $record)
        {
            if (empty($record) || strpos($record, ';') === false)
            {
                $results[] = $record;
            } else {
                list($key, $value) = preg_split('/;/', $record, 2);
                $results[$key] = $value;
            }
        }
        return $results;
    }
}