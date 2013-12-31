<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/30
 * Time: 12:04
 * To change this template use File | Settings | File Templates.
 */

require_once '../Garapon/Garapon.php';

$garapon = new \Garapon\Garapon();
$results = $garapon->login()->request->connection ?: 'error';
var_dump($results);
