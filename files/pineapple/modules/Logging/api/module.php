<?php namespace pineapple;

class Logging extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getSyslog':
                $this->getSyslog();
                break;

            case 'getDmesg':
                $this->getDmesg();
                break;

            case 'getReportingLog':
                $this->getReportingLog();
                break;

            case 'getPineapLog':
                $this->getPineapLog();
                break;

            case 'clearPineapLog':
                $this->clearPineapLog();
                break;

            case 'getPineapLogLocation':
                $this->getPineapLogLocation();
                break;

            case 'setPineapLogLocation':
                $this->setPineapLogLocation();
                break;

            case 'downloadPineapLog':
                $this->downloadPineapLog();
                break;
        }
    }

    private function downloadPineapLog()
    {
        $this->response = array("download" => $this->downloadFile(file_get_contents('/etc/pineapple/pineap_log_location') . 'pineap.log'));
    }

    private function getSyslog()
    {
        exec("logread", $syslogOutput);
        $this->response = implode("\n", $syslogOutput);
    }

    private function getDmesg()
    {
        exec("dmesg", $syslogOutput);
        $this->response = implode("\n", $syslogOutput);
    }

    private function getReportingLog()
    {
        touch('/tmp/reporting.log');
        $this->streamFunction = function () {
            $fp = fopen('/tmp/reporting.log', 'r');
            while (!feof($fp)) {
                echo fgets($fp);
            }
            fclose($fp);
        };
    }

    private function getPineapLog()
    {
        touch(file_get_contents('/etc/pineapple/pineap_log_location') . 'pineap.log');
        $this->streamFunction = function () {
            $fp = fopen(file_get_contents('/etc/pineapple/pineap_log_location') . 'pineap.log', 'r');
            echo '[';
            while (!feof($fp)) {
                $line = fgets($fp);
                $entry = explode(",\t", $line);
                echo json_encode($entry);
                if (!feof($fp)) {
                    echo ',';
                }
            }
            fclose($fp);
            echo ']';
        };
    }

    private function clearPineapLog()
    {
        file_put_contents(file_get_contents('/etc/pineapple/pineap_log_location') . 'pineap.log', '');
        $this->response = array('Success');
    }

    private function getPineapLogLocation()
    {
        $this->response = array('location' => trim(file_get_contents('/etc/pineapple/pineap_log_location')));
    }

    private function setPineapLogLocation()
    {
        file_put_contents('/etc/pineapple/pineap_log_location', $this->request->location);
        $this->response = array('success' => true);
    }
}
