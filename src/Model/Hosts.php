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
use SplFileObject;

/**
 * Hosts Model class
 */
class Hosts
{
    public const REQUEST_OBJECT_KEY_MAC_ADDRESS = 'macAddress';

    private const DDNS_HOSTS_CONFIG_PATH = __DIR__ . '/../../config/ddns-hosts.json';
    private const LOCAL_CACHE_NAMESPACE = 'SmartAPI_DynamicDNS_Hosts';
    private const LOCAL_CACHE_TIME_TO_LIVE = 2592000; // 20 days
    private const DDNS_HOSTS_HARD_LIMIT = 2;

    private $list;
    private $cache;

    /**
     * Instantiate hosts list and local cache
     */
    public function __construct()
    {
        $content = '{}';

        if (file_exists(self::DDNS_HOSTS_CONFIG_PATH)) {
            $configFile = new SplFileObject(self::DDNS_HOSTS_CONFIG_PATH);
            $content = $configFile->fread($configFile->getSize());
        }

        $rawList = ExtendedArray::fromJSON($content);

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
                strtoupper($search)
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
     * Create Host Object (save to ddns-hosts.json)
     */
    public function create(ExtendedArray $data): void
    {
        if ($this->list->count() >= self::DDNS_HOSTS_HARD_LIMIT) {
            throw new HostException("Hosts limit exceded!");
        }

        if (!$data->offsetExists('macAddress') || !$data->offsetExists('ipAddress')) {
            throw new HostException("Invalid create data!");
        }

        $data->macAddress = preg_replace(
            '/(?:([0-9a-fA-F]{2})[\:\-]?(?!$))/',
            '$1-',
            strtoupper($data->macAddress)
        );

        $this->list->offsetSet($data->macAddress, new HostInfo($data));

        $configFile = new SplFileObject(self::DDNS_HOSTS_CONFIG_PATH, 'w');
        $configFile->fwrite($this->list->jsonSerialize(JSON_PRETTY_PRINT));

        $this->update($data);
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
