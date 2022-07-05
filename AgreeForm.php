<?php
 if(1==1):

 ?>
<div id="popModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Agreement</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          
        </div>
        <div class="modal-body">
          <p align="center">Kim is awesome.</p>
        </div>

        <div class="modal-footer">
            <div class="checkbox">
                <label>
                    I agree
                    <input type="checkbox" class="check_list" id="Check1" onclick="checkBox()" >
                </label>
            </div>
        
            <button type="button" class="btn btn-default" data-dismiss="modal" class="close1">Close</button>
            
            <button type="button" class="btn btn-primary" id="btn1"  data-toggle="collapse"  aria-expanded="false" data-dismiss="modal" onclick="popupShown()" > Accept</button>
            
        </div>
    </div>
</div>
<script>
//onlick from "agree" Eula button
function popupShown() {
    localStorage.setItem('popState','shown');
    
}
//onclick from EULA checkbox
function checkBox() {
  var checkBox = document.getElementById("Check1");

  if (checkBox.checked == true) {
    document.getElementById("btn1").disabled = false;
  } else {
    document.getElementById("btn1").disabled = true;

  }
}
</script>
<?php endif;?>