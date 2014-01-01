<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/31
 * Time: 11:19
 * To change this template use File | Settings | File Templates.
 */

namespace CoEdo\Garapon;


class Request {

    /**
     * @var array $options
     */
    public $options = array();

    /**
     * @var string $url
     */
    public $url;

    /**
     * @var int $timeout
     */
    public $timeout = 10;

    /**
     * @var Resource $_ch
     */
    protected $_ch;

    /**
     * @var array $connection
     */
    public $connection = array();

    /**
     * @var array $error_messages
     */
    protected $error_messages = array(
        'auth' => array(
            '0' => 'error status or empty params',
            '100' => 'login failed',
            '200' => 'login failed',
        ),
    );

    /**
     * @var string $method
     */
    public $method;

    public function __construct($url = null)
    {
        $this->response = new Response($this);
        if ($url) {
            $this->url = $url;
        }
    }

    protected function _close()
    {
        $result = curl_close($this->_ch);
        if ($result) {
            $this->_ch = null;
        }
        return $result;
    }

    protected function _exec()
    {
        return curl_exec($this->_ch);
    }

    public function method($method)
    {
        $this->method = $method;
        return $this;
    }

    public function options($options, $value = null)
    {
        if (!is_null($value) && is_string($options))
        {
            $this->options[$options] = $value;
        } else {
            foreach ($options as $_key => $_value)
            {
                $this->options[$_key] = $_value;
            }
        }
        return $this;
    }

    protected function _init($url = null)
    {
        $url = $url ? $url : $this->url;
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
            $result = curl_setopt_array($this->_ch, $option);
        } else {
            $result = curl_setopt($this->_ch, $option, $value);
        }
        return $result;
    }

    public function request($method = '', $data = array(), $options = array())
    {
        $options += $this->connection;
        $this->buildUrl($method, $options);
        $this->method($method);
        $this->_init($this->url);
        $httpMethod = $options['httpMethod'];
        unset($options['httpMethod']);
        if (strtolower($httpMethod) == 'post')
        {
            $this->_setOption(CURLOPT_POST, 1);
            if (!empty($data))
            {
                $this->_setOption(CURLOPT_POSTFIELDS, $data);
            }
        }
        $this->_send($options);
        return $this->response->results;
    }

    protected function _result($results)
    {
        $this->response->results = $results;
        if (empty($results['status']))
        {
            // web
            $this->response->success = array_key_exists('0', $results);
            if (!$this->response->success)
            {
                $this->response->error_message = $results['1'];
            }
        } else {
            // API
            $this->response->success = $results['status'] == '1';
            if (!$this->response->success)
            {
                $messages = $this->error_messages[$this->method];
                $this->response->error_message = $messages[$results['status']];
            }
        }
        return $this->response->success;
    }

    protected function _send($options = array())
    {
        $type = null;
        if (!empty($options['type']))
        {
            $type = $options['type'];
            unset($options['type']);
        }
        if (!empty($options)) {
            foreach ($options as $key => $value)
            {
                if (!is_int($key))
                {
                    unset($options[$key]);
                }
            }
            $this->_setOption($options);
        }
        $result = $this->_exec();
        if ($result) {
            $this->_close();
        }
        $results = $this->_parse($result, $type);
        $this->_result($results);
        return $results;
    }

    public function get($method = '', $options = array())
    {
        $options['httpMethod'] = 'get';
        return $this->request($method, array(), $options);
    }

    protected function _parse($result, $type = null)
    {
        $type = $type ? $type : 'json';
        $method = '_parse' . strtoupper($type[0]) . strtolower(substr($type, 1));
        return $this->$method($result);
    }

    protected function _parseGarapon($result)
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

    protected function _parseJson($result)
    {
        $results = json_decode($result, true);
        return $results;
    }

    public function post($method = '', $data = array(), $options = array())
    {
        $options['httpMethod'] = 'post';
        return $this->request($method, $data, $options);
    }

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function buildUrl($method = '', $options = null)
    {
        $options = $options ? : $this->connection;
        extract($options);
        $url = sprintf("http://%s:%s/%s/%s/%s", $ip, $port, $api_dir, $api_version, $method);
        if (isset($query))
        {
            $url .= '?' . http_build_query($query);
        }
        $this->url = $url;
        return $this;
    }

    protected function _buildQuery($query, $addParams = true)
    {
        if ($addParams) {
            if (!empty($this->connection['developer_id']))
            {
                $query['dev_id'] = $this->connection['developer_id'];
            }
            if (!empty($this->connection['gtvsession']))
            {
                $query['gtvsession'] = $this->connection['gtvsession'];
            }
        }
        $result = '?' . http_build_query($query);
        return $result;
    }

    public function webRequest($data)
    {
        $this->_init($this->url);
        $this->_setOption(CURLOPT_POST, 1);
        $this->_setOption(CURLOPT_POSTFIELDS, $data);
        $options = array(
            'type' => 'garapon',
        );
        $this->_send($options);
        return $this->response->results;
    }

}