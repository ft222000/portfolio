<?php
/**
 * Allows users to view, manage, and create users within the application.
 */

require_once('util/session.php');

if (!isSustainabilityTeamUser($session_permission)) {
    header("Location: ./login.php");
}
?>
<html>
    <head>
        <!--
            JN:
            Imports for Bootstrap 4 and jquery
        -->
        <title>Food Finder | Users</title>

        <link rel="stylesheet" href="styles/List.css" />
        <link rel="stylesheet" href="styles/Font.css" />
    </head>
    <body>

        <!--
            JN: Navigation Bar
        -->
        <?php
            require("./util/navigationBar.php");
        ?>
        <div class="container-fluid">

            <!-- JN: Body Section -->
            <div class="row">
                <!-- List Side -->
                <div class="col-sm border" id="leftPanel">
                    <!-- JN: Section heading -->
                    <div class="p-1">
                        <h3 class="text-center">User Management</h3>
                    </div>

                    <!-- JN: Create user button -->
                    <div class="row px-3 justify-content-center">
                        <input class="form-control w-70" id="search-bar" type="text" onkeyup="FilterUserList()" placeholder="Search..." />
                        <button class="btn btn-success w-15" data-toggle="modal" data-target="#filterModal">Filter</button>
                        <button class="btn btn-primary w-15" onclick="LoadCreateUserSection()">Create</button>
                    </div>

                    <!-- JN: User list -->
                    <table class="table table-hover">
                        <tbody id="userTable">
                            <!-- JN: rows are populated via a script -->
                        </tbody>
                    </table>
                </div>

                <!--
                    Form Side
                    JN:
                    Starts empty and then will be filled with relevant content using AJAX
                -->
                <div class="col-sm border" id="RightPanel">
                    <!-- JN: This section will be filled using scripts-->
                    <div class="p-1">
                        <h3 class="text-center">Users</h3>
                    </div>
                    <h5 class="text-center">Select a user</h5>
                </div>

                <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filterModalTitle">Filtering Options</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="filterOptionSortType">Sort By</label>
                                    <select id="filterOptionSortType" onchange="FilterUserList()" class="form-control">
                                        <option value="1">Most Recent First</option>
                                        <option value="2">Least Recent First</option>
                                        <option value="3">A-Z</option>
                                        <option value="4">Z-A</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="filterOptionPermission">Permission Level</label>
                                    <select id="filterOptionPermission" onchange="FilterUserList()" class="form-control">
                                        <option value="0">All Users</option>
                                        <option value="1">General User</option>
                                        <option value="2">Event Organiser</option>
                                        <option value="3">Sustainability Team</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="filterOptionStartDate" class="text-center">Earliest Date</label>
                                    <input type="date" onchange="FilterUserList()" class="form-control" id="filterOptionStartDate">
                                </div>

                                <div class="form-group">
                                    <label for="filterOptionendDate" class="text-center">Latest Date</label>
                                    <input type="date" onchange="FilterUserList()" class="form-control" id="filterOptionEndDate"/>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="ResetFilteringOptions()">Clear</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>
        var updatingUserInProgress = false;
        var campusList = null;
        const MAX_USERS_PER_PAGE = 10;
        var userList = null;
        var filteredUserList = null;

        /**
         * On Document Load
         *
         * Calls RefreshUserList, which prepares data ready for the user.
         * */
        $(function() {
            RefreshUserList();
        });

        /**
         * Updates the user information.
         *
         * Makes a GET request to api/users.php, and handles the result using UpdateUserTable
         * Makes a GET request to api/sendCampus.php, and handles the result using StoreCampusList
         */
        function RefreshUserList() {
            $.get("api/users.php", UpdateUserTable);
            $.get("api/sendCampus.php", StoreCampusList);
            AddClickHandlerToUserTable();
        }

        /**
         * Stores the campus list locally for use by forms
         *
         * @param data The data of campuses that will be stored.
         * @param status Information about the success of the AJAX request, unused.
         * */
        function StoreCampusList(data, status) {
            var JSONResponse;
            try {
                JSONResponse = JSON.parse(data);
                campusList = JSONResponse;
            } catch (error) {
                campusList = [{"Campus_Name":"Newnham", "Campus_ID":"1"}];
            }
        }

        /**
         * Adds an onClick handler to all rows in the user table.
         *
         * Allows rows to visually look selected.
         * */
        function AddClickHandlerToUserTable() {
            // JN: Add an onclick function to each row in the table
            $("#userTable tr").click(function () {
                // JN: Remove selected-row class from all other selected rows
                $('.selected-row').removeClass('selected-row');

                // JN: Add selected-row to this specific row
                $(this).addClass('selected-row');

                // JN: Pass the UserID onto another function to load details for update
                var id = $('.id',this).html();
                LoadEditUserForm(id);
            });
        }

        /**
         * Loads the create user section in the right panel of the page.
         */
        function LoadCreateUserSection() {
            // JN: Deselect any selected users, as the create user button has been pressed
            $('.selected-row').removeClass('selected-row');

            // JN: Load the create user section in the right panel
            $("#RightPanel").html("");
            $('#RightPanel').load("sections/createUser.php", function() {
                $('#createUserIDDiv').html("");
                $('#createUserActivationDiv').html("");

                PopulateCampusOptions();

                CreateUserRequestHandler();
            });
        }

        /**
         * Adds an event handler to the submission of the create user form.
         *
         * Makes a POST request to api/users.php
         * */
        function CreateUserRequestHandler() {
            // JN:
            $('#createUserForm').submit(function(event) {
                // JN: Prevent default
                event.preventDefault();

                // JN: Check to see if something is already trying to do this
                if (updatingUserInProgress) {
                    return;
                }

                // JN: Set the flag to prevent this from being run by two different things
                updatingUserInProgress = true;

                // JN: Send post request with the form data
                $.post("api/users.php", $('#createUserForm').serialize() , function(data, status) {
                    // JN: Convert response to JSON so that it can be read by the server
                    try {
                        // JN: Parse the result
                        response = JSON.parse(data);

                        // JN: Check the response of the request
                        if (response.wasSuccessful == 1) {
                            // JN: Set the headings back to the default for the page
                            LoadDefaultRightSideHeadings();

                            // JN: Refresh the user list
                            RefreshUserList();

                            // JN: Give feedback to the user
                            alert(response.message);
                        } else {
                            // JN: Display the error message that has been passed through
                            console.log(data);

                            // JN: Give feedback to the user
                            alert(response.message);
                        }
                    } catch (err){
                        console.log(data); // JN: For debugging
                    }
                });
                // JN: Release the semaphore
                updatingUserInProgress = false;
            });
        }

        /**
         * Replaces everything in the right panel with the default headings for the page
         * */
        function LoadDefaultRightSideHeadings() {
            $("#RightPanel").html(
                "<h3 class=\"text-center\">Users</h3>\n" +
                "<h5 class=\"text-center\">Select a user</h5>"
            );
        }

        /**
         * Loads the user form for a given user, with all thier information prefilled.
         *
         * An update and delete button is added to the form in place of the create button.
         *
         * Makes a GET request to api/users.php to get user information.
         * Makes a GET request to sections/createUser.php to get the blank form template.
         *
         * Adds an event handler to the update button, which makes a POST request to api/UpdateUser.php
         *
         * @PARAM id The id of the user to be loaded in the edit user form
         */
        function LoadEditUserForm(id) {
            var user;

            // JN: Clear the html from the Right Panel
            $('#RightPanel').html("");

            // JN: This prevent the user from seeing the create user form before manipulation has occured
            $('#RightPanel').addClass("invisible");

            // JN: Request a copy of the create user form via http get
            $.get("sections/createUser.php", function(data, status) {
                // JN: set the right panel to the HTML returned
                $('#RightPanel').html(data);

                // JN: Hide the password field, as this cannot be updated
                $('.password-group').html("");


                // JN: Request specific details about the user being edited
                $.get("api/users.php?id="+id, function(data, status) {
                    // JN: parse response text into JSON object
                    obj = JSON.parse(data);
                    if (obj.wasSuccessful != 1) {
                        console.log(obj.message);
                        return;
                    }
                    // JN: Check to ensure that there is only one user selected
                    if (obj.data.length == 1) {
                        // JN: JSON object is an array, so get the first element
                        user = obj.data[0];

                        // JN: Set the values of the fields to the information collected from the database
                        $('#createUserID').val(id);
                        $('#createUserEmail').val(user.EmailAddress);

                        PopulateCampusOptions();
                        $('#createUserCampus option').removeAttr('selected').filter('[value=' + user.PrimaryCampus + ']').attr('selected', true);
                        $('#createUserActivation option').removeAttr('selected').filter('[value=' + user.Activated + ']').attr('selected', true);
                        $('#createUserPermissionLevel').val(user.HasPermissionLevel);
                        $('#formSubmissionButton').val("Update").attr("name","update");
                        $('#createUserHeading').text("Update User").addClass("pt-1");

                        if (id == <?php echo $session_user_id; ?>) {
                            $('#createUserPermissionLevel').attr("disabled", "true");
                            $('#createUserPermissionLevel').attr("readonly", "true");
                            $('#createUserActivation').attr("disabled", "true");
                            $('#createUserActivation').attr("readonly", "true");

                        } else {
                            $("#userFormButtons").append(" <input type=\"button\" class=\"btn btn-danger\" value=\"Delete\" onclick=DeleteUser(" + id + ")>");
                        }
                        // JN: Set the action of the form
                        $('#createUserForm').submit(function(event) {
                            // JN: Prevent default
                            event.preventDefault();
                            if (updatingUserInProgress) {
                                return;
                            }

                            $('#createUserPermissionLevel').removeAttr("disabled");
                            $('#createUserActivation').removeAttr("disabled");

                            updatingUserInProgress = true;

                            $.post("api/UpdateUser.php",$('#createUserForm').serialize() , function(data, status) {
                                // JN: Convert response to JSON so that it can be read by the server
                                try {
                                    console.log(data);
                                    // JN: Parse the result
                                    response = JSON.parse(data);

                                    // JN: Check the response of the request
                                    if (response.wasSuccessful) {
                                        // JN: Set the headings back to the default for the page
                                        LoadDefaultRightSideHeadings();
                                        alert("User was successfully updated!");
                                    } else { // JN: Display the error message that has been passed through
                                        $('#createUserActivation').attr("disabled", "true");
                                        $('#createUserPermissionLevel').attr("disabled", "true");
                                        alert(response.message);
                                    }
                                } catch (err) {
                                    console.log(data);
                                }

                                // JN: Semaphore release to prevent spamming of this function
                                updatingUserInProgress = false;
                            });
                        });
                        $('#RightPanel').removeClass("invisible");
                    } else {
                        console.log("Had an issue with returning this users information");
                    }
                })


            });

        }

        /**
         * Fills the campus selector with campuses that were retrieved from the database using StoreCampusList.
         *
         * Each entry has the value of the campus id, and displays the readable name of the campus.
         * */
        function PopulateCampusOptions() {
            var campusSelector = $("#createUserCampus");
            campusList.forEach(function(element) {
                campusSelector.append("<option value="+element.Id+">"+element.Name+"</option>");
            });
            $("#campus").removeAttr("readonly");
        }

        /**
         * Updates the user table with data retrieved from the a HTTP GET request
         *
         * @param data
         * Expected format: JSON
         * An array of users in the following format
         * [{User_ID, EmailAddress}, ...]
         */
        function UpdateUserTable(data) {
            var obj; // JN: Variable to hold JSON information

            // JN: Clear the current table
            $("#userTable").html("");

            try {
                // JN: Parse the JSON information from text
                obj = JSON.parse(data);
                if (obj.wasSuccessful) {
                    // JN: Add a row for each user in the JSON object
                    //obj.data.forEach(AddRowToUserTable);
                    userList = obj.data;
                    filteredUserList = obj.data;
                    PopulateUserTable(userList);
                    //ShowUserPage(1);
                } else  {
                    // JN: Output the message from the packet
                    console.log(obj.message);
                    alert(obj.message);
                }
            } catch (err) {
                // JN: Output the entire data for debugging
                console.log(err);
                console.log(data);
                alert("Something went wrong");
            }

            ResetFilteringOptions();
            // JN: Refresh click handlers
            //AddClickHandlerToUserTable(); Moved to PopulateUserTable
        }

        /**
         * Fills the user table with data passed into the function, then displays the first page.
         *
         * Note: You should clear the #userTable before calling this function
         *
         * @param data The list of users to be added to the usertable
         * */
        function PopulateUserTable(data) {
            data.forEach(AddRowToUserTable);
            ShowUserPage(1);
            AddClickHandlerToUserTable();
        }

        /**
         * Adds a single row of content to the user table
         *
         * @param user - A JSON object containing a single users information
         * Expected fields: User_ID, EmailAddress
         * @param index - The index of the user out of all users being added
         */
        function AddRowToUserTable(user, index) {
            var classToAdd = "userListPage"+parseInt(index/MAX_USERS_PER_PAGE);
            $("#userTable").append("<tr class='"+classToAdd+" userTableEntry'><td hidden class='id'>"+user.User_ID+"</td><td>"+user.EmailAddress+"</td></tr>");
        }

        /**
         * Hides any users that are not to be displayed, and shows users that should be displayed.
         *
         * @param pageNumber The number of the page to be shown starting with page 1
         */
        function ShowUserPage(pageNumber) {
            // Hide unwanted, show the correct page
            $('.userTableEntry').hide();
            $('.userListPage'+(pageNumber-1)).show();

            // Delete and readd the navigation bar
            $('#UserListPageNav').remove();
            AddPageNumbersToUserList(pageNumber);

            // Set the active page number
            $(".page-item:contains("+pageNumber+")").addClass('active');
        }

        /**
         * Adds the page numbers to bottom of the user list
         *
         * @param currentPage The page that is currently selected by the user
         * */
        function AddPageNumbersToUserList(currentPage) {
            var rangeOfNumberDisplayed = 3;

            currentPage = parseInt(currentPage);

            var amountOfPages = parseInt(filteredUserList.length/MAX_USERS_PER_PAGE);

            // Add the relevant buttons
            var lowestPageToDisplay = 0;
            var highestPageToDisplay = amountOfPages;

            if (currentPage < lowestPageToDisplay + rangeOfNumberDisplayed + 1) {
                highestPageToDisplay = rangeOfNumberDisplayed*2;
                if (highestPageToDisplay > amountOfPages) {
                    highestPageToDisplay = amountOfPages;
                }

            } else if (currentPage > highestPageToDisplay - rangeOfNumberDisplayed) {
                lowestPageToDisplay = highestPageToDisplay - rangeOfNumberDisplayed*2;
                if (lowestPageToDisplay < 0) {
                    lowestPageToDisplay = 0;
                }
            } else {
                lowestPageToDisplay = Math.max(currentPage-1 - rangeOfNumberDisplayed, 0);
                highestPageToDisplay = Math.min(highestPageToDisplay, currentPage-1+rangeOfNumberDisplayed);
            }

            if (amountOfPages) {
                var htmlToAdd = "<nav id='UserListPageNav' aria-label=\"Page navigation example\"><ul class=\"pagination justify-content-center\"><li id='previousButton' class='page-item'><a class=\"page-link\" href=\"#\">Previous</a></li>";
                for (var i = lowestPageToDisplay; i <= highestPageToDisplay; i++) {
                    if (!(parseInt(filteredUserList.length % MAX_USERS_PER_PAGE) == 0 && i == highestPageToDisplay)) {
                        htmlToAdd += '<li class="page-item"><a class="page-link" href="#">' + (i + 1) + '</a></li>';
                    }
                }
                htmlToAdd += "<li id='nextButton' class=\"page-item text-center\"><a class=\"page-link\" href=\"#\">Next</a></li></ul></nav>";

                // Add to the appropriate section
                $("#userTable").append(htmlToAdd);
            }

            $("#nextButton").width($("#previousButton").width());
            // Set the handler for when a page button is clicked
            $(".page-link").click(function() {
                var selectedValue = $(this).text();

                if (selectedValue === "Previous") {
                    if (parseInt(currentPage) > 1) {
                        ShowUserPage(parseInt(currentPage) - 1);
                    }
                } else if (selectedValue === "Next") {
                    if (currentPage <= amountOfPages && (!(parseInt(filteredUserList.length % MAX_USERS_PER_PAGE) == 0 && currentPage == highestPageToDisplay))) {
                        ShowUserPage(parseInt(currentPage) + 1);
                    } else {
                        ShowUserPage(currentPage);
                    }
                } else {
                    ShowUserPage(selectedValue);
                }
            });
        }

        /**
         * Deletes the given user from the database using AJAX.
         *
         * Makes a POST request to api/DeleteUser.php to delete the user.
         *
         * @param id The id of the user in the database
         */
        function DeleteUser(id) {
            var confirmation = confirm("Are you sure you want to delete this user?");
            var sending = {};
            sending.id = id;

            if (confirmation == true) {
                $.post("./api/DeleteUser.php", sending, function (data) {
                    try {
                        var dataJSON = JSON.parse(data);
                        if (dataJSON.wasSuccessful) {
                            // Remove right panel and refresh user list
                            LoadDefaultRightSideHeadings();
                            RefreshUserList();
                            alert(dataJSON.message);
                        } else {
                            alert(dataJSON.message);
                        }
                    } catch (err) {
                        console.log(data);
                    }
                });
            }
        }

        /**
         *   Handles the deletion of an user.
         *
         *   Prompts the user for confirmation before continuing with the deletion.
         *   Sends a POST request to api/DeleteEvent.php to handle the deletion from the application.
         *   @param id The id of the event to be deleted.
         **/
        function FilterUserList() {
            var searchBarInput = $('#search-bar').val();
            var sortingOption = $('#filterOptionSortType').val();
            var permissionLevel = $("#filterOptionPermission").val();

            sortingOption = parseInt(sortingOption);
            permissionLevel = parseInt(permissionLevel);

            try {
                var filterList = jQuery.extend(true, [], userList);
            } catch (err) {
                console.log(err);
            }

            if (searchBarInput != "") {
                filterList = FilterUserListByEmail(filterList, searchBarInput);
            }

            filterList = SortUserListByOption(filterList, sortingOption);

            filterList = FilterUserListBetweenDates(filterList);

            filterList = FilterUserPermission(filterList, permissionLevel);

            $('#userTable').html("");
            filteredUserList = filterList;
            PopulateUserTable(filterList);

        }

        /**
         * Filters the given list of users by a user permission level.
         *
         * @param listToFilter The list of users to be filtered.
         * @param selectedOption The permission level that you want to filter by.
         * @return The new list of users after filtering.
         * */
        function FilterUserPermission(listToFilter, selectedOption) {
            console.log(selectedOption);
            if (selectedOption < 1 || selectedOption > 3) {
                return listToFilter;
            } else {
                let filteredList;
                filteredList = listToFilter.filter(function(data) {
                    if (data.HasPermissionLevel == selectedOption) {
                        return true;
                    } else {
                        return false;
                    }
                });
                return filteredList;
            }
        }

        /**
         * Filters a user list by the given email address and then returns the resulting list.
         *
         * @param listToFilter The list of users to be filtered.
         * @param byEmail The email address that the list should be filter by
         * @return A filtered list of users.
         */
        function FilterUserListByEmail(listToFilter, byEmail) {
            var filteredList = [];
            listToFilter.forEach(function(data, index) {
                if (data.EmailAddress.toLowerCase().indexOf(byEmail.toLowerCase()) >= 0) {
                    filteredList.push(data);
                }
            });
            return filteredList;
        }

        /**
         * Filters Event List Between Dates
         *
         * Gets the dates to filter directly from the html of the form using jquery.
         *
         * @param listToFilter The list of events to be filtered
         * @return A filtered list of users
         */
        function FilterUserListBetweenDates(listToFilter) {
            var startTime = $("#filterOptionStartDate").val();
            var endTime = $("#filterOptionEndDate").val();
            var filteredList = [];

            if (startTime != "") {
                startTime = moment(startTime, "YYYY-MM-DD");
            }

            if (endTime != "") {
                endTime = moment(endTime, "YYYY-MM-DD");
            }

            filteredList = listToFilter.filter(function(data) {
                var isIncluded = true;
                if (startTime != "") {
                    isIncluded = moment(data.Created, "YYYY-MM-DD HH:mm:SS").isSameOrAfter(startTime, "day");
                }
                if (endTime != "" && isIncluded == true) {
                    isIncluded = moment(data.Created, "YYYY-MM-DD HH:mm:SS").isSameOrBefore(endTime, "day");
                }
                return isIncluded;
            });
            return filteredList;
        }

        /**
         * String No Case Compare
         *
         * A helper function for sorting options
         *
         * @param a The first string to be compared
         * @param b The second string to be compared
         * @returns -1|0|1
         */
        function StringNoCaseCompare(a, b)
        {
            if((a+'').toLowerCase() > (b+'').toLowerCase()) return 1;
            if((a+'').toLowerCase() < (b+'').toLowerCase()) return -1;
            return 0
        }

        /**
         * Reset Filtering Options
         *
         * Resets all of the filtering options back to their defaults
         */
        function ResetFilteringOptions() {
            $("#search-bar").val('');
            $("#filterOptionSortType").val(1);
            $("#filterOptionPermission").val(0);
            $("#filterOptionStartDate").val("");
            $("#filterOptionEndDate").val("");
            FilterUserList();
        }

        /**
         * Sort Event List By Option
         *
         * Options:
         * 1 - Most Recent First
         * 2 - Least Recent First
         * 3 - A to Z
         * 4 - Z to A
         * 4 - Z to A
         *
         * @param listToSort The list of events to be sorted
         * @param sortOption The type of sort, indicated by a numerical value
         * @return The sorted list of users, sorted by name
         */
        function SortUserListByOption(listToSort, sortOption) {
            let compareFunction;
            switch(sortOption) {
                case 1: compareFunction = function (a, b) {
                    if (moment(a.Created, "YYYY-MM-DD HH:mm:SS").isSameOrAfter(moment(b.Created, "YYYY-MM-DD HH:mm:SS"), "minute")) {
                        return -1;
                    } else {
                        return 1;
                    }
                }; break;
                case 2: compareFunction = function (a, b) {
                    if (moment(a.Created, "YYYY-MM-DD HH:mm:SS").isSameOrBefore(moment(b.Created, "YYYY-MM-DD HH:mm:SS"), "minute")) {
                        return -1;
                    } else {
                        return 1;
                    }
                }; break;
                case 3:compareFunction = function (a, b) {
                    return StringNoCaseCompare(a.EmailAddress, b.EmailAddress);
                }; break;
                case 4:compareFunction = function (a, b) {
                    return StringNoCaseCompare(b.EmailAddress, a.EmailAddress);
                }; break;
                default: return listToSort;
            }
            return listToSort.sort(compareFunction);
        }
    </script>
</html>
