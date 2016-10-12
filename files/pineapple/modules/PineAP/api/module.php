<?php namespace pineapple;

require_once('/pineapple/modules/PineAP/api/PineAPHelper.php');

class PineAP extends SystemModule
{
    private $pineAPHelper;

    public function __construct($request)
    {
        parent::__construct($request, __CLASS__);
        $this->pineAPHelper = new PineAPHelper();
    }
    public function route()
    {
        switch ($this->request->action) {
            case 'getPool':
                $this->getPool();
                break;

            case 'clearPool':
                $this->clearPool();
                break;

            case 'addSSID':
                $this->addSSID();
                break;

            case 'addSSIDs':
                $this->addSSIDs();
                break;

            case 'removeSSID':
                $this->removeSSID();
                break;

            case 'setPineAPSettings':
                $this->setPineAPSettings();
                break;

            case 'getPineAPSettings':
                $this->getPineAPSettings();
                break;

            case 'deauth':
                $this->deauth();
                break;

            case 'enable':
                $this->enable();
                break;

            case 'disable':
                $this->disable();
                break;

            case 'saveAsDefault':
                $this->saveAsDefault();
                break;

            case 'downloadPineAPPool':
                $this->downloadPineAPPool();
                break;

            case 'loadProbes':
                $this->loadProbes();
                break;
        }
    }

    private function loadProbes()
    {
        $mac = strtolower($this->request->mac);
        $probe_array = array();

        touch(file_get_contents('/etc/pineapple/pineap_log_location') . 'pineap.log');
        $fp = fopen(file_get_contents('/etc/pineapple/pineap_log_location') . 'pineap.log', 'r');
        while (!feof($fp)) {
            $line = fgets($fp);
            if (strpos($line, $mac) !== false) {
                $entry = explode(",\t", $line);
                array_push($probe_array, $entry[3]);
            }
        }
        fclose($fp);

        $this->response = array("success" => true, "probes" => implode("", array_unique($probe_array)));
    }

    private function downloadPineAPPool()
    {
        $this->response = array("download" => $this->downloadFile('/etc/pineapple/ssid_file'));
    }

    private function enable()
    {
        $this->pineAPHelper->enablePineAP();
        $this->response = array("success" => true);
    }

    private function disable()
    {
        $this->pineAPHelper->disablePineAP();
        $this->response = array("success" => true);
    }

    private function checkPineAP()
    {
        if (!$this->checkRunning('/usr/sbin/pineap')) {
            $this->response = array('error' => 'Please start PineAP', 'success' => false);
            return false;
        }
        return true;
    }

    private function deauth()
    {
        $this->checkPineAP();
        $sta = $this->request->sta;
        $clients = $this->request->clients;
        $multiplier = $this->request->multiplier;
        $channel = $this->request->channel;
        foreach ($clients as $client) {
            $success = $this->pineAPHelper->deauth($client, $sta, $channel, $multiplier);
        }
        if ($success) {
            $this->response = array('success' => true);
        } else {
            $this->response = array('error' => 'Please start PineAP', 'success' => false);
        }
    }

    private function getPool()
    {
        $this->checkPineAP();
        $this->response = array('ssidPool' => file_get_contents('/etc/pineapple/ssid_file'), 'success' => true);
    }

    private function clearPool()
    {
        $this->checkPineAP();
        $this->pineAPHelper->clearSSIDs();
        $this->response = array('success' => true);
    }

    private function addSSID()
    {
        $this->checkPineAP();
        $ssid = $this->request->ssid;
        if (strlen($ssid) < 1 || strlen($ssid) > 32) {
            $this->error = true;
        } else {
            $this->response = array('success' => true);
            $this->pineAPHelper->addSSID($ssid);
        }
    }

    private function addSSIDs()
    {
        $this->checkPineAP();
        $ssidList = $this->request->ssids;

        foreach ($ssidList as $ssid) {
            if (strlen($ssid) >= 1 && strlen($ssid) <= 32) {
                $this->response = array('success' => true);
                $this->pineAPHelper->addSSID($ssid);
            }
        }
    }

