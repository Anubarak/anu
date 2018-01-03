myApp.controller('sectionController', ['$scope', '$attrs', '$http', function ($scope, $attrs, $http) {

    $scope.sections = anu.sections;
 console.log($scope.sections);
}]);