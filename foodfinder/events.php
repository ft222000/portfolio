<?php
/**
*  The event management page
 *
 * Allows users to create, edit, view, and delete events from the application
*/
require_once('util/Dbconnection.php');
require_once('util/session.php');
?>
<html>
<head>
    <title>Food Finder | Events</title>
    <!--Joeby: Style for a selected event-->
    <link rel="stylesheet" href="styles/List.css" />
    <link rel="stylesheet" href="styles/Font.css" />
</head>
<body>
<!--Kim: Navigation bar-->
<?php
require('util/navigationBar.php');
?>

<div class="container-fluid">
    <div class="row">
        <!--Kim: Pending events section-->
        <div class="col-sm border" id="LeftPanel">
            <!--Kim: Section heading-->

            <h3 class="p-1 text-center">Event Management</h3>


            <!--JN: Create Event button-->
            <div class="row px-3 justify-content-center">
                <input class="w-70 form-control" id="search-bar" type="text" onkeyup="FilterEventList()" placeholder="Search..." />
                <button class="btn btn-success w-15" data-toggle="modal" data-target="#filterModal">Filter</button>
                <button class="btn btn-primary w-15" onclick="LoadCreateEventSection()">Create</button>
            </div>

            <!--Joeby: Modal Popover-->
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
                                <select id="filterOptionSortType" onchange="FilterEventList()" class="form-control">
                                    <option value="1">Most Recent First</option>
                                    <option value="2">Least Recent First</option>
                                    <option value="3">A-Z</option>
                                    <option value="4">Z-A</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="filterOptionStartDate" class="text-center">Earliest Date</label>
                                <input type="date" onchange="FilterEventList()" class="form-control" id="filterOptionStartDate">
                            </div>

                            <div class="form-group">
                                <label for="filterOptionendDate" class="text-center">Latest Date</label>
                                <input type="date" onchange="FilterEventList()" class="form-control" id="filterOptionEndDate"/>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="filterOptionFinishedEvents">
                                <label class="form-check-label" for="filterOptionFinishedEvents">
                                    Show events that have finished
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ResetFilteringOptions()">Clear</button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!--Kim: Event list-->
            <table class="table table-hover">
                <tbody id="eventsList">
                <!--Kim: Rows are populated from a script-->

                </tbody>
            </table>
        </div>

        <!--Kim: Event form section-->
        <div class="col-sm border" id="RightPanel">
            <!--Kim: Empty until a event is selected from the Pending events section-->
            <h3 class="p-1 text-center">Events</h3>
            <h5 class="text-center">Select an event</h5>

        </div>
    </div>
