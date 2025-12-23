function showLogin() {
    document.getElementById('login-form').style.display = 'flex';
    document.getElementById('signup-form').style.display = 'none';
    document.querySelector('.wrapper').classList.remove('animated-signup');
    document.querySelector('.wrapper').classList.add('animated-signin');
}

function showSignup() {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('signup-form').style.display = 'flex';
    document.querySelector('.wrapper').classList.remove('animated-signin');
    document.querySelector('.wrapper').classList.add('animated-signup');
}

document.querySelector('.signin-link').addEventListener('click', (e) => {
    e.preventDefault();
    showLogin();
});

document.querySelector('.signup-link').addEventListener('click', (e) => {
    e.preventDefault();
    showSignup();
});

