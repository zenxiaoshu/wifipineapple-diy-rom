<?php namespace pineapple;

class Advanced extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getResources':
                $this->getResources();
                break;

            case 'dropCaches':
                $this->dropCaches();
                break;

            case 'getUSB':
                $this->getUSB();
                break;

            case 'getFstab':
                $this->getFstab();
                break;

            case 'saveFstab':
                $this->saveFstab();
                break;

            case 'getCSS':
                $this->getCSS();
                break;

            case 'saveCSS':
                $this->saveCSS();
                break;

            case 'formatSDCard':
                $this->formatSDCard();
                break;

            case 'formatSDCardStatus':
                $this->formatSDCardStatus();
                break;

            case 'checkForUpgrade':
                $this->checkForUpgrade();
                break;

            case 'downloadUpgrade':
                $this->downloadUpgrade();
                break;

            case 'getDownloadStatus':
                $this->getDownloadStatus();
                break;

            case 'performUpgrade':
                $this->performUpgrade();
                break;

            case 'getCurrentVersion':
                $this->getCurrentVersion();
                break;
        }
    }

    private function getResources()
    {
        exec('df -h', $freeDisk);
        $freeDisk = implode("\n", $freeDisk);

        exec('free -m', $freeMem);
        $freeMem = implode("\n", $freeMem);

        $this->response = array("freeDisk" => $freeDisk, "freeMem" => $freeMem);
    }

    private function dropCaches()
    {
        $this->execBackground('echo 3 > /proc/sys/vm/drop_caches');
        $this->response = array('success' => true);
    }

    private function formatSDCard()
    {
        $this->execBackground("/pineapple/modules/Advanced/formatSD/format_sd");
        $this->response = array('success' => true);
    }

    private function formatSDCardStatus()
    {
        if (!file_exists('/tmp/sd_format.progress')) {
            $this->response = array('success' => true);
        } else {
            $this->response = array('success' => false);
        }
    }

    private function getUSB()
    {
        exec('lsusb', $lsusb);
        $lsusb = implode("\n", $lsusb);

        $this->response = array('lsusb' => $lsusb);
    }

    private function getFstab()
    {
        $fstab = file_get_contents('/etc/config/fstab');
        $this->response = array('fstab' => $fstab);
    }

    private function saveFstab()
    {
        if (isset($this->request->fstab)) {
            file_put_contents('/etc/config/fstab', $this->request->fstab);
            $this->response = array("success" => true);
        }
    }

    private function getCSS()
    {
        $css = file_get_contents('/pineapple/css/main.css');
        $this->response = array('css' => $css);
    }

    private function saveCSS()
    {
        if (isset($this->request->css)) {
            file_put_contents('/pineapple/css/main.css', $this->request->css);
            $this->response = array("success" => true);
        }
    }

    private function checkForUpgrade()
    {
        $context = stream_context_create(["ssl" => ["verify_peer" => true, "cafile" => "/etc/ssl/certs/cacert.pem"]]);
        $upgradeData = @file_get_contents("https://www.wifipineapple.com/nano/upgrades", false, $context);

        if ($upgradeData !== false) {
            $upgradeData = json_decode($upgradeData);
            if (json_last_error() === JSON_ERROR_NONE) {
                if ($this->compareFirmwareVersion($upgradeData->version) === true) {
                    $this->response = array("upgrade" => true, "upgradeData" => $upgradeData);
                } else {
                    $this->error = "No upgrade found.";
                }
            }
        } else {
            $this->error = "Error connecting to WiFiPineapple.com. Please check your connection.";
        }
        
    }

    private function downloadUpgrade()
    {
        $version = $this->request->version;
        @unlink("/tmp/upgrade.bin");
        @unlink("/tmp/upgradeDownloaded");
        $this->execBackground("wget 'https://www.wifipineapple.com/nano/upgrades/{$version}' -O /tmp/upgrade.bin && touch /tmp/upgradeDownloaded");
        $this->response = array("success" => true);
    }

    private function getDownloadStatus()
    {
        if (file_exists("/tmp/upgradeDownloaded")) {
            if (hash_file('sha256', '/tmp/upgrade.bin') == $this->request->checksum) {
                $this->response = array("completed" => true);
            } else {
                $this->error = true;
            }
        } else {
            $this->response = array("completed" => false, "downloaded" => filesize('/tmp/upgrade.bin'));
        }
    }

    private function performUpgrade()
    {
        if (file_exists('/tmp/upgrade.bin')) {
            $size = escapeshellarg(filesize('/tmp/upgrade.bin') - 33);
            exec("dd if=/dev/null of=/tmp/upgrade.bin bs=1 seek={$size}");
            $this->execBackground("sysupgrade -n /tmp/upgrade.bin");
            $this->response = array("success" => true);
        } else {
            $this->error = true;
        }
    }

    private function compareFirmwareVersion($version)
    {
        return version_compare($this->getFirmwareVersion(), $version, '<');
    }

    private function getCurrentVersion()
    {
        $this->response = array("firmwareVersion" => $this->getFirmwareVersion());
    }
}
