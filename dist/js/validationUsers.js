$(function()
{ 
$("#validationStatus").hide();
$("#UserName2").val("");
$("#RegistrationPassword").val("");
//$("#updateValidator").hide();
$("#createValidators").hide();
var id;
//menu
$("#manageValidatorButton").click(function(){
    $("#updateValidator").show();
    $("#createValidators").hide();
    
});
//menu
$("#addValidatorButton").click(function(){
   $("#createValidators").show();    
});

// User Login 
    $(".signIn").click(function(event)
    {  
     event.preventDefault();
    var username=$("#UserName1").val();
    var password=$("#Password").val();

    if(username==""){
     $("#messagebox").html("User Name Required");
     return false; 
    }

    if(password==""){
     $("#messagebox").html("Password Required");
     return false; 
    }

    var data={username:username,password:password};   
    var temp=JSON.stringify(data); 
    $.ajax({
            type: "post",
            url: "../../modules/ajax/validationUsers.php?type=1",
            data: temp,
            async:false,
            contentType:"application/json",
            dataType:"json",	
            success:function(data)
            {   
                if(data["status"]==200){  
                     window.location = "home.php"; 
                  }
                else if(data["status"]==500){
                     alert("Wrong Username / Password Combination");
                  }
            },				
            error:function(jqXHR,textStatus,errorThrown){
                     alert("Error For Login");                                    
                }//end of error
            });	//end of aajax

       });

//End Login

//Add User Registration

  $("#addValidator").click(function(event)
        {            
        event.preventDefault();
        if($(this).val()=="Submit")
        {
            if(!UserValidation())
            {
                return false;
            }
            else
             {
            var temp=getInputUserData();        
            $.ajax({
                    type: "post",
                     url: "../../modules/ajax/validationUsers.php?type=2",
                    data: temp,
                    cache:false,
                    dataType:"json",
                    contentType:"application/json",	                             				
                    success:function(data){                   

                    if(data["status"]==200)
                        {
                            alert("Created Validator user"); 
                            location.reload(true); 

                        }
                    else
                        alert(data["message"]);
                 },	

                    error:function(jqXHR,data,errorThrown){                                               
                                alert("Failed User Registration");                                   
                                alert(data);alert(errorThrown);
                                    } //end of error
                                  });	//end of aajax
            }   //end of else 
        }//end of submit
        
         if($(this).val()=="Update")
        {     
            if($("#RegistrationPassword").val()!= $("#ConfirmPassword").val())
                {
                   alert("Password And Confirm password Not Match");   
                   return false;
                }

            if(!validateEmail())
                {
                alert("You Must Enter The Valid Email Address");
                return false;  
                }
            if($("#RegistrationPassword").val()!="")
                { 
                if(!validatePassword())
                    return false;  
                } 
        
            var data={};
            data["validation_id"]=id;
            data["name"]=$("#FirstName").val();
            data["userName"]=$("#UserName2").val();
            data["password"]=$("#RegistrationPassword").val();
            data["companyName"]=$("#CompanyName").val();
            data["phone"]=$("#Phone").val();
            data["email"]=$("#Email").val();
            data["statusValue"]=$("#statusValue").val();           
            var temp=JSON.stringify(data);  
            //alert(temp);
            //alert(temp);
            //update
            $.ajax({
            type: "post",
            url: "../../modules/ajax/validationUsers.php?type=3",
            data: temp,
            cache:false,
            contentType:"application/json",	
            dataType:"json",                             				
            success:function(data){                                 
                                alert(data["message"]);                             
                                window.location.reload();
                                },				
            error:function(jqXHR,textStatus,errorThrown){                                                                                
                                        alert(errorThrown+" "+textStatus);                                       
                                                        }
            });	//end of ajax    
        }
        
     });
//End of $("#AddUserSubmitbtn").click
  
//Edit Button
    $(".editValidator").on("click",function(){  
        $("#createValidators").show();
        $("#updateValidator").hide();
       var btnid=$(this).attr('id');
       id=btnid.replace('btn','');
              
        $("#validationStatus").show(); 
        $("#FirstName").val($("#name"+id).text());
        $("#UserName2").val($("#username"+id).text());
        $("#Email").val($("#email"+id).text());               
        $("#CompanyName").val($("#company"+id).text());        
        $("#Phone").val($("#phone"+id).text());
        $("#statusValue").val($("#status"+id).text());

        $("#addValidator").val('Update');
               
       
    });
    
    
//Function Cancel Button

$(".btn.btn-default").click(function(event){
    event.preventDefault();
    location.reload(true);
    });//end of $("btnCancel"),click()


function getInputUserData()
    {
        var firstName=$("#FirstName").val();
        var username=$("#UserName2").val();
        var email=$("#Email").val();
        var password=$("#RegistrationPassword").val();       
        var companyName=$("#CompanyName").val();        
        var phone=$("#Phone").val();       
        
        var data={firstName:firstName,userName:username,email:email,password:password, companyName:companyName,phone:phone};   
        var temp=JSON.stringify(data);     
        return temp;
    }//end of .getInputData
       
    
    
    // Validation Section
    
    function UserValidation()
    {
 

        var firstName=$("#FirstName").val();
        var userName=$("#UserName2").val();
        var email=$("#Email").val();
        var password=$("#RegistrationPassword").val();
        var confirmPassword=$("#ConfirmPassword").val();        

        if(firstName==""){
         alert("Please Fill First Name");
         return false; 
        }
        
        if(userName==""){
         alert("Please Fill User Name");
         return false; 
        }

       // if(!validatePassword()){           
         // return false;  
        //} 
        
        if(password!= confirmPassword)
        {
           alert("Password And Confirm password Not Match");   
           return false;
        }

        if(!validateEmail()){
           alert("You Must Enter The Valid Email Address");
          return false;  
        } 
        else
            {
            return true
            }
    
    } // End Validattion
    

function validateEmail(){
    
        var email=$("#Email").val();
        var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

        if (reg.test(email) == false) 
        {
            alert('Invalid Email Address');
            return false;
        }
        return true;
}

function validatePassword(){
    var password=$("#RegistrationPassword").val();
    var reg = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#])[A-Za-z\d$@$!%*#]{8,}$/;

    if (reg.test(password) == false) 
    {
        alert('Password must contain one special charector and one number and minimum 8 letters');
        return false;
    }
    return true;
}



});  
