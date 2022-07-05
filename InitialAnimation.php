<div class="initial_animation_overflow">
  <div class="overlay">
    <p class="screen">WELCOME</p>

  </div>
  <div class="intro">
    <button class="animationButton" onclick="fadeOut()">Welcome
    </button>
  </div>
</div>

<script>
  function fadeOut() {

    TweenMax.to(".animationButton", 0.5, {
      y: -100,
      opacity: 0
    });

    TweenMax.to(".screen", 0.3, {
      y: -400,
      opacity: 0,
      ease: Power2.easeInOut,
      delay: 2
    });

    TweenMax.from(".overlay", 0.3, {
      ease: Power2.easeInOut
    });

    TweenMax.to(".overlay", 0.3, {
      delay: 1,
      top: "-110%",
      ease: Expo.easeInOut
    });

    TweenMax.to(".overlay-2", 0.5, {
      delay: 1,
      top: "-110%",
      ease: Expo.easeInOut
    });

    TweenMax.from(".content", 0.3, {
      delay: 3.2,
      opacity: 0,
      ease: Power2.easeInOut
    });

    TweenMax.to(".content", 0.3, {
      opacity: 1,
      y: -300,
      delay: 3.2,
      ease: Power2.easeInOut
    });

    localStorage.setItem('animationState', 'shown');
  }
</script>
<script>
  const bg = document.querySelector('.overlay');
  const windowWidth = window.innerWidth / 5;
  const windowHeight = window.innerHeight / 5;

  bg.addEventListener('mousemove', (e) => {
    const mouseX = e.clientX / windowWidth;
    const mouseY = e.clientY / windowHeight;

    bg.style.transform = `translate3d(-${mouseX}%, -${mouseY}%, 0)`;
  });
</script>