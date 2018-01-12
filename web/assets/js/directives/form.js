myApp.directive('field', function ($compile, $templateRequest) {
    return {
        compile: function (element, attrs) {
            return function (scope, element, attrs) {
                var url = '/' + scope.field.type + ".html";
                console.log(url);
                //start creating an html string for our "view".

                $templateRequest(url).then(function (html) {
                    var el = angular.element(html),
                        //compile the view into a function.
                        compiled = $compile(el);
                    element.append(el);
                    compiled(scope);
                });
            }
        },
        scope: {
            field: '=field',
            element: '=element'
        },
        link: function (scope, element, attrs) {

        }
    }
});
