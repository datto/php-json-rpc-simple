<?php

namespace Datto\Test;

class Offsite
{
    public function getTargetType(DeviceIdentifier $identifier)
    {
        return "deviceID=" . $identifier->getDeviceID() . ", mac=" . $identifier->getMacAddress();
    }

    public function invalidEndpoint(InvalidClassName $invalid)
    {
        return "this code is never reached";
    }
}
