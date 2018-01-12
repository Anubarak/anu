(function () {
    myApp.controller('elementIndexController', ['$scope', '$attrs', '$http', function ($scope, $attrs, $http) {
        $scope.sections = anu.sections;
        $scope.entries = anu.entries;
        console.log($scope.entries);
    }]);
}(angular));