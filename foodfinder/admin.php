<?php
/**
 * The admin page of the web portal
 *
 * Handles the configuration of administration options that can be managed from the database.
 */
require_once('util/Dbconnection.php');
?>
<html>
    <head>
        <title>Food Finder | Admin</title>
        <link rel="stylesheet" href="styles/Font.css">
    </head>
    <body>
        <?php
            require ('./util/navigationBar.php');
        ?>
        <!--Body containing the admin page-->
        <div class="container-fluid">
            <div class="row">
                <!--Admin options section-->
                <div class="col-sm border" id="leftPanel">
                    <!--Section heading-->
                    <h3 class="text-center p-1">Administration Options</h3>

                    <!--Search bar-->
                    <div class="row px-3 justify-content-center">
                        <input class="form-control w-100" id="search-bar" type="text" onkeyup="FilterAdminOptionsList()" placeholder="Search..." />
                    </div>

                    <!--Options list-->
                    <table class="table table-hover">
                        <tbody id="adminOptionsList">
                            <!--Rows are populated from a script-->
                        </tbody>
                    </table>
                </div>

                <!--Admin form section-->
                <div class="col-sm border" id="rightPanel">
                    <!--Empty until an admin option is selected from the administration options section-->
                    <h3 class="text-center p-1">Administration</h3>
                    <h5 class="text-center">Select an administration option</h5>
                </div>
            </div>
        </div>
    </body>
    <script>
        const MAX_ADMIN_OPTIONS_PER_PAGE = 10;
        var adminOptionsList = null;
        var filteredAdminOptionsList = null;
        var campusList = null;
        var sustainabilityEmailsList = null;
        var tagList = null;

        /**
         * On document load
         *
         * Calls the refresh admin list function so that information is populated
         */
        $(function() {
            RefreshAdminList();
        });

        /**
         * RefreshAdminList
         *
         * @description Makes a GET request for a list of admin options
         * Handles the response with the UpdateAdminList function
         */
        function RefreshAdminList() {
            $("#search-bar").val('');
            $.get('./api/admin.php', UpdateAdminList);
            $.get('./api/sendCampus.php', StoreCampusList);
            $.get('./api/GetSustainabilityTeamEmails.php', StoreSustainabilityEmails);
            $.get('./api/sendTag.php', StoreTags);
            SelectAdminOptionFromAdminList();
        }

        /**
         * SelectAdminOptionFromAdminList
         *
         * @description Adds an onClick handler to all rows of the admin list
         * Highlights a row when selected, and passes it's option_ID to the LoadAdminForm function
         */
        function SelectAdminOptionFromAdminList() {
            $('#adminOptionsList tr').click(function () {
                // Remove the selected-row class from any currently selected rows
                $('.selected-row').removeClass('selected-row');
                // Add the selected-row class to the selected row
                $(this).addClass('selected-row');
                // Pass the option_ID to another function to display the specific admin option
                var id = $('.id', this).html();
                LoadAdminForm(id);
            })
        }

        /**
         * LoadAdminForm
         *
         * @description Load the admin form in the right panel, and populates it with the selected admin option's data
         * @param id The option_ID for the selected admin option to be loaded
         */
        function LoadAdminForm(id) {
            // Variable to hold the selected admin option's data
            var option;

            // Clear the right panel of any forms
            $('#rightPanel').html("");

            // Prevent the user from seeing the panel before it has finished loading
            $('#rightPanel').addClass("invisible");

            // Request a copy of the admin form via http get
            $.get('./sections/adminForm.php', function (data) {
                // Set the right panel to the retrieved admin form
                $('#rightPanel').html(data);

                // Request the details for the selected admin option
                $.get('./api/admin.php?id=' + id, function (data) {
                    // Parse the JSON information
                    obj = JSON.parse(data);

                    if (obj.wasSuccessful != 1) {
                        console.log(obj.message);
                        return;
                    }

                    // Check that only one admin option is selected
                    if (obj.data != null) {
                        // Get the first element of the JSON array
                        option = obj.data;

                        // Set the forms fields to match the data of the selected admin option
                        if (option.Option_ID == 1) {
                            $('#adminContent').append(
                                // Add campus
                                "<div class='text-center'>" +
                                    "<h5>Add Campus</h5>" +
                                    "<label>Enter details for the new campus</label>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                    "<label for='adminCampusName'>Name</label>" +
                                    "<input id='adminCampusName' class='form-control' type='text' name='campusName'>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                    "<label for='adminCampusLong'>Longitude</label>" +
                                    "<input id='adminCampusLong' class='form-control' type='text' name='campusLong'>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                    "<label for='adminCampusLat'>Latitude</label>" +
                                    "<input required id='adminCampusLat' class='form-control' type='text' name='campusLat'>" +
                                "</div>" +
                                "<div class='form-group text-center'>" +
                                    "<input id='campusAddButton' class='btn btn-primary' type='button' value='Add Campus' onclick='AddCampus()'>" +
                                "</div>" +
                                "<hr>" +

                                // Remove campus
                                "<div class='text-center'>" +
                                    "<h5>Remove Campus</h5>" +
                                    "<label>Select a campus to remove</label>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                    "<label for='adminCampusList'>Campus</label>" +
                                    "<select id='adminCampusList' class='form-control' name='campusList'/>" +
                                "</div>" +
                                "<div class='form-group text-center'>" +
                                    "<input id='campusRemoveButton' class='btn btn-danger' type='button' onclick='DeleteCampus()' value='Remove Campus'>" +
                                "</div>"
                            );
                            PopulateCampusOptions();
                        }

                        if (option.Option_ID == 2) {
                            $('#adminContent').append(
                                // Add tag
                                "<div class='text-center'>" +
                                "<h5>Add Tag</h5>" +
                                "<label>Enter details for the new tag</label>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                "<label for='adminTagName'>Name</label>" +
                                "<input id='adminTagName' class='form-control' type='text' name='tagName'>" +
                                "</div>" +
                                "<div class='form-group text-center'>" +
                                "<input id='TagAddButton' class='btn btn-primary' type='button' value='Add Tag' onclick='AddTag()'>" +
                                "</div>" +
                                "<hr>" +
                                // Remove tag
                                "<div class='text-center'>" +
                                "<h5>Remove Tag</h5>" +
                                "<label>Select a tag to remove</label>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                "<label for='adminTagList'>Tag</label>" +
                                "<select id='adminTagList' class='form-control' name='tagList'/>" +
                                "</div>" +
                                "<div class='form-group text-center'>" +
                                "<input id='tagRemoveButton' class='btn btn-danger' type='button' onclick='DeleteTag()' value='Remove Tag'>" +
                                "</div>"
                            );
                            PopulateTagOptions();
                        }

                        if (option.Option_ID == 3) {
                            $('#adminContent').append(
                                // Add subscription
                                "<div class='text-center'>" +
                                    "<h5>Subscribe</h5>" +
                                    "<label>Select a Sustainability email to subscribe</label>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                    "<select id='adminNonSubEmailList' class='form-control' name='adminNonSubEmailList'/>" +
                                "</div>" +
                                "<div class='form-group text-center'>" +
                                    "<input id='adminSubButton' class='btn btn-primary' type='button' value='Subscribe' onclick='SubscribeEmail()'>" +
                                "</div>" +
                                "<hr>" +
                                // Remove subscription
                                "<div class='text-center'>" +
                                    "<h5>Unsubscribe</h5>" +
                                    "<label>Select a Sustainability email to unsubscribe</label>" +
                                "</div>" +
                                "<div class='form-group'>" +
                                    "<select id='adminSubEmailList' class='form-control' name='adminSubEmailList'/>" +
                                "</div>" +
                                "<div class='form-group text-center'>" +
                                    "<input id='adminUnsubButton' class='btn btn-danger' type='button' value='Unsubscribe' onclick='UnsubscribeEmail()'>" +
                                "</div>"
                            );
                            PopulateSubscribedSustainabilityEmails();
                        }

                        // Show the loaded panel
                        $('#rightPanel').removeClass("invisible");
                    } else {
                        console.log("There was an issue accessing the selected administration option");
                    }
                })
            });
        }

        /**
         * StoreCampusList
         *
         * @description Stores a list of existing campuses from the database
         * @param data An array of campuses in JSON
         * [{Campus_ID, Campus_Name}, ...]
         */
        function StoreCampusList(data) {
            var JSONResponse;

            try {
                JSONResponse = JSON.parse(data);
                campusList = JSONResponse;
            } catch (error) {
                campusList = [{"Campus_Name":"Newnham", "Campus_ID":"1"}];
            }
        }

        /**
         * PopulateCampusOptions
         *
         * @description Fills the campus selector with the campuses from the database
         */
        function PopulateCampusOptions() {
            var campusSelector = $("#adminCampusList");

            campusList.forEach(function (element) {
                campusSelector.append("<option value="+element.Id+">"+element.Name+"</option>");
            });
        }

        /**
         * PopulateTagOptions
         *
         * @description Fills the tag selector with the tags from the database
         */
        function PopulateTagOptions() {
            var tagSelector = $("#adminTagList");

            tagList.forEach(function (element) {
                tagSelector.append("<option value="+element.Tag_Id+">"+element.Description+"</option>");
            });
        }

        /**
         * StoreSustainabilityEmails
         *
         * @description Stores a list of sustainability emails from the database
         * @param data An array of emails in JSON
         * [{User_ID, EmailAddress, IsSubscribedToRequests}, ...]
         */
        function StoreSustainabilityEmails(data) {
            var JSONResponse;

            try {
                JSONResponse = JSON.parse(data);
                sustainabilityEmailsList = JSONResponse;
            } catch (error) {
                sustainabilityEmailsList = [{"User_ID":"-1","EmailAddress":"","IsSubscribedToRequests":"0"}];
            }
        }

        /**
         * StoreTags
         *
         * @description Stores a list of tags from the database
         * @param data an array of tags
         */
        function StoreTags(data) {
            var JSONResponse;

            try {
                JSONResponse = JSON.parse(data);
                tagList = JSONResponse;
            } catch (error) {
                tagList = null;
            }
        }

        /**
         * PopulateSubscribedSustainabilityEmails
         *
         * @description Fills the email selector with the subscribed emails from the database
         */
        function PopulateSubscribedSustainabilityEmails() {
            var emailSubscribedSelector = $("#adminSubEmailList");
            var emailNonSubscribedSelector = $("#adminNonSubEmailList");

            sustainabilityEmailsList.forEach(function (element) {
                if (element.Sub == 1) {
                    emailSubscribedSelector.append("<option value="+element.Id+">"+element.Email+"</option>");
                } else {
                    emailNonSubscribedSelector.append("<option value="+element.Id+">"+element.Email+"</option>");
                }
            })
        }

        /**
         * AddCampus
         *
         * @description Add a new campus to the database
         */
        function AddCampus() {
            // var $inputs = $('#adminContent :input');

            //var sending = $('#adminContent').serialize();
            let sending = {};
            sending.Name = $('#adminCampusName').val();
            sending.Long = $('#adminCampusLong').val();
            sending.Lat = $('#adminCampusLat').val();
            if (sending.Name == "" || sending.Long == "" || sending.Lat == "") {
                console.log("Please fill our all of the fields for adding a new campus.");
            } else {

                // console.log(sending);

                $.post("./api/addCampus.php", sending, function (data) {
                    try {
                        var dataJSON = JSON.parse(data);

                        if (dataJSON.wasSuccessful) {
                            // Reset the right panel and refresh the admin list
                            LoadDefaultRightSideHeadings();
                            RefreshAdminList();
                            alert(dataJSON.message);
                        } else {
                            alert(dataJSON.message);
                            console.log(data);
                        }
                    } catch (err) {
                        console.log(data);
                    }
                });
            }
        }

        /**
         * DeleteCampus
         *
         * @description Soft deletes the given campus from the database
         */
        function DeleteCampus() {
            var confirmation = confirm("Are you sure you want to delete this campus?");
            let sending = {};
            sending.id = $("#adminCampusList").val();

            if (confirmation == true){
                $.post("./api/softDeleteCampus.php", sending, function (data) {
                    try {
                        var dataJSON = JSON.parse(data);

                        if (dataJSON.wasSuccessful){
                            // Reset the right panel and refresh the admin list
                            LoadDefaultRightSideHeadings();
                            RefreshAdminList();
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
         * AddTag
         *
         * @description Add a new tag to the database
         */
        function AddTag() {
            // var $inputs = $('#adminContent :input');

            //var sending = $('#adminContent').serialize();
            let sending = {};
            sending.Name = $('#adminTagName').val();
            if (sending.Name == "") {
                console.log("Please fill out the name field for the new tag.");
            } else {

                // console.log(sending);

                $.post("./api/addTag.php", sending, function (data) {
                    try {
                        var dataJSON = JSON.parse(data);

                        if (dataJSON.wasSuccessful) {
                            // Reset the right panel and refresh the admin list
                            LoadDefaultRightSideHeadings();
                            RefreshAdminList();
                            alert(dataJSON.message);
                        } else {
                            alert(dataJSON.message);
                            console.log(data);
                        }
                    } catch (err) {
                        console.log(data);
                    }
                });
            }
        }

        /**
         * DeleteTag
         *
         * @description Soft deletes the given tag from the database
         */
        function DeleteTag() {
            var confirmation = confirm("Are you sure you want to delete this tag?");
            let sending = {};
            sending.id = $("#adminTagList").val();

            if (confirmation == true){
                $.post("./api/softDeleteTag.php", sending, function (data) {
                    try {
                        var dataJSON = JSON.parse(data);

                        if (dataJSON.wasSuccessful){
                            // Reset the right panel and refresh the admin list
                            LoadDefaultRightSideHeadings();
                            RefreshAdminList();
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
         * SubscribeEmail
         *
         * @description Subscribes the currently selected user to admin emails.
         * */
        function SubscribeEmail() {
            let sending = {};
            sending.id = $("#adminNonSubEmailList").val();
            $.post("./api/SubscribeToAdminEmails.php", sending, function (data) {
                try {
                    // JA: Parse the result
                    response = JSON.parse(data);
                    console.log(response.message);

                    // JA: Check the response of the request
                    if (response.wasSuccessful) {
                        // JA: Reset the right panel and refresh the admin list
                        LoadDefaultRightSideHeadings();
                        RefreshAdminList();
                        alert(response.message);
                    } else { // JA: Display the error message
                        alert(response.message);
                    }
                } catch (err) {
                    console.log(data);
                }
            });
        }

        /**
         * UnsubscribeEmail
         *
         * @description Unsubscribes the currently selected user to admin emails.
         * */
        function UnsubscribeEmail() {
            let sending = {};
            sending.id = $("#adminSubEmailList").val();
            $.post("./api/UnsubscribeFromAdminEmails.php", sending, function (data) {
                try {
                    // JA: Parse the result
                    response = JSON.parse(data);
                    console.log(response.message);

                    // JA: Check the response of the request
                    if (response.wasSuccessful) {
                        // JA: Reset the right panel and refresh the admin list
                        LoadDefaultRightSideHeadings();
                        RefreshAdminList();
                        alert(response.message);
                    } else { // JA: Display the error message
                        alert(response.message);
                    }
                } catch (err) {
                    console.log(data);
                }
            });
        }

        /**
         * LoadDefaultRightSideHeadings
         *
         * @description Replaces everything in the right panel with the default headings for the page
         */
        function LoadDefaultRightSideHeadings() {
            $("#rightPanel").html(
                "<h3 class=\"text-center p-1\">Administration</h3>\n" +
                "<h5 class=\"text-center\">Select an administration option</h5>"
            );
        }

        /**
         * UpdateAdminList
         *
         * @description Updates the admin options list with data retrieved from the api
         * @param data An array of admin options in JSON
         * [{Option_ID, Option_Name}, ...]
         */
        function UpdateAdminList(data) {
            // Variable to hold the JSON information
            var obj;

            // Clear the current list
            $('#adminOptionsList').html('');

            try {
                // Parse the JSON information from text
                obj = JSON.parse(data);

                if (obj.wasSuccessful) {
                    // Add a row for each admin option in the JSON object
                    adminOptionsList = obj.data;
                    filteredAdminOptionsList = obj.data;
                    PopulateAdminOptionsList(adminOptionsList);
                } else {
                    // Output the message from the packet
                    console.log(obj.message);
                    alert(obj.message);
                }
            } catch (err) {
                // Output the entirety of the data for debugging
                console.log(err);
                console.log(data);
                alert("Something went wrong");
            }
        }

        /**
         * PopulateAdminOptionsList
         *
         * @description A modular function, allowing multiple sections to add content to the admin options list.
         * Note: You should clear the #adminOptionsList before calling this function
         * @param data The list of admin options to be added to the adminOptionsList
         */
        function PopulateAdminOptionsList(data) {
            data.forEach(AddRowToAdminOptionsList);
            ShowAdminPage(1);
            SelectAdminOptionFromAdminList();
        }

        /**
         * AddRowToAdminOptionsList
         *
         * @description Adds a single admin option as a new row in the admin options list
         * @param option JSON for a single admin object's information
         * @param index The index of the admin object out of all admin objects being added
         */
        function AddRowToAdminOptionsList(option, index) {
            // Variables to hold the relevant information from the JSON
            let $optionID = option.Option_ID;
            let $optionName = option.Option_Name;
            var classToAdd = "adminOptionListPage"+parseInt(index/MAX_ADMIN_OPTIONS_PER_PAGE);
            $('#adminOptionsList').append("<tr class='"+classToAdd+" adminOptionTableEntry'><td class='id' hidden>"+$optionID+"</td><td>"+$optionName+"</td></tr>");
        }

        /**
         * ShowAdminPage
         *
         * @description Hides any admin options that are not to be displayed, and shows admin options that should be displayed.
         * @param pageNumber The number of the page to be shown starting with page 1
         */
        function ShowAdminPage(pageNumber) {
            // Hide unwanted, show the correct page
            $('.adminOptionsTableEntry').hide();
            $('.adminOptionsListPage'+(pageNumber-1)).show();
            // Delete and read the navigation bar
            $('#AdminOptionsListPageNav').remove();
            AddPageNumbersToAdminOptionsList(pageNumber);
            // Set the active page number
            $(".page-item:contains("+pageNumber+")").addClass('active');
        }

        /**
         * AddPageNumbersToAdminOptionsList
         *
         * @description Adds the page numbers to bottom of the admin options list
         * @param currentPage The page that is currently selected by the user
         * */
        function AddPageNumbersToAdminOptionsList(currentPage) {
            var rangeOfNumberDisplayed = 3;
            currentPage = parseInt(currentPage);
            var amountOfPages = parseInt(filteredAdminOptionsList.length/MAX_ADMIN_OPTIONS_PER_PAGE);
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
                var htmlToAdd = "<nav id='AdminOptionsListPageNav' aria-label=\"Page navigation example\"><ul class=\"pagination justify-content-center\"><li id='previousButton' class='page-item'><a class=\"page-link\" href=\"#\">Previous</a></li>";
                for (var i = lowestPageToDisplay; i <= highestPageToDisplay; i++) {
                    if (!(parseInt(filteredAdminOptionsList.length % MAX_ADMIN_OPTIONS_PER_PAGE) == 0 && i == highestPageToDisplay)) {
                        htmlToAdd += '<li class="page-item"><a class="page-link" href="#">' + (i + 1) + '</a></li>';
                    }
                }
                htmlToAdd += "<li id='nextButton' class=\"page-item text-center\"><a class=\"page-link\" href=\"#\">Next</a></li></ul></nav>";
                // Add to the appropriate section
                $("#adminOptionsList").append(htmlToAdd);
            }
            $("#nextButton").width($("#previousButton").width());
            // Set the handler for when a page button is clicked
            $(".page-link").click(function() {
                var selectedValue = $(this).text();
                if (selectedValue === "Previous") {
                    if (parseInt(currentPage) > 1) {
                        ShowAdminPage(parseInt(currentPage) - 1);
                    }
                } else if (selectedValue === "Next") {
                    if (currentPage <= amountOfPages && (!(parseInt(filteredAdminOptionsList.length % MAX_ADMIN_OPTIONS_PER_PAGE) == 0 && currentPage == highestPageToDisplay))) {
                        ShowAdminPage(parseInt(currentPage) + 1);
                    } else {
                        ShowAdminPage(currentPage);
                    }
                } else {
                    ShowAdminPage(selectedValue);
                }
            });
        }

        /**
         * FilterAdminOptionsList
         *
         * @description This function serves as a reaction to a change in the selected filtering options.
         * Checks all possible options to see if they have been selected, then filters by them.
         * Clears and repopulates the admin options list once finished.
         */
        function FilterAdminOptionsList() {
            var searchBarInput = $('#search-bar').val();
            var filterList = jQuery.extend(true, [], adminOptionsList);
            if (searchBarInput != "") {
                filterList = FilterAdminOptionsListByName(filterList, searchBarInput);
            }
            console.log(filterList);
            $("#adminOptionsList").html("");
            filteredAdminOptionsList = filterList;
            PopulateAdminOptionsList(filterList);
        }

        /**
         * FilterAdminOptionsListByName
         *
         * @description Filters the admin options list by the given name and returns the resulting list
         * Does not make any changes to the html being displayed
         * @param listToFilter The list to be filtered
         * @param byName The name to filter by
         */
        function FilterAdminOptionsListByName(listToFilter, byName) {
            let filteredList = [];
            listToFilter.forEach(function(data, index) {
                if (data.Option_Name.toLowerCase().indexOf(byName.toLowerCase()) >= 0) {
                    filteredList.push(data);
                }
            });
            return filteredList;
        }
    </script>
</html>