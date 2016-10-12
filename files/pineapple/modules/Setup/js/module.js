registerController('SetupController', ['$api', '$scope', '$interval', '$timeout', function($api, $scope, $interval, $timeout) {
    $scope.ssid = '';
    $scope.wpaPassword = '';
    $scope.confirmWpaPassword = '';
    $scope.rootPassword = '';
    $scope.confirmRootPassword = '';
    $scope.verified = false;
    $scope.errorMessage = '';
    $scope.eula = false;
    $scope.license = false;
    $scope.complete = false;
    $scope.booted = false;

    $scope.doSetup = function(){
        $scope.errorMessage = '';
        $api.request({
            system: 'setup',
            action: 'performSetup',
            ssid: $scope.ssid,
            wpaPassword: $scope.wpaPassword,
            confirmWpaPassword: $scope.confirmWpaPassword,
            rootPassword: $scope.rootPassword,
            confirmRootPassword: $scope.confirmRootPassword,
            eula: $scope.eula,
            license: $scope.license
        }, function(response){
            if (response.error === undefined) {
                $scope.verified = false;
                $scope.complete = true;
                $("#loginModal").remove();
                $timeout(function() {
                    window.location = '/';
                }, 5000);
            } else {
                $scope.errorMessage = response.error;
            }
        });
    };
    $scope.checkButton = function(){
        $api.request({
            system: 'setup',
            action: 'checkButtonStatus'
        }, function(response){
            if (response.booted === true) {
                $scope.booted = true;
            } else {
                $scope.booted = false;
            }
            if (response.buttonPressed === true) {
                $('#verificationModal').modal('hide');
                $interval.cancel($scope.buttonCheckInterval);
                $scope.verified = true;
            }
        });
    };
    $scope.showVerificationModal = function(){
        $('#verificationModal').modal(true);
        $scope.buttonCheckInterval = $interval($scope.checkButton, 1000);
    };

    $scope.$on('$destroy', function() {
        $interval.cancel($scope.buttonCheckInterval);
    });

    $('#welcomeModal').modal(true);
}]);