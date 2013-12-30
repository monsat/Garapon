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

    public function __construct($string)
    {
        foreach (preg_split("/\r\n|\r|\n/", $string) as $record)
        {
            list($key, $value) = preg_split('/;/', $record);
            switch ($key)
            {
                case '1':
                    throw new \Exception('ERROR: ' . $value);
                case 'ipaddr':
                    $this->ip = $value;
                    break;
                case 'gipaddr':
                    $this->global_ip = $value;
                    break;
                case 'pipaddr':
                    $this->private_ip = $value;
                    break;
                case 'port':
                    $this->port = $value;
                    break;
                case 'port2':
                    $this->ts_port = $value;
                    break;
                case 'gtvver':
                    $this->version = $value;
                    break;
                case '0':
                    break;
                default:
                    echo 'WARNING: unknown response: ' . $key . ' = ' . $value;
                    break;
            }
        }
    }
}
