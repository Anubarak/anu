// angular app
const myApp = angular.module('myApp',['ngAnimate']).config(function($interpolateProvider, $httpProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');

    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function(obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for(name in obj) {
            value = obj[name];

            if(value instanceof Array) {
                for(i=0; i<value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value instanceof Object) {
                for(subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
});

$(document).ready(function () {
    $('select').material_select();

    $('#save').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: '',
            data: $('#main-form').serialize(),
            dataType: 'json',
            type: 'POST',
            success: function(response) {
                console.log(response);
                if(response.success === true){
                    window.location = response.redirect;
                }else{
                    displayErrors(response.errors);
                }
            },
            error: function (XMLHttpRequest, textStatus) {
                console.log('Ajax Error invite user', textStatus);
            }
        });
    })
});

var displayErrors = function(errors, form){
    if(typeof form === 'undefined'){
        form = $('#main-form');
    }
    form.find('.invalid').removeClass('invalid');
    if(form){
        $.each(errors, function(handle, messages){
            var item = $('#' + form.attr('id') + ' input[name="' + handle + '"]');
            var message = messages.join(', ');
            var label = $("label[for='"+item.attr('id')+"']");
            label.first().attr('data-error', message);
            label.first().attr('data-test', message);
            item.addClass('invalid');
            console.log(label.first().data('error'));
        })
    }
};
