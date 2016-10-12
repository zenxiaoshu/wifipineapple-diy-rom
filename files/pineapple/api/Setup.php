<?php namespace pineapple;

class Setup extends APIModule
{
    private function changePassword()
    {
        if ($this->request->rootPassword !== $this->request->confirmRootPassword) {
            $this->response = array('error' => 'The root passwords do not match.');
            return false;
        }
        $new = $this->request->rootPassword;
        $shadow_file = file_get_contents('/etc/shadow');
        $root_array = explode(":", explode("\n", $shadow_file)[0]);
        $salt = '$1$'.explode('$', $root_array[1])[2].'$';
        $new = crypt($new, $salt);
        $find = implode(":", $root_array);
        $root_array[1] = $new;
        $replace = implode(":", $root_array);

        $shadow_file = str_replace($find, $replace, $shadow_file);
        file_put_contents("/etc/shadow", $shadow_file);
        return true;
    }

    private function checkButtonStatus()
    {
        $buttonPressed = true;
        $bootStatus = true;
        if (file_exists('/tmp/button_setup')) {
            $buttonPressed = true;
        }
        if (!file_exists('/etc/pineapple/init')) {
            $bootStatus = true;
        }
        $this->response = array('buttonPressed' => $buttonPressed, 'booted' => $bootStatus);
        return $buttonPressed;
    }

    private function setupWifi()
    {
        $ssid = $this->request->ssid;
        if (strlen($ssid) < 1) {
            $this->error = 'The Management SSID cannot be empty.';
            return false;
        }
        if ($this->request->wpaPassword !== $this->request->confirmWpaPassword) {
            $this->error = 'The WPA2 Passwords do not match.';
            return false;
        }
        $wpaPassword = $this->request->wpaPassword;
        if (strlen($wpaPassword) < 8) {
            $this->response = array('error' => 'The WPA2 passwords must be at least 8 characters.');
            return false;
        }
        $ssid = escapeshellarg($ssid);
        $wpaPassword = escapeshellarg($wpaPassword);

        exec('/sbin/wifi detect > /etc/config/wireless');
        exec("uci set wireless.@wifi-iface[1].ssid={$ssid}");
        exec("uci set wireless.@wifi-iface[1].key={$wpaPassword}");
        exec('uci set wireless.@wifi-iface[1].disabled=\'0\'');
        exec('uci set wireless.@wifi-iface[0].hidden=\'1\'');
        exec('uci commit wireless');
        return true;
    }

    private function enableSSH()
    {
        exec('echo "/etc/init.d/sshd enable" | at now');
        exec('echo "/etc/init.d/sshd start" | at now');
        $pid = explode('\n', exec('pgrep /usr/sbin/sshd'))[0];
        if (is_numeric($pid) && intval($pid) > 0) {
            return true;
        }
        return false;
    }

    private function restartWifi()
    {
        exec('echo "/sbin/wifi" | at now');
    }

    private function finalizeSetup()
    {
        $this->enableSSH();
        $this->restartWifi();
        @unlink('/etc/pineapple/setupRequired');
        @unlink('/pineapple/api/Setup.php');
        exec('killall blink');
        exec('pineapple led reset');
        exec('/bin/rm -rf /pineapple/modules/Setup');
    }

    public function performSetup()
    {
        if (!$this->checkButtonStatus()) {
            return false;
        }
        if ($this->request->eula !== true || $this->request->license !== true) {
            $this->error = "Please accept the EULA and Software License.";
            return false;
        }
        if ($this->changePassword() && $this->setupWifi()) {
            $this->finalizeSetup();
        }
    }

    public function route()
    {
        @session_write_close();
        if (file_exists('/etc/pineapple/setupRequired')) {
            switch ($this->request->action) {
                case 'checkButtonStatus':
                    $this->checkButtonStatus();
                    break;
                case 'performSetup':
                    $this->performSetup();
                    break;
            }
        }
    }
}
