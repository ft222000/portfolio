<!--
Template for the user page
-->
<div>
    <!--Section Heading -->
    <h3 id="createUserHeading" class="text-center">Create User</h3>

    <!--Form for user creation -->
    <form id="createUserForm" method="post" action="">
        <!--Hidden id field -->
        <div id="createUserIDDiv">
            <input readonly hidden id="createUserID" type="number" name="UserID">
        </div>
        <!--Email field -->
        <div class="form-group">
            <label for="createUserEmail">Email Address</label>
            <input required id="createUserEmail" class="form-control" type="email" name="EmailAddress">
        </div>

        <!--Password field -->
        <div class="form-group password-group">
            <label for="createUserPassword">Password</label>
            <input required id="createUserPassword" class="form-control" type="password" name="Password">
        </div>

        <!--Permission Level Selection -->
        <div class="form-group">
            <label for="createUserPermissionLevel">Permission Level</label>
            <select class="form-control" id="createUserPermissionLevel" name="PermissionLevel">
                <option selected value="1">General User</option>
                <option value="2">Event Organiser</option>
                <option value="3">Sustainability Team</option>
            </select>
        </div>

        <!--Campus Selection -->
        <div class="form-group">
            <label for="createUserCampus">Campus</label>
            <select class="form-control" id="createUserCampus" name="PrimaryCampus">
            </select>
        </div>
        
        <!--Activation status -->
        <div id="createUserActivationDiv" class="form-group">
            <label for="createUserActivation">Account Status</label>
            <select class="form-control" id="createUserActivation" name="Activated">
                <option selected value="1">Enabled</option>
                <option value="0">Disabled</option>
            </select>
        </div>

        <!--Submission button -->
        <div class="form-group container text-center" id="userFormButtons">
            <input id="formSubmissionButton" type="submit" class="btn btn-primary" name="create" value="Submit">
        </div>
    </form>
</div>