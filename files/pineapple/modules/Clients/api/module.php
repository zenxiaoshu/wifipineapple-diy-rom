<?php namespace pineapple;

class Clients extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getClientData':
                $this->getClientData();
                break;
            case 'kickClient':
                $this->kickClient();
                break;
        }
    }
    private function getClientData()
    {
        $clientReport = array();
         exec('
            iw dev wlan0 station dump |
            awk \'{ if ($1 == "Station") { printf "%s ", $2; } else if ($1 == "inactive") {print $3;} }\'
        ', $stations);
        $clientReport['stations'] = array();
        foreach ($stations as $_ => $station) {
            if (empty($station)) {
                continue;
            }
            $stationArray = explode(' ', $station);
            $clientReport['stations'][$stationArray[0]] = $stationArray[1];
        }
        $clientReport['dhcp'] = array();
        $leases = explode("\n", @file_get_contents('/var/dhcp.leases'));
        if ($leases) {
            foreach ($leases as $lease) {
                $clientReport['dhcp'][explode(' ', $lease)[1]] = array_slice(explode(' ', $lease), 2, 2);
            }
        }
        $clientReport['arp'] = array();
        exec('cat /proc/net/arp | awk \'{ if ($1 != "IP") {printf "%s %s\n", $1, $4;}}\'', $arpEntries);
        foreach ($arpEntries as $arpEntry) {
            $arpEntryArray = explode(' ', $arpEntry);
            $clientReport['arp'][$arpEntryArray[1]] = $arpEntryArray[0];
        }
        $clientReport['ssids'] = $this->getSSIDData();
        $this->response = array('clients' => $clientReport);
    }
    private function getSSIDData()
    {
        $ssidData = array();
        $pineAPLogPath = trim(file_get_contents('/etc/pineapple/pineap_log_location'));
        $file = fopen($pineAPLogPath . 'pineap.log', 'r');
        while (($line = fgets($file)) !== false) {
            if (strpos($line, "\tAssociation,\t") !== false) {
                $line = explode(",\t", $line);
                $ssidData[$line[2]] = $line[3];
            }
        }
        return $ssidData;
    }
    private function kickClient()
    {
        exec("hostapd_cli -i wlan0 deauthenticate {$this->request->mac}");
        exec("hostapd_cli -i wlan0 disassociate {$this->request->mac}");
        $this->response = array('success' => true);
    }
}
