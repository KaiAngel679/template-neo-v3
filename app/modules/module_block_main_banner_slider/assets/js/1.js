const slides = document.querySelectorAll('.image-slider .swiper-slide');
const loopMode = slides.length > 2;
const swiper = new Swiper(".image-slider", {
  direction: "horizontal",
  loop: loopMode,
  simulateTouch: true,
  touchRatio: 2,
  touchAngle: 45,
  grabCursor: true,
  slidesPerView: 1,
  watchOverflow: true,
  speed: 800,
  effect: "fade",
  fadeEffect: {
    crossFade: true,
  },
  pagination: {
    el: ".swiper-pagination",
    type: "bullets",
    clickable: true,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  keyboard: {
    enable: true,
    onlyInViewport: true,
    pageUpDown: true,
  },
  autoplay: {
    delay: 3000,
    stopOnLastSlide: true,
    disableOnInteraction: false,
    pauseOnMouseEnter: true,
    waitForTransition: true,
  },
  preloadImages: false,
  lazy: {
    loadOnTransitionStart: false,
    loadPrevNext: false,
  },
  parallax: true,
});