</div>
</body>
<script>
    var updatingEventInProgress = false;
    var selectedEvent = null;
    var currentUserID = <?php echo $session_user_id; ?>;
    var currentUserEmail = "<?php echo $session_user_email; ?>";
    var campusList = null;
    const MAX_EVENTS_PER_PAGE = 10;
    var eventList = null;
    var filteredEventList = null;

    /**
     * On Document Load
     *
     * Calls RefreshEventsList and SetupFilteringOptions, which prepares the page for use.
     */
    $(function() {
        RefreshEventsList();
        SetupFilteringOptions();
    });

    /**
     * Sets up the filtering options
     *
     * Sets up an event to ensure that filtering takes place as needed.
     * */
    function SetupFilteringOptions() {
        $("#filterOptionFinishedEvents").change(() => FilterEventList());
    }

    /**
     * Stores the campus list locally for use by forms.
     *
     * Handles the response from api/sendCampus.php
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
     * Updates the locally stored list of campuses and events.
     *
     * Makes GET requests to api/sendCampus.php and api/LoadEvent.php
     * Calls SelectEventFromEventsList to add event handlers to all of the events in the list.
     */
    function RefreshEventsList() {
        $('#search-bar').val('');
        $.get('api/sendCampus.php', StoreCampusList);
        $.get('api/LoadEvent.php', UpdateEventsList);
        SelectEventFromEventsList();
    }

    /**
     * Sets the right panel back to its default state.
     *
     * Replaces any existing content.
     * */
    function ResetRightPanelHeading() {
        $('#RightPanel').html("<h3 class=\"text-center\">Edit Event</h3>" +
            "<h5 class=\"text-center\">Select an Event</h5>");
    }

    /**
     * Adds an onClick handler to all rows of the events list
     *
     * Highlights a row when selected, and passes it's User_ID to the LoadEventForm function
     */
    function SelectEventFromEventsList() {
        $('#eventsList tr').click(function () {
            // Variable to hold the selected event's id
            let id;
            // Remove the selected-row class from any currently selected rows
            $('.selected-row').removeClass('selected-row');
            // Add the selected-row class to the selected row
            $(this).addClass('selected-row');
            // Pass the User_ID to another function to display the specific event
            id = $('.id', this).html();
            LoadEventForm(id);
        })
    }

    /**
     * Fills the campus selector with campuses that were retrieved from the database using StoreCampusList.
     *
     * Each entry has the value of the campus id, and displays the readable name of the campus.
     * */
    function PopulateCampusOptions() {
        var campusSelector = $("#campus");
        campusList.forEach(function(element) {
            campusSelector.append("<option value="+element.Id+">"+element.Name+"</option>");
        });
        $("#campus").removeAttr("readonly");
    }

    /**
     * Loads the event form in the right panel, and populates it with the selected event's data
     *
     * @param id - The Event_ID for the selected event to be loaded
     */
    function LoadEventForm(id) {
        // Variable to hold the selected event's data
        let event;

        // Clear the right panel of any forms
        $('#RightPanel').html("");

        // Hide the panel while filling with data
        $('#RightPanel').addClass("invisible");

        // Retrieve a copy of the events form via http get
        $.get('sections/eventsForm.php', function (data) {
            // Set the right panel to the retrieved event form
            $('#RightPanel').html(data);
            // Event the details for the selected event
            $.get('api/LoadEvent.php?id=' + id, function (data) {
                try {
                    // Parse the JSON information
                    obj = JSON.parse(data);

                    if (obj.wasSuccessful == 1) {
                        // Check that only one event is selected
                        if (obj.data.length == 1) {
                            // Get the first element of the JSON array
                            event = obj.data[0];

                            // Set the forms fields to match the data of the selected event
                            $('#eventID').val(event.Event_ID);
                            $('#eventName').val(event.Name);
                            $('#organiser').val(event.Organiser);
                            $('#locatedIn').val(event.LocatedIn);
                            $('#startTime').val(event.StartTime);
                            $('#endTime').val(event.EventClosed);

                            // Allow editing to changable fields
                            $('#eventName').removeAttr("readonly");
                            $('#locatedIn').removeAttr("readonly");
                            $('#startTimeStaging').removeAttr("readonly");
                            $('#endTimeStaging').removeAttr("readonly");
                            $('#endDateStaging').removeAttr("readonly");
                            $('#startDateStaging').removeAttr("readonly");

                            // Fill the campus selector with available options
                            PopulateCampusOptions();
                            $('#campus option').removeAttr('selected').filter('[value=' + event.Campus_ID + ']').attr('selected', true);


                            // Date magic from timestamp to date format for display to user
                            var dateToDisplay = moment(event.StartTime, "YYYY-MM-DD HH:mm:SS");
                            $('#startDateStaging').val(dateToDisplay.format("YYYY-MM-DD"));
                            $('#startTimeStaging').val(dateToDisplay.format("HH:mm"));

                            dateToDisplay = moment(event.EventClosed, "YYYY-MM-DD HH:mm:SS");
                            $('#endDateStaging').val(dateToDisplay.format("YYYY-MM-DD"));
                            $('#endTimeStaging').val(dateToDisplay.format("HH:mm"));

                             // Button for updating the event
                            $('#eventFormButtons').append("<input id=\"formSubmissionButton\" type=\"submit\" class=\"btn btn-primary text-center\" name=\"create\" value=\"Update\">");

                            // Add button for the deletion of events
                            $("#eventFormButtons").append(" <input type=\"button\" class=\"btn btn-danger text-center\" value=\"Delete\" onclick=DeleteEvent("+id+")>");

                            // Store the event for use in the update function
                            selectedEvent = event;

                            $("#RightPanel").removeClass("invisible");

                            UpdateEventRequestHandler();
                        } else {
                            console.log("There was an issue returning the selected event's information");
                        }
                    } else {
                        console.log("There was an issue returning the selected event");
                    }
                } catch (err) {
                }

            })
        });
    }

    /**
     * Loads the event creation section of the page from the file template
     *
     * Sets the default values of the date and time pickers to aid with input.
     * */
    function LoadCreateEventSection() {
        // Clear the right panel of the screen
        $('#RightPanel').html("");

        // Hide the panel while filling with data
        $('#RightPanel').addClass("invisible");

        $.get('sections/eventsForm.php', function (data) {
            // Add the data to the right section of the page
            $('#RightPanel').html(data);

            // Make the input fields active
            $("input").removeAttr("readonly");

            // Add the create button
            $('#eventFormButtons').append("<input id=\"formSubmissionButton\" type=\"submit\" class=\"btn btn-primary\" name=\"create\" value=\"Submit\">");

            // Setup the form ready for the user
            $("#organiser").val(currentUserEmail).attr("readonly", true);
            $("#eventID").html("");

            // Default event times for convenience
            $('#endDateStaging').val(moment().add(4,"hours").format("YYYY-MM-DD"));
            $('#startDateStaging').val(moment().format("YYYY-MM-DD"));

            // Set the times
            $('#endTimeStaging').val(moment().add(4,"hours").format("HH:mm"));
            $('#startTimeStaging').val(moment().format("HH:mm"));

            // Fill the options of the campus selector
            PopulateCampusOptions();

            // Show the panel again
            $('#RightPanel').removeClass("invisible");

            // Add a handler for the submission
            CreateEventCreationHandler();
        });

    }

    /**
     * Adds a request handler to any active create event form.
     *
     * Makes a POST request to api/CreateEvent.php to handle to event creation.
     * Converts the date and time to a format that will work better with the database.
     * On success, the event list is refreshed using RefreshEventsList, and the right panel is reset.
     * */
    function CreateEventCreationHandler() {
        // Make the submit run this function
        $('#eventsForm').submit(function(event) {

            // Prevent default
            event.preventDefault();

            // Check to see if something is already trying to do this
            if (updatingEventInProgress) {
                return;
            }

            // Prevent repeat presses
            updatingEventInProgress = true;

            // Change the email address to the id value
            $("#organiser").val(currentUserID);

            // Prepare time formats
            $("#startTime").val(moment($("#startDateStaging").val()+" "+$("#startTimeStaging").val(),"YYYY-MM-DD HH:mm").format("YYYY-MM-DD HH:mm:SS"));
            $("#endTime").val(moment($("#endDateStaging").val()+" "+$("#endTimeStaging").val(),"YYYY-MM-DD HH:mm").format("YYYY-MM-DD HH:mm:SS"));



            // Send post request with the form data
            $.post("api/CreateEvent.php", $('#eventsForm').serialize() , function(data, status) {
                // Change the value back to the
                $("#organiser").val(currentUserEmail);
                try {
                    // Parse the result
                    response = JSON.parse(data);

                    // Check the response of the request
                    if (response.wasSuccessful == 1) {


                        // Set the headings back to the default for the page
                        ResetRightPanelHeading();

                        // Refresh the user list
                        RefreshEventsList();

                        // Give feedback to the user
                        alert(response.message);
                    } else {

                        // Display the error message that has been passed through
                        console.log(data);

                        // Give feedback to the user
                        alert(response.message);
                    }
                } catch (err){
                    console.log(data); // For debugging
                }
                  updatingEventInProgress = false;
            });
        });
    }

    /**
     * Attaches an event to the update button in the view event panel.
     *
     * Stops default submit, and replaces it with a POST request to api/UpdateEvent.php.
     * On Success, refreshes event list with RefreshEventsList and resets the right panel.
     * */
    function UpdateEventRequestHandler() {
        // JN: Add the event to the correct button
        $('#eventsForm').submit(function(event) {

            // JN: Prevent default
            event.preventDefault();

            // JN: Check to see if something is already trying to do this
            if (updatingEventInProgress) {
                return;
            }

            // Handle the old email switcharo
            var currentEmailField = $("#organiser").val();
            $("#organiser").val(eventList.filter(function(i,n){ return i.Event_ID == $("#eventID").val()})[0].Organiser);
            // JN: Prevent repeat presses
            updatingEventInProgress = true;

            // JN: Prepare time formats
            $("#startTime").val(moment($("#startDateStaging").val()+" "+$("#startTimeStaging").val(),"YYYY-MM-DD HH:mm").format("YYYY-MM-DD HH:mm:SS"));
            $("#endTime").val(moment($("#endDateStaging").val()+" "+$("#endTimeStaging").val(),"YYYY-MM-DD HH:mm").format("YYYY-MM-DD HH:mm:SS"));

            // JN: Send post request with the form data
            $.post("api/UpdateEvent.php", $('#eventsForm').serialize() , function(data, status) {

                try {
                    // JN: Parse the result
                    response = JSON.parse(data);

                    // JN: Check the response of the request
                    if (response.wasSuccessful == 1) {
                        // JN: Change the value back to the
                        $("#organiser").val(currentEmailField);
                        // JN: Set the headings back to the default for the page
                        ResetRightPanelHeading();

                        // JN: Refresh the user list
                        RefreshEventsList();

                        // JN: Give feedback to the user
                        alert(response.message);
                    } else {
                        // JN: Change the value back to the
                        $("#organiser").val(currentEmailField);
                        // JN: Display the error message that has been passed through
                        console.log(data);

                        // JN: Give feedback to the user
                        alert(response.message);
                    }
                } catch (err){
                    // JN: Change the value back to the
                    $("#organiser").val(currentEmailField);
                    console.log(data); // JN: For debugging
                }
                updatingEventInProgress = false;

            });
        });
    }

    /**
     * Updates the events list with data retrieved from the database
     *
     * @param data - An array of events in JSON
     * [{Event_ID, Name, Organiser,StartTime,FoodServeTime,EventClosed,SuggestedToClose,LocatedIn,NotificationSent}, ...]
     */
    function UpdateEventsList(data) {
        // Kim: Variable to hold the JSON information
        let obj;

        // Kim: Clear the current list
        $('#eventsList').html('');

        try {
            // Kim: Parse the JSON information
            obj = JSON.parse(data);

            if (obj.wasSuccessful == 1) {
                eventList = obj.data;
                filteredEventList = obj.data;
                FilterEventList();
            }
        } catch (err) {
            console.log(err);
            console.log(data);
        }
    }

    /**
     * Adds each data entry into the html for the page.
     *
     * Calls ShowEventPage(1), which displays the first page of content in the list.
     * Calls SelectEventFromEvnetsList, ensuring that all of the events are clickable.
     * */
    function PopulateEventTable(data) {
        data.forEach(AddRowToEventsList);
        ShowEventPage(1);
        SelectEventFromEventsList();
    }

    /**
     * Displays the given page number of list items.
     *
     * Hides any users that are not to be displayed, and shows users that should be displayed.
     * @param pageNumber The number of the page to be shown starting with page 1
     */
    function ShowEventPage(pageNumber) {
        // Hide unwanted, show the correct page
        $('.eventTableEntry').hide();
        $('.eventListPage'+(pageNumber-1)).show();

        // Delete and readd the navigation bar
        $('#EventListPageNav').remove();
        AddPageNumbersToEventList(pageNumber);

        // Set the active page number
        $(".page-item:contains("+pageNumber+")").addClass('active');
    }

    /**
     * Adds page number to the bottom of the event list.
     *
     *
     * Only shows a small range of page numbers based on the currently selected page.
     * @param currentPage The page that is currently selected by the user
     * */
    function AddPageNumbersToEventList(currentPage) {
        var rangeOfNumberDisplayed = 3;

        var amountOfPages = parseInt(filteredEventList.length/MAX_EVENTS_PER_PAGE);

        currentPage = parseInt(currentPage);

        // Add the relevant buttons
        var lowestPageToDisplay = 0;
        var highestPageToDisplay = amountOfPages;

        if (currentPage < lowestPageToDisplay + rangeOfNumberDisplayed +1) {
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
            var htmlToAdd = "<nav id='EventListPageNav' aria-label=\"Page navigation example\"><ul class=\"pagination justify-content-center\"><li id='previousButton' class='page-item'><a class=\"page-link\" href=\"#\">Previous</a></li>";
            for (var i = lowestPageToDisplay; i <= highestPageToDisplay; i++) {
                if (!(parseInt(filteredEventList.length % MAX_EVENTS_PER_PAGE) == 0 && i == highestPageToDisplay)) {
                    htmlToAdd += '<li class="page-item"><a class="page-link" href="#">' + (i + 1) + '</a></li>';
                }
            }
            htmlToAdd += "<li id='nextButton' class=\"page-item text-center\"><a class=\"page-link\" href=\"#\">Next</a></li></ul></nav>";

            // Add to the appropriate section
            $("#eventsList").append(htmlToAdd);
        }
        $("#nextButton").width($("#previousButton").width());

        // Set the handler for when a page button is clicked
        $(".page-link").click(function() {
            var selectedValue = $(this).text();

            if (selectedValue === "Previous") {
                if (parseInt(currentPage) > 1) {
                    ShowEventPage(parseInt(currentPage) - 1);
                }
            } else if (selectedValue === "Next") {
                if (currentPage <= amountOfPages  && (!(parseInt(filteredEventList.length % MAX_EVENTS_PER_PAGE) == 0 && currentPage == highestPageToDisplay))) {
                    ShowEventPage(parseInt(currentPage) + 1);
                } else {
                    ShowEventPage(currentPage);
                }
            } else {
                ShowEventPage(selectedValue);
            }
        });
    }

    /**
     * Adds a single event as a new row in the events list
     *
     * Ensures that the event is added to the correct page.
     * @param event - JSON for a single event's information
     */
    function AddRowToEventsList(event, index) {
        // Kim: Variables to hold the relevant information from the JSON
        let $id = event.Event_ID;
        let $name = event.Name;
        var classToAdd = "eventListPage"+parseInt(index/MAX_EVENTS_PER_PAGE);

        $('#eventsList').append("<tr class='"+classToAdd+" eventTableEntry'><td class='id' hidden>"+$id+"</td><td>"+$name+"</td></tr>");
    }

    /**
    *   Handles the deletion of an event.
     *
     *   Prompts the user for confirmation before continueing with the deletion.
     *   Sends a POST request to api/DeleteEvent.php to handle the deletion from the application.
    *   @param id The id of the event to be deleted.
    **/
    function DeleteEvent(id) {
        // JN: Prepare data to send
        var confirmation = confirm("Are you sure you would like to delete this event?");
        var sending = {};
        sending.id = id;

        if (confirmation == true) {
            // JN: Make the post request
            $.post("./api/DeleteEvent.php", sending, function (data) {
                try {
                    // JN: Attempt JSON
                    var dataJSON = JSON.parse(data);

                    if (dataJSON.wasSuccessful) {
                        // JN: Reset
                        $("#RightPanel").html("");

                        RefreshEventsList();

                        ResetRightPanelHeading();

                        alert(dataJSON.message);
                    } else {
                        alert(dataJSON.message);
                    }
                } catch (err) {
                    console.log("Was unable to delete the event");
                    console.log(data);
                    console.log(err);
                    alert("Something went wrong while attempting to delete the event.");
                }
            });
        }
    }

    /**
     * Updates the displayed options using the currently selector filtering options.
     */
    function FilterEventList() {
        // Setup variables from the correct fields.
        var searchBarInput = $('#search-bar').val();
        var filterList = jQuery.extend(true, [], eventList);
        var isClosedFilter = $("#filterOptionFinishedEvents").is(":checked");
        var sortingOption = $("#filterOptionSortType").val();

        // Handle the filtering
        if (searchBarInput != "") {
            filterList = FilterEventListByName(filterList, searchBarInput);
        }

        filterList = FilterEventListPastClosed(filterList,isClosedFilter);

        filterList = FilterEventListBetweenDates(filterList);

        filterList = SortEventListByOption(filterList, parseInt(sortingOption));

        // Update the displayed event list.
        $('#eventsList').html("");
        filteredEventList = filterList;
        PopulateEventTable(filterList);
    }

    /**
     * Filters a given event list by name
     *
     * @param listToFilter The event list to be filtered
     * @param byName The name that is being searched for
     * @returns Array The filtered array
     */
    function FilterEventListByName(listToFilter, byName){
        var filteredList = [];
        filteredList = listToFilter.filter(function(data) {
            return data.Name.toLowerCase().indexOf(byName.toLocaleLowerCase()) >= 0;
        });

        return filteredList;
    }

    /**
     * Filter Event List Past Closed
     *
     * @param listToFilter The event list to be filtered
     * @param hasClosed Indicates whether closed events should be included.
     * @returns Array The filtered array of events
     */
    function FilterEventListPastClosed(listToFilter, hasClosed) {
        var currentTime = moment();
        if (hasClosed) {
            return listToFilter
        } else {
            filteredList = [];
            filteredList = listToFilter.filter(function(data) {
               return moment(data.EventClosed, "YYYY-MM-DD HH:mm:SS") > currentTime;
            });
            return filteredList;
        }
    }

    /**
     * Filter Event List Between Dates
     *
     * @param listToFilter The list of events to be filtered
     * @returns Array The filtered array of events
     */
    function FilterEventListBetweenDates(listToFilter) {
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
                isIncluded = moment(data.StartTime, "YYYY-MM-DD HH:mm:SS").isSameOrAfter(startTime, "day");
            }
            if (endTime != "" && isIncluded == true) {
                isIncluded = moment(data.StartTime, "YYYY-MM-DD HH:mm:SS").isSameOrBefore(endTime, "day");
            }
            return isIncluded;
        });
        return filteredList;
    }

    /**
     * Sort Event List By Option
     *
     * @param listToSort The list of events to be sorted
     * @param sortOption The type of sort, indicated by a numerical value
     * @returns Array The sorted list of events, sorted by name
     */
    function SortEventListByOption(listToSort, sortOption) {
        let compareFunction;
        switch(sortOption) {
            case 1: compareFunction = function (a, b) {
                    if (moment(a.StartTime, "YYYY-MM-DD HH:mm:SS").isSameOrAfter(moment(b.StartTime, "YYYY-MM-DD HH:mm:SS"), "minute")) {
                        return -1;
                    } else {
                        return 1;
                    }
                }; break;
            case 2: compareFunction = function (a, b) {
                if (moment(a.StartTime, "YYYY-MM-DD HH:mm:SS").isSameOrBefore(moment(b.StartTime, "YYYY-MM-DD HH:mm:SS"), "minute")) {
                    return -1;
                } else {
                    return 1;
                }
            }; break;
            case 3:compareFunction = function (a, b) {
                return StringNoCaseCompare(a.Name, b.Name);
            }; break;
            case 4:compareFunction = function (a, b) {
                return StringNoCaseCompare(b.Name, a.Name);
            }; break;
            default: return listToSort;
        }
        return listToSort.sort(compareFunction);
    }

    /**
     * String No Case Compare
     *
     * A helper function for SortEventListByOption
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
     * @description sets all of the filtering options on the page back to thier default values
     */
    function ResetFilteringOptions() {
        $('#search-bar').val("");
        $("#filterOptionFinishedEvents").prop('checked', false);
        $("#filterOptionSortType").val(1);
        $("#filterOptionStartDate").val("");
        $("#filterOptionEndDate").val("");
        FilterEventList();
    }
</script>
</html>
