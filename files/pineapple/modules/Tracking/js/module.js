registerController("TrackingListController", ['$api', '$scope', function($api, $scope) {
    $scope.trackingList = "";
    $scope.mac = "";

    $api.request({
        module: 'Tracking',
        action: 'getTrackingList'
    }, function(response) {
        if (response.error === undefined) {
            $scope.trackingList = response.trackingList;
        }
    });

    $scope.addMac = (function() {
        $api.request({
            module: 'Tracking',
            action: 'addMac',
            mac: $scope.mac
        }, function(response) {
            if (response.error === undefined) {
                $scope.trackingList = response.trackingList;
                $scope.mac = "";
            }
        });
    });

    $scope.removeMac = (function() {
        $api.request({
            module: 'Tracking',
            action: 'removeMac',
            mac: $scope.mac
        }, function(response) {
            if (response.error === undefined) {
                $scope.trackingList = response.trackingList;
                $scope.mac = "";
            }
        });
    });

    $scope.clearMacs = (function() {
        $api.request({
            module: 'Tracking',
            action: 'clearMacs'
        }, function(response) {
            if (response.error === undefined) {
                $scope.trackingList = response.trackingList;
            }
        });
    });
}]);

registerController("TrackingScriptController", ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.trackingScript = "";
    $scope.scriptSaved = false;

    $api.request({
        module: 'Tracking',
        action: 'getScript'
    }, function(response) {
        if (response.error === undefined) {
            $scope.trackingScript = response.trackingScript;
        }
    });

    $scope.saveScript = (function() {
        $api.request({
            module: 'Tracking',
            action: 'saveScript',
            trackingScript: $scope.trackingScript
        }, function(response) {
            if (response.success === true) {
                $scope.scriptSaved = true;
                $timeout(function(){
                    $scope.scriptSaved = false;
                }, 2000);
            }
        });
    });
}]);

function getLineNumber(textarea) {
    var lineNumber = textarea.value.substr(0, textarea.selectionStart).split("\n").length;
    var mac = textarea.value.split("\n")[lineNumber-1].trim();
    $("input[name='mac']").val(mac).trigger('input');
}