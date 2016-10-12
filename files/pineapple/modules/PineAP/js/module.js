registerController('PineapPoolController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.ssid = "";
    $scope.ssidPool = "";
    $scope.addedSSID = "";
    $scope.removedSSID = "";
    $scope.clearedSSIDPool = "";
    $scope.lengthError = "";

    $scope.downloadPool = (function() {
        $api.request({
            module: 'PineAP',
            action: 'downloadPineAPPool'
        }, function(response) {
            if (response.error === undefined) {
                window.location = '/api/?download=' + response.download;
            } 
        });
    });

    $scope.addSSID = (function() {
        $api.request({
            module: 'PineAP',
            action: 'addSSID',
            ssid: $scope.ssid
        }, function(response) {
            if (response.error === undefined) {
                $scope.ssid = "";
                $scope.addedSSID = true;
            } else {
                console.log(response);
                $scope.lengthError = true;
            }
            $timeout(function(){
                $scope.addedSSID = false;
                $scope.lengthError = false;
            }, 2000);
            $scope.getPool();
        });
    });

    $scope.removeSSID = (function() {
        $api.request({
            module: 'PineAP',
            action: 'removeSSID',
            ssid: $scope.ssid
        }, function(response) {
            if (response.error === undefined) {
                $scope.removedSSID = true;
            } else {
                $scope.lengthError = true;
            }
            $timeout(function(){
                $scope.removedSSID = false;
                $scope.lengthError = false;
            }, 2000);
            $scope.getPool();
        });
    });


    $scope.getPool = (function() {
        $api.request({
            module: 'PineAP',
            action: 'getPool'
        }, function(response) {
            $scope.ssidPool = response.ssidPool;
        });
    });

    $scope.clearPool = (function() {
        $api.request({
            module: 'PineAP',
            action: 'clearPool'
        }, function(response) {
            if (response.success === true) {
                $scope.ssidPool = "";
                $scope.clearedSSIDPool = true;
                $timeout(function(){
                    $scope.clearedSSIDPool = false;
                }, 2000);
            }
        });
        $scope.getPool();
    });

    $scope.getSSIDLineNumber = function() {
        var textarea = $('#ssidPool');
        var lineNumber = textarea.val().substr(0, textarea[0].selectionStart).split('\n').length;
        var ssid = textarea.val().split('\n')[lineNumber-1].trim();
        $("input[name='ssid']").val(ssid).trigger('input');
    };

    $scope.getPool();
}]);

registerController('PineAPSettingsController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.disableInputs = true;
    $scope.disableButton = false;
    $scope.saveAlert = false;
    $scope.pineAPenabling = false;
    $scope.settings = {
        allowAssociations: false,
        logProbes: false,
        logAssociations: false,
        pineAPDaemon: false,
        beaconResponses: false,
        captureSSIDs: false,
        broadcastSSIDs: false,
        broadcastInterval: 'low',
        responseInterval: 'low',
        sourceMAC: '00:00:00:00:00:00',
        targetMAC: 'FF:FF:FF:FF:FF:FF'
    };

    $scope.togglePineAP = (function() {
        $scope.pineAPenabling = true;
        var actionString = $scope.settings.pineAPDaemon ? "disable" : "enable";
        $api.request({
            module: 'PineAP',
            action: actionString
        }, function(response) {
            $scope.pineAPenabling = false;
            if (response.error === undefined) {
                $scope.getSettings();
            }
        });
    });

    $scope.getSettings = function() {
        $api.request({
            module: 'PineAP',
            action: 'getPineAPSettings'
        }, function(response) {
            if (response.success === true) {
                $scope.disableInputs = !response.settings.pineAPDaemon;
                $scope.settings = response.settings;
            }
        });
    };
    $scope.updateSettings = function() {
        $scope.disableButton = true;
        $api.request({
            module: 'PineAP',
            action: 'setPineAPSettings',
            settings: $scope.settings
        }, function(response) {
            $scope.getSettings();
            $scope.disableButton = false;
        });
    };
    $scope.saveAsDefault = function() {
        $scope.saveAlert = true;
        $api.request({
            module: 'PineAP',
            action: 'saveAsDefault'
        }, function(response){
            $timeout(function(){$scope.saveAlert = false;}, 3000);
        });
    };

    $scope.getSettings();
}]);