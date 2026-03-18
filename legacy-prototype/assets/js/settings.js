(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp;

    function renderTeacherSettings(user) {
        app.renderProtectedShell({
            activePage: "settings",
            title: "Settings",
            subtitle: "Update your teacher profile and use settings as the home for profile details and logout actions.",
            headerMeta: [
                app.getRoleLabel(user.role),
                app.formatDate(user.joinedAt),
                user.department || "BSIT Program"
            ],
            headerActions: '<div class="button-row"><button type="button" class="btn btn-danger-outline btn-fit" data-delete-account>Delete Account</button><button type="button" class="btn btn-outline btn-fit" data-logout>Log Out</button></div>',
            content: '' +
                '<div id="settings-message"></div>' +
                '<section class="profile-grid">' +
                    '<article class="glass-card profile-summary-card">' +
                        '<div class="profile-header">' +
                            '<div class="profile-avatar">' + app.escapeHtml(app.getInitials(user.name)) + '</div>' +
                            '<div>' +
                                '<h2 id="settings-display-name">' + app.escapeHtml(user.name) + '</h2>' +
                                '<p>' + app.escapeHtml(user.email) + '</p>' +
                                '<span class="status-pill good">' + app.escapeHtml(app.getRoleLabel(user.role)) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<ul class="detail-list">' +
                            '<li><strong>Department:</strong> <span id="settings-department-text">' + app.escapeHtml(user.department || "BSIT Program") + '</span></li>' +
                            '<li><strong>Contact Number:</strong> <span id="settings-phone-text">' + app.escapeHtml(user.phone || "Not provided") + '</span></li>' +
                            '<li><strong>Joined Date:</strong> ' + app.escapeHtml(app.formatDate(user.joinedAt)) + '</li>' +
                        '</ul>' +
                        '<div class="profile-note"><strong>Teacher Note:</strong> Use this page to update your profile while keeping the teacher workflow inside the same glass-style shell.</div>' +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Profile Settings</h2><p>Edit your teacher information. Account role stays fixed for this demo.</p></div></div>' +
                        '<form id="settings-form" class="form-grid">' +
                            '<div class="form-group-settings full-width"><label for="settings-name">Full Name</label><input id="settings-name" class="form-input" type="text" value="' + app.escapeHtml(user.name) + '"></div>' +
                            '<div class="form-group-settings"><label for="settings-email">Email Address</label><div id="settings-email" class="readonly-field">' + app.escapeHtml(user.email) + '</div></div>' +
                            '<div class="form-group-settings"><label for="settings-role">Role</label><div id="settings-role" class="readonly-field">' + app.escapeHtml(app.getRoleLabel(user.role)) + '</div></div>' +
                            '<div class="form-group-settings"><label for="settings-department">Department</label><input id="settings-department" class="form-input" type="text" value="' + app.escapeHtml(user.department || "BSIT Program") + '"></div>' +
                            '<div class="form-group-settings"><label for="settings-phone">Contact Number</label><input id="settings-phone" class="form-input" type="text" value="' + app.escapeHtml(user.phone || "") + '"></div>' +
                            '<div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Save Changes</button><button type="button" class="btn btn-danger-outline btn-fit" data-delete-account>Delete Account</button><button type="button" class="btn btn-outline btn-fit" data-logout>Log Out</button></div>' +
                        '</form>' +
                    '</article>' +
                '</section>'
        });

        document.getElementById("settings-form").addEventListener("submit", function (event) {
            event.preventDefault();

            const name = document.getElementById("settings-name").value.trim();
            const department = document.getElementById("settings-department").value.trim();
            const phone = document.getElementById("settings-phone").value.trim();
            const messageBox = document.getElementById("settings-message");

            if (!name) {
                app.showMessage(messageBox, "error", "Full name is required.");
                return;
            }

            const updatedUser = app.updateUser(user.id, {
                name: name,
                department: department || "BSIT Program",
                phone: phone
            });

            app.syncShellUser(updatedUser);
            document.getElementById("settings-display-name").textContent = updatedUser.name;
            document.getElementById("settings-department-text").textContent = updatedUser.department || "BSIT Program";
            document.getElementById("settings-phone-text").textContent = updatedUser.phone || "Not provided";
            app.showMessage(messageBox, "success", "Settings updated successfully.");
        });

        document.querySelectorAll("[data-delete-account]").forEach(function (button) {
            button.addEventListener("click", function () {
                const confirmed = window.confirm("Delete this teacher account from the local system?");

                if (!confirmed) {
                    return;
                }

                app.deleteUserAccount(user.id);
                app.setPageNotice("login", "success", "Teacher account deleted successfully.");
                window.location.href = "login.html";
            });
        });
    }

    function renderStudentSettings(user) {
        const snapshot = app.getStudentSnapshot(user);

        if (!snapshot || !snapshot.student) {
            app.renderProtectedShell({
                activePage: "settings",
                title: "Settings",
                subtitle: "Update your student profile details and use settings as the place for profile and logout actions.",
                headerMeta: [
                    app.getRoleLabel(user.role),
                    "Profile unavailable"
                ],
                headerActions: '<div class="button-row"><button type="button" class="btn btn-danger-outline btn-fit" data-delete-account>Delete Account</button><button type="button" class="btn btn-outline btn-fit" data-logout>Log Out</button></div>',
                content: '' +
                    '<div id="settings-message"></div>' +
                    '<section class="glass-card">' +
                        app.emptyStateMarkup(
                            "Student profile not found",
                            "Your account is still available, but the linked learner profile is missing. You can sign out or remove this local account."
                        ) +
                    '</section>'
            });

            document.querySelectorAll("[data-delete-account]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const confirmed = window.confirm("Delete this student account from the local system?");

                    if (!confirmed) {
                        return;
                    }

                    app.deleteUserAccount(user.id);
                    app.setPageNotice("login", "success", "Student account deleted successfully.");
                    window.location.href = "login.html";
                });
            });

            return;
        }

        app.renderProtectedShell({
            activePage: "settings",
            title: "Settings",
            subtitle: "Update your student profile details and use settings as the place for profile and logout actions.",
            headerMeta: [
                app.getRoleLabel(user.role),
                snapshot.section.name,
                snapshot.student.studentNumber
            ],
            headerActions: '<div class="button-row"><button type="button" class="btn btn-danger-outline btn-fit" data-delete-account>Delete Account</button><button type="button" class="btn btn-outline btn-fit" data-logout>Log Out</button></div>',
            content: '' +
                '<div id="settings-message"></div>' +
                '<section class="profile-grid">' +
                    '<article class="glass-card profile-summary-card">' +
                        '<div class="profile-header">' +
                            '<div class="profile-avatar">' + app.escapeHtml(app.getInitials(user.name)) + '</div>' +
                            '<div>' +
                                '<h2 id="settings-display-name">' + app.escapeHtml(user.name) + '</h2>' +
                                '<p>' + app.escapeHtml(user.email) + '</p>' +
                                '<span class="status-pill good">' + app.escapeHtml(snapshot.section.name) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<ul class="detail-list">' +
                            '<li><strong>Student Number:</strong> ' + app.escapeHtml(snapshot.student.studentNumber) + '</li>' +
                            '<li><strong>Section:</strong> ' + app.escapeHtml(snapshot.section.name + " - " + snapshot.section.strand) + '</li>' +
                            '<li><strong>Guardian:</strong> <span id="settings-guardian-text">' + app.escapeHtml(snapshot.student.guardian) + '</span></li>' +
                            '<li><strong>Contact Number:</strong> <span id="settings-phone-text">' + app.escapeHtml(snapshot.student.contact) + '</span></li>' +
                        '</ul>' +
                        '<div class="profile-note"><strong>Student Note:</strong> Update your guardian and contact details while keeping grades and attendance in the other pages.</div>' +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Profile Settings</h2><p>Edit your personal student information. Section assignment is shown as read-only in this demo.</p></div></div>' +
                        '<form id="settings-form" class="form-grid">' +
                            '<div class="form-group-settings full-width"><label for="settings-name">Full Name</label><input id="settings-name" class="form-input" type="text" value="' + app.escapeHtml(user.name) + '"></div>' +
                            '<div class="form-group-settings"><label for="settings-email">Email Address</label><div id="settings-email" class="readonly-field">' + app.escapeHtml(user.email) + '</div></div>' +
                            '<div class="form-group-settings"><label for="settings-section">Section</label><div id="settings-section" class="readonly-field">' + app.escapeHtml(snapshot.section.name + " - " + snapshot.section.strand) + '</div></div>' +
                            '<div class="form-group-settings"><label for="settings-phone">Contact Number</label><input id="settings-phone" class="form-input" type="text" value="' + app.escapeHtml(snapshot.student.contact) + '"></div>' +
                            '<div class="form-group-settings"><label for="settings-guardian">Guardian / Parent</label><input id="settings-guardian" class="form-input" type="text" value="' + app.escapeHtml(snapshot.student.guardian) + '"></div>' +
                            '<div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Save Changes</button><button type="button" class="btn btn-danger-outline btn-fit" data-delete-account>Delete Account</button><button type="button" class="btn btn-outline btn-fit" data-logout>Log Out</button></div>' +
                        '</form>' +
                    '</article>' +
                '</section>'
        });

        document.getElementById("settings-form").addEventListener("submit", function (event) {
            event.preventDefault();

            const name = document.getElementById("settings-name").value.trim();
            const guardian = document.getElementById("settings-guardian").value.trim();
            const phone = document.getElementById("settings-phone").value.trim();
            const messageBox = document.getElementById("settings-message");

            if (!name) {
                app.showMessage(messageBox, "error", "Full name is required.");
                return;
            }

            const updatedUser = app.updateUser(user.id, {
                name: name,
                phone: phone
            });

            app.updateStudent(snapshot.student.id, {
                name: name,
                guardian: guardian || "Pending guardian details",
                contact: phone || "Not provided"
            });

            app.syncShellUser(updatedUser);
            document.getElementById("settings-display-name").textContent = updatedUser.name;
            document.getElementById("settings-guardian-text").textContent = guardian || "Pending guardian details";
            document.getElementById("settings-phone-text").textContent = phone || "Not provided";
            app.showMessage(messageBox, "success", "Settings updated successfully.");
        });

        document.querySelectorAll("[data-delete-account]").forEach(function (button) {
            button.addEventListener("click", function () {
                const confirmed = window.confirm("Delete this student account and its linked local records?");

                if (!confirmed) {
                    return;
                }

                app.deleteUserAccount(user.id);
                app.setPageNotice("login", "success", "Student account deleted successfully.");
                window.location.href = "login.html";
            });
        });
    }

    function renderSettingsPage() {
        const user = app.requireAuth();

        if (!user) {
            return;
        }

        if (user.role === "teacher") {
            renderTeacherSettings(user);
            return;
        }

        renderStudentSettings(user);
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (document.body.getAttribute("data-page") === "settings") {
            renderSettingsPage();
        }
    });
}(window, document));
