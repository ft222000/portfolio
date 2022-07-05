<!DOCTYPE html>
<html>
<?php include('HeadFile.php'); ?>

<body>
    <?php include('Header.php'); ?>
    <?php include('Menu.php'); ?>
    <div class="container">
        <div class="row">
            <div class="emailMe col">

                <form name="EmailForm" method="post">

                    <div class="form-group">
                        <h4 class="text-center">If you wish to contact me direacly. </h4>
                        <small id="emailHelp" class="form-text text-muted font-weight-bold">Please note: all fields are
                            required.</small>
                        <label for="exampleInputEmail">Company Email/Your Email:</label>
                        <input type="email" class="form-control" id="exampleInputEmail" aria-describedby="emailHelp"
                            placeholder="Enter email">

                    </div>
                    <div class="form-group">
                        <label for="exampleInputFirstName">First name:</label>
                        <input type="text" class="form-control" id="exampleInputFirstName" placeholder="First Name">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputLastName">Last name:</label>
                        <input type="text" class="form-control" id="exampleInputLastName" placeholder="Last Name">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputCompanyName">Company name:</label>
                        <input type="text" class="form-control" id="exampleInputCompanyName" placeholder="Company Name">
                    </div>
                    <div class="form-group">
                        <label for="exampleFormControlTextarea">Message:</label>
                        <textarea class="form-control" id="exampleFormControlTextarea" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="return validateForm()">Submit</button>
                </form>
            </div>
            <div class="ExplanationEmailMe col">
                <h2>
                    This will email the message direactly to me by using PHPMailer.
                </h2>
                <p>
                </p>
            </div>
        </div>
    </div>
</body>
<script>
function validateForm() {
    var exampleInputEmail = document.getElementById("exampleInputEmail").value;
    var exampleInputFirstName = document.getElementById("exampleInputFirstName").value;
    var exampleInputLastName = document.getElementById("exampleInputLastName").value;
    var exampleInputCompanyName = document.getElementById("exampleInputCompanyName").value;
    var exampleFormControlTextarea = document.getElementById("exampleFormControlTextarea").value;

    if (exampleInputEmail == "" || exampleInputFirstName == "" || exampleInputLastName == "" || exampleInputCompanyName == "" || exampleFormControlTextarea == "") {
        alert("Please filled out all fields");

    }
    if (exampleInputEmail == "") {
        document.getElementById("exampleInputEmail").style.borderColor = "red";

    } else {
        document.getElementById("exampleInputEmail").style.borderColor = "";

    }
    if (exampleInputFirstName == "") {
        document.getElementById("exampleInputFirstName").style.borderColor = "red";
    

    } else {
        document.getElementById("exampleInputFirstName").style.borderColor = "";
      
    }
    if (exampleInputLastName == "") {
        document.getElementById("exampleInputLastName").style.borderColor = "red";
       

    } else {
        document.getElementById("exampleInputLastName").style.borderColor = "";
       
    }
    if (exampleInputCompanyName == "") {
        document.getElementById("exampleInputCompanyName").style.borderColor = "red";
   

    } else {
        document.getElementById("exampleInputCompanyName").style.borderColor = "";
       
    }
    if (exampleFormControlTextarea == "") {
        document.getElementById("exampleFormControlTextarea").style.borderColor = "red";
        return false;
    } else {
        document.getElementById("exampleFormControlTextarea").style.borderColor = "";
        return false;
    }
}
</script>
</html>
