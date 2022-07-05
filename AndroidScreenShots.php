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
    $files = scandir("./images/Application/Android/");
    foreach($files as $file) {
        if ($file == "." || $file == "..") {

        } else {
            echo "<div class='col shots' >";
            echo "<img class=images src=./images/Application/Android/$file >";
            echo "</div>";
        }

    }
    ?>
        </div>
    </div>
</body>

</html>