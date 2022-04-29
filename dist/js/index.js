 $(document).ready(function() {
      

        var formElement = $("#login-form");
        
        formElement.validate({
            rules: {
                Username: {
                    required: true,
                    minlength: 3,                
                    maxlength: 100
                },
                Password: {
                    required: true,
                    minlength: 6,
                    maxlength: 20
                }              
            },
            messages: {
                Username: {
                    required: "Please enter your username",
                    minlength: "Username must have at least 3 characters"
                },
                Password: {
                    required: "Please enter your password",
                    minlength: "Password must have at least 5 characters"
                }
            },            
            errorElement: "div",
            errorPlacement: function (error, element) {
                $("#messagebox").html(error);      
                $("#messagebox").removeClass("alert-light");
                $("#messagebox").addClass("alert-danger"); 
                
                
            },
            submitHandler: function(){                             
                var data = {};                                      
                data["user_name"] = $("#username").val();
                data["password"] = $("#password").val();
                data["task"] = "12";
                var jsondata = JSON.stringify(data);   
                console.log(jsondata)
                $.post("modules/ajax/users.php", jsondata, function (result) {
                  console.log(result);
                  var json=JSON.parse(result)
                    if (json.message === "Success")
                      window.location=json.home;
                    else
                        showMessage(json.message);
                  });               
            }
        });     
    });
    
    
  function showMessage(msg)
    {
    $("#messagebox").html("<i class='fas fa-exclamation-triangle'></i>"+msg);
    $("#messagebox").removeClass("alert-light");
    $("#messagebox").addClass("alert-danger");    
    }
    

//End Login
