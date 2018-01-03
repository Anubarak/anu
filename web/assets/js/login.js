$(document).ready(function(){
    $("#loginButton").click(function(e){
        e.preventDefault();
        $.ajax({
            url: '',
            data: $('#login').serialize(),
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
                console.log("Ajax Error invite user", textStatus);
            }
        });
    });
});