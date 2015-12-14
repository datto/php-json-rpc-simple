<?php

namespace Datto\Test;

class DeviceIdentifier
{
    private $deviceID;
    private $macAddress;

    public function __construct($idStr)
    {
        if (preg_match('/^id\{(\d+)\}$/', $idStr, $match)) {
            $this->deviceID = intval($match[1]);
            $this->macAddress = "dummy";
        } elseif (preg_match('/^mac\{([a-f0-9]+)\}$/i', $idStr, $match)) {
            $this->macAddress = strtolower($match[1]);
            $this->deviceID = "dummy";
        }
    }

    public function getDeviceID()
    {
        return $this->deviceID;
    }

    public function getMacAddress()
    {
        return $this->macAddress;
    }
}

