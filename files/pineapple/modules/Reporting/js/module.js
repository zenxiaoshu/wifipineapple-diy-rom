registerController('ReportConfigurationController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.configSaved = false;
    $scope.sdDisabled = false;
    $scope.config = {
        generateReport: false,
        storeReport: false,
        sendReport: false,
        interval: 1
    };

    $scope.saveConfiguration = (function() {
        $api.request({
            module: 'Reporting',
            action: 'setReportConfiguration',
            config: $scope.config
        }, function(response) {
            if (response.error === undefined) {
                $scope.configSaved = true;
                $timeout(function() {
                    $scope.configSaved = false;
                }, 2000);
            }
        });
    });

    $api.request({
        module: 'Reporting',
        action: 'getReportConfiguration'
    }, function(response) {
        $scope.config = response.config;
        $scope.sdDisabled = response.sdDisabled;
    });
}]);

registerController('ReportContentController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.configSaved = false;
    $scope.config = {
        pineAPLog: false,
        clearLog: false,
        siteSurvey: false,
        client: false,
        tracking: false,
        siteSurveyDuration: 15
    };

    $scope.saveConfiguration = (function() {
        $api.request({
            module: 'Reporting',
            action: 'setReportContents',
            config: $scope.config
        }, function(response) {
            if (response.error === undefined) {
                $scope.configSaved = true;
                $timeout(function() {
                    $scope.configSaved = false;
                }, 2000);
            }
        });
    });

    $api.request({
        module: 'Reporting',
        action: 'getReportContents'
    }, function(response) {
        $scope.config = response.config
    });
}]);

registerController('EmailConfigurationController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.configSaved = false;
    $scope.config = {
        from : "",
        to : "",
        server: "",
        port: "",
        domain: "",
        username: "",
        password: "",
        tls: true,
        starttls: true
    };

    $scope.saveConfiguration = (function() {
        $api.request({
            module: 'Reporting',
            action: 'setEmailConfiguration',
            config: $scope.config
        }, function(response) {
            if (response.error === undefined) {
                $scope.configSaved = true;
                $timeout(function() {
                    $scope.configSaved = false;
                }, 2000);
            }
        });
    });

    $api.request({
        module: 'Reporting',
        action: 'getEmailConfiguration'
    }, function(response) {
        $scope.config = response.config;
    });
}]);
