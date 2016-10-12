<?php namespace pineapple;

class Help extends SystemModule
{
    public function route()
    {
        switch ($this->request->action) {
            case 'generateDebugFile':
                $this->generateDebugFile();
                break;
            case 'downloadDebugFile':
                $this->downloadDebugFile();
                break;
        }
    }

    private function generateDebugFile()
    {
        $this->execBackground("/pineapple/modules/Help/files/debug");
        $this->response = array("success" => true);
    }

    private function downloadDebugFile()
    {
        $this->response = array("success" => true, "downloadToken" => $this->downloadFile("/tmp/debug.log"));
    }
}
