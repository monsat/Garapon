<?php
/**
 * Created by IntelliJ IDEA.
 * User: ktanaka
 * Date: 2013/12/31
 * Time: 11:19
 * To change this template use File | Settings | File Templates.
 */

namespace Garapon;


class Response {

    /**
     * @var int $status
     */
    public $status;

    /**
     * @var bool $success
     */
    public $success = false;

    /**
     * @var string $error_message
     */
    public $error_message = '';

    /**
     * @var array $results
     */
    public $results = array();

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}