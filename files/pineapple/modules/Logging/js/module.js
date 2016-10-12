registerController('PineAPLogController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.log = [];
    $scope.mac = '';
    $scope.ssid = '';
    $scope.logLocation = '';
    $scope.locationModified = false;

    $scope.checkboxOptions = {
        probes: true,
        associations: true,
        removeDuplicates: false
    };


    $scope.refreshLog = (function() {
        $scope.log = [];
        $api.request({
            module: 'Logging',
            action: 'getPineapLog'
        }, function(response) {
            if (response.error === undefined) {
                $scope.log = response;
                $scope.applyFilter();
            } 
        });
    });

    $scope.downloadLog = (function() {
        $api.request({
            module: 'Logging',
            action: 'downloadPineapLog'
        }, function(response) {
            if (response.error === undefined) {
                window.location = '/api/?download=' + response.download;
            } 
        });
    });

    $scope.getPineapLogLocation = (function () {
        $api.request({
            module: 'Logging',
            action: 'getPineapLogLocation'
        }, function(response) {
            if (response.error === undefined) {
                $scope.logLocation = response.location;
            } 
        });
    });

    $scope.setPineapLogLocation = (function () {
        $api.request({
            module: 'Logging',
            action: 'setPineapLogLocation',
            location: $scope.logLocation
        }, function(response) {
            if (response.error === undefined) {
                $scope.locationModified = true;
                $timeout(function() {
                    $scope.locationModified = false;
                }, 3000);
            } 
        });
    });

    $scope.applyFilter = (function() {
        var hashArray = [];
        $.each($scope.log, function(index, value){
            if (value[0] !== '') {
                value.hidden = false;
                if ($scope.checkboxOptions.removeDuplicates) {
                    var index = value[1][0] + value[2] + value[3];
                    if (hashArray[index] === undefined) {
                        hashArray[index] = true;
                    } else {
                        value.hidden = true;
                        return true;
                    }
                }

                if (!$scope.checkboxOptions.probes) {
                    if (value[1] == 'Probe Request') {
                        value.hidden = true;
                    }
                }
                if (!$scope.checkboxOptions.associations) {
                    if (value[1] == 'Association' || value[1] == 'Deassociation') {
                        value.hidden = true;
                    }
                }

                if ($scope.mac.trim() !== '' && value[2].toLowerCase() != $scope.mac.toLowerCase()) {
                    value.hidden = true;
                } else if ($scope.ssid.trim() !== '' && value[3].trim().toLowerCase() != $scope.ssid.toLowerCase()) {
                    value.hidden = true;
                }
            }
        });
    });

    $scope.clearFilter = (function() {
        $scope.mac = '';
        $scope.ssid = '';
        $scope.checkboxOptions.probes = true;
        $scope.checkboxOptions.associations = true;
        $scope.checkboxOptions.removeDuplicates = false;

        $scope.applyFilter();
    });

    $scope.clearLog = (function(mac) {
        $api.request({
            module: 'Logging',
            action: 'clearPineapLog'
        }, function(response) {
            if (response.error === undefined) {
                $scope.log = [];
            } 
        });
        $scope.log = [];
    });

    $scope.getPineapLogLocation();
    $scope.refreshLog();    
}]);

registerController('SyslogController', ['$api', '$scope', function($api, $scope) {
    $scope.syslog = 'Loading..';

    $scope.refreshLog = (function() {
        $api.request({
            module: 'Logging',
            action: 'getSyslog'
        }, function(response) {
            if (response.error === undefined) {
                $scope.syslog = response;
            } 
        })
    });

    $scope.refreshLog();
}]);

registerController('DmesgController', ['$api', '$scope', function($api, $scope) {
    $scope.dmesg = 'Loading..';

    $scope.refreshLog = (function() {
        $api.request({
            module: 'Logging',
            action: 'getDmesg'
        }, function(response) {
            if (response.error === undefined) {
                $scope.dmesg = response;
            } 
        })
    });

    $scope.refreshLog();
}]);

registerController('ReportingLogController', ['$api', '$scope', function($api, $scope) {
    $scope.reportingLog = "";

    $scope.refreshLog = (function() {
        $api.request({
            module: 'Logging',
            action: 'getReportingLog'
        }, function(response) {
            if (response.error === undefined) {
                $scope.reportingLog = response;
            } 
        })
    });

    $scope.refreshLog();
}]);
