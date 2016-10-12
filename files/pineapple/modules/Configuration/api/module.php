<?php namespace pineapple;

class Configuration extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getCurrentTimeZone':
                $this->getCurrentTimeZone();
                break;

            case 'getLandingPageData':
                $this->getLandingPageData();
                break;

            case 'saveLandingPage':
                $this->saveLandingPageData();
                break;

            case 'changePassword':
                $this->changePassword();
                break;

            case 'changeTimeZone':
                $this->changeTimeZone();
                break;

            case 'resetPineapple':
                $this->resetPineapple();
                break;

            case 'haltPineapple':
                $this->haltPineapple();
                break;

            case 'rebootPineapple':
                $this->rebootPineapple();
                break;

            case 'getLandingPageStatus':
                $this->getLandingPageStatus();
                break;

            case 'enableLandingPage':
                $this->enableLandingPage();
                break;

            case 'disableLandingPage':
                $this->disableLandingPage();
                break;
        }
    }

    private function haltPineapple()
    {
        $this->execBackground("sync && pineapple led off && halt");
        $this->response = array("success" => true);
    }

    private function rebootPineapple()
    {
        $this->execBackground("reboot");
        $this->response = array("success" => true);
    }

    private function resetPineapple()
    {
        $this->execBackground("mtd -r erase rootfs_data");
        $this->response = array("success" => true);
    }

    private function getCurrentTimeZone()
    {
        $currentTimeZone = exec('date +%Z%z');
        $this->response = array("currentTimeZone" => $currentTimeZone);
    }

    private function changeTimeZone()
    {
        $timeZone = escapeshellarg($this->request->timeZone);
        exec("echo {$timeZone} > /etc/TZ");
        $this->uciSet('system.@system[0].timezone', $this->request->timeZone);
        $this->response = array("success" => true);
    }

    private function getLandingPageData()
    {
        $landingPage = file_get_contents('/etc/pineapple/landingpage.php');
        $this->response = array("landingPage" => $landingPage);
    }

    private function getLandingPageStatus()
    {
        if (!empty(exec("iptables -L -vt nat | grep 'www to:.*:80'"))) {
            $this->response = array("enabled" => true);
            return;
        }
        $this->response = array("enabled" => false);
    }

    private function enableLandingPage()
    {
        exec('iptables -t nat -A PREROUTING -p tcp --dport 80 -j DNAT --to-destination $(uci get network.lan.ipaddr):80');
        exec('iptables -t nat -A POSTROUTING -j MASQUERADE');
        copy('/pineapple/modules/Configuration/api/landingpage_index.php', '/www/index.php');
        $this->response = array("success" => true);
    }

    private function disableLandingPage()
    {
        @unlink('/www/index.php');
        exec('iptables -t nat -D PREROUTING -p tcp --dport 80 -j DNAT --to-destination $(uci get network.lan.ipaddr):80');
        $this->response = array("success" => true);
    }

    private function saveLandingPageData()
    {
        if (file_put_contents('/etc/pineapple/landingpage.php', $this->request->landingPageData) !== false) {
            $this->response = array("success" => true);
        } else {
            $this->error = "Error saving Landing Page.";
        }
    }

    protected function changePassword()
    {
        if ($this->request->newPassword === $this->request->newPasswordRepeat) {
            if (parent::changePassword($this->request->oldPassword, $this->request->newPassword) === true) {
                $this->response = array("success" => true);
                return;
            }
        }

        $this->response = array("success" => false);
    }
}
