<!--
Template for the feedback right panel.
-->
<!--Section heading-->
<h3 class="text-center pt-1" id="feedbackFormHeading">Selected Feedback</h3>

<!--Form for responding to feedback items-->
<form id="feedbackForm" method="post" action="">

    <!--Feedback ID-->
    <div class="form-group">
        <input class="id" id="feedbackID" name="feedbackID" readonly hidden>
    </div>

    <!--Submitted by-->
    <div class="form-group">
        <label class="form-text" for="feedbackSubmittedBy">Submitted by</label>
        <input class="form-control" id="feedbackSubmittedBy" type="text" name="feedbackSubmittedBy" readonly>
    </div>

    <!--Submitted on-->
    <div class="form-group">
        <label class="form-text" for="feedbackSubmittedTime">Submitted on</label>
        <input class="form-control" id="feedbackSubmittedTime" type="text" name="feedbackSubmittedTime" readonly>
    </div>


    <!--Feedback message-->
    <div class="form-group">
        <label class="form-text" for="feedbackMessage">Message</label>
        <textarea class="form-control" id="feedbackMessage" readonly></textarea>
    </div>

    <!--Response buttons-->
    <div class="form-group text-center" id="feedbackFormButtons">
    </div>
</form>