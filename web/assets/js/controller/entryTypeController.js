var e = null;
myApp.controller('entryTypeController', ['$scope', '$attrs', '$http', function ($scope, $attrs, $http) {
    e = $scope;
    $scope.groups = anu.fieldGroups;

    $scope.attributes = anu.entryType;

    $scope.fieldLayout = anu.fieldLayout;
    console.log($scope.fieldLayout);

    $scope.save = function () {
        var form = new FormData();
        angular.forEach($scope.attributes, function (value, name) {
            console.log(name, value);
            form.append(name, value);
        });
        form.append('action', 'sections/save-entry-type');
        form.append('fieldLayout', JSON.stringify(angular.copy($scope.fieldLayout)));

        $http({
            method: 'POST',
            url: '',
            data: form,
            headers: {'Content-Type': undefined},
            transformRequest: angular.identity
        }).then(function successCallback(response) {
            console.log(response);
        }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            console.log(response);
        });

    };
}]);