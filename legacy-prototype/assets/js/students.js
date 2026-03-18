(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp;
    const state = {
        sectionId: "",
        studentId: "",
        attendanceId: ""
    };

    function attendanceTable(records, teacherMode) {
        if (!records.length) {
            return '<div class="empty-state"><div><h3>No attendance records found</h3><p>Saved attendance entries will appear here.</p></div></div>';
        }

        return '' +
            '<div class="table-responsive">' +
                '<table class="history-table">' +
                    '<thead><tr><th>Date</th><th>Learner</th><th>Topic</th><th>Status</th><th>Remarks</th>' + (teacherMode ? '<th>Actions</th>' : '') + '</tr></thead>' +
                    '<tbody>' +
                        records.map(function (record) {
                            const learnerName = record.studentName || (record.student ? record.student.name : "");
                            return '' +
                                '<tr>' +
                                    '<td>' + app.escapeHtml(app.formatDate(record.date)) + '</td>' +
                                    '<td>' + app.escapeHtml(learnerName) + '</td>' +
                                    '<td>' + app.escapeHtml(record.topic) + '</td>' +
                                    '<td><span class="status-pill ' + app.attendanceTone(record.status) + '">' + app.escapeHtml(record.statusLabel || app.attendanceStatusLabel(record.status)) + '</span></td>' +
                                    '<td>' + app.escapeHtml(record.remarks || "") + '</td>' +
                                    (teacherMode
                                        ? '<td><div class="button-row table-action-row"><button type="button" class="btn btn-outline btn-fit btn-xs" data-edit-attendance="' + record.id + '">Edit</button><button type="button" class="btn btn-danger-outline btn-fit btn-xs" data-delete-attendance="' + record.id + '">Delete</button></div></td>'
                                        : '') +
                                '</tr>';
                        }).join("") +
                    '</tbody>' +
                '</table>' +
            '</div>';
    }

    function profileCards(records, teacherMode) {
        if (!records.length) {
            return app.emptyStateMarkup(
                teacherMode ? "No student profiles yet" : "No profile details available",
                teacherMode
                    ? "Create a learner profile to populate the roster and attendance tools for this section."
                    : "Your learner profile details are not available right now."
            );
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
                    '<ul class="detail-list compact-detail-list">' +
                        '<li><strong>Focus:</strong> ' + app.escapeHtml(record.student.focus) + '</li>' +
                        '<li><strong>Guardian:</strong> ' + app.escapeHtml(record.student.guardian) + '</li>' +
                        '<li><strong>Contact:</strong> ' + app.escapeHtml(record.student.contact) + '</li>' +
                    '</ul>' +
                    '<div class="recent-session-meta">' +
                        '<span class="feature-badge">' + record.gradeAverage + '% average</span>' +
                        '<span class="feature-badge">' + record.attendanceSummary.rate + '% attendance</span>' +
                    '</div>' +
                    (teacherMode
                        ? '<div class="button-row entity-actions"><button type="button" class="btn btn-outline btn-fit btn-xs" data-edit-student="' + record.student.id + '">Edit</button><button type="button" class="btn btn-danger-outline btn-fit btn-xs" data-delete-student="' + record.student.id + '">Delete</button></div>'
                        : '') +
                '</article>';
        }).join("") + '</div>';
    }

    function buildSectionOptions(sections) {
        return sections.map(function (section) {
            const selected = state.sectionId === section.id ? " selected" : "";
            return '<option value="' + section.id + '"' + selected + '>' + app.escapeHtml(section.name + " - " + section.strand) + '</option>';
        }).join("");
    }

    function buildStudentOptions(roster, selectedStudentId) {
        return ['<option value="">Select learner</option>'].concat(roster.map(function (record) {
            const selected = selectedStudentId === record.student.id ? " selected" : "";
            return '<option value="' + record.student.id + '"' + selected + '>' + app.escapeHtml(record.student.name + " (" + record.student.studentNumber + ")") + '</option>';
        })).join("");
    }

    function bindTeacherControls(user, sections, activeSection, selectedStudent, selectedAttendance) {
        const messageBox = document.getElementById("students-message");
        const sectionSelect = document.getElementById("records-section");
        const studentForm = document.getElementById("student-form");
        const attendanceForm = document.getElementById("attendance-form");
        const roster = activeSection.roster;

        if (sectionSelect) {
            sectionSelect.addEventListener("change", function (event) {
                state.sectionId = event.target.value;
                state.studentId = "";
                state.attendanceId = "";
                renderStudentsPage();
            });
        }

        document.querySelectorAll("[data-edit-student]").forEach(function (button) {
            button.addEventListener("click", function () {
                state.studentId = button.getAttribute("data-edit-student");
                renderStudentsPage();
            });
        });

        document.querySelectorAll("[data-delete-student]").forEach(function (button) {
            button.addEventListener("click", function () {
                const studentId = button.getAttribute("data-delete-student");
                const confirmed = window.confirm("Delete this student profile and all of its attendance and grade records?");

                if (!confirmed) {
                    return;
                }

                const result = app.deleteStudentProfile(studentId);

                if (!result.success) {
                    app.showMessage(messageBox, "error", result.message);
                    return;
                }

                state.studentId = "";
                app.setPageNotice("students", "success", "Student profile deleted successfully.");
                renderStudentsPage();
            });
        });

        document.querySelectorAll("[data-edit-attendance]").forEach(function (button) {
            button.addEventListener("click", function () {
                state.attendanceId = button.getAttribute("data-edit-attendance");
                renderStudentsPage();
            });
        });

        document.querySelectorAll("[data-delete-attendance]").forEach(function (button) {
            button.addEventListener("click", function () {
                const attendanceId = button.getAttribute("data-delete-attendance");
                const confirmed = window.confirm("Delete this attendance record?");

                if (!confirmed) {
                    return;
                }

                app.deleteAttendanceRecord(attendanceId);
                state.attendanceId = "";
                app.setPageNotice("students", "success", "Attendance record deleted successfully.");
                renderStudentsPage();
            });
        });

        const newStudentButton = document.getElementById("new-student-mode");
        if (newStudentButton) {
            newStudentButton.addEventListener("click", function () {
                state.studentId = "";
                renderStudentsPage();
            });
        }

        const clearAttendanceButton = document.getElementById("new-attendance-mode");
        if (clearAttendanceButton) {
            clearAttendanceButton.addEventListener("click", function () {
                state.attendanceId = "";
                renderStudentsPage();
            });
        }

        if (studentForm) {
            studentForm.addEventListener("submit", function (event) {
                event.preventDefault();

                const payload = {
                    name: document.getElementById("student-name").value.trim(),
                    email: document.getElementById("student-email").value.trim(),
                    studentNumber: document.getElementById("student-number").value.trim(),
                    guardian: document.getElementById("student-guardian").value.trim(),
                    contact: document.getElementById("student-contact").value.trim(),
                    focus: document.getElementById("student-focus").value.trim(),
                    sectionId: activeSection.id
                };

                if (!payload.name || !payload.email || !payload.studentNumber) {
                    app.showMessage(messageBox, "error", "Please complete the student profile form before saving.");
                    return;
                }

                if (selectedStudent) {
                    app.updateStudent(selectedStudent.student.id, payload);
                    app.setPageNotice("students", "success", "Student profile updated successfully.");
                } else {
                    app.createStudentProfile(payload);
                    app.setPageNotice("students", "success", "Student profile created successfully.");
                }

                state.studentId = "";
                renderStudentsPage();
            });
        }

        const deleteSelectedStudent = document.getElementById("delete-selected-student");
        if (deleteSelectedStudent) {
            deleteSelectedStudent.addEventListener("click", function () {
                if (!selectedStudent) {
                    return;
                }

                const confirmed = window.confirm("Delete the selected student profile?");

                if (!confirmed) {
                    return;
                }

                app.deleteStudentProfile(selectedStudent.student.id);
                state.studentId = "";
                app.setPageNotice("students", "success", "Student profile deleted successfully.");
                renderStudentsPage();
            });
        }

        if (attendanceForm) {
            attendanceForm.addEventListener("submit", function (event) {
                event.preventDefault();

                const studentId = document.getElementById("attendance-student").value;
                const date = document.getElementById("attendance-date").value;
                const topic = document.getElementById("attendance-topic").value.trim();
                const status = document.getElementById("attendance-status").value;
                const remarks = document.getElementById("attendance-remarks").value.trim();

                if (!studentId || !date || !topic || !status) {
                    app.showMessage(messageBox, "error", "Please complete the attendance form before saving.");
                    return;
                }

                if (selectedAttendance) {
                    app.updateAttendanceRecord(selectedAttendance.id, {
                        studentId: studentId,
                        sectionId: activeSection.id,
                        date: date,
                        topic: topic,
                        status: status,
                        remarks: remarks,
                        markedBy: user.id
                    });
                    app.setPageNotice("students", "success", "Attendance record updated successfully.");
                } else {
                    app.saveAttendanceRecord({
                        studentId: studentId,
                        sectionId: activeSection.id,
                        date: date,
                        topic: topic,
                        status: status,
                        remarks: remarks,
                        markedBy: user.id
                    });
                    app.setPageNotice("students", "success", "Attendance record created successfully.");
                }

                state.attendanceId = "";
                renderStudentsPage();
            });
        }

        const deleteSelectedAttendance = document.getElementById("delete-selected-attendance");
        if (deleteSelectedAttendance) {
            deleteSelectedAttendance.addEventListener("click", function () {
                if (!selectedAttendance) {
                    return;
                }

                const confirmed = window.confirm("Delete the selected attendance record?");

                if (!confirmed) {
                    return;
                }

                app.deleteAttendanceRecord(selectedAttendance.id);
                state.attendanceId = "";
                app.setPageNotice("students", "success", "Attendance record deleted successfully.");
                renderStudentsPage();
            });
        }
    }

    function renderTeacherView(user) {
        const sections = app.getSectionSummaries(user.advisorySections);

        if (!sections.length) {
            app.renderProtectedShell({
                activePage: "students",
                title: "Students",
                subtitle: "Manage learner profiles and full attendance CRUD operations for the selected class section.",
                headerMeta: [
                    "0 active sections",
                    "0 learners",
                    "Create a section first"
                ],
                headerActions: '' +
                    '<a href="class-list.html" class="btn btn-primary btn-fit">Create Section</a>' +
                    '<a href="dashboard.html" class="btn btn-outline btn-fit">Dashboard</a>',
                content: app.emptyStateMarkup(
                    "No section is available for student management",
                    "Create a class section first, then return here to add learner profiles and attendance records.",
                    '<a href="class-list.html" class="btn btn-primary btn-fit">Go to Class List</a>'
                )
            });

            return;
        }

        const activeSection = sections.find(function (section) {
            return section.id === (state.sectionId || sections[0].id);
        }) || sections[0];
        const selectedStudent = activeSection.roster.find(function (record) {
            return record.student.id === state.studentId;
        }) || null;
        const attendanceRecords = app.getAttendance()
            .map(app.decorateAttendanceEntry)
            .filter(function (record) {
                return record.sectionId === activeSection.id;
            });
        const selectedAttendance = attendanceRecords.find(function (record) {
            return record.id === state.attendanceId;
        }) || null;
        const attendanceStudentId = selectedAttendance
            ? selectedAttendance.studentId
            : (selectedStudent ? selectedStudent.student.id : state.studentId);

        state.sectionId = activeSection.id;

        app.renderProtectedShell({
            activePage: "students",
            title: "Students",
            subtitle: "Manage learner profiles and full attendance CRUD operations for the selected class section.",
            headerMeta: [
                activeSection.name,
                activeSection.studentCount + " learners",
                activeSection.attendanceRate + "% attendance"
            ],
            headerActions: '' +
                '<a href="class-list.html" class="btn btn-primary btn-fit">Back to Class List</a>' +
                '<a href="grading.html" class="btn btn-outline btn-fit">Open Grading</a>',
            content: '' +
                '<div id="students-message"></div>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Section Filter</h2><p>Switch section to manage another set of student and attendance records.</p></div></div>' +
                    '<div class="form-group-settings"><label for="records-section">Section</label><select id="records-section" class="form-input">' + buildSectionOptions(sections) + '</select></div>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Student Profiles</h2><p>Teacher-side student record CRUD for the current section.</p></div><div class="button-row no-print"><button type="button" class="btn btn-outline btn-fit" id="new-student-mode">New Student</button></div></div>' +
                        profileCards(activeSection.roster, true) +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>' + (selectedStudent ? "Edit Student" : "Create Student") + '</h2><p>Save new learners or update the selected profile.</p></div></div>' +
                        '<form id="student-form" class="form-grid">' +
                            '<div class="form-group-settings full-width"><label for="student-name">Full Name</label><input id="student-name" class="form-input" type="text" value="' + app.escapeHtml(selectedStudent ? selectedStudent.student.name : "") + '"></div>' +
                            '<div class="form-group-settings"><label for="student-email">Email</label><input id="student-email" class="form-input" type="email" value="' + app.escapeHtml(selectedStudent ? selectedStudent.student.email : "") + '"></div>' +
                            '<div class="form-group-settings"><label for="student-number">Student Number</label><input id="student-number" class="form-input" type="text" value="' + app.escapeHtml(selectedStudent ? selectedStudent.student.studentNumber : "") + '"></div>' +
                            '<div class="form-group-settings"><label for="student-focus">Focus</label><input id="student-focus" class="form-input" type="text" value="' + app.escapeHtml(selectedStudent ? selectedStudent.student.focus : "") + '"></div>' +
                            '<div class="form-group-settings"><label for="student-guardian">Guardian</label><input id="student-guardian" class="form-input" type="text" value="' + app.escapeHtml(selectedStudent ? selectedStudent.student.guardian : "") + '"></div>' +
                            '<div class="form-group-settings"><label for="student-contact">Contact</label><input id="student-contact" class="form-input" type="text" value="' + app.escapeHtml(selectedStudent ? selectedStudent.student.contact : "") + '"></div>' +
                            '<div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">' + (selectedStudent ? "Save Changes" : "Create Student") + '</button><button type="button" class="btn btn-danger-outline btn-fit" id="delete-selected-student"' + (selectedStudent ? "" : " disabled") + '>Delete</button></div>' +
                        '</form>' +
                    '</article>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>' + (selectedAttendance ? "Edit Attendance" : "Create Attendance") + '</h2><p>Save or update attendance for a learner on a specific class date.</p></div><div class="button-row no-print"><button type="button" class="btn btn-outline btn-fit" id="new-attendance-mode">New Attendance</button></div></div>' +
                        '<form id="attendance-form" class="form-grid">' +
                            '<div class="form-group-settings full-width"><label for="attendance-student">Learner</label><select id="attendance-student" class="form-input">' + buildStudentOptions(activeSection.roster, attendanceStudentId) + '</select></div>' +
                            '<div class="form-group-settings"><label for="attendance-date">Date</label><input id="attendance-date" class="form-input" type="date" value="' + app.escapeHtml(selectedAttendance ? selectedAttendance.date : new Date().toISOString().slice(0, 10)) + '"></div>' +
                            '<div class="form-group-settings"><label for="attendance-status">Status</label><select id="attendance-status" class="form-input"><option value="present"' + (selectedAttendance && selectedAttendance.status === "present" ? " selected" : "") + '>Present</option><option value="late"' + (selectedAttendance && selectedAttendance.status === "late" ? " selected" : "") + '>Late</option><option value="absent"' + (selectedAttendance && selectedAttendance.status === "absent" ? " selected" : "") + '>Absent</option></select></div>' +
                            '<div class="form-group-settings full-width"><label for="attendance-topic">Topic / Meeting</label><input id="attendance-topic" class="form-input" type="text" value="' + app.escapeHtml(selectedAttendance ? selectedAttendance.topic : "") + '" placeholder="Enter class activity or lesson"></div>' +
                            '<div class="form-group-settings full-width"><label for="attendance-remarks">Remarks</label><textarea id="attendance-remarks" class="form-input" placeholder="Optional notes">' + app.escapeHtml(selectedAttendance ? selectedAttendance.remarks : "") + '</textarea></div>' +
                            '<div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">' + (selectedAttendance ? "Save Attendance" : "Create Attendance") + '</button><button type="button" class="btn btn-danger-outline btn-fit" id="delete-selected-attendance"' + (selectedAttendance ? "" : " disabled") + '>Delete</button></div>' +
                        '</form>' +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Attendance Records</h2><p>All attendance entries for the selected section with edit and delete actions.</p></div></div>' +
                        attendanceTable(attendanceRecords, true) +
                    '</article>' +
                '</section>'
        });

        app.renderPageNotice("students", document.getElementById("students-message"));
        bindTeacherControls(user, sections, activeSection, selectedStudent, selectedAttendance);
    }

    function renderStudentView(user) {
        const snapshot = app.getStudentSnapshot(user);

        if (!snapshot || !snapshot.student) {
            app.renderProtectedShell({
                activePage: "students",
                title: "My Records",
                subtitle: "Review your profile details and personal attendance history stored inside the class record system.",
                headerMeta: [
                    app.getRoleLabel(user.role),
                    "Profile unavailable"
                ],
                headerActions: '' +
                    '<a href="settings.html" class="btn btn-primary btn-fit">Open Settings</a>' +
                    '<a href="login.html" class="btn btn-outline btn-fit" data-logout>Log Out</a>',
                content: app.emptyStateMarkup(
                    "Student profile not found",
                    "There is no linked learner profile available for this account, so personal attendance records cannot be shown."
                )
            });

            return;
        }

        const attendanceRecords = snapshot.attendance.map(function (record) {
            return Object.assign({}, record, {
                student: snapshot.student,
                statusLabel: app.attendanceStatusLabel(record.status)
            });
        });

        app.renderProtectedShell({
            activePage: "students",
            title: "My Records",
            subtitle: "Review your profile details and personal attendance history stored inside the class record system.",
            headerMeta: [
                snapshot.student.studentNumber,
                snapshot.section.name,
                snapshot.attendanceSummary.rate + "% attendance"
            ],
            headerActions: '' +
                '<a href="class-list.html" class="btn btn-primary btn-fit">My Class</a>' +
                '<a href="grading.html" class="btn btn-outline btn-fit">View Grades</a>',
            content: '' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Profile Details</h2><p>Your saved learner profile and contact information.</p></div></div>' +
                        profileCards([snapshot], false) +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Attendance Summary</h2><p>Present and late records are counted as attended meetings.</p></div></div>' +
                        '<div class="summary-grid">' +
                            '<div class="summary-item"><span class="summary-emphasis">Present</span><strong>' + snapshot.attendanceSummary.present + '</strong></div>' +
                            '<div class="summary-item"><span class="summary-emphasis">Late</span><strong>' + snapshot.attendanceSummary.late + '</strong></div>' +
                            '<div class="summary-item"><span class="summary-emphasis">Absent</span><strong>' + snapshot.attendanceSummary.absent + '</strong></div>' +
                        '</div>' +
                        '<div class="profile-note"><strong>Attendance Rate:</strong> ' + snapshot.attendanceSummary.rate + '%</div>' +
                    '</article>' +
                '</section>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Attendance Records</h2><p>Your complete attendance history for the currently seeded meetings.</p></div></div>' +
                    attendanceTable(attendanceRecords, false) +
                '</section>'
        });
    }

    function renderStudentsPage() {
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
        if (document.body.getAttribute("data-page") === "students") {
            renderStudentsPage();
        }
    });
}(window, document));
