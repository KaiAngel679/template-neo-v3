document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('WelcomeModal');
  if (!modal) return;

  const closeBtn = modal.querySelector('.popup_modal_close');
  const disableBtn = document.getElementById('disabledWelcomeModal');

  function shouldShowModal() {
    const hiddenUntil = localStorage.getItem('modalHiddenUntil');
    if (!hiddenUntil) return true;
    const now = Date.now();
    return now > parseInt(hiddenUntil, 10);
  }

  function openModal() {
    modal.classList.add('visible');
  }
  function closeModal() {
    modal.classList.remove('visible');
  }

  if (shouldShowModal()) {
    openModal();
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal);
  }

  if (disableBtn) {
    disableBtn.addEventListener('click', function() {
      const oneDayFromNow = Date.now() + 24 * 60 * 60 * 1000;
      localStorage.setItem('modalHiddenUntil', oneDayFromNow.toString());
      closeModal();
    });
  }

  modal.addEventListener('click', function(event) {
    if (event.target === modal) {
      closeModal();
    }
  });
});