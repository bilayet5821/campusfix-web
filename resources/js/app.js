import './bootstrap';
import * as bootstrap from 'bootstrap';
import './sidebar';
import './image-preview';
import './form-validation';
import './location-selector';
import './user-permissions';

window.bootstrap = bootstrap;

const navbar = document.querySelector('[data-site-navbar]');

const updateNavbar = () => {
    navbar?.classList.toggle('is-scrolled', window.scrollY > 16);
};

updateNavbar();
window.addEventListener('scroll', updateNavbar, { passive: true });

document.querySelectorAll('[data-navbar-link]').forEach((link) => {
    link.addEventListener('click', () => {
        const menu = document.querySelector('#publicNavigation');

        if (menu?.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(menu).hide();
        }
    });
});
