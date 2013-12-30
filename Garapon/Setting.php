<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 14:45
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;


class Setting
{
    public $user_id;
    public $password;
    public $developer_id;

    private $data_field_list = array('user_id', 'password', 'developer_id');

    public function __construct($ini_file_path = "developer_info.json")
    {
        if ($ini_file_path) {
            $ini_file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $ini_file_path;
        }
        if (!file_exists($ini_file_path))
        {
            throw new \Exception("File not found: $ini_file_path");
        }
        $json = file_get_contents($ini_file_path);
        $data = json_decode($json, true);
        if ($data == null || !is_array($data))
        {
            throw new \Exception("Cannot Decode JSON file: $ini_file_path");
        }
        foreach ($this->data_field_list as $key)
        {
            if (!array_key_exists($key, $data))
            {
                throw new \Exception("$key not found in file: $ini_file_path");
            }
            if (in_array($key, $this->data_field_list) === false)
            {
                continue;
            }
            $this->$key = $data[$key];
        }
    }
}
