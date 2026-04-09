# SocialHub 🌐

**SocialHub** is a feature-rich, premium social media web application inspired by modern design principles. It allows users to connect with friends, share updates (text, photo, and video), chat in real time, post stories, and explore a dynamic social feed. The platform also includes an interactive AI Chat Bot and a comprehensive Admin Panel for content moderation.

---

## ✨ Features

### 💎 Premium Design & UX (New!)
- **Glassmorphic UI**: High-end aesthetic with semi-transparent containers, backdrop blurs, and smooth CSS transitions.
- **Dynamic Theming**: Integrated **Light & Dark Mode** toggle for a comfortable viewing experience at any time of day.
- **Micro-interactions**: Hover effects, scale animations, and smooth sliding transitions for a more "alive" feel.

### 👤 User Capabilities
- **Authentication**: Secure Login and Registration system with password hashing (`password_hash`). Includes a visually appealing success popup.
- **Dynamic News Feed**: View posts from friended users mixed with your own posts.
- **Post Management**: 
  - Create posts with Text, Photos (JPG, PNG, GIF), or Videos (MP4).
  - **Advanced Editing**: Popup-based editing UI with live media preview and selective media removal.
  - Delete own posts.
- **Interactions**: 
  - Like posts and see live counts.
  - **Dynamic Commenting**: Infinite-style comment threads.
  - **Comment Self-Management**: Edit your own comments inline with automatic textarea resizing or delete them.
- **Friend & Safety System**: 
  - Search for users, send/cancel Friend Requests.
  - **User Blocking**: Block/Unblock users to maintain a safe and personalized experience.
- **Stories**: Upload 24-hour stories (images/videos) and view friends' stories.
- **Real-Time Notifications**: Get notified when someone likes, comments, or accepts your friend request.
- **Profile Customization**: Update profile pictures and personal details via a modern profile card UI.

### 💬 Real-Time Chat & AI Bot
- **Instant Messaging**: Chat seamlessly with friends directly from the Messenger page. Features include a clean chat UI with proper message alignment, readable date/time timestamps, auto-scrolling, and unread message counters.
- **🤖 SocialHub Bot**: A built-in virtual assistant. The bot is automatically added as a friend for all new users and seamlessly auto-friends existing ones. It detects keywords to auto-reply to greetings, answers questions, tells jokes, and keeps users engaged.

### 🛡️ Admin Features
- Secure **Admin Dashboard** for platform moderation with strict authentication checks and standardized routing.
- **User Management**: View all registered users and delete accounts ensuring all associated records (posts, comments, etc.) are handled appropriately.
- **Post Moderation**: Filter posts by type (Image, Video, Text) and remove inappropriate content.
- **Story Moderation**: View all active stories and delete them if necessary.

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3 (Modern Flexbox/Grid), JavaScript (using jQuery for AJAX and DOM manipulation)
- **Backend**: PHP (Procedural & Object-Oriented approaches)
- **Database**: MySQL (using `mysqli` with prepared statements for security)
- **Icons & Fonts**: FontAwesome 5, Google Fonts (Inter)

---

## 🚀 Installation & Setup (Local Environment)

Follow these steps to run SocialHub on your local machine using XAMPP:

### 1. Prerequisites
- Install [XAMPP](https://www.apachefriends.org/index.html) (Make sure Apache and MySQL services are running).

### 2. Project Setup
1. Download or clone this repository.
2. Move the project folder into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\facebook`).

### 3. Database Configuration
1. Open your browser and go to `http://localhost/phpmyadmin/`.
2. Create a new database named **`facebook`**.
3. Import the database schema:
   - Go to the **Import** tab in phpMyAdmin.
   - Select the file located at: `_dev_scripts/facebook(1).sql`.
   - Click **Go/Import**.
4. *Note: If your MySQL password is not empty, update the connection settings in `_db_connect.php`.*

### 4. Running the Application
1. Open your browser and navigate to:
   `http://localhost/facebook/` (or whatever you named the folder in htdocs).
2. **Register a new account** to get started, or set up the admin account!

---

## 📂 Project Structure

```text
/facebook
├── admin/                 # Admin Panel Dashboard and Scripts
├── chat_img/              # Directory for chat-related media
├── icon/                  # UI icons
├── img/                   # General platform images and logos
├── post_img/              # User-uploaded images and videos for posts
├── story_media/           # User-uploaded media for stories
├── _db_connect.php        # Database connection configuration
├── ajax.php               # Core backend API handling all AJAX requests (Posts, Chat, Likes, Bot logic)
├── create_admin.php       # Script to generate admin credentials
├── find_friends.php       # Page to search and add new friends
├── friends.php            # Manage incoming requests and current friends
├── index.php              # Login page / Landing page
├── messanger.php          # Chat application UI
├── navbar.php             # Reusable navigation bar
├── profile.php            # User profile view and management
├── other_user_profile.php # View other users' profiles
├── script.js              # Core frontend JavaScript logic
├── signup.php             # User registration page
├── style.css              # Main stylesheet (Glassmorphism + Themes)
├── theme.js               # Theme switching logic (Light/Dark mode)
└── welcome.php            # Main News Feed and Post creation page
```

---

## 🔒 Security Measures Implemented
- **User Blocking**: Empowerment of users to autonomously moderate their interactions.
- **Authentication Flow Guards**: Active sessions protect user-only areas and prevent access to login/register while authenticated.
- **Cache-Control & State Security**: Prevents caching of sensitive pages to mitigate back-button security risks.
- **Session Management**: Implements `session_status()` checks and complete state resets during logout.
- **Password Protection**: Industry-standard hashing using PHP's `password_hash()`.
- **SQL Injection Prevention**: Implementation of `mysqli_prepare` and parameterized queries.
- **XSS Prevention**: Content sanitization using `htmlspecialchars()`.
- **File Upload Security**: Strict MIME type validation, extension whitelisting, and unique filename generation.

---
*Created with ❤️ for modern web development.*
