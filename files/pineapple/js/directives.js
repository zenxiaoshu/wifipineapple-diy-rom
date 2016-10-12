(function(){
    angular.module('pineapple')
    .directive('hookModal', function(){
        return {
            restrict: 'E',
            template: '<div class="modal fade" data-keyboard="false" id="pineap-hook" role="dialog"> \
            <div class="modal-dialog"> \
                <div class="modal-content"> \
                    <div class="modal-header"> \
                        <button type="button" ng-click="destroyModal()" class="close">&times;</button> \
                        <h3 class="text-center" style="word-wrap: break-word">{{ content }}</h3> \
                    </div> \
                    <div class="modal-body"> \
                        <div id="ssid-actions" ng-if="hook == \'ssid\' && content != \'\'"> \
                            <h4>PineAP Pool</h4> \
                            <button type="button" class="btn btn-default" ng-click="addSSIDToPool()">Add SSID</button> \
                            <button type="button" class="btn btn-default" ng-click="removeSSIDFromPool()">Remove SSID</button> \
                            <hr> \
                            <h4>PineAP Filter</h4> \
                            <button type="button" class="btn btn-default" ng-click="addSSIDToFilter()">Add SSID</button> \
                            <button type="button" class="btn btn-default" ng-click="removeSSIDFromFilter()">Remove SSID</button> \
                            <button type="button" class="btn btn-default" ng-click="addClientsToFilter()">Add all Clients</button> \
                            <hr ng-if="deauth && ((hook === \'ssid\' && deauth.clients) || hook === \'mac\')"> \
                        </div> \
                        <div id="mac-actions" ng-if="hook == \'mac\'"> \
                            <h4>PineAP Filter</h4> \
                            <button type="button" class="btn btn-default" ng-click="addMACToFilter()">Add MAC</button> \
                            <button type="button" class="btn btn-default" ng-click="removeMacFromFilter()">Remove MAC</button> \
                            <hr> \
                            <h4>PineAP Tracking</h4> \
                            <button type="button" class="btn btn-default" ng-click="addMacToTracking()">Add MAC</button> \
                            <button type="button" class="btn btn-default" ng-click="removeMacFromTracking()">Remove MAC</button> \
                            <hr ng-if="deauth && ((hook === \'ssid\' && deauth.clients) || hook === \'mac\')"> \
                        </div> \
                        <h4 ng-if="deauth && ((hook === \'ssid\' && deauth.clients) || hook === \'mac\')">Deauth Client</h4> \
                        <div class="form-group" ng-if="deauth && ((hook === \'ssid\' && deauth.clients) || hook === \'mac\')" ng-hide="error"> \
                            <label for="deauthMultiplier">Deauth Multiplier</label> \
                            <select class="form-control" id="deauthMultiplier" ng-model="deauthMultiplier" ng-options="multiplier for multiplier in [1,2,3,4,5,6,7,8,9,10]"> \
                            </select> \
                            <br> \
                            <button type="button" class="btn btn-default" ng-if="hook === \'mac\'" ng-click="deauthClient()">Deauth</button> \
                            <button type="button" class="btn btn-default" ng-if="hook === \'ssid\'" ng-click="deauthAP()">Deauth</button> \
                        </div> \
                        <div ng-if="show_probes == true">\
                            <hr>\
                            <h4>PineAP Logged Probes</h4>\
                            <button type="button" class="btn btn-default" ng-click="loadProbes()">Load</button> \
                            <button type="button" class="btn btn-default" ng-click="addProbes()" ng-show="probes">Add all probes to PineAP Pool</button>\
                            <br> \
                            <div class="well-sm alert-success" ng-show="probesAdded">All probes added to the PineAP Pool</div>\
                            <div class="alert alert-danger text-center" ng-show="probeError">No Probes found. Is probe logging enabled?</div> \
                            <textarea class="form-control" rows="10" ng-model="probes" ng-show="probes" readonly></textarea> \
                        </div>\
                    </div> \
                    <div class="modal-footer" ng-show="success"> \
                        <div class="alert alert-success text-center">Action completed successfully.</div> \
                    </div> \
                    <div class="modal-footer" ng-show="error"> \
                        <div class="alert alert-danger text-center">Please start PineAP and try again.</div> \
                        <button ng-hide="pineAPStarting" type="button" class="btn btn-default center-block" ng-click="startPineAP()">Start PineAP</button> \
                        <img class="center-block" ng-show="pineAPStarting" src="img/throbber.gif"> \
                    </div> \
                </div> \
            </div>',
            scope: {
                hook: '=hook',
                content: '=content',
                deauth: '=deauth',
                show_probes: '=probes',
            },
            controller: ['$scope', '$api', '$timeout', function($scope, $api, $timeout){
                $scope.deauthMultiplier = 1;
                $scope.error = false;
                $scope.success = false;
                $scope.pineAPStarting = false;
                $scope.probes = "";

                $scope.handleResponse = function(response){
                    if (response.error === undefined) {
                        $scope.success = true;
                        $timeout(function() {
                            $scope.success = false;
                        }, 2000);
                    } else {
                        $scope.error = true;
                    }
                };

                $scope.destroyModal = function(){
                    $('#pineap-hook').modal('hide');
                    $('#pineap-hook').detach();
                };
                $scope.startPineAP = function(){
                    $scope.pineAPStarting = true;
                    $api.request({
                        module: 'PineAP',
                        action: 'enable'
                    }, function(response){
                        $scope.error = false;
                        $scope.pineAPStarting = false;
                    });
                };
                $scope.addSSIDToPool = function(){
                    $api.request({
                        module: 'PineAP',
                        action: 'addSSID',
                        ssid: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.removeSSIDFromPool = function(){
                    $api.request({
                        module: 'PineAP',
                        action: 'removeSSID',
                        ssid: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.addSSIDToFilter = function(){
                    $api.request({
                        module: 'Filters',
                        action: 'addSSID',
                        ssid: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.removeSSIDFromFilter = function(){
                    $api.request({
                        module: 'Filters',
                        action: 'removeSSID',
                        ssid: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.deauthAP = function(){
                    console.log($scope.deauth);
                    $api.request({
                        module: 'PineAP',
                        action: 'deauth',
                        sta: $scope.deauth.bssid,
                        clients: $scope.deauth.clients,
                        channel: $scope.deauth.channel,
                        multiplier: $scope.deauthMultiplier
                    }, $scope.handleResponse);
                };
                $scope.addMACToFilter = function(){
                    $api.request({
                        module: 'Filters',
                        action: 'addClient',
                        mac: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.removeMacFromFilter = function(){
                    $api.request({
                        module: 'Filters',
                        action: 'removeClient',
                        mac: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.addClientsToFilter = function() {
                    $api.request({
                        module: 'Filters',
                        action: 'addClients',
                        clients: $scope.deauth.clients
                    }, $scope.handleResponse);
                };
                $scope.addMacToTracking = function(){
                    $api.request({
                        module: 'Tracking',
                        action: 'addMac',
                        mac: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.removeMacFromTracking = function(){
                    $api.request({
                        module: 'Tracking',
                        action: 'removeMac',
                        mac: $scope.content
                    }, $scope.handleResponse);
                };
                $scope.deauthClient = function(){
                    $api.request({
                        module: 'PineAP',
                        action: 'deauth',
                        sta: $scope.deauth.bssid,
                        clients: [$scope.content,],
                        channel: $scope.deauth.channel,
                        multiplier: $scope.deauthMultiplier
                    }, $scope.handleResponse);
                };
                $scope.loadProbes = function(){
                    $api.request({
                        module: 'PineAP',
                        action: 'loadProbes',
                        mac: $scope.content
                    }, function(response) {
                        $scope.probes = response.probes;
                        if ($scope.probes == "") {
                            $scope.probeError = true;
                        } else {
                            $scope.probeError = false;
                        }
                    });
                };
                $scope.addProbes = function(){
                    $api.request({
                        module: 'PineAP',
                        action: 'addSSIDs',
                        ssids: $scope.probes.split("\n")
                    }, function(response) {
                        if (response.success) {
                            $scope.probesAdded = true;
                        } else {
                            $scope.probesAdded = false;
                        }
                    });
                };
            }]
        };
    })
    .directive('hookButton', function(){
        return {
            restrict: 'E',
            template: '<button ng-click="showModal($event)" class="btn btn-xs btn-default" type="button"><span class="caret"></span></button>',
            scope: {
                hook: '@hook',
                content: '=content',
                deauth: '=deauth',
                show_probes: '=probes',
            },
            controller: ['$scope', '$compile', function($scope, $compile){
                $scope.makeModalWithContent = function(){

                    var html = '<hook-modal hook="hook" content="content"';
                    if ($scope.deauth !== undefined) {
                        html += ' deauth="deauth"';
                    }
                    if ($scope.show_probes !== undefined) {
                        html += ' probes="true"';
                    }
                    html += '></hook-modal>';
                    var el = $compile(html)($scope);
                    $('body').append(el);
                    $('#pineap-hook').modal({
                        show: true,
                        keyboard: false,
                        backdrop: 'static'
                    });
                };
                $scope.showModal = function($event){
                    $('#pineap-hook').remove();
                    $scope.makeModalWithContent();
                };
            }]
            };
        })
})();