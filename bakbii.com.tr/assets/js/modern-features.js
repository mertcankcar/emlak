document.addEventListener('DOMContentLoaded', () => {
  // Initialize all carousels on the page
  const carousels = document.querySelectorAll('.carousel-container');
  carousels.forEach(initializeCarousel);

  // Add keyboard navigation for property cards
  initializeKeyboardNavigation();
});

function initializeCarousel(container) {
  const slides = container.querySelectorAll('.carousel-slide');
  if (slides.length === 0) return;

  let currentSlide = 0;
  const autoPlayInterval = 3000; // 3 seconds between slides

  // Create navigation dots
  const dotsContainer = document.createElement('div');
  dotsContainer.className = 'carousel-dots';
  slides.forEach((_, index) => {
    const dot = document.createElement('button');
    dot.className = 'carousel-dot';
    dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
    dot.addEventListener('click', () => goToSlide(index));
    dotsContainer.appendChild(dot);
  });
  container.appendChild(dotsContainer);

  // Initialize first slide and dots
  updateSlides();

  // Start autoplay
  let autoplayTimer = setInterval(nextSlide, autoPlayInterval);

  // Pause autoplay on hover or focus
  container.addEventListener('mouseenter', () => clearInterval(autoplayTimer));
  container.addEventListener('mouseleave', () => {
    clearInterval(autoplayTimer);
    autoplayTimer = setInterval(nextSlide, autoPlayInterval);
  });

  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlides();
  }

  function goToSlide(index) {
    currentSlide = index;
    updateSlides();
  }

  function updateSlides() {
    slides.forEach((slide, index) => {
      slide.classList.toggle('active', index === currentSlide);
      const dot = dotsContainer.children[index];
      dot.classList.toggle('active', index === currentSlide);
      dot.setAttribute('aria-current', index === currentSlide);
    });
  }
}

function initializeKeyboardNavigation() {
  const cards = document.querySelectorAll('.property-card');
  if (cards.length === 0) return;

  // Make cards focusable
  cards.forEach(card => {
    if (!card.hasAttribute('tabindex')) {
      card.setAttribute('tabindex', '0');
    }
  });

  // Add keyboard navigation
  document.addEventListener('keydown', (e) => {
    const focusedElement = document.activeElement;
    if (!focusedElement.classList.contains('property-card')) return;

    const cards = Array.from(document.querySelectorAll('.property-card'));
    const currentIndex = cards.indexOf(focusedElement);

    switch (e.key) {
      case 'ArrowRight':
      case 'ArrowDown':
        e.preventDefault();
        if (currentIndex < cards.length - 1) {
          cards[currentIndex + 1].focus();
        }
        break;
      case 'ArrowLeft':
      case 'ArrowUp':
        e.preventDefault();
        if (currentIndex > 0) {
          cards[currentIndex - 1].focus();
        }
        break;
      case 'Enter':
      case ' ':
        e.preventDefault();
        focusedElement.querySelector('a')?.click();
        break;
    }
  });
}