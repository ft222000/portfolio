<?php
/**
 * Created by PhpStorm.
 * User: John Robert Aslin
 * Date: 9/05/2018
 * Time: 10:48 PM
 *
 * This file provides the base for the requests screen of the application
 *
 * It will:
 * -Checks for EventOrganiser permissions requests
 * -Allow the user to accept or deny requests
 */
require_once('util/Dbconnection.php');
?>

<html>
    <head>
        <title>Food Finder | Requests</title>
        <!--JA: Style for a selected request-->
        <link rel="stylesheet" href="styles/List.css" />
        <link rel="stylesheet" href="styles/Font.css" />
        <link rel="stylesheet" href="styles/Feedback.css" />
    </head>

    <body>
        <!--JA: Navigation bar-->
        <?php
        require('util/navigationBar.php');
        ?>

        <!--JA: Body containing the requests page-->
        <div class="container-fluid">
            <div class="row">
                <!--JA: Pending requests section-->
                <div class="col-sm border" id="leftPanel">
                    <!--JA: Section heading-->
                    <div class="p-1">
                        <h3 class="text-center">Pending Requests</h3>
                    </div>

                    <div class="row px-3 justify-content-center">
                        <input class="form-control w-100" id="search-bar" type="text" onkeyup="FilterUserList()" placeholder="Search..." />
                    </div>

                    <!--JA: Request list-->
                    <table class="table table-hover">
                        <tbody id="requestList">
                            <!--JA: Rows are populated from a script-->
                        </tbody>
                    </table>
                </div>

                <!--JA: Request form section-->
                <div class="col-sm border" id="rightPanel">
                    <!--JA: Empty until a request is selected from the Pending requests section-->
                    <div class="p-1">
                        <h3 class="text-center">Requests</h3>
                    </div>
                    <h5 class="text-center">Select a request</h5>
                </div>
            </div>
        </div>
    </body>

    <script>
        const MAX_USERS_PER_PAGE = 10;
        var userList = null;
        var filteredUserList = null;

        /**
         * JA: On document load
         */
        $(function() {
            RefreshRequestsList();
        });

        /**
         * JA: Makes a GET request for a list of requests
         * Handles the response with the UpdateRequestsList function
         */
        function RefreshRequestsList() {
            $("#search-bar").val('');
            $.get('api/requests.php', UpdateRequestsList);
            SelectRequestFromRequestsList();
        }

        /**
         * JA: Adds an onClick hander to all rows of the requests list
         * Highlights a row when selected, and passes it's User_ID to the LoadRequestForm function
         */
        function SelectRequestFromRequestsList() {
            $('#requestList tr').click(function () {
                // JA: Variable to hold the selected request's id
                let id;

                // JA: Remove the selected-row class from any currently selected rows
                $('.selected-row').removeClass('selected-row');

                // JA: Add the selected-row class to the selected row
                $(this).addClass('selected-row');

                // JA: Pass the User_ID to another function to display the specific request
                id = $('.id', this).html();
                LoadRequestForm(id);
            })
        }

        /**
         * JA: Load the request form in the right panel, and populates it with the selected request's data
         *
         * @param id - The User_ID for the selected request to be loaded
         */
        function LoadRequestForm(id) {
            // JA: Variable to hold the selected request's data
            let request;

            // JA: Clear the right panel of any forms
            $('#rightPanel').html("");

            // JA: Request a copy of the requests form via http get
            $.get('sections/requestForm.html', function (data) {
                // JA: Set the right panel to the retrieved request form
                $('#rightPanel').html(data);
                $('#requestFormHeading').addClass("pt-1");
                $('#requestDeclineButton').removeClass("btn-secondary").addClass("btn-danger");

                // JA: Request the details for the selected request
                $.get('api/requests.php?id=' + id, function (data) {
                    // JA: Parse the JSON information
                    obj = JSON.parse(data);


                    // JA: Check that only one request is selected
                    if (obj.length == 1){
                        // JA: Get the first element of the JSON array
                        request = obj[0];

                        // JA: Set the forms fields to match the data of the selected event

                        $('#requestID').val(obj[0].User_ID);
                        $('#requestEmail').val(obj[0].EmailAddress);
                        $('#requestReason').val(obj[0].RequestReason);
                    } else {
                        console.log("There was an issue returning the selected request's information");
                    }
                })
            });
        }

        /**
         * JA: Updates the requests list with data retrieved from the database
         *
         * @param data - An array of requests in JSON
         * [{User_ID, EmailAddress, RequestReason}, ...]
         */
        function UpdateRequestsList(data) {
            // JA: Variable to hold the JSON information
            let obj;

            // JA: Clear the current list
            $('#requestsList').html('');

            // JA: Parse the JSON information
            obj = JSON.parse(data);

            // JA: Add each request from the JSON to a new row in the list
            filteredUserList = obj;
            userList = obj;

            PopulateUserList(obj);
            //obj.forEach(AddRowToRequestsList);

            //ShowUserPage(1);

            // JA: Refresh click handlers
            //SelectRequestFromRequestsList();
        }

        function PopulateUserList(data) {
            data.forEach(AddRowToRequestsList);
            ShowUserPage(1);
            SelectRequestFromRequestsList();
        }

        /**
         * JA: Adds a single request as a new row in the requests list
         *
         * @param request - JSON for a single request's information
         */
        function AddRowToRequestsList(request, index) {
            // JA: Variables to hold the relevant information from the JSON
            let $id = request.User_ID;
            let $email = request.EmailAddress;
            var classToAdd = "userListPage"+parseInt(index/MAX_USERS_PER_PAGE);
            $('#requestList').append("<tr class='"+classToAdd+" userTableEntry'><td class='id' hidden>"+$id+"</td><td>"+$email+"</td></tr>");
        }

        /**
         * ShowUserPage
         * @description Hides any users that are not to be displayed, and shows users that should be displayed.
         * @param pageNumber The number of the page to be shown starting with page 1
         * @author Joeby Neil
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
         * AddPageNumbersToUserList
         * @description Adds the page numbers to bottom of the user list
         * @param currentPage The page that is currently selected by the user
         * */
        function AddPageNumbersToUserList(currentPage) {
            var rangeOfNumberDisplayed = 3;

            var amountOfPages = parseInt(filteredUserList.length/MAX_USERS_PER_PAGE);

            currentPage = parseInt(currentPage);

            // Add the relevant buttons
            var lowestPageToDisplay = 0;
            var highestPageToDisplay = amountOfPages+1;

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

            if (amountOfPages != 0) {
                var htmlToAdd = "<nav id='UserListPageNav' aria-label=\"Page navigation example\"><ul class=\"pagination justify-content-center\"><li id='previousButton' class='page-item'><a class=\"page-link\" href=\"#\">Previous</a></li>";
                for (var i = lowestPageToDisplay; i < highestPageToDisplay; i++) {
                    if (!(parseInt(filteredUserList.length % MAX_USERS_PER_PAGE) == 0 && i == highestPageToDisplay)) {
                        htmlToAdd += '<li class="page-item"><a class="page-link" href="#">' + (i + 1) + '</a></li>';
                    }
                }
                htmlToAdd += "<li id='nextButton' class=\"page-item text-center\"><a class=\"page-link\" href=\"#\">Next</a></li></ul></nav>";
            }


            // Add to the appropriate section
            $("#requestList").append(htmlToAdd);
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

        function FilterUserList() {
            var searchBarInput = $('#search-bar').val();
            var filterList = jQuery.extend(true, [], userList);


            if (searchBarInput != "") {
                filterList = FilterUserListByEmail(filterList, searchBarInput);
            }

            console.log(filterList);

            $("#requestList").html("");
            filteredUserList = filterList;
            PopulateUserList(filterList);
        }

        function FilterUserListByEmail(listToFilter, byEmail) {
            var filteredList = [];
            listToFilter.forEach(function(data, index) {
                if (data.EmailAddress.toLowerCase().indexOf(byEmail.toLowerCase()) >= 0) {
                    filteredList.push(data);
                }
            });
            return filteredList;
        }
    </script>
</html>