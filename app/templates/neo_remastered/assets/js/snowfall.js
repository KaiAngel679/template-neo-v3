const storageKey = "snow";
const snow = document.querySelector(".snow");
const snowflakes = document.querySelectorAll(".snow__flake");
const snowToggle = document.querySelector(".snow-toggle");
const snow_buttons = snowToggle.querySelectorAll(".snow-toggle__btn");
const stopSoundButton = document.querySelector(".stop-sound");
const currentYear = new Date().getFullYear();
const newYear = new Date(`${currentYear + 1}-01-01T00:00:00`);

function getRndInteger(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function getRndFloat(min, max) {
  return (Math.random() * (max - min) + min).toFixed(1);
}

snowflakes.forEach((snowflake) => {
  snowflake.style.fontSize = getRndFloat(0.2, 0.7) + "em";
  snowflake.style.animationDuration = getRndInteger(20, 30) + "s";
  snowflake.style.animationDelay = getRndInteger(-1, snowflakes.length / 2) + "s";
});

function changeSnowAnimation(animationName) {
  snow.style.setProperty("--animation-name", animationName);
}

let currentAudio;

function handleSnowToggle(value, showNoty = true) {
  changeSnowAnimation(value);
  localStorage.setItem(storageKey, value);

  if (currentAudio) {
    currentAudio.pause();
    currentAudio.currentTime = 0;
  }

  const soundPath = value === "none"
    ? "/storage/assets/sounds/snowon.mp3"
    : "/storage/assets/sounds/snowoff.mp3";

  if (showNoty) {
    currentAudio = new Audio(soundPath);
    currentAudio.volume = 0.2;
    currentAudio.play();

    const message = value === "none"
      ? get_translate_phrase('_snowflakeStopped')
      : get_translate_phrase('_snowflakeStarted');
    noty(message, "success");
  }

  snow_buttons.forEach(btn => {
    btn.classList.toggle("active", btn.dataset.value === value);
  });
}


snow_buttons.forEach(btn => {
  btn.addEventListener("click", () => {
    handleSnowToggle(btn.dataset.value);
  });
});

stopSoundButton.addEventListener("click", () => {
  if (currentAudio) {
    currentAudio.pause();
    currentAudio.currentTime = 0;
    currentAudio = null;
    noty("Звук остановлен", "success");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  let currentStorage = localStorage.getItem(storageKey) || "snowfall";
  handleSnowToggle(currentStorage, false);

  window.addEventListener("storage", () => {
    handleSnowToggle(localStorage.getItem(storageKey), false);
  });
});

function updateTimer() {
  const now = new Date();
  const timeDifference = newYear - now;

  if (timeDifference <= 0) {
    const nextYear = new Date().getFullYear();
    document.getElementById('timer').innerHTML = `С новым ${nextYear} годом!`;
    document.querySelector('.timer-items')?.style.setProperty('display', 'none');
    clearInterval(timerInterval);
    return;
  }

  const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
  const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

  const d = days.toString().padStart(2, "0");
  const h = hours.toString().padStart(2, "0");
  const m = minutes.toString().padStart(2, "0");
  const s = seconds.toString().padStart(2, "0");

  const timerText = `До нового года<br> <div class="timer-wrapper"><span>${d}</span>:<span>${h}</span>:<span>${m}</span>:<span>${s}</span></div>`;
  document.getElementById('timer').innerHTML = timerText;
}

const timerInterval = setInterval(updateTimer, 1000);
updateTimer();