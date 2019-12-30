<?php

/**
 * PHP Version 7
 *
 * Request Exception
 *
 * @category Exception
 * @package  SmartAPI\Exception
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Exception;

use Exception;

/**
 * Request Exception class
 */
class RequestException extends Exception
{
    /**
     * Set default error code to 400
     */
    public function __construct(
        $message = '',
        $code = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