    private function removeSSID()
    {
        $this->checkPineAP();
        $ssid = $this->request->ssid;
        if (strlen($ssid) < 1 || strlen($ssid) > 32) {
            $this->error = true;
        } else {
            $this->pineAPHelper->delSSID($ssid);
            $this->response = array('success' => true);
        }
    }

    private function getPineAPSettings()
    {
        $sourceMAC = $this->pineAPHelper->getSource();
        $sourceMAC = $sourceMAC === false ? '00:00:00:00:00:00' : $sourceMAC;
        $sourceMAC = strtoupper($sourceMAC);
        $targetMAC = $this->pineAPHelper->getTarget();
        $targetMAC = $targetMAC === false ? 'FF:FF:FF:FF:FF:FF' : $targetMAC;
        $targetMAC = strtoupper($targetMAC);
        $settings = array(
            'allowAssociations' => exec('/usr/sbin/hostapd_cli -i wlan0 karma_get_state') === 'ENABLED',
            'logProbes' => exec('/usr/sbin/hostapd_cli -i wlan0 karma_log_probes_state') === 'ENABLED',
            'logAssociations' => exec('/usr/sbin/hostapd_cli -i wlan0 karma_log_associations_state') === 'ENABLED',
            'pineAPDaemon' => $this->checkRunning('/usr/sbin/pineap'),
            'beaconResponses' => $this->pineAPHelper->isResponderRunning(),
            'captureSSIDs' => $this->pineAPHelper->isHarvesterRunning(),
            'broadcastSSIDs' => $this->pineAPHelper->isBeaconerRunning(),
            'broadcastInterval' => $this->pineAPHelper->getBeaconInterval(),
            'responseInterval' => $this->pineAPHelper->getResponseInterval(),
            'sourceMAC' => $sourceMAC,
            'targetMAC' => $targetMAC
            );
        $this->response = array('settings' => $settings, 'success' => true);
        return $settings;
    }

    private function saveAsDefault()
    {
        $settings = $this->getPineAPSettings();
        $this->uciSet('pineap.autostart.karma', $settings['allowAssociations']);
        $this->uciSet('pineap.autostart.log_probes', $settings['logProbes']);
        $this->uciSet('pineap.autostart.log_associations', $settings['logAssociations']);
        $this->uciSet('pineap.autostart.pineap', $settings['pineAPDaemon']);
        $this->uciSet('pineap.autostart.beacon_responses', $settings['beaconResponses']);
        $this->uciSet('pineap.autostart.harvester', $settings['captureSSIDs']);
        $this->uciSet('pineap.autostart.dogma', $settings['broadcastSSIDs']);
        $this->response = array('success' => true);
    }

    private function setPineAPSettings()
    {
        $settings = $this->request->settings;
        if ($settings->allowAssociations) {
            exec('/bin/pineapple karma start');
        } else {
            exec('/bin/pineapple karma stop');
        }
        if ($settings->logProbes) {
            exec('/usr/sbin/hostapd_cli -i wlan0 karma_log_probes_enable');
        } else {
            exec('/usr/sbin/hostapd_cli -i wlan0 karma_log_probes_disable');
        }
        if ($settings->logAssociations) {
            exec('/usr/sbin/hostapd_cli -i wlan0 karma_log_associations_enable');
        } else {
            exec('/usr/sbin/hostapd_cli -i wlan0 karma_log_associations_disable');
        }
        if ($settings->beaconResponses) {
            $this->pineAPHelper->enableResponder();
        } else {
            $this->pineAPHelper->disableResponder();
        }
        if ($settings->captureSSIDs) {
            $this->pineAPHelper->enableHarvester();
        } else {
            $this->pineAPHelper->disableHarvester();
        }
        if ($settings->broadcastSSIDs) {
            $this->pineAPHelper->enableBeaconer();
        } else {
            $this->pineAPHelper->disableBeaconer();
        }
        $this->pineAPHelper->setBeaconInterval($settings->broadcastInterval);
        $this->pineAPHelper->setResponseInterval($settings->responseInterval);
        $this->pineAPHelper->setTarget($settings->targetMAC);
        $this->pineAPHelper->setSource($settings->sourceMAC);
        $this->response = array("success" => true);
    }
}
