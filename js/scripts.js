document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');

    if (header) {
        window.onscroll = function() {
            if (window.pageYOffset > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        };
    }
});
