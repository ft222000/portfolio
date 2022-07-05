<!DOCTYPE html>
<html>
<?php include('HeadFile.php'); ?>

<body>
    <?php include('Header.php'); ?>
    <?php include('Menu.php'); ?>
    <?php include('FoodFinderSubMenu.php'); ?>

    <div class="container">

        <a class="TreeTitle" href="FoodFinderApplication.apk" data-toggle="tooltip" title="Download the Application" >Food Finder</a>

        <p>
            This project was launched by the Sustainability Team from University of Tasmania.</p>
        <p>
            The application is developed by using Xamarin which is a development kit from Visual studio. The
            application is supported by a web backend, allows the users with the highest privilege
            to manage data without using the application.
            The application is compilable on both Anroid and IOS with one code basis.
        </p>
        <p>
            The objective of this project is to design, develop and document the creation and deployment of a
            mobile application, that aligns with the Sustainability’s Team goals of reducing food waste and
            improving food security for UTAS students. The purpose of this application is to facilitate the
            communication of events which have been over-catered for. The subsequent project product will help
            improve food security for users, whilst also reducing the environmental impacts of waste food being
            sent to landfills.
        </p>
        <p>
            Food Finder addresses the problems by mainly allowing students to receive notifications about
            leftover food.
            However, students can change preferences to suit their natural habits, in order to receive and view
            notifications effectively. Students also can use the embedded map to locate and navigate to events
            effectively.
            Students are allowed to share the sustainability team responsibility to reduce the food wastage.
            They can access information about the sustainability team and communicate with them directly, or
            they can request to be event organisers to manage leftover food.
            In addition, Food Finder helps event organisers to advertise their leftover food by allowing them
            to create events in the application. This will send notifications to the relevant students, so they
            can join the events and consume the leftover food within a consumable time to reduce food waste.
        </p>

    </div>



</body>
<script>
$(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
</html>