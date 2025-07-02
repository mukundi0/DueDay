# DueDay

DueDay: University Companion Platform
Never miss what's due. Ever. DueDay is a comprehensive web platform designed to help university students, module leaders, and event coordinators stay organized and connected. It centralizes assignments, events, polls, and timetables into a single, easy-to-use interface.

Key Features
Dashboard: A personalized home page showing upcoming classes, assignments due, active polls, and recent notifications.

Assignment Management: Module leaders can create and manage assignments. Students can submit their work, view grades, and communicate via a comment section for each assignment.

Interactive Polls: Module leaders can create polls for their classes to gather feedback, with real-time results.

Event Coordination: Event coordinators can create and manage university events. Students can view upcoming events and RSVP.

Personal Timetable: Students get a personalized weekly view of their class schedule.

Role-Based Access Control: The application supports distinct roles (Student, Module Leader, Event Coordinator, Admin) with specific permissions.

Admin Panel: A complete backend interface for administrators to manage users, classes, venues, announcements, and the master schedule.

Tech Stack
Backend: PHP

Database: MySQL

Frontend: HTML, CSS, JavaScript (no frameworks)

Getting Started
Follow these instructions to get a local copy of the project up and running for development and testing purposes.

Prerequisites
You will need a local server environment that supports PHP and MySQL. We recommend using one of the following:

XAMPP (Windows, macOS, Linux)

WAMP (Windows)

MAMP (macOS)

Installation
Clone the repository:

git clone <your-repository-url>

Move to your server's web directory:
Move the cloned project folder into the htdocs directory (for XAMPP) or www (for WAMP/MAMP).

Set up the database:

Open your database management tool (e.g., phpMyAdmin).

Create a new database named dueday.

Select the dueday database and import the dueday.sql file located in the project's root directory. This will create all the necessary tables and seed them with initial data.

Configure the database connection:

Open the file core/init.php.

Update the database credentials if they are different from the default:

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DueDay";

Run the application:

Start your Apache and MySQL services from your XAMPP/WAMP/MAMP control panel.

Open your web browser and navigate to http://localhost/DueDay-6c2bc8c1c46ae031320135d3e6231462e5477af3/ (or the name you gave the project folder).

Project Structure
The project follows a modular structure to keep the code organized:

/admin: Contains all files related to the administrator panel.

/assets: Holds all static files like CSS, JavaScript, and icons.

/auth: Manages user authentication (login, registration, logout).

/core: Core application logic, including database initialization and helper functions.

/templates: Reusable header and footer files for the main site.

/uploads: Default directory for file submissions.

*.php: Root-level files are the main user-facing pages (e.g., home.php, assignment.php).

Roles & Responsibilities
This project is divided among five team members, each with a specific focus:

Karanei: Backend and Database

Focus: Core server-side logic, database schema (dueday.sql), and data provider scripts (get_comments.php, etc.).

Branch: feature/backend-core

Travis: Assignments & Polls

Focus: User-facing features for assignment and poll management.

Branch: feature/assignments-polls

Natalie: Events, Timetable & Profile

Focus: User-facing components for events, the weekly timetable, user profiles, and the main dashboard.

Branch: feature/events-timetable

Alvin: Admin Panel

Focus: The entire admin section, including its specific CSS and JS files.

Branch: feature/admin-panel

Mukundi: Authentication & System Files

Focus: User authentication flow (login/register), file upload handling, and system logs.

Branch: feature/authentication

Contributing
Contributions are welcome! Please follow these steps:

Create a new branch for your feature (git checkout -b feature/YourFeatureName).

Commit your changes (git commit -m 'Add some YourFeatureName').

Push to the branch (git push origin feature/YourFeatureName).

Open a Pull Request.

License
This project is licensed under the MIT License - see the LICENSE.md file for details.