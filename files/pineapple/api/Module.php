<?php namespace pineapple;

/**
 * This class will contain the base code which all modules
 * must extend.
 */

abstract class Module
{
    protected $request;
    protected $response;
    protected $moduleClass;
    protected $error;
    protected $streamFunction;

    abstract public function route();

    public function __construct($request, $moduleClass)
    {
        $this->request = $request;
        $this->moduleClass = $moduleClass;
        $this->error = '';
    }

    public function getResponse()
    {
        if (empty($this->error) && !empty($this->response)) {
            return $this->response;
        } elseif (!empty($this->streamFunction)) {
            header('Content-Type: text/plain');
            $this->response = false;
            $this->streamFunction->__invoke();
        } elseif (empty($this->error) && empty($this->response)) {
            return array('error' => 'Module returned empty response');
        } else {
            return array('error' => $this->error);
        }
    }

    protected function execBackground($command)
    {
        return exec("echo \"{$command}\" | at now");
    }

    protected function installDependency($dependencyName, $installToSD = false)
    {
        if ($installToSD && !$this->isSDAvailable()) {
            return false;
        }

        $destination = $installToSD ? '--dest sd' : '';
        $dependencyName = escapeshellarg($dependencyName);
        
        if (!$this->checkDependency($dependencyName)) {
            exec("opkg update");
            exec("opkg install {$dependencyName} {$destination}");
        }
        return $this->checkDependency($dependencyName);
    }

    protected function isSDAvailable()
    {
         return (exec('mount | grep "on /sd" -c') >= 1) ? true : false;
    }

    protected function checkDependency($dependencyName)
    {
        return (trim(exec("which {$dependencyName}")) == '' ? false : true);
    }

    protected function checkRunning($processName)
    {
        $processName = escapeshellarg($processName);
        return exec("ps | grep -w {$processName} | grep -v grep") !== '';
    }

    protected function uciGet($uciString)
    {
        $uciString = escapeshellarg($uciString);
        $result = exec("uci get {$uciString}");

        $result = ($result === "1") ? true : $result;
        $result = ($result === "0") ? false : $result;

        return $result;
    }

    protected function uciSet($settingString, $value)
    {
        $settingString = escapeshellarg($settingString);
        if (!empty($value)) {
            $value = escapeshellarg($value);
        }
        
        if ($value === "''") {
            $value = "'0'";
        }

        exec("uci set {$settingString}={$value}");
        exec("uci commit {$settingString}");
    }

    protected function uciAddList($settingString, $value)
    {
        $settingString = escapeshellarg($settingString);
        if (!empty($value)) {
            $value = escapeshellarg($value);
        }
        
        if ($value === "''") {
            $value = "'0'";
        }

        exec("uci add_list {$settingString}={$value}");
        exec("uci commit {$settingString}");
    }

    protected function downloadFile($file)
    {
        $token = hash('sha256', $file . time());

        require_once('DatabaseConnection.php');
        $database = new DatabaseConnection("/etc/pineapple/pineapple.db");
        $database->exec("CREATE TABLE IF NOT EXISTS downloads (token VARCHAR NOT NULL, file VARCHAR NOT NULL, time timestamp default (strftime('%s', 'now')));");
        $database->exec("INSERT INTO downloads (token, file) VALUES ('%s', '%s')", $token, $file);
        
        return $token;
    }

    protected function getFirmwareVersion()
    {
        return trim(file_get_contents('/etc/pineapple/pineapple_version'));
    }

    protected function getDevice()
    {
        return 'nano';
    }

    protected function getMacFromInterface($interface)
    {
        $interface = escapeshellarg($interface);
        return trim(exec("ifconfig {$interface} | grep HWaddr | awk '{print $5}'"));
    }
}
