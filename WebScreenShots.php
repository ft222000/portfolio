<!DOCTYPE html>
<html>
<?php include('HeadFile.php'); ?>

<body>
    <?php include('Header.php'); ?>
    <?php include('Menu.php'); ?>
    <?php include('FoodFinderSubMenu.php'); ?>

    <div class="container">
        <div class="row">

            <?php
    $files = scandir("./images/WebPortal/");
    foreach($files as $file) {
        if ($file == "." || $file == "..") {

        } else {
            echo "<div class=col>";
            echo "<img class=web-shots src=./images/WebPortal/$file >";
            echo "</div>";
        }

    }
    ?>
        </div>
    </div>
</body>

</html>