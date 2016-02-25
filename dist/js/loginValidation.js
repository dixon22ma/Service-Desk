var loginFormValidation = (function() {
	$('#login').submit(function (event) {
		var $username = $('#username').val();
		var $password = $('#password').val();
		var $error = '<div class="alert alert-danger alert-dismissable" id="error">';
		var $formError = "";
		function calculatePossibleInjection(value) {
			var isPossibleOfInjection = false;
			var checkInject = /^\W/g;
			var injection = checkInject.exec(value);
			if(!injection) {
				isPossibleOfInjection = true;
			} 
			return isPossibleOfInjection;
		}
		// Handle username validation
		if($username === "") {
			 $formError += "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>" +
                           "<span class='sr-only'>Error:</span>" +
                           " Username cannot be empty<br/>";
		} else if(!calculatePossibleInjection($username)) {
			 $formError += "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>" +
                           "<span class='sr-only'>Error:</span>" +
                           " Invalid characters in username<br/>";
		}
		// Handle password validation
		if($password === "") {
			 $formError += "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>" +
                           "<span class='sr-only'>Error:</span>" +
                           " Password cannot be empty<br/>";
		} else if(!calculatePossibleInjection($password)) {
			 $formError += "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>" +
                           "<span class='sr-only'>Error:</span>" +
                           " Invalid characters in password<br/>";
		}
		if($formError !== "") {
			event.preventDefault();
            $error += ("<button type='button' class='close' data-dismiss='alert'>x</button>" + $formError);
            $error += '</div>';
            $("#error-container").html($error);
        }
	});
});