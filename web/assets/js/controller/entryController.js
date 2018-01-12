(function () {
    myApp.directive('fieldLayout', function ($http) {
        return {
            templateUrl: '/fieldLayout.html',
            scope: {
                fieldLayout: '=layout',
                element: '=element',
            },
            link: function (scope, element, attrs) {
                console.log(scope.element);
                scope.titleField = {
                    handle: 'title',
                    name: scope.element.type.titleLabel,
                    required: true,
                    type: 'anu\\models\\TextField'
                }
            }
        }
    });


    myApp.controller('entryController', ['$scope', '$attrs', '$http', function ($scope, $attrs, $http) {
        $scope.fieldLayout = anu.fieldLayout;
        $scope.attributes = anu.entry;
        $scope.element = anu.entry;
        $scope.rootScrope = $scope;

        $scope.saveEntry = function () {
            var form = new FormData();
            form.append('action', 'entry/saveEntry');
            angular.forEach($scope.element, function (value, handle) {

                form.append(handle, value);
            });
            $http({
                method: 'POST',
                url: '',
                data: form,
                headers: {'Content-Type': undefined},
                transformRequest: angular.identity
            }).then(function successCallback(response) {
                console.log(response);
                if (response.data.success === true) {
                    window.location = response.data.redirect;
                } else {
                    displayErrors(response.data.errors);
                }
            }, function errorCallback(response) {
                // called asynchronously if an error occurs
                // or server returns response with an error status.
                console.log(response);
            });
        }
    }]);
}(angular));