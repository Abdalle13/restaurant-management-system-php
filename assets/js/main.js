// ---------- SHOW / HIDE PASSWORD ----------
function togglePassword(id, icon) {
    const input = document.getElementById(id);

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}

// ---------- AUTO HIDE ALERT ----------
setTimeout(() => {
    const alert = document.querySelector(".alert");
    if (alert) {
        alert.style.display = "none";
    }
}, 3000);

// ---------- CONFIRM DELETE ----------
function confirmDelete() {
    return confirm("Are you sure you want to delete?");
}
