document.addEventListener("DOMContentLoaded", () => {
    // Animate the right panel on load
    const panel = document.querySelector(".right-panel");
    if (panel) {
        panel.style.transition = "all 0.6s ease-in-out";
        panel.style.opacity = 1;
        panel.style.transform = "translateY(0)";
    }

    // --- Password Strength Checker ---
    const passwordInput = document.getElementById("password");
    const strengthMessage = document.getElementById("strengthMessage");

    if (passwordInput && strengthMessage) {
        const getPasswordStrength = (password) => {
            let score = 0;
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            if (score <= 1) return { label: "Weak", color: "red" };
            if (score === 2) return { label: "Moderate", color: "orange" };
            if (score >= 3) return { label: "Strong", color: "green" };
            return { label: "", color: "" };
        };

        passwordInput.addEventListener("input", () => {
            const password = passwordInput.value;
            const strength = getPasswordStrength(password);
            strengthMessage.textContent = "Strength: " + strength.label;
            strengthMessage.style.color = strength.color;
        });
    }

    // --- Password Match Validation (for registration form) ---
    const registerForm = document.getElementById("registerForm");
    const confirmPasswordInput = document.getElementById("confirm_password");

    if (registerForm && passwordInput && confirmPasswordInput) {
        registerForm.addEventListener("submit", (event) => {
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert("Passwords do not match. Please try again.");
                event.preventDefault(); // Stop the form from submitting
            }
        });
    }

    // --- NEW: Logic for Invitation Code Field ---
    const roleSelect = document.getElementById("role");
    const invitationCodeGroup = document.getElementById("invitationCodeGroup");

    if (roleSelect && invitationCodeGroup) {
        roleSelect.addEventListener("change", function() {
            // Role ID '2' is for Student. Show the code field for any other role.
            if (this.value !== '2' && this.value !== '') {
                invitationCodeGroup.style.display = 'block';
            } else {
                invitationCodeGroup.style.display = 'none';
            }
        });
    }
});