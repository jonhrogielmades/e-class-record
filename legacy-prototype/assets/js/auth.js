(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp;

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || "").trim());
    }

    function getDemoUser(email) {
        return app.getUsers().find(function (user) {
            return String(user.email || "").toLowerCase() === String(email || "").toLowerCase();
        }) || null;
    }

    function bindPasswordToggles() {
        document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
            if (button.dataset.bound === "true") {
                return;
            }

            button.dataset.bound = "true";
            button.addEventListener("click", function () {
                const targetId = button.getAttribute("data-password-toggle");
                const field = document.getElementById(targetId);

                if (!field) {
                    return;
                }

                const isPassword = field.type === "password";
                field.type = isPassword ? "text" : "password";
                button.textContent = isPassword ? "Hide" : "Show";
            });
        });
    }

    function populateSectionOptions(select) {
        if (!select) {
            return;
        }

        select.innerHTML = ['<option value="">Select section</option>'].concat(app.getSections().map(function (section) {
            return '<option value="' + section.id + '">' + app.escapeHtml(section.name + " - " + section.strand) + '</option>';
        })).join("");
    }

    function updateRegistrationRoleState() {
        const role = document.getElementById("role");
        const studentOnlyFields = document.querySelectorAll("[data-student-only]");
        const sectionField = document.getElementById("section-id");
        const helper = document.getElementById("role-helper");

        if (!role) {
            return;
        }

        const isStudent = role.value !== "teacher";

        studentOnlyFields.forEach(function (field) {
            field.classList.toggle("hidden", !isStudent);
        });

        if (sectionField) {
            sectionField.required = isStudent;
        }

        if (helper) {
            helper.textContent = isStudent
                ? "Students can be linked to a section and personal record profile immediately after registration."
                : "Teachers receive a management workspace for sections, attendance, and grading.";
        }
    }

    function initLoginPage() {
        app.redirectIfAuthenticated();
        app.refreshCommonUI();
        bindPasswordToggles();

        const form = document.getElementById("login-form");
        const messageBox = document.getElementById("auth-message");

        if (!form) {
            return;
        }

        app.renderPageNotice("login", messageBox);

        const teacherDemo = document.getElementById("use-teacher-demo");
        const studentDemo = document.getElementById("use-student-demo");
        const teacherDemoUser = getDemoUser("teacher@eclass.local");
        const studentDemoUser = getDemoUser("student@eclass.local");

        if (teacherDemo && teacherDemoUser) {
            teacherDemo.addEventListener("click", function () {
                document.getElementById("email").value = "teacher@eclass.local";
                document.getElementById("password").value = "teacher123";
                app.showMessage(messageBox, "info", "Teacher demo credentials loaded. Click Sign In to continue.");
            });
        } else if (teacherDemo) {
            teacherDemo.disabled = true;
            teacherDemo.title = "Teacher demo account is no longer available.";
        }

        if (studentDemo && studentDemoUser) {
            studentDemo.addEventListener("click", function () {
                document.getElementById("email").value = "student@eclass.local";
                document.getElementById("password").value = "student123";
                app.showMessage(messageBox, "info", "Student demo credentials loaded. Click Sign In to continue.");
            });
        } else if (studentDemo) {
            studentDemo.disabled = true;
            studentDemo.title = "Student demo account is no longer available.";
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();
            app.clearMessage(messageBox);

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;

            if (!email || !password) {
                app.showMessage(messageBox, "error", "Please enter both your email and password.");
                return;
            }

            if (!isValidEmail(email)) {
                app.showMessage(messageBox, "error", "Please provide a valid email address.");
                return;
            }

            const result = app.loginUser(email, password);

            if (!result.success) {
                app.showMessage(messageBox, "error", result.message);
                return;
            }

            app.showMessage(messageBox, "success", result.message);
            window.setTimeout(function () {
                window.location.href = "dashboard.html";
            }, 800);
        });
    }

    function initRegisterPage() {
        app.redirectIfAuthenticated();
        app.refreshCommonUI();
        bindPasswordToggles();

        const form = document.getElementById("register-form");
        const messageBox = document.getElementById("auth-message");
        const roleSelect = document.getElementById("role");
        const sectionSelect = document.getElementById("section-id");

        populateSectionOptions(sectionSelect);
        updateRegistrationRoleState();

        if (roleSelect) {
            roleSelect.addEventListener("change", updateRegistrationRoleState);
        }

        if (!form) {
            return;
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();
            app.clearMessage(messageBox);

            const name = document.getElementById("fullname").value.trim();
            const email = document.getElementById("email").value.trim();
            const role = document.getElementById("role").value;
            const sectionId = document.getElementById("section-id").value;
            const phone = document.getElementById("phone").value.trim();
            const guardian = document.getElementById("guardian").value.trim();
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;

            if (!name || !email || !password || !confirmPassword || !role) {
                app.showMessage(messageBox, "error", "Please complete all required fields before creating an account.");
                return;
            }

            if (!isValidEmail(email)) {
                app.showMessage(messageBox, "error", "Please provide a valid email address.");
                return;
            }

            if (password.length < 6) {
                app.showMessage(messageBox, "error", "Use a password with at least 6 characters for this demo system.");
                return;
            }

            if (password !== confirmPassword) {
                app.showMessage(messageBox, "error", "Your password confirmation does not match.");
                return;
            }

            if (role === "student" && !sectionId) {
                app.showMessage(messageBox, "error", "Please select a section for the student account.");
                return;
            }

            const result = app.registerUser({
                name: name,
                email: email,
                password: password,
                role: role,
                sectionId: sectionId,
                phone: phone,
                guardian: guardian
            });

            if (!result.success) {
                app.showMessage(messageBox, "error", result.message);
                return;
            }

            app.showMessage(messageBox, "success", result.message);
            window.setTimeout(function () {
                window.location.href = "dashboard.html";
            }, 900);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        const page = document.body.getAttribute("data-page");

        if (page === "login") {
            initLoginPage();
        }

        if (page === "register") {
            initRegisterPage();
        }
    });
}(window, document));
