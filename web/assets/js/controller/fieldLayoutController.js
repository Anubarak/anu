myApp.directive('fieldLayout', function ($http) {
    return {
        templateUrl: '/fieldLayoutTemplate.html',
        scope: {
            groups: '=groups',
            fieldLayout: '=fieldLayout'
        },
        link: function (scope, element, attrs) {
            console.log(scope);
            scope.sortableOptions = {
                placeholder: "app",
                connectWith: ".droppable",
                helper: 'clone',
                appendTo: 'body'
            };


            scope.addGroup = function () {
                var name = prompt('Name of the group');
                if (name) {
                    scope.fieldLayout.tabs.push({
                        name: name,
                        fields: [],
                        id: null
                    });
                }
            };

            scope.notUsedFieldFilter = function (field) {
                return scope.usedFieldIds.indexOf(field.id) === -1;
            };

            scope.removeField = function (field, group) {
                group.fields = group.fields.filter(function (el) {
                    return el.id !== field.id;
                });

                scope.groups.find(function (el) {
                    if (parseInt(el.id) === parseInt(field.groupId)) {
                        el.fields.push(field);
                    }
                });
            };

            scope.usedFieldIds = [];
            scope.init = function () {
                angular.forEach(scope.fieldLayout.tabs, function (tab) {
                    angular.forEach(tab.fields, function (field) {
                        scope.usedFieldIds.push(field.id);
                    });
                });
            };
            scope.init();
            console.log(scope.usedFieldIds);
        }
    };
});