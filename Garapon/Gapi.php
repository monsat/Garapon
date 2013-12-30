<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 11:59
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;


class Gapi {

	public $isHttps = false;
	public $version = 'v3';
	public $timeout = 10;
	protected $_url;
	protected $_ch;

	public function __construct($host, $version = null) {
		$url = $this->isHttps ? 'https://' : 'http://';
		$version = $version ?: $this->version;
		$url .= "$host/$version/";
		$this->_url = $url;
	}

	public function post($method, $data = [], $options = []) {
		$url = $this->_url . $method;
		$this->_setOption(CURLOPT_POST, 1);
		if (!empty($data)) {
			$this->_setOption(CURLOPT_POSTFIELDS, $data);
		}
		return $this->_send($url, $options);
	}

	public function get($method, $data = [], $options = []) {
		$url = $this->_url . $method . '?' . http_build_query($data);
		return $this->_send($url, $options);
	}

	public function _send($url, $options = []) {
		$this->_init($url);
		if (!empty($options)) {
			$this->_setOption($options);
		}
		return $this->_exec() && $this->_close();
	}

	protected function _init($url) {
		$ch = curl_init($url);
		if (!$ch) {
			trigger_error('curl init failed');
		} else {
			$this->_ch = $ch;
			$this->_setOption(CURLOPT_RETURNTRANSFER, true);
			$this->_setOption(CURLOPT_TIMEOUT, $this->timeout);
		}
		return $ch;
	}

	protected function _setOption($option, $value = null) {
		if (is_array($option) && !$value) {
			return curl_setopt_array($this->_ch, $option);
		} else {
			return curl_setopt($this->_ch, $option, $value);
		}
	}

	protected function _exec() {
		return curl_exec($this->_ch);
	}

	protected function _close() {
		$result = curl_close($this->_ch);
		if ($result) {
			$this->_ch = null;
		}
		return $result;
	}
}