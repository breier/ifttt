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
use SmartAPI\Traits\IFTTTaware;
use SmartAPI\Model\Hosts;
use ErrorException;

/**
 * Actions Controller class
 */
class Actions extends BaseController
{
    use IFTTTaware;

    private $hosts;

    /**
     * Instantiate Hosts
     */
    public function __construct()
    {
        $this->hosts = new Hosts();
    }

    /**
     * @route POST /ifttt/v1/actions/wol_pcd
     */
    public function wakeOnLan(Request $request): Response
    {
        try {
            $this->IFTTTvalidateRequest($request);
        } catch (RequestException $e) {
            return $this->createResponse($e->getMessage(), 401);
        }

        $hostInfo = $this->hosts->getFullHostInfo(
            $this->hosts->getAll()->first()->key()
        );

        $actionName = basename($request->getPathInfo());

        if ($this->IFTTTisTestMode($request)) {
            return $this->createResponse("/system script run {$actionName}");
        }

        try {
            $response = $this->sshExec(
                $hostInfo,
                "/system script run {$actionName}"
            );
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
    private function sshExec(ExtendedArray $hostInfo, string $command)
    {
        $sshConnection = new SSH2($hostInfo->ipAddress);
        if ($sshConnection->login($hostInfo->user, $hostInfo->password)) {
            return $sshConnection->exec($command);
        }
    }
}
