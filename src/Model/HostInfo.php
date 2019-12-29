<?php

/**
 * PHP Version 7
 *
 * Host Info Model
 *
 * @category Model
 * @package  SmartAPI\Model
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Model;

use Breier\ExtendedArray\ExtendedArray;

/**
 * Host Info Model class
 */
class HostInfo extends ExtendedArray
{
    /**
     * Instantiate host info with default properties
     */
    public function __construct($init)
    {
        parent::__construct($init);

        if (!$this->offsetExists('macAddress')) {
            $this->macAddress = null;
        }

        if (!$this->offsetExists('ipAddress')) {
            $this->ipAddress = null;
        }

        if (!$this->offsetExists('user')) {
            $this->user = 'admin';
        }

        if (!$this->offsetExists('password')) {
            $this->password = 'admin';
        }

        if (!$this->offsetExists('port')) {
            $this->port = 22;
        }
    }
}
