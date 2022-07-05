<?php
/**
 * Allows users to manage feedback that has been submitted by users.
 */
require_once('util/Dbconnection.php');
?>

<html>
    <head>
        <title>Food Finder | Feedback</title>
        <!--JA: Style for a selected request-->
        <link rel="stylesheet" href="styles/List.css" />
        <link rel="stylesheet" href="styles/Feedback.css" />
        <link rel="stylesheet" href="styles/Font.css" />
    </head>

    <body>
        <!--JA: Navigation bar-->
        <?php
        require('util/navigationBar.php');
        ?>

        <!--JA: Body containing the feedback page-->
        <div class="container-fluid">
            <div class="row">
                <!--JA: Pending feedback section-->
                <div class="col-sm border" id="leftPanel">
                    <!--JA: Section heading-->
                    <h3 class="text-center p-1">Available Feedback</h3>

                    <div class="row px-3 justify-content-center">
                        <input class="form-control w-100" id="search-bar" type="text" onkeyup="FilterFeedbackList()" placeholder="Search..." />
                    </div>

                    <!--JA: Feedback list-->
                    <table class="table table-hover">
                        <tbody id="feedbackList">
                            <!--JA: Rows are populated from a script-->
                        </tbody>
                    </table>
                </div>

                <!--JA: Feedback form section-->
                <div class="col-sm border" id="rightPanel">
                    <!--JA: Empty until a feedback item is selected from the Available feedback section-->
                    <h3 class="text-center p-1">Feedback</h3>
                    <h5 class="text-center">Select a feedback item</h5>
                </div>
            </div>
        </div>
    </body>

    <script>
       const MAX_FEEDBACK_ITEMS_PER_PAGE = 10;
       var feedbackList = null;
       var filteredFeedbackList = null;

       /**
        * On document load
        *
        * Calls RefreshFeedbackList so that the page can be setup ready for use
        */
       $(function() {
           RefreshFeedbackList();
       });

       /**
        * RefreshFeedbackList
        *
        * Makes a GET request for a list of feedback items
        * Handles the response with the UpdateFeedbackList function
        */
       function RefreshFeedbackList() {
           $("#search-bar").val('');
           $.get('api/feedback.php', UpdateFeedbackList);
           SelectFeedbackFromFeedbackList();
       }

       /**
        * SelectFeedbackFromFeedbackList
        *
        * Adds an onClick handler to all rows of the feedback list
        * Highlights a row when selected, and passes it's Feedback_ID to the LoadFeedbackForm function
        */
       function SelectFeedbackFromFeedbackList() {
           $('#feedbackList tr').click(function () {
               // JA: Remove the selected-row class from any currently selected rows
               $('.selected-row').removeClass('selected-row');

               // JA: Add the selected-row class to the selected row
               $(this).addClass('selected-row');

               // JA: Pass the Feedback_ID to another function to display the specific feedback item
               var id = $('.id', this).html();
               LoadFeedbackForm(id);
           })
       }

       /**
        * LoadFeedbackForm
        *
        * @description Load the feedback form in the right panel, and populates it with the selected feedback item's data
        * @param id The Feedback_ID for the selected feedback item to be loaded
        */
       function LoadFeedbackForm(id) {
           // JA: Variable to hold the selected feedback item's data
           var feedback;

           // JA: Clear the right panel of any forms
           $('#rightPanel').html("");

           // JA: Prevent the user from seeing the panel before it has finished loading
           $('#rightPanel').addClass("invisible");

           // JA: Request a copy of the feedback form via http get
           $.get('sections/feedbackForm.php', function (data) {
               // JA: Set the right panel to the retrieved feedback form
               $('#rightPanel').html(data);

               // JA: Request the details for the selected feedback item
               $.get('api/feedback.php?id=' + id, function (data) {
                   // JA: Parse the JSON information
                   obj = JSON.parse(data);
                   if (obj.wasSuccessful != 1) {
                       console.log(obj.message);
                       return;
                   }

                   // JA: Check that only one feedback item is selected
                   if (obj.data.length == 1) {
                       // JA: Get the first element of the JSON array
                       feedback = obj.data[0];

                       // JA: Set the forms fields to match the data of the selected feedback item
                       $('#feedbackID').val(id);
                       $('#feedbackSubmittedBy').val(feedback.EmailAddress);
                       $('#feedbackSubmittedTime').val(feedback.SubmittedTime.toString());
                       $('#feedbackMessage').val(feedback.FeedbackMessage);

                       $("#feedbackFormButtons").append("<input type=\"button\" class=\"btn btn-danger\" value=\"Delete\" onclick=DeleteFeedbackItem("+id+")>");

                       // JA: Show the loaded panel
                       $('#rightPanel').removeClass("invisible");
                   } else {
                       console.log("There was an issue returning the selected feedback item's information");
                   }
               })
           });
       }

       /**
        * LoadDefaultRightSideHeadings
        *
        * @description Replaces everything in the right panel with the default headings for the page
        */
       function LoadDefaultRightSideHeadings() {
           $("#rightPanel").html(
               "<h3 class=\"text-center p-1\">Feedback</h3>\n" +
               "<h5 class=\"text-center\">Select a feedback item</h5>"
           );
       }

       /**
        * UpdateFeedbackList
        *
        * @description Updates the feedback list with data retrieved from the database
        * @param data An array of feedback items in JSON
        * [{User_ID, Feedback_ID, SubmittedTime, IsDeleted, FeedbackMessage>}, ...]
        */
       function UpdateFeedbackList(data) {
           // JA: Variable to hold the JSON information
           var obj;

           // JA: Clear the current list
           $('#feedbackList').html('');

           try {
               // JA: Parse the JSON information from text
               obj = JSON.parse(data);
               if (obj.wasSuccessful) {
                   // JA: Add a row for each feedback item in the JSON object
                   feedbackList = obj.data;
                   filteredFeedbackList = obj.data;
                   PopulateFeedbackList(feedbackList);
               } else {
                   // JA: Output the message from the packet
                   console.log(obj.message);
                   alert(obj.message);
               }
           } catch (err) {
               // JA: Output the entirety of the data for debugging
               console.log(err);
               console.log(data);
               alert("Something went wrong");
           }
       }

       /**
        * PopulateFeedbackList
        *
        * @description A modular function, allowing multiple sections to add content to the feedback list.
        * Note: You should clear the #feedbackList before calling this function
        * @param data The list of feedback items to be added to the feedbackList
        */
       function PopulateFeedbackList(data) {
           data.forEach(AddRowToFeedbackList);
           ShowFeedbackPage(1);
           SelectFeedbackFromFeedbackList();
       }

       /**
        * AddRowToFeedbackList
        *
        * @description Adds a single feedback item as a new row in the feedback list
        * @param feedback JSON for a single feedback item's information
        * @param index The index of the feedback item out of all feedback items being added
        */
       function AddRowToFeedbackList(feedback, index) {
           // JA: Variables to hold the relevant information from the JSON
           let $feedbackID = feedback.Feedback_ID;
           let $emailAddress = feedback.EmailAddress;
           var classToAdd = "feedbackListPage"+parseInt(index/MAX_FEEDBACK_ITEMS_PER_PAGE);
           $('#feedbackList').append("<tr class='"+classToAdd+" feedbackTableEntry'><td class='id' hidden>"+$feedbackID+"</td><td>"+$emailAddress+"</td></tr>");
       }

       /**
        * ShowFeedbackPage
        *
        * @description Hides any feedback items that are not to be displayed, and shows feedback items that should be displayed.
        * @param pageNumber The number of the page to be shown starting with page 1
        */
       function ShowFeedbackPage(pageNumber) {
           // Hide unwanted, show the correct page
           $('.feedbackTableEntry').hide();
           $('.feedbackListPage'+(pageNumber-1)).show();

           // Delete and read the navigation bar
           $('#FeedbackListPageNav').remove();
           AddPageNumbersToFeedbackList(pageNumber);

           // Set the active page number
           $(".page-item:contains("+pageNumber+")").addClass('active');
       }

       /**
        * AddPageNumbersToFeedbackList
        *
        * @description Adds the page numbers to bottom of the feedback list
        * @param currentPage The page that is currently selected by the user
        * */
       function AddPageNumbersToFeedbackList(currentPage) {
           var rangeOfNumberDisplayed = 3;

           currentPage = parseInt(currentPage);

           var amountOfPages = parseInt(filteredFeedbackList.length/MAX_FEEDBACK_ITEMS_PER_PAGE);

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
               var htmlToAdd = "<nav id='FeedbackListPageNav' aria-label=\"Page navigation example\"><ul class=\"pagination justify-content-center\"><li id='previousButton' class='page-item'><a class=\"page-link\" href=\"#\">Previous</a></li>";
               for (var i = lowestPageToDisplay; i <= highestPageToDisplay; i++) {
                   if (!(parseInt(filteredFeedbackList.length % MAX_FEEDBACK_ITEMS_PER_PAGE) == 0 && i == highestPageToDisplay)) {
                       htmlToAdd += '<li class="page-item"><a class="page-link" href="#">' + (i + 1) + '</a></li>';
                   }
               }
               htmlToAdd += "<li id='nextButton' class=\"page-item text-center\"><a class=\"page-link\" href=\"#\">Next</a></li></ul></nav>";

               // Add to the appropriate section
               $("#feedbackList").append(htmlToAdd);
           }

           $("#nextButton").width($("#previousButton").width());
           // Set the handler for when a page button is clicked
           $(".page-link").click(function() {
               var selectedValue = $(this).text();

               if (selectedValue === "Previous") {
                   if (parseInt(currentPage) > 1) {
                       ShowFeedbackPage(parseInt(currentPage) - 1);
                   }
               } else if (selectedValue === "Next") {
                   if (currentPage <= amountOfPages && (!(parseInt(filteredFeedbackList.length % MAX_FEEDBACK_ITEMS_PER_PAGE) == 0 && currentPage == highestPageToDisplay))) {
                       ShowFeedbackPage(parseInt(currentPage) + 1);
                   } else {
                       ShowFeedbackPage(currentPage);
                   }
               } else {
                   ShowFeedbackPage(selectedValue);
               }
           });
       }

       /**
        * DeleteFeedbackItem
        *
        * @description Soft deletes the selected feedback item from the database
        * @param id The id of the feedback item in the database
        */
       function DeleteFeedbackItem(id) {
           var confirmation = confirm("Are you sure you want to delete this feedback?");
           var sending = {};
           sending.id = id;

           if (confirmation == true) {
               $.post("./api/feedback.php", sending, function (data) {
                   try {
                       var dataJSON = JSON.parse(data);
                       if (dataJSON.wasSuccessful) {
                           // Remove the right panel and refresh the feedback list
                           LoadDefaultRightSideHeadings();
                           RefreshFeedbackList();
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
        * FilterFeedbackList
        *
        * @description This function serves as a reaction to a change in the selected filtering options.
        * Checks all possible options to see if they have been selected, then filters by them.
        * Clears and repopulates the feedback list once finished.
        */
       function FilterFeedbackList() {
           var searchBarInput = $('#search-bar').val();
           var filterList = jQuery.extend(true, [], feedbackList);


           if (searchBarInput != "") {
               filterList = FilterFeedbackListByEmail(filterList, searchBarInput);
           }

           console.log(filterList);

           $("#feedbackList").html("");
           filteredFeedbackList = filterList;
           PopulateFeedbackList(filterList);
       }

       /**
        * FilterFeedbackListByEmail
        *
        * @description Filters the feedback list by the given email address and returns the resulting list
        * Does not make any changes to the html being displayed
        * @param listToFilter The list to be filtered
        * @param byEmail The email to filter by
        */
       function FilterFeedbackListByEmail(listToFilter, byEmail) {
           let filteredList = [];
           listToFilter.forEach(function(data, index) {
               if (data.EmailAddress.toLowerCase().indexOf(byEmail.toLowerCase()) >= 0) {
                   filteredList.push(data);
               }
           });
           return filteredList;
       }
    </script>
</html>