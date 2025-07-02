document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENT SELECTORS ---
    const switchViewBtn = document.getElementById('switchViewBtn');
    const dailyView = document.getElementById('dailyView');
    const weeklyView = document.getElementById('weeklyView');
    const dailyScheduleContainer = document.getElementById('dailyScheduleContainer');
    const currentDayEl = document.getElementById('current-day');
    const prevDayBtn = document.querySelector('.prev-day');
    const nextDayBtn = document.querySelector('.next-day');

    // --- DATA & STATE ---
    const scheduleDataEl = document.getElementById('schedule-data');
    if (!scheduleDataEl) {
        console.error('Schedule data element not found!');
        return;
    }
    const scheduleData = JSON.parse(scheduleDataEl.textContent || '{}');
    const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    let currentDayIndex = new Date().getDay(); // JS standard: 0=Sun, 1=Mon...

    /**
     * Renders the schedule for a given day index.
     */
    function renderDailySchedule(dayIndex) {
        if (!dailyScheduleContainer || !currentDayEl) return;

        dailyScheduleContainer.innerHTML = '';
        currentDayEl.textContent = dayNames[dayIndex];
        
        // Map JS day index (0-6) to DB day key (1-7)
        const dayKey = dayIndex + 1; 

        if (scheduleData[dayKey] && scheduleData[dayKey].length > 0) {
            scheduleData[dayKey].forEach(cls => {
                const card = document.createElement('div');
                card.className = 'class-block'; // Use the same styling
                
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

    /**
     * Sets up all event listeners.
     */
    function initializeTimetable() {
        if (!switchViewBtn || !dailyView || !weeklyView) return;

        // Default state: show weekly view
        weeklyView.style.display = 'grid'; // Use 'grid' as defined in your CSS
        dailyView.style.display = 'none';
        switchViewBtn.textContent = 'Day View';

        // View Switching Logic
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

        // Day Navigation Logic
        if (prevDayBtn && nextDayBtn) {
            prevDayBtn.addEventListener('click', () => {
                currentDayIndex = (currentDayIndex - 1 + 7) % 7; // Cycle backwards
                renderDailySchedule(currentDayIndex);
            });

            nextDayBtn.addEventListener('click', () => {
                currentDayIndex = (currentDayIndex + 1) % 7; // Cycle forwards
                renderDailySchedule(currentDayIndex);
            });
        }
        
        // Initial render for the daily view (so it's ready when toggled)
        renderDailySchedule(currentDayIndex);
    }

    initializeTimetable();
});