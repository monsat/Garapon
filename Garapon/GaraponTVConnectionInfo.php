<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 14:52
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;


class GaraponTVConnectionInfo
{
    public $ip;
    public $private_ip;
    public $global_ip;
    public $port;
    public $ts_port;
    public $version;
    private $_map = array(
        '0' => 'status',
        'ipaddr' => 'ip',
        'gipaddr' => 'global_ip',
        'pipaddr' => 'private_ip',
        'port' => 'port',
        'port2' => 'ts_port',
        'gtvver' => 'version',
    );

    public function __construct($results)
    {
        if (array_key_exists('1', $results)) {
            throw new \Exception('ERROR: ' . $results['1']);
        }
        $keys = array_keys($this->_map);
        foreach ($results as $key => $value)
        {
            if (in_array($key, $keys))
            {
                $this->{$this->_map[$key]} = $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
