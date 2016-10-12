registerController("ClientsController", ['$api', '$scope', '$timeout', function($api, $scope, $timeout){
    $scope.clientData = [];
    $scope.clients = [];

    $scope.getClientData = function(){
        $api.request({
            module: "Clients",
            action: "getClientData"
        }, function(response) {
            $scope.clientData = response.clients;
            $scope.parseClients();
        });
    };
    $scope.parseClients = function(){
        $scope.clients = [];
        for (var mac in $scope.clientData.stations) {
            var dhcp = $scope.clientData.dhcp[mac];
            $scope.clients.push({
                mac: mac,
                lastSeen: $scope.clientData.stations[mac],
                ip: dhcp !== undefined ? dhcp[0] : $scope.clientData.arp[mac],
                hostname: dhcp !== undefined ? dhcp[1] : undefined,
                ssid: $scope.clientData.ssids[mac] !== undefined ? $scope.clientData.ssids[mac] : undefined
            });
        }
    };
    $scope.kickClient = function(client){
        $api.request({
            module: "Clients",
            action: "kickClient",
            mac: client.mac
        }, function(){
            client['kicking'] = true;
            $timeout(function() {
                client['kicking'] = false;
                $scope.getClientData();
            }, 3000);
        });
    };

    $scope.getClientData();
}]);
