const menuToggle = document.querySelector('#menuToggle');
const mainNav = document.querySelector('#mainNav');

if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', () => {
        const isOpen = mainNav.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    mainNav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mainNav.classList.remove('open');
            menuToggle.setAttribute('aria-expanded', 'false');
        });
    });
}

const tourForm = document.querySelector('#tourForm');
const formMessage = document.querySelector('#formMessage');

if (tourForm && formMessage) {
    tourForm.addEventListener('submit', (event) => {
        event.preventDefault();
        formMessage.textContent = 'Thanks. We received your request and will be in touch shortly.';
        formMessage.classList.add('is-visible');
        tourForm.reset();
    });
}

document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-carousel-track]');
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');

    if (!track || !previous || !next) return;

    const move = (direction) => {
        track.scrollBy({ left: direction * track.clientWidth * 0.85, behavior: 'smooth' });
    };

    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
});
