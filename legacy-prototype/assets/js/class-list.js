(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp;
    const state = {
        sectionId: "",
        formMode: "edit"
    };

    function sectionTabs(sections) {
        return '<div class="segment-control">' + sections.map(function (section) {
            const activeClass = state.sectionId === section.id ? " active" : "";
            return '<button type="button" class="segment-button' + activeClass + '" data-section-id="' + section.id + '">' + app.escapeHtml(section.name) + '</button>';
        }).join("") + '</div>';
    }

    function rosterCards(records) {
        if (!records.length) {
            return '<div class="empty-state"><div><h3>No learners in this section yet</h3><p>Create student profiles from the Students page to populate this class roster.</p></div></div>';
        }

        return '<div class="roster-grid">' + records.map(function (record) {
            return '' +
                '<article class="student-card">' +
                    '<div class="student-card-head">' +
                        '<div class="profile-avatar">' + app.escapeHtml(app.getInitials(record.student.name)) + '</div>' +
                        '<div>' +
                            '<h3>' + app.escapeHtml(record.student.name) + '</h3>' +
                            '<p>' + app.escapeHtml(record.student.studentNumber) + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<p class="category-copy">' + app.escapeHtml(record.student.focus) + '</p>' +
                    '<div class="recent-session-meta">' +
                        '<span class="feature-badge">' + record.gradeAverage + '% average</span>' +
                        '<span class="feature-badge">' + record.attendanceSummary.rate + '% attendance</span>' +
                    '</div>' +
                '</article>';
        }).join("") + '</div>';
    }

    function bindSectionSwitch() {
        document.querySelectorAll("[data-section-id]").forEach(function (button) {
            button.addEventListener("click", function () {
                state.sectionId = button.getAttribute("data-section-id");
                state.formMode = "edit";
                renderClassListPage();
            });
        });
    }

    function bindSectionCrud(user, sections, activeSection) {
        const form = document.getElementById("section-form");
        const createButton = document.getElementById("create-section-mode");
        const deleteButton = document.getElementById("delete-section");
        const messageBox = document.getElementById("class-list-message");

        if (createButton) {
            createButton.addEventListener("click", function () {
                state.formMode = "create";
                renderClassListPage();
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener("click", function () {
                if (!activeSection) {
                    return;
                }

                const confirmed = window.confirm("Delete this section? The section must not contain any learners.");

                if (!confirmed) {
                    return;
                }

                const result = app.deleteSection(activeSection.id);

                if (!result.success) {
                    app.showMessage(messageBox, "error", result.message);
                    return;
                }

                app.updateUser(user.id, function (currentUser) {
                    return {
                        advisorySections: (currentUser.advisorySections || []).filter(function (sectionId) {
                            return sectionId !== activeSection.id;
                        })
                    };
                });

                const remainingSections = sections.filter(function (section) {
                    return section.id !== activeSection.id;
                });

                state.sectionId = remainingSections.length ? remainingSections[0].id : "";
                state.formMode = "edit";
                app.setPageNotice("class-list", "success", "Section deleted successfully.");
                renderClassListPage();
            });
        }

        if (!form) {
            return;
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const payload = {
                name: document.getElementById("section-name").value.trim(),
                strand: document.getElementById("section-strand").value.trim(),
                room: document.getElementById("section-room").value.trim(),
                schedule: document.getElementById("section-schedule").value.trim(),
                adviser: document.getElementById("section-adviser").value.trim(),
                description: document.getElementById("section-description").value.trim()
            };

            if (!payload.name || !payload.strand || !payload.room || !payload.schedule || !payload.adviser) {
                app.showMessage(messageBox, "error", "Please complete the section form before saving.");
                return;
            }

            if (state.formMode === "create") {
                const newSection = app.createSection(payload);
                app.updateUser(user.id, function (currentUser) {
                    const advisorySections = Array.isArray(currentUser.advisorySections) ? currentUser.advisorySections.slice() : [];

                    if (advisorySections.indexOf(newSection.id) === -1) {
                        advisorySections.push(newSection.id);
                    }

                    return {
                        advisorySections: advisorySections
                    };
                });
                state.sectionId = newSection.id;
                state.formMode = "edit";
                app.setPageNotice("class-list", "success", "Section created successfully.");
                renderClassListPage();
                return;
            }

            app.updateSection(activeSection.id, payload);
            app.setPageNotice("class-list", "success", "Section updated successfully.");
            renderClassListPage();
        });
    }

    function renderTeacherView(user) {
        const sections = app.getSectionSummaries(user.advisorySections);

        if (!sections.length) {
            state.sectionId = "";
            state.formMode = "create";

            app.renderProtectedShell({
                activePage: "class-list",
                title: "Class List",
                subtitle: "Switch between sections and review the current learner roster with attendance and grade snapshots.",
                headerMeta: [
                    "0 active sections",
                    "0 learners",
                    "Create your first section"
                ],
                headerActions: '' +
                    '<a href="dashboard.html" class="btn btn-primary btn-fit">Dashboard</a>' +
                    '<a href="settings.html" class="btn btn-outline btn-fit">Settings</a>',
                content: '' +
                    '<div id="class-list-message"></div>' +
                    '<section class="glass-card">' +
                        '<div class="section-head"><div><h2>Create Your First Section</h2><p>Add a class section to unlock roster, attendance, and grading views.</p></div></div>' +
                        '<form id="section-form" class="form-grid">' +
                            '<div class="form-group-settings"><label for="section-name">Section Name</label><input id="section-name" class="form-input" type="text" value=""></div>' +
                            '<div class="form-group-settings"><label for="section-strand">Strand / Label</label><input id="section-strand" class="form-input" type="text" value=""></div>' +
                            '<div class="form-group-settings"><label for="section-room">Room</label><input id="section-room" class="form-input" type="text" value=""></div>' +
                            '<div class="form-group-settings"><label for="section-schedule">Schedule</label><input id="section-schedule" class="form-input" type="text" value=""></div>' +
                            '<div class="form-group-settings full-width"><label for="section-adviser">Adviser</label><input id="section-adviser" class="form-input" type="text" value="' + app.escapeHtml(user.name) + '"></div>' +
                            '<div class="form-group-settings full-width"><label for="section-description">Description</label><textarea id="section-description" class="form-input"></textarea></div>' +
                            '<div class="btn-group no-print">' +
                                '<button type="submit" class="btn btn-primary btn-fit">Create Section</button>' +
                            '</div>' +
                        '</form>' +
                    '</section>' +
                    '<section class="glass-card">' +
                        app.emptyStateMarkup(
                            "Roster and section stats will appear here",
                            "Once a section exists, this page will automatically show section details, learner cards, and performance snapshots."
                        ) +
                    '</section>'
            });

            bindSectionCrud(user, sections, null);
            return;
        }

        state.sectionId = state.sectionId || (sections[0] || {}).id;
        const activeSection = sections.find(function (section) {
            return section.id === state.sectionId;
        }) || sections[0];
        const formSection = state.formMode === "create"
            ? {
                name: "",
                strand: "",
                room: "",
                schedule: "",
                adviser: user.name,
                description: ""
            }
            : activeSection;

        app.renderProtectedShell({
            activePage: "class-list",
            title: "Class List",
            subtitle: "Switch between sections and review the current learner roster with attendance and grade snapshots.",
            headerMeta: [
                activeSection.name,
                activeSection.studentCount + " learners",
                activeSection.schedule
            ],
            headerActions: '' +
                '<a href="students.html" class="btn btn-primary btn-fit">Open Student Records</a>' +
                '<a href="grading.html" class="btn btn-outline btn-fit">Go to Grading</a>',
            content: '' +
                '<div id="class-list-message"></div>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Section Selector</h2><p>Choose a class section to inspect roster details and current performance status.</p></div></div>' +
                    sectionTabs(sections) +
                '</section>' +
                '<section class="stats-grid compact-stats">' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Enrolled Learners</h3><div class="stat-value">' + activeSection.studentCount + '</div><span class="stat-change emerald">Class roster size</span></div><div class="stat-icon emerald">' + app.navIcon("students") + '</div></div></div>' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Section Average</h3><div class="stat-value">' + activeSection.averageGrade + '%</div><span class="stat-change gold">' + app.performanceLabel(activeSection.averageGrade) + '</span></div><div class="stat-icon gold">' + app.navIcon("grading") + '</div></div></div>' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Attendance Rate</h3><div class="stat-value">' + activeSection.attendanceRate + '%</div><span class="stat-change blue">Present + late counted</span></div><div class="stat-icon blue">' + app.navIcon("class-list") + '</div></div></div>' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Pending Grades</h3><div class="stat-value">' + activeSection.pendingGrades + '</div><span class="stat-change rose">Seeded checklist gap</span></div><div class="stat-icon rose">' + app.navIcon("settings") + '</div></div></div>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Section Overview</h2><p>' + app.escapeHtml(activeSection.description) + '</p></div></div>' +
                        '<ul class="detail-list">' +
                            '<li><strong>Section:</strong> ' + app.escapeHtml(activeSection.name + " - " + activeSection.strand) + '</li>' +
                            '<li><strong>Adviser:</strong> ' + app.escapeHtml(activeSection.adviser) + '</li>' +
                            '<li><strong>Schedule:</strong> ' + app.escapeHtml(activeSection.schedule) + '</li>' +
                            '<li><strong>Room:</strong> ' + app.escapeHtml(activeSection.room) + '</li>' +
                        '</ul>' +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>' + (state.formMode === "create" ? "Create Section" : "Edit Section") + '</h2><p>Use CRUD controls to add, update, or delete class sections.</p></div></div>' +
                        '<form id="section-form" class="form-grid">' +
                            '<div class="form-group-settings"><label for="section-name">Section Name</label><input id="section-name" class="form-input" type="text" value="' + app.escapeHtml(formSection.name || "") + '"></div>' +
                            '<div class="form-group-settings"><label for="section-strand">Strand / Label</label><input id="section-strand" class="form-input" type="text" value="' + app.escapeHtml(formSection.strand || "") + '"></div>' +
                            '<div class="form-group-settings"><label for="section-room">Room</label><input id="section-room" class="form-input" type="text" value="' + app.escapeHtml(formSection.room || "") + '"></div>' +
                            '<div class="form-group-settings"><label for="section-schedule">Schedule</label><input id="section-schedule" class="form-input" type="text" value="' + app.escapeHtml(formSection.schedule || "") + '"></div>' +
                            '<div class="form-group-settings full-width"><label for="section-adviser">Adviser</label><input id="section-adviser" class="form-input" type="text" value="' + app.escapeHtml(formSection.adviser || user.name) + '"></div>' +
                            '<div class="form-group-settings full-width"><label for="section-description">Description</label><textarea id="section-description" class="form-input">' + app.escapeHtml(formSection.description || "") + '</textarea></div>' +
                            '<div class="btn-group no-print">' +
                                '<button type="submit" class="btn btn-primary btn-fit">' + (state.formMode === "create" ? "Create Section" : "Save Changes") + '</button>' +
                                '<button type="button" class="btn btn-outline btn-fit" id="create-section-mode">New Section</button>' +
                                '<button type="button" class="btn btn-danger-outline btn-fit" id="delete-section"' + (state.formMode === "create" ? ' disabled' : "") + '>Delete</button>' +
                            '</div>' +
                        '</form>' +
                    '</article>' +
                '</section>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Roster Cards</h2><p>Each card combines a learner profile with attendance and grading snapshots.</p></div></div>' +
                    rosterCards(activeSection.roster) +
                '</section>'
        });

        app.renderPageNotice("class-list", document.getElementById("class-list-message"));
        bindSectionSwitch();
        bindSectionCrud(user, sections, activeSection);
    }

    function renderStudentView(user) {
        const snapshot = app.getStudentSnapshot(user);

        if (!snapshot || !snapshot.student) {
            app.renderProtectedShell({
                activePage: "class-list",
                title: "My Class",
                subtitle: "Review your assigned section details, adviser information, and a quick snapshot of classmates.",
                headerMeta: [
                    app.getRoleLabel(user.role),
                    "Profile unavailable"
                ],
                headerActions: '' +
                    '<a href="settings.html" class="btn btn-primary btn-fit">Open Settings</a>' +
                    '<a href="login.html" class="btn btn-outline btn-fit" data-logout>Log Out</a>',
                content: app.emptyStateMarkup(
                    "Student profile not found",
                    "Your account is signed in, but there is no linked learner profile to display on the class page."
                )
            });

            return;
        }

        const classmates = [snapshot].concat(snapshot.classmates).filter(Boolean);

        app.renderProtectedShell({
            activePage: "class-list",
            title: "My Class",
            subtitle: "Review your assigned section details, adviser information, and a quick snapshot of classmates.",
            headerMeta: [
                snapshot.section.name,
                snapshot.section.room,
                snapshot.classmates.length + 1 + " learners"
            ],
            headerActions: '' +
                '<a href="students.html" class="btn btn-primary btn-fit">Attendance Records</a>' +
                '<a href="grading.html" class="btn btn-outline btn-fit">Grade Summary</a>',
            content: '' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Section Information</h2><p>' + app.escapeHtml(snapshot.section.description) + '</p></div></div>' +
                        '<ul class="detail-list">' +
                            '<li><strong>Section:</strong> ' + app.escapeHtml(snapshot.section.name + " - " + snapshot.section.strand) + '</li>' +
                            '<li><strong>Adviser:</strong> ' + app.escapeHtml(snapshot.section.adviser) + '</li>' +
                            '<li><strong>Schedule:</strong> ' + app.escapeHtml(snapshot.section.schedule) + '</li>' +
                            '<li><strong>Room:</strong> ' + app.escapeHtml(snapshot.section.room) + '</li>' +
                        '</ul>' +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Class Snapshot</h2><p>Your current class overview based on the seeded section roster.</p></div></div>' +
                        rosterCards(classmates) +
                    '</article>' +
                '</section>'
        });
    }

    function renderClassListPage() {
        const user = app.requireAuth();

        if (!user) {
            return;
        }

        if (user.role === "teacher") {
            renderTeacherView(user);
            return;
        }

        renderStudentView(user);
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (document.body.getAttribute("data-page") === "class-list") {
            renderClassListPage();
        }
    });
}(window, document));
