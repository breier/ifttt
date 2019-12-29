<?php

/**
 * PHP Version 7
 *
 * Hosts Model
 *
 * @category Model
 * @package  SmartAPI\Model
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Model;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\HostException;

/**
 * Hosts Model class
 */
class Hosts
{
    public const REQUEST_OBJECT_KEY_MAC_ADDRESS = 'macAddress';

    private const LOCAL_CACHE_NAMESPACE = 'SmartAPI_DynamicDNS_Hosts';
    private const LOCAL_CACHE_TIME_TO_LIVE = 2592000; // 20 days

    private $list;
    private $cache;

    /**
     * Instantiate hosts list and local cache
     */
    public function __construct()
    {
        $rawList = ExtendedArray::fromJSON(
            $_ENV['DDNS_HOSTS'] ?? '{}'
        );

        $this->list = new ExtendedArray();
        foreach ($rawList as $key => $info) {
            $this->list->offsetSet($key, new HostInfo($info));
        }

        $this->cache = new FilesystemAdapter(
            self::LOCAL_CACHE_NAMESPACE,
            self::LOCAL_CACHE_TIME_TO_LIVE
        );
    }

    /**
     * Get full list
     */
    public function getAll(): ExtendedArray
    {
        return $this->list;
    }

    /**
     * Checks for matching mac-addresses insensitively
     */
    public function find(string $search): ExtendedArray
    {
        if ($this->list->keys()->contains($search, true)) {
            return $this->getFullHostInfo($search);
        }

        foreach (['$1-', '$1:', '$1'] as $replacement) {
            $macAddress = preg_replace(
                '/(?:([0-9a-fA-F]{2})[\:\-]?(?!$))/',
                $replacement,
                $search
            );

            if ($this->list->keys()->contains($macAddress, true)) {
                return $this->getFullHostInfo($macAddress);
            }
        }

        return new ExtendedArray();
    }

    /**
     * Get Full Host Info
     *
     * @throws HostException
     */
    public function getFullHostInfo(string $macAddress): ExtendedArray
    {
        if (!$this->list->keys()->contains($macAddress, true)) {
            throw new HostException("Host not found!");
        }

        $hostInfo = $this->list->offsetGet($macAddress);
        $hostInfo->macAddress = $macAddress;

        $hostItem = $this->cache->getItem($macAddress);
        if (!empty($hostItem->get())) {
            $hostInfo->ipAddress = $hostItem->get();
        }

        return $hostInfo;
    }

    /**
     * Update Host Object
     *
     * @throws HostException
     */
    public function update(ExtendedArray $data): void
    {
        if (!$data->offsetExists('macAddress') || !$data->offsetExists('ipAddress')) {
            throw new HostException("Invalid update data!");
        }

        $hostItem = $this->cache->getItem($data->macAddress);
        $hostItem->set($data->ipAddress);
        $this->cache->save($hostItem);
    }
}
