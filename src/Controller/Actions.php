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
use SmartAPI\Exception\{RequestException, HostException};
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
        $actionName = basename($request->getPathInfo());

        return $this->sshAction(
            $request,
            "/system script run {$actionName}"
        );
    }

    /**
     * IFTTT prepare to execute given ssh action
     */
    private function sshAction(Request $request, string $command): Response
    {
        try {
            $this->IFTTTvalidateRequest($request);
        } catch (RequestException $e) {
            return $this->IFTTTresponse($e->getMessage(), 401);
        }

        if ($this->IFTTTisTestMode($request)) {
            return $this->IFTTTresponse('1q0o2w9i3e8u4r7y5t');
        }

        
        try {
            $hostInfo = $this->hosts->getFullHostInfo(
                $this->hosts->getAll()->first()->key() ?? 'INVALID'
            );

            $response = $this->sshExec($hostInfo, $command);
        } catch (HostException $e) {
            return $this->IFTTTresponse($e->getMessage(), 404);
        } catch (ErrorException $e) {
            return $this->IFTTTresponse($e->getMessage(), 422);
        }

        return $this->IFTTTresponse($response);
    }

    /**
     * SSH Exec command on given host
     *
     * @throws ErrorException
     */
    private function sshExec(ExtendedArray $hostInfo, string $command): string
    {
        $sshConnection = new SSH2($hostInfo->ipAddress, $hostInfo->port ?? 22);

        if (!$sshConnection->login($hostInfo->user, $hostInfo->password)) {
            throw new ErrorException(
                "Failed to login to host {$hostInfo->ipAddress}"
            );
        }

        return $sshConnection->exec($command);
    }
}
