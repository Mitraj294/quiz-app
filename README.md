
# Quiz Application

A Laravel 11-based quiz management system with topic organization, media-rich questions, and advanced quiz configuration.

## Tech Stack

- Laravel 11.x (PHP >= 8.2)
- MySQL or SQLite
- Node.js (>= 18), NPM
- Vite (build tool)
- Tailwind CSS (UI)
- Alpine.js (interactivity)
- Axios (AJAX)

## Quick Install & Setup

```bash
git clone https://github.com/Mitraj294/quiz-app.git
cd quiz-app
composer install
npm install
cp .env.example .env
php artisan key:generate
# Edit .env for DB credentials
php artisan migrate --seed
php artisan storage:link
npm run dev &
php artisan serve --host=127.0.0.1 --port=8000
```

Visit: http://127.0.0.1:8000

## Asset Compilation

- Source: `resources/css/app.css`, `resources/js/app.js`
- Build: `npm run dev` (development) or `npm run build` (production)
- Output: `public/build/assets/app-[hash].css` and `app-[hash].js`
- Blade loads assets via `@vite(['resources/css/app.css', 'resources/js/app.js'])`

## Usage

- Create/manage topics, quizzes, and questions via dashboard
- Upload images/audio/video to questions
- Negative marking is dynamic per question

## Troubleshooting

- PHP version error: Use PHP >= 8.2
- Encryption key error: Run `php artisan key:generate`
- File upload error: Increase `post_max_size` and `upload_max_filesize` in `php.ini`
- DB connection error: Check `.env` credentials and DB server
- Permissions error: Ensure `storage/` and `bootstrap/cache` are writable

## Testing

```bash
./vendor/bin/phpunit
# or
./vendor/bin/sail php artisan test
```

## License

MIT
