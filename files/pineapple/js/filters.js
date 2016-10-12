(function(){
    angular.module('pineapple')
    .filter('timesince', function() {
        return function(input) {
            var then = new Date(input*1000);
            var now = new Date();
            var minutesSince = Math.round(Math.abs(now - then)/60/1000);
            if (minutesSince < 30) {
                return  minutesSince === 0 ? 'just now' : minutesSince.toString() + ((minutesSince === 1 ? " minute ago" : " minutes ago"));
            } else if (minutesSince/60/24 < 1) {
                var hours = then.getHours();
                var minutes = then.getMinutes();
                return 'at ' + (hours % 12 === 0 ? '12' : (hours%12).toString()) + ':' + minutes + (hours > 12 ? ' PM' : ' AM');
            } else {
                var hours = then.getHours();
                var minutes = then.getMinutes();
                var month = then.getMonth();
                var day = then.getDay();
                var year = then.getYear();
                return 'on ' + month + '/' + day + '/' + (year+1900) + ' at ' + (hours % 12 === 0 ? '12' : (hours%12).toString()) + ':' + minutes + (hours > 12 ? ' PM' : ' AM');
            }
        }
    })

    .filter('rawHTML', ['$sce', function($sce) {
        return function(input) {
            return $sce.trustAsHtml(input);
        }
    }]);
})();