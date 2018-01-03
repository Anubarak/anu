myApp.controller('fieldController', ['$scope', '$attrs', '$http', function ($scope, $attrs, $http) {

    $scope.fields = anu.fields;


    $scope.groups = anu.fieldGroups;

    $scope.init = function(){
        $scope.groups.unshift({
            id: 0,
            name: 'all'
        });
    };

    $scope.currentSelectedGroup = 0;

    $scope.groupFilter = function (item) {
        console.log($scope.currentSelectedGroup);
        if($scope.currentSelectedGroup === 0){
            return true;
        }
        return item.groupId === $scope.currentSelectedGroup;
    };

    $scope.newGroup = function(){
        var name = prompt('Name of the field');
        if(name){
            var form = new FormData();
            form.append('name', name);
            form.append('action', 'fields/save-group');
            return $http({
                url: '',
                method: 'POST',
                data: form,
                headers: { 'Content-Type': undefined},
                transformRequest: angular.identity
            }).then(function(response) {
                console.log(response);
                if(response.data.success === true){
                    $scope.groups.push({
                        name: name,
                        id: response.data.groupId
                    });
                }else{
                }
            }, function errorCallback(response) {
                // TODO show error message
                console.log(response);
            }).catch(function onError(response) {
                // TODO show error message
                console.log(response);
            });
        }
    };

    $scope.changeGroup = function(id){
        $scope.currentSelectedGroup = parseInt(id);
    };

    $scope.init();
}]);