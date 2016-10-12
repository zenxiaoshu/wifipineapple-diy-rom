<?php namespace pineapple;

class Tracking extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getScript':
                $this->getScript();
                break;
            
            case 'saveScript':
                $this->saveScript();
                break;

            case 'getTrackingList':
                $this->getTrackingList();
                break;

            case 'addMac':
                $this->addMac();
                break;

            case 'removeMac':
                $this->removeMac();
                break;

            case 'clearMacs':
                $this->clearMacs();
                break;
        }
    }

    private function getScript()
    {
        $trackingScript = file_get_contents("/etc/pineapple/tracking_script_user");
        $this->response = array("trackingScript" => $trackingScript);
    }

    private function saveScript()
    {
        if (isset($this->request->trackingScript)) {
            file_put_contents("/etc/pineapple/tracking_script_user", $this->request->trackingScript);
        }
        $this->response = array("success" => true);
    }

    private function getTrackingList()
    {
        $trackingList = file_get_contents("/etc/pineapple/tracking_list");
        $this->response =  array("trackingList" => $trackingList);
    }

    private function addMac()
    {
        if (isset($this->request->mac) && !empty($this->request->mac)) {
            $mac = strtolower($this->request->mac);
            file_put_contents("/etc/pineapple/tracking_list", "{$mac}\n", FILE_APPEND);
            $this->execBackground("/usr/bin/pineapple/uds_send /var/run/log_daemon.sock 'track:$mac'");
            $this->getTrackingList();
        }
    }

    private function removeMac()
    {
        if (isset($this->request->mac) && !empty($this->request->mac)) {
            $mac = strtolower($this->request->mac);
            exec("sed -r '/^({$mac})$/d' -i /etc/pineapple/tracking_list");
            $this->execBackground("/usr/bin/pineapple/uds_send /var/run/log_daemon.sock 'untrack:$mac'");
            $this->getTrackingList();
        }
    }

    private function clearMacs()
    {
        file_put_contents("/etc/pineapple/tracking_list", "");
        $this->execBackground("killall log_daemon; log_daemon /tmp/pineap.log 30");
        $this->getTrackingList();
    }
}
