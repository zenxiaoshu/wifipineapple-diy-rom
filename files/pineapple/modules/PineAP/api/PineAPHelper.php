<?php namespace pineapple;

class PineAPHelper
{
    private function communicate($command, $return_bytes = 0)
    {
        $socket = @fsockopen("unix:///var/run/pineap.sock");
        if ($socket) {
            fwrite($socket, $command);

            if ($return_bytes) {
                $output = fgets($socket, $return_bytes);
                return $output;
            }
            return true;
        }
        return false;
    }

    public function enablePineAP()
    {
        $mac = exec('/sbin/ifconfig wlan0 | grep HWaddr | awk \'{print $5}\'');
        $chan = exec('/usr/sbin/iw dev wlan0 info | grep channel | awk \'{print $2}\'');
        $iface = exec('/sbin/ifconfig -a | grep wlan1mon | head -n1 | awk \'{print $1}\'');
        if (trim($iface) == '') {
            exec('airmon-ng start wlan1');
            $iface = 'wlan1mon';
        }
        exec("echo '/usr/sbin/pinejector {$iface} &' | at now");
        exec("echo '/usr/sbin/pineap {$chan} {$mac} &' | at now");
    }

    public function disablePineAP()
    {
        exec('killall -9 pineap');
        exec('killall -9 pinejector');
        unlink('/var/run/pineap.sock');
        unlink('/var/run/pinejector.sock');
    }

    public function enableBeaconer()
    {
        return $this->communicate('beaconer:on');
    }

    public function disableBeaconer()
    {
        return $this->communicate('beaconer:off');
    }

    public function enableResponder()
    {
        return $this->communicate('responder:on');
    }

    public function disableResponder()
    {
        return $this->communicate('responder:off');
    }

    public function enableHarvester()
    {
        return $this->communicate('harvest:on');
    }

    public function disableHarvester()
    {
        return $this->communicate('harvest:off');
    }

    public function getTarget()
    {
        return $this->communicate('get_target', 1024);
    }

    public function getSource()
    {
        return $this->communicate('get_source', 1024);
    }

    public function isBeaconerRunning()
    {
        if ($this->communicate('beaconer_status', 1024) == '0') {
            return false;
        }
        return true;
    }

    public function isResponderRunning()
    {
        if ($this->communicate('responder_status', 1024) == '0') {
            return false;
        }
        return true;
    }

    public function isHarvesterRunning()
    {
        if ($this->communicate('get_harvest', 1024) == '0') {
            return false;
        }
        return true;
    }

    public function getBeaconInterval()
    {
        return $this->communicate('get_beacon_interval', 1024);
    }

    public function getResponseInterval()
    {
        return $this->communicate('get_response_interval', 1024);
    }

    public function setBeaconInterval($interval)
    {
        return $this->communicate("beacon_interval:{$interval}");
    }

    public function setResponseInterval($interval)
    {
        return $this->communicate("response_interval:{$interval}");
    }

    public function setSource($mac)
    {
        return $this->communicate("source:{$mac}");
    }

    public function setTarget($mac)
    {
        return $this->communicate("target:{$mac}");
    }

    public function deauth($target, $source, $channel, $multiplier = 1)
    {
        $channel = str_pad($channel, 2, "0", STR_PAD_LEFT);
        return $this->communicate("deauth:{$target}{$source}{$channel}{$multiplier}");
    }

    public function addSSID($ssid)
    {
        if (!$this->communicate("add_ssid:{$ssid}")) {
            if (trim(exec("grep -x " . escapeshellarg($ssid) . " /etc/pineapple/ssid_file")) == '') {
                file_put_contents("/etc/pineapple/ssid_file", "{$ssid}\n", FILE_APPEND);
            } else {
                return false;
            }
        }
        return true;
    }

    public function delSSID($ssid)
    {
        $this->communicate("del_ssid:{$ssid}");

        $ssids = file_get_contents('/etc/pineapple/ssid_file');
        $ssidsArray = explode("\n", $ssids);
        if ($ssidsArray[0] === $ssid) {
            array_shift($ssidsArray);
            $ssids = is_array($ssidsArray) ? implode("\n", $ssidsArray) : '';
        } else {
            $ssids = str_replace("\n{$ssid}", '', $ssids);
        }
        file_put_contents('/etc/pineapple/ssid_file', $ssids);
        return true;
    }

    public function clearSSIDs()
    {
        if (!$this->communicate('clear_ssids')) {
            file_put_contents('/etc/pineapple/ssid_file', '');
        }
        return false;
    }
}
