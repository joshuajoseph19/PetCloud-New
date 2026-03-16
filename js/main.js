
// 0. CRITICAL: Check Protocol for Google Sign-In
if (window.location.protocol === 'file:') {
    alert("⚠️ CRITICAL ERROR ⚠️\n\nGoogle Sign-In WILL NOT WORK from a file.\n\nYou are currently opening this file directly.\nYou MUST run this via your XAMPP server.\n\nPlease open your browser and type:\nhttp://localhost/PetCloud/index.html");
    document.body.innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;text-align:center;font-family:sans-serif;"><h1>⚠️ SETUP ERROR</h1><p>Google Sign-In requires a Web Server (http://).</p><p>You are using a local file (file://).</p><br><p>Please open: <b>http://localhost/PetCloud/index.html</b></p></div>';
    throw new Error("Execution stopped due to file protocol");
}

// 4. Google Sign-In Handler (Real) - MUST BE GLOBAL
window.handleGoogleSignIn = function (response) {
    console.log("Google JWT Token:", response.credential);

    // Decode token to get user info (Simple decode for demo)
    const payload = JSON.parse(atob(response.credential.split('.')[1]));
    console.log("User:", payload);

    // Save user info to localStorage (for client-side reference)
    localStorage.setItem('petcloud_user', JSON.stringify({
        name: payload.name,
        email: payload.email,
        picture: payload.picture
    }));

    // Send to PHP Backend to start Session
    fetch('google-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: payload.name,
            email: payload.email,
            picture: payload.picture
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'dashboard.php';
            } else {
                console.error("Backend Session Failed");
                alert("Login failed with backend.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

document.addEventListener('DOMContentLoaded', () => {

    // 1. Password Visibility Toggle
    const togglePasswordButtons = document.querySelectorAll('.fa-eye, .fa-eye-slash');

    togglePasswordButtons.forEach(icon => {
        icon.addEventListener('click', function () {
            // Find the input in the same wrapper
            const input = this.parentElement.querySelector('input');

            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.remove('fa-eye'); // Ensure clean state
                this.classList.add('fa-eye'); // Show open eye (visible)
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.remove('fa-eye-slash'); // Ensure clean state
                this.classList.add('fa-eye-slash'); // Show slashed eye (hidden)
            }
        });
    });

    // 2. Form Submission Simulation (REMOVED for Real PHP Backend)
    // We now rely on standard HTML <form> submission to .php files.
    /*
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) { ... });
    });
    */

    // 3. Social Login Simulation (Keep for fake buttons, but add Real GSI Handler)
    const socialBtns = document.querySelectorAll('.social-btn');

    socialBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            // Add a simple pressed effect
            this.style.transform = 'scale(0.98)';
            setTimeout(() => this.style.transform = 'scale(1)', 100);

            // Allow manual click simulation to also "work"
            console.log("Simulating Login");
            window.location.href = 'dashboard.php';
        });
    });

    // 5. Check for Logged In User (Dashboard Only)
    const userJson = localStorage.getItem('petcloud_user');
    if (userJson) {
        try {
            const user = JSON.parse(userJson);

            // Update Mini Profile (Sidebar)
            const miniName = document.querySelector('.mini-name');
            const miniAvatar = document.querySelector('.mini-avatar');
            if (miniName) miniName.textContent = user.name;
            if (miniAvatar) miniAvatar.src = user.picture;

            // Update Greeting (Hero)
            const greeting = document.querySelector('.hero-overlay h1');
            if (greeting) {
                const hour = new Date().getHours();
                let txt = "Good Morning";
                if (hour >= 12 && hour < 17) txt = "Good Afternoon";
                else if (hour >= 17 && hour < 21) txt = "Good Evening";
                else if (hour >= 21 || hour < 5) txt = "Good Night";
                greeting.textContent = `${txt}, ${user.name.split(' ')[0]}!`;
            }

        } catch (e) {
            console.error("Error parsing user data", e);
        }
    }

});
