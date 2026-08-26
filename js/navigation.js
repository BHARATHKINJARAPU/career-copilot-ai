document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const nav = document.querySelector('.navbar');

  if (hamburger && nav) {
    hamburger.addEventListener('click', () => {
      nav.classList.toggle('nav-mobile-open');
    });
  }
});