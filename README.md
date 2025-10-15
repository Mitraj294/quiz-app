# Quiz Application

A comprehensive quiz management system built with Laravel 11, featuring topic-based organization, media-rich questions, and advanced quiz configuration options.

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [API & Routes](#api--routes)
- [Database Schema](#database-schema)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Topic Management
- Create and organize topics
- Hierarchical topic structure (topics can have sub-topics)
- Attach quizzes and questions to topics
- Topic descriptions and metadata

### Quiz Management
- Create quizzes with flexible configuration
- Link quizzes to existing or new topics
- Set total marks, pass marks, and duration
- Configure max attempts and time between attempts
- Set quiz validity period (valid_from and valid_upto)
- Publish/unpublish quizzes
- Draft and published status

### Question Management
- **Three Question Types:**
  - Multiple Choice (Single Answer)
  - Multiple Choice (Multiple Answers)
  - Text/Short Answer

- **Rich Media Support:**
  - Upload images, audio, or video files
  - Drag-and-drop file upload
  - Real-time upload progress
  - Preview media before submission
  - Supported formats: JPG, PNG, GIF, WebP, MP3, MP4, WAV, OGG, WebM, AVI
  - Maximum file size: 10MB

- **Question Features:**
  - A/B/C/D labeled options with visual indicators
  - Dynamic option management (add/remove options)
  - Mark correct answers with checkboxes
  - Question editing and deletion
  - Media display in question views

### Quiz Configuration
- **Per-Question Settings:**
  - Custom marks for each question
  - Dynamic negative marking (0, 1/4, 1/3, 1/2, or full marks)
  - Optional questions flag
  - Question ordering

- **Bulk Operations:**
  - Select multiple questions from topic pool
  - Apply default settings to all questions
  - Select/deselect all questions at once
  - Already-attached questions indicator

### User Interface
- Clean, modern design with Tailwind CSS
- Responsive layout for all screen sizes
- Centralized button styles (`.btn`, `.btn-green`)
- Visual feedback for user actions
- Success/error message notifications
- Form validation with helpful error messages

### Authentication & Authorization
- Laravel Breeze authentication
- Admin role checks for protected actions
- User registration and login
- Password reset functionality

## 🛠 Tech Stack

- **Framework:** Laravel 11.x
- **Authentication:** Laravel Breeze
- **Frontend:** Blade Templates, Tailwind CSS, Vanilla JavaScript
- **Database:** MySQL (SQLite for development)
- **Package:** [harishdurga/laravel-quiz](https://github.com/harishdurga/laravel-quiz)
- **File Storage:** Laravel Storage (local disk with public access)
- **Build Tool:** Vite

## 📦 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM or Yarn
- MySQL 8.0+ or SQLite
- Apache/Nginx web server

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Mitraj294/quiz-app.git
cd quiz-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quiz_app
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

For SQLite (development):
```env
DB_CONNECTION=sqlite
# DB_HOST, DB_PORT, DB_DATABASE, etc. can be commented out
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Create Storage Symlink

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public` for serving uploaded media files.

### 8. Seed Database (Optional)

```bash
php artisan db:seed
```

### 9. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 10. Start Development Server

```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

## ⚙️ Configuration

### Quiz Package Configuration

The application uses the `harishdurga/laravel-quiz` package. Configuration file:

```bash
config/laravel-quiz.php
```

### File Upload Limits

Edit `app/Http/Controllers/MediaController.php` to change:
- Maximum file size (default: 10MB)
- Allowed MIME types
- Storage path

### CSS Customization

Button styles are centralized in `resources/css/app.css`:

```css
.btn {
    @apply inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150;
}

.btn-green {
    @apply inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150;
}
```

## 📖 Usage

### Creating a Topic

1. Navigate to Dashboard → Manage Topics
2. Click "Create New Topic"
3. Enter topic name and description
4. Submit

### Creating a Quiz

1. Go to "Manage Quiz" → "Create New Quiz"
2. Fill in quiz details:
   - Name, description
   - Total marks, pass marks
   - Duration (minutes)
   - Max attempts
   - Validity period
3. Choose to link to existing topic or create new one
4. Click "Create Quiz"

### Adding Questions to Quiz

**Method 1: Create New Question**
1. Open quiz details
2. Click "Create New Question"
3. Select question type
4. Enter question text
5. (Optional) Click "+ Add Media" to upload image/audio/video
6. Add options (for MCQ) or text answer
7. Set marks and negative marking
8. Submit

**Method 2: Select Existing Questions**
1. Open quiz details
2. Click "Add from Existing Questions"
3. Check questions to attach
4. Configure marks and negative marking for each
5. Click "Attach Selected"

### Editing Questions

1. Find question in quiz or topic view
2. Click "Edit" button
3. Modify question text, options, or media
4. Update settings
5. Save changes

### Managing Negative Marks

Negative marks are **dynamically calculated** based on question marks:
- If marks = 1: Options are 0, 0.25, 0.33, 0.5, 1
- If marks = 4: Options are 0, 1, 1.33, 2, 4
- Formula: negative = marks × fraction (1/4, 1/3, 1/2, or full)

### Uploading Media

1. Click "+ Add Media" button in question form
2. Either:
   - Drag and drop file into dropzone
   - Click to browse and select file
3. Watch upload progress
4. Preview appears after successful upload
5. Submit form to save question with media

## 📁 Project Structure

```
quiz-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── MediaController.php      # Media upload handling
│   │   │   ├── QuestionController.php   # Question CRUD
│   │   │   ├── QuizController.php       # Quiz management
│   │   │   └── TopicController.php      # Topic management
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Question.php                 # Extends vendor Question
│   │   ├── Quiz.php                     # Quiz model
│   │   ├── QuizQuestion.php             # Pivot with settings
│   │   └── Topic.php                    # Topic model
│   └── Providers/
├── config/
│   └── laravel-quiz.php                 # Quiz package config
├── database/
│   ├── migrations/                      # Database migrations
│   ├── seeders/                         # Database seeders
│   └── factories/                       # Model factories
├── public/
│   ├── storage/                         # Symlink to storage/app/public
│   └── index.php
├── resources/
│   ├── css/
│   │   └── app.css                      # Tailwind + custom styles
│   ├── js/
│   │   └── app.js                       # JavaScript entry point
│   └── views/
│       ├── questions/
│       │   ├── create.blade.php         # Create question (topic)
│       │   └── edit.blade.php           # Edit question
│       ├── quizzes/
│       │   ├── create.blade.php         # Create quiz
│       │   ├── show.blade.php           # Quiz details
│       │   ├── select_questions.blade.php  # Attach questions
│       │   └── create_question.blade.php   # Create question (quiz)
│       └── topics/
│           ├── index.blade.php          # Topics list
│           ├── create.blade.php         # Create topic
│           └── show.blade.php           # Topic details
├── routes/
│   └── web.php                          # Application routes
├── storage/
│   └── app/
│       └── public/
│           └── question-media/          # Uploaded media files
├── composer.json                        # PHP dependencies
├── package.json                         # Node dependencies
├── tailwind.config.js                   # Tailwind configuration
├── vite.config.js                       # Vite configuration
└── README.md                            # This file
```

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Local development setup

1. Install PHP dependencies:
	```bash
	composer install
	```
2. Install Node dependencies:
	```bash
	npm install
	```
3. Configure your `.env` for MySQL (already set to `quiz_app` with the `quizapp/quizapp` user).
4. Run migrations and seed data (optional if already done):
	```bash
	php artisan migrate --force
	php artisan db:seed --force
	```
5. Start the Laravel backend on `http://127.0.0.1:8000` (everything will be served by Laravel on port 8000):
	```bash
	php artisan serve --host=127.0.0.1 --port=8000
	```
6. In a separate terminal, build assets in watch mode (Vite will only compile assets and proxy requests to Laravel; you don't need to visit Vite directly):
	```bash
	npm run watch
	```

Now visit `http://127.0.0.1:8000` to use the application — the Laravel server will serve the HTML and built assets. During development, the watch task will keep assets up-to-date.

## 🔁 Setting up this project on another device

The steps below walk you through getting the project running on a fresh machine. There are two recommended approaches:

- Native (install PHP, Composer, Node, and a database on the host)
- Docker / Laravel Sail (encapsulated, fast to get started)

Choose the approach that best matches your environment.

### Option A — Native install (Linux / macOS / WSL)

1. Install system prerequisites

  - PHP >= 8.2 with the following extensions enabled: mbstring, sqlite/pdo_sqlite, pdo_mysql, fileinfo, ctype, json, openssl, tokenizer, xml, curl, gd (or imagick)
  - Composer (https://getcomposer.org/)
  - Node.js >= 18 and npm or Yarn
  - MySQL 8.0+ (or use SQLite for quick local setup)

  On Ubuntu a quick install (example):

  ```bash
  sudo apt update
  sudo apt install -y php8.2 php8.2-xml php8.2-mbstring php8.2-pdo php8.2-mysql php8.2-sqlite3 php8.2-curl php8.2-gd unzip curl
  curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
  curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
  sudo apt install -y nodejs
  sudo apt install -y mysql-server
  ```

2. Clone and install

  ```bash
  git clone https://github.com/Mitraj294/quiz-app.git
  cd quiz-app
  composer install --no-interaction --prefer-dist
  npm install
  cp .env.example .env
  php artisan key:generate
  ```

3. Configure `.env`

  - Edit `.env` to set your DB credentials. For quick development you can use SQLite:

  ```bash
  touch database/database.sqlite
  # then in .env set DB_CONNECTION=sqlite and comment out DB_HOST, DB_PORT etc
  ```

4. Migrate, seed, storage link

  ```bash
  php artisan migrate
  php artisan db:seed   # optional
  php artisan storage:link
  ```

5. Build assets and run

  ```bash
  npm run dev    # or npm run build for production
  php artisan serve --host=127.0.0.1 --port=8000
  ```

6. Open `http://127.0.0.1:8000`

### Option B — Docker & Laravel Sail (recommended for reproducible environments)

This project includes a Docker setup compatible with Laravel Sail. Sail supplies a ready-to-run PHP + MySQL/Postgres environment.

1. Ensure Docker (and docker-compose) is installed and running on your machine.

2. From project root, start Sail (first install composer deps if not present):

```bash
# If composer not installed on host, use the provided script
./vendor/bin/sail up -d
# or if vendor not present, install composer deps first: composer install && ./vendor/bin/sail up -d
```

3. Run migrations and seed inside the container:

```bash
./vendor/bin/sail artisan migrate --force
./vendor/bin/sail artisan db:seed --force
./vendor/bin/sail artisan storage:link
```

4. Install and build assets inside the container (or build on host):

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

5. Visit the app at `http://localhost` (Sail maps ports automatically).

Notes:

- To stop the environment: `./vendor/bin/sail down`
- To run an interactive shell: `./vendor/bin/sail shell`

### Platform-specific tips

- macOS: Use Homebrew to install PHP, Composer, Node. Docker Desktop works well for Sail.
- Windows: Use WSL2 + Ubuntu for native installs; or use Docker Desktop with WSL integration and Sail.
- File permissions: If you see permission errors when uploading files, ensure `storage/` and `bootstrap/cache` are writable by the webserver user (or adjust owner to your user during development):

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Running tests

The project includes PHPUnit tests. Run them locally or via Sail.

```bash
# native
./vendor/bin/phpunit

# via sail
./vendor/bin/sail php artisan test
```

### Common environment variables you may want to set in `.env`

- APP_URL (example: http://localhost)
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, MAIL_FROM_ADDRESS
- FILESYSTEM_DRIVER (default: public/local)

### Troubleshooting

- Error: "No application encryption key has been specified." — run `php artisan key:generate`.
- Error: "syntax error, unexpected" during composer install — ensure you have a supported PHP version (>= 8.2).
- File upload problems — check `php.ini` for `post_max_size` and `upload_max_filesize` and set them larger than 10MB if you need bigger uploads.
- Database connection refused — confirm DB_HOST/DB_PORT and credentials in `.env` and that your DB server is running. For Sail use the provided container names.
- Permissions errors — ensure `storage` and `bootstrap/cache` are writable.

### Deployment notes (short)

- Use `php artisan config:cache` and `php artisan route:cache` in production.
- Use `npm run build` to create production assets.
- Configure a process manager (supervisor) for queue workers and set up a cron entry for scheduled tasks.

### Try it — quick checklist for a new device

1. Clone repository
2. Copy `.env.example` -> `.env` and set DB credentials
3. composer install && npm install
4. php artisan key:generate
5. php artisan migrate --seed
6. php artisan storage:link
7. npm run dev && php artisan serve

If you want, copy these exact commands to get started quickly (Linux/macOS):

```bash
git clone https://github.com/Mitraj294/quiz-app.git
cd quiz-app
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev &
php artisan serve --host=127.0.0.1 --port=8000
```

## ✅ Changes made

Added step-by-step setup instructions for native installs and Docker/Sail, platform tips, tests, troubleshooting, and a quick-start checklist.
