document.addEventListener('DOMContentLoaded', function () {

    // ======================================================================
    //  1. GENERIC INITIALIZERS (RUN ON ALL PAGES)
    // ======================================================================

    const initAllModals = () => {
        const modalTriggers = document.querySelectorAll('[data-modal-target]');
        modalTriggers.forEach(trigger => {
            const modalId = trigger.dataset.modalTarget;
            const modal = document.getElementById(modalId);
            if (modal) {
                const closeModal = modal.querySelector('.close-modal');
                trigger.addEventListener('click', () => modal.classList.add('is-active'));
                if (closeModal) {
                    closeModal.addEventListener('click', () => modal.classList.remove('is-active'));
                }
            }
        });
        window.addEventListener('click', e => {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('is-active');
            }
        });
    };

    const initViewToggler = () => {
        const createBtn = document.getElementById('createBtn');
        const viewBtn = document.getElementById('viewBtn');
        const createSection = document.getElementById('createSection');
        const viewSection = document.getElementById('viewSection');

        if (!createBtn || !viewBtn || !createSection || !viewSection) return;

        viewSection.style.display = 'block';
        createSection.style.display = 'none';
        viewBtn.classList.add('is-active');
        createBtn.classList.remove('is-active');

        createBtn.addEventListener('click', (e) => {
            e.preventDefault();
            viewSection.style.display = 'none';
            createSection.style.display = 'block';
            viewBtn.classList.remove('is-active');
            createBtn.classList.add('is-active');
        });

        viewBtn.addEventListener('click', (e) => {
            e.preventDefault();
            createSection.style.display = 'none';
            viewSection.style.display = 'block';
            createBtn.classList.remove('is-active');
            viewBtn.classList.add('is-active');
        });
    };

    const initConfirmForms = () => {
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', function (e) {
                const confirmationMessage = this.getAttribute('data-confirm');
                if (!confirm(confirmationMessage)) {
                    e.preventDefault();
                }
            });
        });
    };

    // ======================================================================
    //  2. PAGE-SPECIFIC INITIALIZERS
    // ======================================================================

    const initAssignmentsPage = () => {
        const modalAssignmentIdInput = document.getElementById('modal_assignment_id');
        if (!modalAssignmentIdInput) return;

        document.querySelectorAll('.submit-work-btn').forEach(button => {
            button.addEventListener('click', function () {
                modalAssignmentIdInput.value = this.getAttribute('data-assignment-id');
            });
        });
    };

    const initPollsPage = () => {
        const addOptionBtn = document.getElementById('addPollOptionBtn');
        const optionsContainer = document.getElementById('pollOptionsContainer');
        if (addOptionBtn && optionsContainer) {
            addOptionBtn.addEventListener('click', () => {
                const optionCount = optionsContainer.children.length + 1;
                const newOption = document.createElement('div');
                newOption.className = 'poll-option-item';
                newOption.innerHTML = `<input type="text" name="options[]" class="form-input" placeholder="Option ${optionCount}"><button type="button" class="btn remove-option-btn">&times;</button>`;
                optionsContainer.appendChild(newOption);
            });
            optionsContainer.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('remove-option-btn')) {
                    if (optionsContainer.children.length > 2) {
                        e.target.parentElement.remove();
                    } else {
                        alert("A poll must have at least two options.");
                    }
                }
            });
        }
        const resultsModal = document.getElementById('resultsModal');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultsTitle = document.getElementById('resultsTitle');
        document.querySelectorAll('.view-results-btn').forEach(button => {
            button.addEventListener('click', function () {
                const pollId = this.dataset.pollId;
                if (!resultsModal || !resultsContainer || !resultsTitle) return;
                fetch(`get_poll_results.php?poll_id=${pollId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            resultsTitle.textContent = `Results for: ${data.poll_title}`;
                            resultsContainer.innerHTML = '';
                            const totalVotes = data.results.reduce((sum, item) => sum + item.vote_count, 0);
                            if (data.results.length > 0 && totalVotes > 0) {
                                data.results.forEach(option => {
                                    const percentage = ((option.vote_count / totalVotes) * 100).toFixed(1);
                                    resultsContainer.innerHTML += `<div class="result-item"><div class="result-label"><span>${option.option_text}</span><span>${percentage}% (${option.vote_count} votes)</span></div><div class="result-bar-container"><div class="result-bar" style="width: ${percentage}%"></div></div></div>`;
                                });
                            } else {
                                resultsContainer.innerHTML = '<p>No votes have been cast in this poll yet.</p>';
                            }
                        } else {
                            resultsContainer.innerHTML = `<p>Error: ${data.message || 'Could not load results.'}</p>`;
                        }
                    })
                    .catch(error => console.error('Error fetching poll results:', error));
            });
        });
    };

    const initCommentsModal = () => {
        const commentsModal = document.getElementById('commentsModal');
        if (!commentsModal) return;

        const modalAssignmentIdInput = document.getElementById('modal_comment_assignment_id');
        const commentsContainer = document.getElementById('comments-container');
        const commentForm = document.getElementById('commentForm');
        const commentTextArea = document.getElementById('comment_text');

        document.querySelectorAll('.comments-btn').forEach(button => {
            button.addEventListener('click', function () {
                const assignmentId = this.getAttribute('data-assignment-id');
                modalAssignmentIdInput.value = assignmentId;
                commentsContainer.innerHTML = '<p>Loading comments...</p>';
                fetch(`get_comments.php?assignment_id=${assignmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderComments(data.comments);
                            const formData = new FormData();
                            formData.append('assignment_id', assignmentId);
                            fetch('mark_comments_as_read.php', { method: 'POST', body: formData })
                                .catch(error => console.error('Error marking comments as read:', error));
                        } else {
                            commentsContainer.innerHTML = `<p>Error: ${data.message}</p>`;
                        }
                    })
                    .catch(error => console.error('Error fetching comments:', error));
            });
        });

        commentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Posting...';
            fetch('assignment.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        addCommentToDOM(data.comment);
                        commentTextArea.value = '';
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(error => console.error('Error posting comment:', error))
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Post Comment';
                });
        });

        const renderComments = (comments) => {
            commentsContainer.innerHTML = (comments.length === 0) ? '<p>No comments have been posted for this assignment yet.</p>' : '';
            comments.forEach(addCommentToDOM);
        };

        const addCommentToDOM = (comment) => {
            const noCommentsMsg = commentsContainer.querySelector('p');
            if (noCommentsMsg) noCommentsMsg.remove();
            const commentEl = document.createElement('div');
            commentEl.className = 'comment-item';
            const formattedDate = new Date(comment.Comment_Date).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
            commentEl.innerHTML = `<div class="comment-header"><span class="comment-author">${comment.F_Name}</span><span class="comment-date">${formattedDate}</span></div><div class="comment-body"><p>${comment.Comment_Text.replace(/\n/g, '<br>')}</p></div>`;
            commentsContainer.appendChild(commentEl);
            commentsContainer.scrollTop = commentsContainer.scrollHeight;
        };
    };
    
    // --- NEW: Code from timetable.js is now fully integrated here ---
    const initTimetablePage = () => {
        const switchViewBtn = document.getElementById('switchViewBtn');
        const dailyView = document.getElementById('dailyView');
        const weeklyView = document.getElementById('weeklyView');
        const dailyScheduleContainer = document.getElementById('dailyScheduleContainer');
        const currentDayEl = document.getElementById('current-day');
        const prevDayBtn = document.querySelector('.prev-day');
        const nextDayBtn = document.querySelector('.next-day');
    
        const scheduleDataEl = document.getElementById('schedule-data');
        if (!scheduleDataEl) {
            // This check handles cases where the timetable page might not have the data script tag.
            // But based on your `timetable.php`, it should always be there.
            return;
        }
        const scheduleData = JSON.parse(scheduleDataEl.textContent || '{}');
        const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        let currentDayIndex = new Date().getDay(); 
    
        function renderDailySchedule(dayIndex) {
            if (!dailyScheduleContainer || !currentDayEl) return;
    
            dailyScheduleContainer.innerHTML = '';
            currentDayEl.textContent = dayNames[dayIndex];
            
            const dayKey = dayIndex + 1; // Map JS day index (0-6) to your DB day key (1-7)
    
            if (scheduleData[dayKey] && scheduleData[dayKey].length > 0) {
                scheduleData[dayKey].forEach(cls => {
                    const card = document.createElement('div');
                    card.className = 'class-block';
                    
                    const startTime = new Date(`1970-01-01T${cls.Start_Time}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const endTime = new Date(`1970-01-01T${cls.End_Time}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
                    card.innerHTML = `
                        <strong>${startTime} - ${endTime}</strong><br>
                        ${cls.Class_Name}<br>
                        <small>${cls.Venue_Name}</small>
                    `;
                    dailyScheduleContainer.appendChild(card);
                });
            } else {
                dailyScheduleContainer.innerHTML = '<p class="no-classes-msg">No classes scheduled for this day.</p>';
            }
        }
    
        function initializeTimetable() {
            if (!switchViewBtn || !dailyView || !weeklyView) return;
    
            weeklyView.style.display = 'grid';
            dailyView.style.display = 'none';
            switchViewBtn.textContent = 'Day View';
    
            switchViewBtn.addEventListener('click', () => {
                const isWeeklyViewVisible = weeklyView.style.display !== 'none';
                if (isWeeklyViewVisible) {
                    weeklyView.style.display = 'none';
                    dailyView.style.display = 'block';
                    switchViewBtn.textContent = 'Week View';
                } else {
                    weeklyView.style.display = 'grid';
                    dailyView.style.display = 'none';
                    switchViewBtn.textContent = 'Day View';
                }
            });
    
            if (prevDayBtn && nextDayBtn) {
                prevDayBtn.addEventListener('click', () => {
                    currentDayIndex = (currentDayIndex - 1 + 7) % 7;
                    renderDailySchedule(currentDayIndex);
                });
    
                nextDayBtn.addEventListener('click', () => {
                    currentDayIndex = (currentDayIndex + 1) % 7;
                    renderDailySchedule(currentDayIndex);
                });
            }
            
            renderDailySchedule(currentDayIndex);
        }
    
        initializeTimetable();
    };

    const initProfilePage = () => {
        const passwordForm = document.getElementById('passwordChangeForm');
        if (!passwordForm) return;

        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(passwordForm);
            const messageContainer = document.getElementById('password-message-container');
            const submitButton = passwordForm.querySelector('button[type="submit"]');

            messageContainer.innerHTML = '';
            submitButton.disabled = true;
            submitButton.textContent = 'Updating...';

            fetch('profile.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                let messageClass = data.status === 'success' ? 'success' : 'error';
                messageContainer.innerHTML = `<div class="message-banner ${messageClass}" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: ${messageClass === 'success' ? '#d4edda' : '#f8d7da'}; color: ${messageClass === 'success' ? '#155724' : '#721c24'};">${data.message}</div>`;
                if(data.status === 'success') {
                    passwordForm.reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageContainer.innerHTML = `<div class="message-banner error" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;">An unexpected error occurred.</div>`;
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Update Password';
            });
        });
    };

    // ======================================================================
    //  3. SCRIPT EXECUTION ROUTER
    // ======================================================================
    initAllModals();
    initViewToggler();
    initConfirmForms();
    if (document.querySelector('.assignment-view')) { initAssignmentsPage(); initCommentsModal(); }
    if (document.querySelector('.polls-view-section')) { initPollsPage(); }
    if (document.querySelector('.weekly-timetable')) { initTimetablePage(); }
    if (document.getElementById('passwordChangeForm')) { initProfilePage(); }
});