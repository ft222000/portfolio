<?php if(1==1):
 
    ?>
<div>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark navbar-sticky-top" id="navbar">
        <!--navbar header-->
        <a class="navbar-brand text-white" href="homepage.php">
            KimmyToday
        </a>

        <!-- navbar-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="#">Resume</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="K-foodfinder.php" onclick="showPopup()">FoodFinder Project</a>
            </li>
            <li class="nav-item">

                <a class="nav-link" href="Tree-Tasmania.php">TREE-Tasmania Website</a>
            </li>
            <li class="nav-item">

                <a class="nav-link" href="#">FlockedEvent Website</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="contact me.php">Contact Me</a>
            </li>
        </ul>
    </nav>
</div>
<?php    //include('AgreeForm.php'); ?>
<script>
// when page loads
// document.getElementById('navbar').classList.add('collapse');
// $(document).ready(function(){ 

function showPopup(){
    document.getElementById("btn1").disabled = true;
    //chcek if the EULA has shown to users
    // if(localStorage.getItem('popState') !='shown' )
    // {
    //     // show EUA popup
        //$("#popModal").modal('show');
    //     alert("Hello! I am an alert box!!");
 
    // }
    // else
    // {
    //    
    // }
 
}


     
</script>
<?php endif;?>