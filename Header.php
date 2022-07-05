
 <?php
 if(1==1):

 ?>
    <div class="header_animation" >
        <canvas></canvas>
        <div class="hdd">
        <h1 class="header_text">Welcome to Kim's portfolio website</h1>
        </div>
<!-- <svg version="1.1" xmlns="http://www.w3.org/2000/svg">
    <filter id="blur">
      <feGaussianBlur stdDeviation="15"/>
    </filter>
</svg> -->
    </div>

<script>
window.requestAnimFrame = (function(){
  return  window.requestAnimationFrame || 
    window.webkitRequestAnimationFrame || 
    window.mozRequestAnimationFrame    || 
    window.oRequestAnimationFrame      || 
    window.msRequestAnimationFrame     ||  
    function( callback ){
    window.setTimeout(callback, 1000 / 60);
  };
})();

var canvas = document.getElementsByTagName("canvas")[0];
var ctx = canvas.getContext("2d");

var w = window.innerWidth, h = window.outerHeight;
canvas.width = w;
canvas.height = h;

var bg_particle_no = 180;

var particles = [];

function init(){
   reset_scene();
  for(var i=0;i<bg_particle_no;i++){
     var p = new bg_particle();
     particles.push(p);
  }
}

function reset_scene(){
  ctx.fillStyle = "#A9A9A9";
  ctx.fillRect(0,0,w,h);
}

function drawscene(){
  reset_scene();
  for(var i=0;i<particles.length;i++){
    var p = particles[i];
    p.x += p.sx;
    if(p.x > w || p.x < 0){
      p.sx = -p.sx;
    }
    p.y += p.sy;
    if(p.y > h || p.y < 0){
      p.sy = -p.sy;
    }
    p.draw();
  }
}

function bg_particle(){
  this.x = Math.random()*w;
  this.y = Math.random()*h;
  this.sx = Math.random()*2;
  this.sy = Math.random()*2;
  var min = 10;
  var max = 40;
  this.r = Math.random()*(max - min);
  
  
  this.draw = function(){
    ctx.fillStyle="#FFFFFF";
    ctx.beginPath();
    ctx.arc(this.x,this.y,this.r, 0, Math.PI*2, false);
    ctx.fill();
  }
}

function animloop() {
  drawscene();
  requestAnimFrame(animloop); 
}
init();
animloop();


</script>
<?php endif;?>