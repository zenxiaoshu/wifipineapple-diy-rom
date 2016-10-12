<?php namespace pineapple;

class Recon extends SystemModule
{
    private $clientInterface = "wlan1";
    private $scanID = null;

    public function route()
    {
        switch ($this->request->action) {
            case 'scanStatus':
                $this->getScanStatus();
                break;

            case 'startScan':
                $this->startScan();
                break;
        }
    }

    private function startScan()
    {
        $this->scanID = rand(0, getrandmax());
        if (isset($this->request->scanType)) {
            if ($this->request->scanType > 2 || $this->request->scanType < 0) {
                $this->request->scanType = 0;
            }
            if (is_numeric($this->request->scanDuration)) {
                if ($this->request->scanDuration < 15 || $this->request->scanDuration > 600) {
                    $this->request->scanDuration = 15;
                }
            } else {
                $this->request->scanDuration = 15;
            }
            $this->startMonitorMode();
            $success = $this->scan($this->request->scanDuration, $this->request->scanType);
            $this->response = array("success" => $success, "scanID" => $this->scanID);
        } else {
            $this->response = array("success" => false);
        }
    }

    private function scan($duration, $type)
    {
        $cmd = "pinesniffer {$this->clientInterface}mon {$duration} {$type} /tmp/recon-{$this->scanID}";
        exec("echo '{$cmd}' | at now");
        sleep(1);
        return $this->checkRunning($cmd);
    }

    private function startMonitorMode()
    {
        if (empty(exec("ifconfig | grep {$this->clientInterface}mon"))) {
            exec("airmon-ng start {$this->clientInterface}");
        }
    }

    private function getScanStatus()
    {
        if (isset($this->request->scanID)) {
            if (file_exists("/tmp/recon-{$this->request->scanID}")) {
                $this->response = array(
                    "completed" => true,
                    "results" => $this->getScanResults(),
                    "interfaceMacs" => array(
                        $this->getMacFromInterface("wlan0"),
                        $this->getMacFromInterface("wlan0-1")
                    )
                );
                return;
            } elseif (isset($this->request->percent) && $this->request->percent == 100) {
                $scanID = intval($this->request->scanID);
                if ($scanID >= 10) {
                    $pid = exec("ps | grep /tmp/recon-{$scanID} | grep -v grep | awk '{print $1}'");
                    exec("kill -SIGALRM {$pid}");
                }
            }
        }
        $this->response = array("completed" => false);
    }

    private function getScanResults()
    {
        sleep(1);
        $results = json_decode($this->removeBOM(file_get_contents("/tmp/recon-{$this->request->scanID}")));
        return $results;
    }

    // http://stackoverflow.com/a/31594983
    private function removeBOM($data)
    {
        if (0 === strpos(bin2hex($data), 'efbbbf')) {
            return substr($data, 3);
        } else {
            return $data;
        }
    }
}
