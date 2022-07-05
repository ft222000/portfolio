<?php
/**
 * Allows users to generate reports about information in the database,
 */
require_once('util/Dbconnection.php');
require_once('util/session.php');
?>
<html>
    <head>
        <title>Food Finder | Reports</title>
        <link rel="stylesheet" href="styles/Nav-tab.css" />
        <link rel="stylesheet" href="styles/Font.css" />
    </head>
    <body>
        <!-- JH: Navigation Bar -->
        <?php
            require("./util/navigationBar.php");
        ?>
        <div class="container-fluid">

            <!-- JH: Body Section -->
                <div class="row">

                <!-- Option Side -->
                <div class="col-sm border" id="leftPanel">

                    <!-- JH: Section heading -->
                    <h3 class="p-1 text-center">Report Options</h3>


                    <!-- JH: List of report options -->
                    <div class="row-left">

                        <!-- JH: List of report tabs -->
                        <ul class="nav nav-tabs nav-pills nav-justified justify-content-center" id="tab-list">
                            <li class="nav-item"><a href="#tabEvents" class="nav-link" role="tab" data-toggle="tab">Events</a></li>
                            <li class="nav-item"><a href="#tabUsers" class="nav-link" role="tab" data-toggle="tab">Users</a></li>
                            <li class="nav-item"><a href="#tabUsage" class="nav-link" role="tab" data-toggle="tab">Usage</a></li>
                            <li class="nav-item"><a href="#tabFeedback" class="nav-link" role="tab" data-toggle="tab">Feedback</a></li>
                            <!--<li><a href="#tabCampuses" class="nav-link disabled">Campuses</a></li>-->
                        </ul>
                        <div class="tab-content">

                            <!-- JH: Options for the Events Tab -->
                            <div role="tab-pane" class="tab-pane" id="tabEvents">
                                <div class="container">
                                    <div class="row">

                                        <!-- JH: date picker -->
                                        <div class="col-6 align-self-center">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col">
                            						    <label>Date Start</label>
                                                        <input type="date" class="form-control" id="date-time-from Event" name="startTimeStaging" required>
                            						</div>
                                                </div>
                        						<div class="row">
                                                    <div class="col">
                            					        <label>Date End</label>
                                                        <input type="date" class="form-control" id="date-time-to Event" name="startTimeStaging" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- JH: checkbox for including deleted events -->
                                        <div class="col-6 align-self-center">
                                            <div class="container">
                                                <div class="row offset-md-2">
                                                    <div class="col">
                                                    <label>
                                                        <input type="checkbox" class="form-check-input" id="deleted Events"> Deleted Events Included
                                                    </label> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- JH: button for generating reports -->
                                <div class="row justify-content-center p-2">
									<button class="btn btn-primary" id="generateReportBtn" onclick="CallReportData()">Generate Report</button>
								</div>                        
                            </div>

                            <!-- JH: Options for the Users Tab -->
                            <div role="tab-pane" class="tab-pane" id="tabUsers">
                                <div class="container">
                                    <div class="row">

                                        <!-- JH: date picker -->
                                        <div class="col-6 align-self-center">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col">
                    									<label>Date Start</label>
                                                        <input type="date" class="form-control" id="date-time-from User" name="startTimeStaging" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col">
                    									<label>Date End</label>
                                                        <input type="date" class="form-control" id="date-time-to User" name="startTimeStaging" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- JH: checkboxes for selecting different permission levels -->
                                        <div class="col-6 align-self-center">
                                            <div class="container">

                                                <div class="row offset-md-2">
                                                    <div class="col">
                                                        <label>
                                                            <input type="checkbox" class="form-check-input" id="user-type GU" checked>General Users
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row offset-md-2">
                                                    <div class="col">
                                                        <label>
                                                            <input type="checkbox" class="form-check-input" id="user-type EO" checked>Event Organisers
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row offset-md-2">
                                                    <div class="col">
                                                        <label>
                                                            <input type="checkbox" class="form-check-input" id="user-type ST" checked>Sustainability Team
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- JH: button for generating reports -->
                                <div class="row justify-content-center p-2">
									<button class="btn btn-primary" id="generateReportBtn" onclick="CallReportData()">Generate Report</button>
								</div>
                            </div>

                            <!-- JH: Options for the Usage Tab -->
                            <div role="tab-pane" class="tab-pane" id="tabUsage">
                                <div class="container">
                                    <div class="row">

                                    <!-- JH: date picker -->
                                        <div class="col-6 align-self-center">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col">
                                                        <label>Date Start</label>
                                                        <input type="date" class="form-control" id="date-time-from Usage" name="startTimeStaging" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <label>Date End</label>
                                                        <input type="date" class="form-control" id="date-time-to Usage" name="startTimeStaging" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- JH: button for generating reports -->
                                <div class="row justify-content-center p-2">
                                    <button class="btn btn-primary" id="generateReportBtn" onclick="CallReportData()">Generate Report</button>
                                </div>
                            </div>

                            <!-- JH: Options for the Feedback Tab -->
                            <div role="tab-pane" class="tab-pane" id="tabFeedback">
                                <div class="container">
                                    <div class="row">

                                        <!-- JH: date picker -->
                                        <div class="col-6 align-self-center">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col">
                                                        <label>Date Start</label>
                                                        <input type="date" class="form-control" id="date-time-from Feedback" name="startTimeStaging" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <label>Date End</label>
                                                        <input type="date" class="form-control" id="date-time-to Feedback" name="startTimeStaging" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <!-- JH: checkbox for including deleted feedback -->
                                    <div class="col-6 align-self-center">
                                        <div class="container">
                                            <div class="row offset-md-2">
                                                <div class="col">
                                                    <label>
                                                        <input type="checkbox" class="form-check-input" id="deleted Feedback"> Deleted Feedback Included
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- JH: button for generating reports -->
                                    <div class="row justify-content-center p-2">
                                        <button class="btn btn-primary" id="generateReportBtn" onclick="CallReportData()">Generate Report</button>
                                    </div>
                                </div>
                            </div>

                            </div>
                            -->
                        </div>
                    </div>
                </div>

                <!-- JH: Generated Reports Side -->
                <div class="col-sm border" id="RightPanel">
                    <h3 id="GenerateReportTitle" class="p-1 text-center">Reports</h3>
                    <!-- JH: Shows report after Generate Report button is clicked-->
                    <div class="container">
                        <div id="reportTitle"><h5 class="text-center">Select a report to generate</h5></div>
                        <div class="text-center invisible" id="reportDetails"></div>
                    </div>

                    <!-- JH: button to download a generated report -->
                    <div class="row justify-content-center invisible p-2" id="downloadReportBtn">
                        <button class="btn btn-primary" onclick="exportTableToCSV('FoodFinderReport.csv')">Download</button>
                    </div>
                </div>
            </div>

            <!-- JH: table of hidden raw data from the generated report
             this is the table that is downloaded from web portal.-->
            <div class="invisible" id="reportData"></div>
        </div>
    </body>

    <script type="text/javascript">

        $('a[aria-expanded="true"]').addClass("text-white");


        /**
         * Handles the event caused by the generate reports button.
         *
         * Calls the appropriate API file using a post request
        */
        function CallReportData()
        {
            var reportTypeField = document.getElementsByClassName('tab-pane active');
            var reportType = reportTypeField[0].id;
            var sending = {};

            /* JH: Collects the inputs; sends a request and inputs to
             *   another file to call the database, and then sends the
             *   returned json file to a function to present the data
             */
            if(reportType == "tabEvents")
            {
                sending.start = document.getElementById('date-time-from Event').value;
                sending.end = document.getElementById('date-time-to Event').value;
                sending.deleted = document.getElementById('deleted Events').checked;
                if(sending.start == "" || sending.end == "")
                {
                    alert("Both date entries are required for the event report.");
                } 
                else 
                {
                    $.post('api/ReportLoadEvents.php', sending, CheckingDataEventReport);
                }
            }
            if(reportType == "tabUsers")
            {
                sending.start = document.getElementById('date-time-from User').value;
                sending.end = document.getElementById('date-time-to User').value;
                sending.level1 = document.getElementById('user-type GU').checked;
                sending.level2 = document.getElementById('user-type EO').checked;
                sending.level3 = document.getElementById('user-type ST').checked;
                if(sending.start == "" || sending.end == "")
                {
                    alert("Both date entries are required for the user report.");
                } 
                else 
                {
                    sending.end += " 23:59:59";
                    sending.start += " 00:00:01";
                    $.post('api/ReportLoadUsers.php', sending, CheckingDataUsersReport);
                }
            }
            if(reportType == "tabUsage")
            {
                sending.start = document.getElementById('date-time-from Usage').value;
                sending.end = document.getElementById('date-time-to Usage').value;
                if(sending.start == "" || sending.end == "")
                {
                    alert("Both date entries are required for the usage report.");
                } 
                else 
                {
                    sending.end += " 23:59:59";
                    sending.start += " 00:00:01";
                    $.post('api/ReportLoadUsage.php', sending, CheckingDataUsageReport);
                }
            }
            if(reportType == "tabFeedback")
            {
                sending.start = document.getElementById('date-time-from Feedback').value;

                sending.end = document.getElementById('date-time-to Feedback').value;

                sending.deleted = document.getElementById('deleted Feedback').checked;
                if(sending.start == "" || sending.end == "")
                {
                    sending.end += " 23:59:59";
                    sending.start += " 00:00:01";
                    alert("Both date entries are required for the feedback report.");
                }
                else
                {
                    sending.end += " 23:59:59";
                    sending.start += " 00:00:01";
                    console.log(sending);
                    $.post('api/ReportLoadFeedback.php', sending, CheckingDataFeedbackReport);
                }
            }
        }

        /**
         * Checks to ensure that data was returned for the event report.
        */
        function CheckingDataEventReport(data)
        {

            var obj = JSON.parse(data);
            if (obj == "")
            { 
                alert("No events with those parameters were found in the database.");
            } 
            else
            {
                GenerateEventReport(data);
            }
        }

        /**
         * Removes the title from the page
         */
        function RemoveGeneratedReportTitle() {
            $('#GenerateReportTitle').remove();
        }

        /**
         * Checks to ensure that data was returned for the Users report.
         */
        function CheckingDataUsersReport(data)
        {
            var obj = JSON.parse(data);
            if (obj == "")
            { 
                alert("No users with those parameters were found in the database.");
            } 
            else
            {
                GenerateUsersReport(data);
            }
        }

        /**
         * Checks to ensure that data was returned for the Usage report.
         */
        function CheckingDataUsageReport(data)
        {
            var obj = JSON.parse(data);
            if (obj == "")
            { 
                alert("No usage data with those parameters were found in the database.");
            } 
            else
            {
                GenerateUsageReport(data);
            }
        }

        /**
         * Checks to ensure that data was returned for the feedback report.
         */
        function CheckingDataFeedbackReport(data)
        {
            console.log(data);
            var obj = JSON.parse(data);
            if (obj == "")
            {
                alert("No feedback data with those parameters were found in the database.");
            }
            else
            {
                GenerateFeedbackReport(data);
            }
        }

        /**
         * Populates the right panel with the event report data.
         *
         * Also fills in an invisible table which is used to generate the CSV file.
         */
        function GenerateEventReport(data)
        {
            var reportGenData = document.getElementById('reportData');
            var reportGenDetails = document.getElementById('reportDetails');
            var obj = JSON.parse(data);

            var collectCampusNames = [];
            var campusName, testBool;

            RemoveGeneratedReportTitle();

            // JH: creates an array of unique campus names found from the called data
            for (var i = 0; i < obj.length; i++)
            {
                campusName = obj[i].Campus;
                testBool = false;
                for (var j = 0; j < collectCampusNames.length; j++)
                {
                    if (campusName == collectCampusNames[j])
                    {
                        testBool = true;
                    }
                }
                if (testBool == false)
                {
                    collectCampusNames[i] = campusName;
                }
            }

            var dateStr = moment(obj[0].Event_Time, 'YYYY-MM-DD HH:mm:ss').format('DD-MM-YYYY HH:mm');
            console.log(dateStr);

            // JH: Create table of useful Data to be displayed
            var dataTable= "<table border=1  cellpadding='5' style='width:100%'>" +
                "<tr><td>Number of events</td><td>" + obj.length + "</td></tr>" +
                "<tr><td>The earliest event date</td><td>" + moment(obj[0].Event_Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>" + 
                "<tr><td>The latest event date</td><td>" + moment(obj[obj.length-1].Event_Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>"; 
            for (var m = 0; m < collectCampusNames.length; m++)
            {
                if (collectCampusNames[m] != undefined)
                {
                    dataTable += "<tr><td>Number of " + collectCampusNames[m] + " events</td><td>" + 
                        obj.filter(value => value.Campus === collectCampusNames[m]).length + "</td></tr>";
                }
            }
            dataTable += "</table>";

            // JH: Create table of raw Data which can be downloaded
            var rawDataTable = "<table class='d-none'>" +
                "<tr><td>Event Start Time</td>" +
                "<td>Event Name</td>" +
                "<td>Campus Location</td></tr>";
            for (var i = 0; i < obj.length; i++)
            {
                rawDataTable += "<tr>" +
                    "<td>" + obj[i].Event_Time + "</td>" +
                    "<td>" + obj[i].Name + "</td>" +
                    "<td>" + obj[i].Campus + "</td></tr>";
            }
            rawDataTable += "</table>";

            // JH: Now alter the generated report side of the page with generated data
            reportGenDetails.innerHTML = dataTable;
            reportGenData.innerHTML = rawDataTable;
            reportTitle.innerHTML = "<h3 class=\"text-center p-1\">Events Report</h3>";
            $("#downloadReportBtn").removeClass("invisible");
            $("#reportDetails").removeClass("invisible");
        }

        /**
         * Populates the right panel with the users report data.
         *
         * Also fills in an invisible table which is used to generate the CSV file.
         */
        function GenerateUsersReport(data)
        {
            var reportGenData = document.getElementById('reportData');
            var reportGenDetails = document.getElementById('reportDetails');
            var obj = JSON.parse(data);

            var collectCampusNames = [];
            var collectPermissionLevels = [];
            var campusName, testBool, permissionName;

            RemoveGeneratedReportTitle();

            // creates an array of unique campus names found from the called data
            for (var i = 0; i < obj.length; i++)
            {
                campusName = obj[i].Campus;
                testBool = false;
                for (var j = 0; j < collectCampusNames.length; j++)
                {
                    if (campusName == collectCampusNames[j])
                    {
                        testBool = true;
                    }
                }
                if (testBool == false)
                {
                    collectCampusNames[i] = campusName;
                }
            }

            // creates an array of unique permission levels found from the called data
            for (var i = 0; i < obj.length; i++)
            {
                permissionName = obj[i].Permission;
                testBool = false;
                for (var j = 0; j < collectPermissionLevels.length; j++)
                {
                    if (permissionName == collectPermissionLevels[j])
                    {
                        testBool = true;
                    }
                }
                if (testBool == false)
                {
                    collectPermissionLevels[i] = permissionName;
                }
            }

            // Create table of useful Data to be displayed
            var dataTable= "<table border=1 cellpadding='5' style='width:100%'>" +
                "<tr><td>Number of users</td><td>" + obj.length + "</td></tr>" +
                "<tr><td>When the oldest user was created</td><td>" + moment(obj[0].Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>" +
                "<tr><td>When the latest user was created</td><td>" + moment(obj[obj.length-1].Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>"; 

            for (var m = 0; m < collectCampusNames.length; m++)
            {
                if (collectCampusNames[m] != undefined)
                {
                    dataTable += "<tr><td>Number of " + collectCampusNames[m] + " users</td><td>" +
                        obj.filter(value => value.Campus === collectCampusNames[m]).length + "</td></tr>";
                }
            }
            for (var m = 0; m < collectPermissionLevels.length; m++)
            {
                if (collectPermissionLevels[m] != undefined)
                {
                    dataTable += "<tr><td>Number of " + collectPermissionLevels[m] + " users</td><td>" + 
                        obj.filter(value => value.Permission === collectPermissionLevels[m]).length + "</td></tr>";
                }
            }
            dataTable += "</table>";

            // Create table of raw Data which can be downloaded
            var rawDataTable = "<table class='d-none'>" +
                "<tr><td>Signup Time</td>" +
                "<td>User Primary Campus</td>" +
                "<td>User Permission Level</td></tr>";
            for (var i = 0; i < obj.length; i++)
            {
                rawDataTable += "<tr>" +
                    "<td>" + obj[i].Time + "</td>" +
                    "<td>" + obj[i].Campus + "</td>" +
                    "<td>" + obj[i].Permission + "</td></tr>";
            }
            rawDataTable += "</table>";

            // Now alter the generated report side of the page with generated data
            reportGenDetails.innerHTML = dataTable;
            reportGenData.innerHTML = rawDataTable;
            reportTitle.innerHTML = "<h3 class=\"text-center p-1\">Users Report</h3>";
            $("#downloadReportBtn").removeClass("invisible");
            $("#reportDetails").removeClass("invisible");
        }

        /**
         * Populates the right panel with the usage report data.
         *
         * Also fills in an invisible table which is used to generate the CSV file.
         */
        function GenerateUsageReport(data)
        {
            var reportGenData = document.getElementById('reportData');
            var reportGenDetails = document.getElementById('reportDetails');
            var obj = JSON.parse(data);

            var collectCampusNames = [];
            var campusName, testBool;

            RemoveGeneratedReportTitle();

            // creates an array of unique campus names found from the called data
            for (var i = 0; i < obj.length; i++)
            {
                campusName = obj[i].Campus;
                testBool = false;
                for (var j = 0; j < collectCampusNames.length; j++)
                {
                    if (campusName == collectCampusNames[j])
                    {
                        testBool = true;
                    }
                }
                if (testBool == false)
                {
                    collectCampusNames[i] = campusName;
                }
            }

            // Create table of useful Data to be displayed
            var dataTable= "<table border=1  cellpadding='5' style='width:100%'>" +
                "<tr><td>Number of users expressing interest</td><td>" + obj.length + "</td></tr>" +
                "<tr><td>The earliest date interest was expressed</td><td>" + moment(obj[0].Event_Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>" + 
                "<tr><td>The latest date interest was expressed</td><td>" + moment(obj[obj.length-1].Event_Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>"; 
            for (var m = 0; m < collectCampusNames.length; m++)
            {
                if (collectCampusNames[m] != undefined)
                {
                    dataTable += "<tr><td>Number of interested users at " + collectCampusNames[m] + "</td><td>" + 
                        obj.filter(value => value.Campus === collectCampusNames[m]).length + "</td></tr>";
                }
            }
            dataTable += "</table>";

            // Create table of raw Data which can be downloaded
            var rawDataTable = "<table class='d-none'>" +
                "<tr><td>Event Start Time</td>" +
                "<td>Campus Location</td></tr>";
            for (var i = 0; i < obj.length; i++)
            {
                rawDataTable += "<tr>" +
                    "<td>" + obj[i].Event_Time + "</td>" +
                    "<td>" + obj[i].Campus + "</td></tr>";
            }
            rawDataTable += "</table>";

            // Now alter the generated report side of the page with generated data
            reportGenDetails.innerHTML = dataTable;
            reportGenData.innerHTML = rawDataTable;
            reportTitle.innerHTML = "<h3 class=\"text-center p-1\">Usage Report</h3>";
            $("#downloadReportBtn").removeClass("invisible");
            $("#reportDetails").removeClass("invisible");
        }

        /**
         * Populates the right panel with the feedback report data.
         *
         * Also fills in an invisible table which is used to generate the CSV file.
         */
        function GenerateFeedbackReport(data)
        {
            var reportGenData = document.getElementById('reportData');
            var reportGenDetails = document.getElementById('reportDetails');
            var obj = JSON.parse(data);

            var collectCampusNames = [];
            var campusName, testBool;

            RemoveGeneratedReportTitle();

            // creates an array of unique campus names found from the called data
            for (var i = 0; i < obj.length; i++)
            {
                campusName = obj[i].Campus;
                testBool = false;
                for (var j = 0; j < collectCampusNames.length; j++)
                {
                    if (campusName == collectCampusNames[j])
                    {
                        testBool = true;
                    }
                }
                if (testBool == false)
                {
                    collectCampusNames[i] = campusName;
                }
            }

            console.log(obj);

            // Create table of useful Data to be displayed
            var dataTable= "<table border=1  cellpadding='5' style='width:100%'>" +
                "<tr><td>Number of feedback entries</td><td>" + obj.length + "</td></tr>" +
                "<tr><td>The earliest date of feedback</td><td>" + moment(obj[0].Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>" +
                "<tr><td>The latest date of feedback</td><td>" + moment(obj[obj.length-1].Time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm DD-MM-YYYY') + "</td></tr>";
            for (var m = 0; m < collectCampusNames.length; m++)
            {
                if (collectCampusNames[m] != undefined)
                {
                    dataTable += "<tr><td>Number of feedback entries from " + collectCampusNames[m] + "</td><td>" +
                        obj.filter(value => value.Campus === collectCampusNames[m]).length + "</td></tr>";
                }
            }
            dataTable += "</table>";


            // JH: Create table of raw Data which can be downloaded
            var rawDataTable = "<table class='d-none'>" +
                "<tr><td>Time Stamp</td>" +
                "<td>Campus Location</td>" +
                "<td>Email Address</td>" +
                "<td>Feedback</td></tr>";
            for (var i = 0; i < obj.length; i++)
            {
                rawDataTable += "<tr>" +
                    "<td>" + obj[i].Time + "</td>" +
                    "<td>" + obj[i].Campus + "</td>" +
                    "<td>" + obj[i].Email + "</td>" +
                    "<td>" + obj[i].Feedback.trim() + "</td></tr>";
            }
            rawDataTable += "</table>";

            // JH: Now alter the generated report side of the page with generated data
            reportGenDetails.innerHTML = dataTable;
            reportGenData.innerHTML = rawDataTable;
            reportTitle.innerHTML = "<h3 class=\"text-center p-1\">Report Report</h3>";
            $("#downloadReportBtn").removeClass("invisible");
            $("#reportDetails").removeClass("invisible");
        }

        /*
        * Downloads the data as a CSV code.
        *
        * Based on code from:
        * https://www.codexworld.com/export-html-table-data-to-csv-using-javascript/
        */ 
        function downloadCSV(csv, filename)
        {
            var csvFile;
            var downloadLink;

            // CSV file
            csvFile = new Blob([csv], {type: "text/csv"});

            // Download link
            downloadLink = document.createElement("a");

            // File name
            downloadLink.download = filename;

            // Create a link to the file
            downloadLink.href = window.URL.createObjectURL(csvFile);

            // Hide download link
            downloadLink.style.display = "none";

            // Add the link to DOM
            document.body.appendChild(downloadLink);

            // Click download link
            downloadLink.click();
        }

        /*
        * Converts html table to csv format
        *
        * Code is based on information from:
        * https://www.codexworld.com/export-html-table-data-to-csv-using-javascript/
        */ 
        function exportTableToCSV(filename)
        {
            var csv = [];
            $("#reportData").removeClass("invisible");
            $("#reportData").removeClass("d-none");

            var rows = document.querySelectorAll("#reportData tr");

            for (var i = 0; i < rows.length; i++)
            {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (var j = 0; j < cols.length; j++)
                {
                    row.push(cols[j].innerText);
                } 
                csv.push(row.join(","));        
            }

            $("#reportData").addClass("invisible");
            $("#reportData").addClass("d-none");

            // Download CSV file
            downloadCSV(csv.join("\n"), filename);
        }
    </script>
</html>

