document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.querySelector('.wrapper');
    const signUpLink = document.querySelector('.signup-link');
    const signInLink = document.querySelector('.signin-link');

    // Check if all required elements exist
    if (!wrapper || !signUpLink || !signInLink) {
        console.error('One or more form elements are missing.');
        return;
    }

    // Switch to sign-up form
    signUpLink.addEventListener('click', (e) => {
        e.preventDefault();
        wrapper.classList.remove('animated-signin');
        wrapper.classList.add('animated-signup');
    });

    // Switch to sign-in form
    signInLink.addEventListener('click', (e) => {
        e.preventDefault();
        wrapper.classList.remove('animated-signup');
        wrapper.classList.add('animated-signin');
    });
});