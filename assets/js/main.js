const menuToggle = document.querySelector('#menuToggle');
const mainNav = document.querySelector('#mainNav');

function closeMobileMenu() {
    if (!menuToggle || !mainNav) return;
    mainNav.classList.remove('open');
    menuToggle.setAttribute('aria-expanded', 'false');
}

if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', () => {
        const isOpen = mainNav.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    mainNav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMobileMenu);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 850) closeMobileMenu();
    });
}

document.querySelectorAll('.customer-account-menu').forEach((menu) => {
    document.addEventListener('click', (event) => {
        if (menu.open && !menu.contains(event.target)) {
            menu.removeAttribute('open');
        }
    });

    menu.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            menu.removeAttribute('open');
            menu.querySelector('summary')?.focus();
        }
    });
});

document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-carousel-track]');
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');

    if (!track || !previous || !next) return;

    const move = (direction) => {
        track.scrollBy({
            left: direction * track.clientWidth * 0.85,
            behavior: 'smooth'
        });
    };

    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
});
