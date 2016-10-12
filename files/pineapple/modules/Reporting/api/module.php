<?php namespace pineapple;

class Reporting extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getReportConfiguration':
                $this->getReportConfiguration();
                break;

            case 'getReportContents':
                $this->getReportContents();
                break;

            case 'getEmailConfiguration':
                $this->getEmailConfiguration();
                break;

            case 'setReportConfiguration':
                $this->setReportConfiguration();
                break;

            case 'setReportContents':
                $this->setReportContents();
                break;

            case 'setEmailConfiguration':
                $this->setEmailConfiguration();
                break;
        }
    }

    private function getReportConfiguration()
    {
        $this->response = array("config" => array(
            "generateReport" => (exec("grep files/reporting /etc/crontabs/root") == "") ? false : true,
            "storeReport" => $this->uciGet("reporting.settings.save_report"),
            "sendReport" => $this->uciGet("reporting.settings.send_email"),
            "interval" => (string) $this->uciGet("reporting.settings.interval")
        ));

        if (!$this->isSDAvailable()) {
            $this->response['config']['storeReport'] = false;
            $this->response['sdDisabled'] = true;
        }
    }

    private function getReportContents()
    {
        $this->response = array("config" => array(
            "pineAPLog" => $this->uciGet("reporting.settings.log"),
            "clearLog" => $this->uciGet("reporting.settings.clear_log"),
            "siteSurvey" => $this->uciGet("reporting.settings.survey"),
            "siteSurveyDuration" => $this->uciGet("reporting.settings.duration"),
            "client" => $this->uciGet("reporting.settings.client"),
            "tracking" => $this->uciGet("reporting.settings.tracking")
        ));
    }

    private function getEmailConfiguration()
    {
        $this->response = array("config" => array(
            "from" => $this->uciGet("reporting.ssmtp.from"),
            "to" => $this->uciGet("reporting.ssmtp.to"),
            "server" => $this->uciGet("reporting.ssmtp.server"),
            "port" => $this->uciGet("reporting.ssmtp.port"),
            "domain" => $this->uciGet("reporting.ssmtp.domain"),
            "username" => $this->uciGet("reporting.ssmtp.username"),
            "password" => $this->uciGet("reporting.ssmtp.password"),
            "tls" => $this->uciGet("reporting.ssmtp.tls"),
            "starttls" => $this->uciGet("reporting.ssmtp.starttls")
        ));
    }

    private function setReportConfiguration()
    {
        $this->uciSet("reporting.settings.save_report", $this->request->config->storeReport);
        $this->uciSet("reporting.settings.send_email", $this->request->config->sendReport);
        $this->uciSet("reporting.settings.interval", $this->request->config->interval);
        $this->response = array("success" => true);
        if ($this->request->config->generateReport === true) {
            $hours_minus_one = $this->uciGet("reporting.settings.interval")-1;
            $hour_string = ($hours_minus_one == 0) ? "*" : "0/" . ($hours_minus_one + 1);
            exec("sed -i '/DO NOT TOUCH/d /\\/pineapple\\/modules\\/Reporting\\/files\\/reporting/d' /etc/crontabs/root");
            exec("echo -e '#DO NOT TOUCH BELOW\\n0 {$hour_string} * * * /pineapple/modules/Reporting/files/reporting\\n#DO NOT TOUCH ABOVE' >> /etc/crontabs/root");
        } else {
            exec("sed -i '/DO NOT TOUCH/d /\\/pineapple\\/modules\\/Reporting\\/files\\/reporting/d' /etc/crontabs/root");
        }
    }

    private function setReportContents()
    {
        $this->uciSet("reporting.settings.log", $this->request->config->pineAPLog);
        $this->uciSet("reporting.settings.clear_log", $this->request->config->clearLog);
        $this->uciSet("reporting.settings.survey", $this->request->config->siteSurvey);
        $this->uciSet("reporting.settings.duration", $this->request->config->siteSurveyDuration);
        $this->uciSet("reporting.settings.client", $this->request->config->client);
        $this->uciSet("reporting.settings.tracking", $this->request->config->tracking);
        $this->response = array("success" => true);
    }

    private function setEmailConfiguration()
    {
        $this->uciSet("reporting.ssmtp.from", $this->request->config->from);
        $this->uciSet("reporting.ssmtp.to", $this->request->config->to);
        $this->uciSet("reporting.ssmtp.server", $this->request->config->server);
        $this->uciSet("reporting.ssmtp.port", $this->request->config->port);
        $this->uciSet("reporting.ssmtp.domain", $this->request->config->domain);
        $this->uciSet("reporting.ssmtp.username", $this->request->config->username);
        $this->uciSet("reporting.ssmtp.password", $this->request->config->password);
        $this->uciSet("reporting.ssmtp.tls", $this->request->config->tls);
        $this->uciSet("reporting.ssmtp.starttls", $this->request->config->starttls);

        file_put_contents("/etc/ssmtp/ssmtp.conf", "FromLineOverride=YES\n");
        file_put_contents("/etc/ssmtp/ssmtp.conf", "AuthUser={$this->request->config->username}\n", FILE_APPEND);
        file_put_contents("/etc/ssmtp/ssmtp.conf", "AuthPass={$this->request->config->password}\n", FILE_APPEND);
        file_put_contents("/etc/ssmtp/ssmtp.conf", "mailhub={$this->request->config->server}:{$this->request->config->port}\n", FILE_APPEND);
        file_put_contents("/etc/ssmtp/ssmtp.conf", "hostname={$this->request->config->domain}\n", FILE_APPEND);
        file_put_contents("/etc/ssmtp/ssmtp.conf", "rewriteDomain={$this->request->config->domain}\n", FILE_APPEND);
        if ($this->request->config->tls) {
            file_put_contents("/etc/ssmtp/ssmtp.conf", "UseTLS=YES\n", FILE_APPEND);
        }
        if ($this->request->config->starttls) {
            file_put_contents("/etc/ssmtp/ssmtp.conf", "UseSTARTTLS=YES\n", FILE_APPEND);
        }
        
        $this->response = array("success" => true);
    }
}
