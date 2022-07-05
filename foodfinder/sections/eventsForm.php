<!--
Template for the event right panel.
-->


<!--Section heading-->
<h3 class="text-center pt-1">Event Details</h3>

<!--Form for responding to events-->
<form id="eventsForm" method="post" action="">

    <!--Event ID-->
    <div class="form-group">
        <input class="id" id="eventID" name="Event_ID" readonly hidden>
    </div>

    <!--Event name-->
    <div class="form-group">
        <label for="eventName" class="text-center">Name</label>
        <input  class="form-control" id="eventName" name="Name" readonly required></input>
    </div>

    <!--Event Creator-->
    <div class="form-group">
        <label for="organiser" class="text-center">Creator</label>
        <input  id="organiser" class="form-control"  name="Organiser" readonly required></input>
    </div>

    <!--Campus-->
    <div class="form-group">
        <label for="campus" class="text-center">Campus</label>
        <select class="form-control" id="campus" name="Campus" readonly required>

        </select>
    </div>

    <!--Located in-->
    <div class="form-group">
        <label for="locatedIn" class="text-center">Room/Building</label>
        <input  class="form-control" id="locatedIn" name="LocatedIn" readonly required/>
    </div>

    <!--Start time-->

    <div class="form-group w-100 d-inline-flex">
        <div class="w-50">
            <label for="startDateStaging" class="text-center">Start Date</label>
            <input type="date" class="form-control" id="startDateStaging" name="startDateStaging" readonly required/>
        </div>
        <div class="w-50">
            <label for="startTimeStaging" class="text-center">Start Time</label>
            <input type="time" class="form-control"  id="startTimeStaging" name="startTimeStaging" readonly required/>
        </div>
    </div>

    <!--Finish time-->
    <div class="form-group w-100 d-inline-flex">
        <div class="w-50">
            <label for="endDateStaging" class="text-center">Finish Date</label>
            <input type="date" class="form-control" id="endDateStaging" name="endDateStaging" readonly required/>
        </div>
        <div class="w-50">
            <label for="endTimeStaging" class="text-center">Finish Time</label>
            <input type="time" class="form-control" id="endTimeStaging" name="endTimeStaging" readonly required/>
        </div>
    </div>

    <!--Hidden Start Time-->
    <div class="form-group">
        <input class="form-control" id="startTime" name="StartTime" readonly hidden/>
    </div>

    <!--Hidden End Time-->
    <div class="form-group">
        <input class="form-control" id="endTime" name="EventClosed" readonly hidden/>
    </div>

    <div class="form-group text-center" id="eventFormButtons">
    </div>
</form>