registerController("DebugController", ['$api', '$scope', '$timeout', function($api, $scope, $timeout){
    $scope.loading = false;

    $scope.generateDebugFile = (function(){
        $api.request({
            module: "Help",
            action: "generateDebugFile"
        }, function(response) {
            if (response.success == true) {
                $scope.loading = true;
                $timeout($scope.downloadDebugFile, 30000);
            }
        })
    });

    $scope.downloadDebugFile = (function(){
        $api.request({
            module: "Help",
            action: "downloadDebugFile"
        }, function(response) {
            $scope.loading = false;
            if (response.success == true) {
                window.location = '/api/?download=' + response.downloadToken;
            }
        })
    });
}]);