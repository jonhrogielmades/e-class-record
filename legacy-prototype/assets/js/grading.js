(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp;
    const state = {
        sectionId: "",
        studentId: "",
        gradeId: ""
    };

    function gradeTable(entries, teacherMode) {
        if (!entries.length) {
            return '<div class="empty-state"><div><h3>No grade records found</h3><p>Saved assessment entries will appear here.</p></div></div>';
        }

        return '' +
            '<div class="table-responsive">' +
                '<table class="history-table">' +
                    '<thead><tr><th>Date</th><th>Learner</th><th>Assessment</th><th>Category</th><th>Score</th><th>Percent</th>' + (teacherMode ? '<th>Actions</th>' : '') + '</tr></thead>' +
                    '<tbody>' +
                        entries.map(function (entry) {
                            return '' +
                                '<tr>' +
                                    '<td>' + app.escapeHtml(app.formatDate(entry.recordedAt)) + '</td>' +
                                    '<td>' + app.escapeHtml(entry.studentName || "") + '</td>' +
                                    '<td>' + app.escapeHtml(entry.title) + '</td>' +
                                    '<td>' + app.escapeHtml(entry.category) + '</td>' +
                                    '<td>' + entry.score + ' / ' + entry.maxScore + '</td>' +
                                    '<td><span class="status-pill ' + app.performanceTone(entry.percentage) + '">' + entry.percentage + '%</span></td>' +
                                    (teacherMode
                                        ? '<td><div class="button-row table-action-row"><button type="button" class="btn btn-outline btn-fit btn-xs" data-edit-grade="' + entry.id + '">Edit</button><button type="button" class="btn btn-danger-outline btn-fit btn-xs" data-delete-grade="' + entry.id + '">Delete</button></div></td>'
                                        : '') +
                                '</tr>';
                        }).join("") +
                    '</tbody>' +
                '</table>' +
            '</div>';
    }

    function leaderboardTable(records) {
        if (!records.length) {
            return app.emptyStateMarkup(
                "No learners in this section yet",
                "Add student profiles to this section to populate the leaderboard."
            );
        }

        return '' +
            '<div class="table-responsive">' +
                '<table class="history-table">' +
                    '<thead><tr><th>Learner</th><th>Student No.</th><th>Average</th><th>Attendance</th></tr></thead>' +
                    '<tbody>' +
                        records.map(function (record) {
                            return '' +
                                '<tr>' +
                                    '<td>' + app.escapeHtml(record.student.name) + '</td>' +
                                    '<td>' + app.escapeHtml(record.student.studentNumber) + '</td>' +
                                    '<td><span class="status-pill ' + app.performanceTone(record.gradeAverage) + '">' + record.gradeAverage + '%</span></td>' +
                                    '<td>' + record.attendanceSummary.rate + '%</td>' +
                                '</tr>';
                        }).join("") +
                    '</tbody>' +
                '</table>' +
            '</div>';
    }

    function buildSectionOptions(sections) {
        return sections.map(function (section) {
            const selected = section.id === state.sectionId ? " selected" : "";
            return '<option value="' + section.id + '"' + selected + '>' + app.escapeHtml(section.name + " - " + section.strand) + '</option>';
        }).join("");
    }

    function buildStudentOptions(roster, selectedStudentId) {
        return ['<option value="">Select learner</option>'].concat(roster.map(function (record) {
            const selected = record.student.id === selectedStudentId ? " selected" : "";
            return '<option value="' + record.student.id + '"' + selected + '>' + app.escapeHtml(record.student.name + " (" + record.student.studentNumber + ")") + '</option>';
        })).join("");
    }

    function drawTeacherChart(activeSection) {
        app.drawBarChart(
            document.getElementById("grading-section-chart"),
            activeSection.roster.map(function (record) { return record.student.name.split(" ")[0]; }),
            activeSection.roster.map(function (record) { return record.gradeAverage; }),
            { max: 100, barColor: "#38bdf8" }
        );
    }

    function drawStudentChart(snapshot) {
        app.drawLineChart(
            document.getElementById("student-grade-history-chart"),
            snapshot.grades.slice(0, 6).reverse().map(function (grade, index) { return "A" + (index + 1); }),
            snapshot.grades.slice(0, 6).reverse().map(function (grade) { return app.getGradePercentage(grade); }),
            { max: 100, lineColor: "#38bdf8", areaColor: "rgba(56, 189, 248, 0.14)" }
        );
    }

    function bindTeacherControls(user, sections, activeSection, selectedGrade) {
        const messageBox = document.getElementById("grading-message");
        const sectionSelect = document.getElementById("grading-section");
        const form = document.getElementById("grade-form");

        if (sectionSelect) {
            sectionSelect.addEventListener("change", function (event) {
                state.sectionId = event.target.value;
                state.studentId = "";
                state.gradeId = "";
                renderGradingPage();
            });
        }

        document.getElementById("grading-student").addEventListener("change", function (event) {
            state.studentId = event.target.value;
        });

        document.querySelectorAll("[data-edit-grade]").forEach(function (button) {
            button.addEventListener("click", function () {
                state.gradeId = button.getAttribute("data-edit-grade");
                renderGradingPage();
            });
        });

        document.querySelectorAll("[data-delete-grade]").forEach(function (button) {
            button.addEventListener("click", function () {
                const gradeId = button.getAttribute("data-delete-grade");
                const confirmed = window.confirm("Delete this grade record?");

                if (!confirmed) {
                    return;
                }

                app.deleteGradeEntry(gradeId);
                state.gradeId = "";
                app.setPageNotice("grading", "success", "Grade record deleted successfully.");
                renderGradingPage();
            });
        });

        const newButton = document.getElementById("new-grade-mode");
        if (newButton) {
            newButton.addEventListener("click", function () {
                state.gradeId = "";
                state.studentId = "";
                renderGradingPage();
            });
        }

        const deleteSelectedButton = document.getElementById("delete-selected-grade");
        if (deleteSelectedButton) {
            deleteSelectedButton.addEventListener("click", function () {
                if (!selectedGrade) {
                    return;
                }

                const confirmed = window.confirm("Delete the selected grade record?");

                if (!confirmed) {
                    return;
                }

                app.deleteGradeEntry(selectedGrade.id);
                state.gradeId = "";
                app.setPageNotice("grading", "success", "Grade record deleted successfully.");
                renderGradingPage();
            });
        }

        if (!form) {
            return;
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const payload = {
                studentId: document.getElementById("grading-student").value,
                sectionId: activeSection.id,
                title: document.getElementById("assessment-title").value.trim(),
                category: document.getElementById("assessment-category").value,
                score: Number(document.getElementById("assessment-score").value),
                maxScore: Number(document.getElementById("assessment-max-score").value),
                remarks: document.getElementById("assessment-remarks").value.trim(),
                recordedBy: user.id
            };

            if (!payload.studentId || !payload.title || !payload.category) {
                app.showMessage(messageBox, "error", "Please complete the grading form before saving.");
                return;
            }

            if (!Number.isFinite(payload.score) || !Number.isFinite(payload.maxScore) || payload.score < 0 || payload.maxScore <= 0 || payload.score > payload.maxScore) {
                app.showMessage(messageBox, "error", "Enter a valid score from 0 up to the max score.");
                return;
            }

            if (selectedGrade) {
                app.updateGradeEntry(selectedGrade.id, payload);
                app.setPageNotice("grading", "success", "Grade record updated successfully.");
            } else {
                app.saveGradeEntry(payload);
                app.setPageNotice("grading", "success", "Grade record created successfully.");
            }

            state.gradeId = "";
            renderGradingPage();
        });
    }

    function renderTeacherView(user) {
        const sections = app.getSectionSummaries(user.advisorySections);

        if (!sections.length) {
            app.renderProtectedShell({
                activePage: "grading",
                title: "Grading",
                subtitle: "Create, update, and delete quiz, exam, or project scores for the selected class section.",
                headerMeta: [
                    "0 active sections",
                    "0 grade records",
                    "Create a section first"
                ],
                headerActions: '' +
                    '<a href="class-list.html" class="btn btn-primary btn-fit">Create Section</a>' +
                    '<a href="dashboard.html" class="btn btn-outline btn-fit">Dashboard</a>',
                content: app.emptyStateMarkup(
                    "No section is available for grading",
                    "Create a class section first, then return here to record quizzes, exams, and project scores.",
                    '<a href="class-list.html" class="btn btn-primary btn-fit">Go to Class List</a>'
                )
            });

            return;
        }

        const activeSection = sections.find(function (section) {
            return section.id === (state.sectionId || sections[0].id);
        }) || sections[0];
        const gradeRecords = app.getGrades()
            .map(app.decorateGradeEntry)
            .filter(function (entry) {
                return entry.sectionId === activeSection.id;
            });
        const selectedGrade = gradeRecords.find(function (entry) {
            return entry.id === state.gradeId;
        }) || null;

        state.sectionId = activeSection.id;
        state.studentId = selectedGrade ? selectedGrade.studentId : state.studentId;

        app.renderProtectedShell({
            activePage: "grading",
            title: "Grading",
            subtitle: "Create, update, and delete quiz, exam, or project scores for the selected class section.",
            headerMeta: [
                activeSection.name,
                activeSection.averageGrade + "% class average",
                activeSection.pendingGrades + " pending slots"
            ],
            headerActions: '' +
                '<a href="dashboard.html" class="btn btn-primary btn-fit">Dashboard</a>' +
                '<a href="students.html" class="btn btn-outline btn-fit">Attendance</a>',
            content: '' +
                '<div id="grading-message"></div>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>' + (selectedGrade ? "Edit Grade" : "Create Grade") + '</h2><p>Use full CRUD controls for assessments in the current section.</p></div><div class="button-row no-print"><button type="button" class="btn btn-outline btn-fit" id="new-grade-mode">New Grade</button></div></div>' +
                        '<form id="grade-form" class="form-grid">' +
                            '<div class="form-group-settings full-width"><label for="grading-section">Section</label><select id="grading-section" class="form-input">' + buildSectionOptions(sections) + '</select></div>' +
                            '<div class="form-group-settings full-width"><label for="grading-student">Learner</label><select id="grading-student" class="form-input">' + buildStudentOptions(activeSection.roster, state.studentId) + '</select></div>' +
                            '<div class="form-group-settings full-width"><label for="assessment-title">Assessment Title</label><input id="assessment-title" class="form-input" type="text" value="' + app.escapeHtml(selectedGrade ? selectedGrade.title : "") + '" placeholder="Quiz 3 - Responsive Layout"></div>' +
                            '<div class="form-group-settings"><label for="assessment-category">Category</label><select id="assessment-category" class="form-input"><option value="Quiz"' + (selectedGrade && selectedGrade.category === "Quiz" ? " selected" : "") + '>Quiz</option><option value="Exam"' + (selectedGrade && selectedGrade.category === "Exam" ? " selected" : "") + '>Exam</option><option value="Project"' + (selectedGrade && selectedGrade.category === "Project" ? " selected" : "") + '>Project</option><option value="Performance Task"' + (selectedGrade && selectedGrade.category === "Performance Task" ? " selected" : "") + '>Performance Task</option></select></div>' +
                            '<div class="form-group-settings"><label for="assessment-score">Score</label><input id="assessment-score" class="form-input" type="number" min="0" step="1" value="' + app.escapeHtml(selectedGrade ? selectedGrade.score : "") + '"></div>' +
                            '<div class="form-group-settings"><label for="assessment-max-score">Max Score</label><input id="assessment-max-score" class="form-input" type="number" min="1" step="1" value="' + app.escapeHtml(selectedGrade ? selectedGrade.maxScore : "100") + '"></div>' +
                            '<div class="form-group-settings full-width"><label for="assessment-remarks">Remarks</label><textarea id="assessment-remarks" class="form-input" placeholder="Optional teacher remarks">' + app.escapeHtml(selectedGrade ? selectedGrade.remarks : "") + '</textarea></div>' +
                            '<div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">' + (selectedGrade ? "Save Changes" : "Create Grade") + '</button><button type="button" class="btn btn-danger-outline btn-fit" id="delete-selected-grade"' + (selectedGrade ? "" : " disabled") + '>Delete</button></div>' +
                        '</form>' +
                    '</article>' +
                    '<article class="glass-card chart-card">' +
                        '<div class="section-head"><div><h2>Section Grade Summary</h2><p>Current learner averages inside the selected section.</p></div></div>' +
                        '<canvas id="grading-section-chart"></canvas>' +
                    '</article>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Leaderboard</h2><p>Average standing for each learner in the selected class.</p></div></div>' +
                        leaderboardTable(activeSection.roster) +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Grade Records</h2><p>All saved records filtered by section with edit and delete actions.</p></div></div>' +
                        gradeTable(gradeRecords, true) +
                    '</article>' +
                '</section>'
        });

        app.renderPageNotice("grading", document.getElementById("grading-message"));
        drawTeacherChart(activeSection);
        bindTeacherControls(user, sections, activeSection, selectedGrade);
    }

    function renderStudentView(user) {
        const snapshot = app.getStudentSnapshot(user);

        if (!snapshot || !snapshot.student) {
            app.renderProtectedShell({
                activePage: "grading",
                title: "Grades",
                subtitle: "Review your assessment history and grouped averages for quizzes, exams, and projects.",
                headerMeta: [
                    app.getRoleLabel(user.role),
                    "Profile unavailable"
                ],
                headerActions: '' +
                    '<a href="settings.html" class="btn btn-primary btn-fit">Open Settings</a>' +
                    '<a href="login.html" class="btn btn-outline btn-fit" data-logout>Log Out</a>',
                content: app.emptyStateMarkup(
                    "Student profile not found",
                    "There is no linked learner profile available for this account, so grade history cannot be displayed."
                )
            });

            return;
        }

        const gradeEntries = snapshot.grades.map(app.decorateGradeEntry);

        app.renderProtectedShell({
            activePage: "grading",
            title: "Grades",
            subtitle: "Review your assessment history and grouped averages for quizzes, exams, and projects.",
            headerMeta: [
                snapshot.student.studentNumber,
                snapshot.gradeAverage + "% average",
                snapshot.grades.length + " assessments"
            ],
            headerActions: '' +
                '<a href="dashboard.html" class="btn btn-primary btn-fit">Dashboard</a>' +
                '<a href="students.html" class="btn btn-outline btn-fit">My Records</a>',
            content: '' +
                '<section class="stats-grid compact-stats">' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>General Average</h3><div class="stat-value">' + snapshot.gradeAverage + '%</div><span class="stat-change gold">' + app.performanceLabel(snapshot.gradeAverage) + '</span></div><div class="stat-icon gold">' + app.navIcon("grading") + '</div></div></div>' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Latest Score</h3><div class="stat-value">' + (snapshot.latestGrade ? app.getGradePercentage(snapshot.latestGrade) + '%' : "N/A") + '</div><span class="stat-change emerald">Most recent assessment</span></div><div class="stat-icon emerald">' + app.navIcon("dashboard") + '</div></div></div>' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Section</h3><div class="stat-value">' + app.escapeHtml(snapshot.section.name) + '</div><span class="stat-change blue">' + app.escapeHtml(snapshot.section.room) + '</span></div><div class="stat-icon blue">' + app.navIcon("class-list") + '</div></div></div>' +
                    '<div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Completed</h3><div class="stat-value">' + snapshot.grades.length + '</div><span class="stat-change rose">Saved grade entries</span></div><div class="stat-icon rose">' + app.navIcon("settings") + '</div></div></div>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card chart-card"><div class="section-head"><div><h2>Assessment Trend</h2><p>Your latest assessment percentages plotted over time.</p></div></div><canvas id="student-grade-history-chart"></canvas></article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Category Summary</h2><p>Average score by assessment type.</p></div></div>' +
                        '<div class="summary-grid">' +
                            snapshot.categoryAverages.map(function (item) {
                                return '<div class="summary-item"><span class="summary-emphasis">' + app.escapeHtml(item.category) + '</span><strong>' + item.average + '%</strong></div>';
                            }).join("") +
                        '</div>' +
                    '</article>' +
                '</section>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Grade History</h2><p>Complete saved assessment history for your student profile.</p></div></div>' +
                    gradeTable(gradeEntries, false) +
                '</section>'
        });

        drawStudentChart(snapshot);
    }

    function renderGradingPage() {
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
        if (document.body.getAttribute("data-page") === "grading") {
            renderGradingPage();
        }
    });
}(window, document));
