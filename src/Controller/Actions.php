<?php

/**
 * PHP Version 7
 *
 * Actions Controller File
 *
 * @category Controller
 * @package  SmartAPI\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use phpseclib\Net\SSH2;
use Breier\ExtendedArray\ExtendedArray;
use Symfony\Component\HttpFoundation\{Request, Response};
use SmartAPI\Exception\RequestException;
use ErrorException;

/**
 * Actions Controller class
 */
class Actions extends HostController
{
    /**
     * @route POST /wol
     */
    public function wakeOnLan(Request $request): Response
    {
        try {
            $this->validateMacAddress($this->getRequestData($request));
        } catch (RequestException $e) {
            return $this->createResponse($e->getMessage(), 400);
        }

        $hostInfo = $this->getHosts()->find(
            $this->getRequestData($request)->offsetGet(
                self::REQUEST_OBJECT_KEY_MAC_ADDRESS
            )
        );

        try {
            $response = $this->sshExec($hostInfo, '/system script run wol-pcd');
        } catch (\Exception $e) {
            return $this->createResponse($e->getMessage(), 422);
        }

        return $this->createResponse($response);
    }

    /**
     * SSH Exec command on given host
     *
     * @return mixed Command output
     * @throws ErrorException
     */
    public function sshExec(ExtendedArray $hostInfo, string $command)
    {
        $sshConnection = new SSH2($hostInfo->ipAddress);
        if ($sshConnection->login($hostInfo->user, $hostInfo->password)) {
            return $sshConnection->exec($command);
        }
    }
}
