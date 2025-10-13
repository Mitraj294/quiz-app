# Role-Based Authentication Guide 🔐

## Overview
Your Quiz App now has complete role-based access control with different UI and permissions for Admin and Regular Users.

## Test Accounts Created

### Admin Account
- **Email**: `admin@example.com`
- **Password**: `password`
- **Role**: Admin
- **Access**: Full system access

### Regular User Account
- **Email**: `user@example.com`
- **Password**: `password`
- **Role**: User
- **Access**: Limited to taking quizzes and viewing topics

## Role System Architecture

### 1. Database Structure
```
users table → role_user pivot table → roles table
```
- Many-to-many relationship
- Users can have multiple roles
- Roles are stored in the `roles` table with a `role` column

### 2. User Model Methods
Located in: `app/Models/User.php`

```php
// Check if user has specific role
$user->hasRole('admin'); // returns boolean

// Check if user is admin
$user->isAdmin(); // returns boolean

// Check if user is regular user
$user->isUser(); // returns boolean

// Get all user roles
$user->roles; // returns collection
```

### 3. Middleware Protection
Located in: `app/Http/Middleware/UserRole.php`

**Usage in routes:**
```php
// Protect route for admin only
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'role:admin']);

// Protect route for specific role
Route::get('/users/area', function () {
    return view('users.area');
})->middleware(['auth', 'role:user']);
```

## UI Differences by Role

### Admin Dashboard Features
When logged in as admin (`admin@example.com`):

✅ **Special Admin Badge**
- Purple gradient banner showing "Admin Panel"
- Indicates administrator privileges

✅ **Admin-Only Cards (4 cards)**
1. **Create Quiz** - Add new quizzes to platform
2. **Manage Topics** - Organize quiz categories
3. **Users** - Manage user accounts
4. **Analytics** - View platform statistics

✅ **Navigation Links**
- Dashboard
- Create Quiz (admin only)
- Manage Topics
- Profile dropdown

### Regular User Dashboard Features
When logged in as regular user (`user@example.com`):

✅ **User Cards (3 cards)**
1. **Take a Quiz** - Start taking quizzes
2. **Browse Topics** - Explore quiz topics
3. **Your Progress** - Track quiz history

✅ **Navigation Links**
- Dashboard
- Topics (browse only)
- Profile dropdown

## Protected Routes

### Admin-Only Routes
These routes return **403 Forbidden** for non-admin users:

```php
/quizzes/create         // Create new quizzes
```

To add more admin routes:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/analytics', [AdminController::class, 'analytics']);
    // Add more admin routes here
});
```

### Authenticated Routes (All Users)
These routes require login but available to all roles:

```php
/dashboard              // User dashboard
/topics                 // Browse topics
/topics/{topic}         // View topic details
/profile                // Edit profile
```

## How to Use in Blade Templates

### Show content only to admins
```blade
@if(Auth::user()->isAdmin())
    <a href="/admin/panel">Admin Panel</a>
@endif
```

### Show content only to regular users
```blade
@if(Auth::user()->isUser())
    <p>Welcome, regular user!</p>
@endif
```

### Show content based on specific role
```blade
@if(Auth::user()->hasRole('admin'))
    <!-- Admin content -->
@elseif(Auth::user()->hasRole('user'))
    <!-- User content -->
@else
    <!-- Default content -->
@endif
```

### Check for multiple roles
```blade
@if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('moderator'))
    <!-- Content for admins or moderators -->
@endif
```

## How to Assign Roles to Users

### Option 1: Using Seeder (for testing)
Already included in `RoleAndUserSeeder.php`:
```bash
php artisan db:seed --class=RoleAndUserSeeder
```

### Option 2: In Code
```php
use App\Models\User;
use App\Models\Role;

// Find user
$user = User::find(1);

// Find or create role
$adminRole = Role::firstOrCreate(['role' => 'admin']);

// Attach role to user
$user->roles()->attach($adminRole);

// Or detach a role
$user->roles()->detach($adminRole);
```

### Option 3: Manual Database Insert
```sql
-- First, get the user ID and role ID
SELECT * FROM users WHERE email = 'user@example.com';
SELECT * FROM roles WHERE role = 'admin';

-- Then insert into pivot table
INSERT INTO role_user (user_id, role_id) VALUES (1, 1);
```

## Creating New Roles

1. **Add role to database:**
```php
use App\Models\Role;

Role::create(['role' => 'moderator']);
Role::create(['role' => 'teacher']);
```

2. **Add helper method to User model** (optional):
```php
public function isModerator(): bool
{
    return $this->hasRole('moderator');
}
```

3. **Protect routes with new role:**
```php
Route::get('/moderator/panel', function () {
    return view('moderator.panel');
})->middleware(['auth', 'role:moderator']);
```

## Testing Role-Based Access

### Test Admin Access:
1. Visit: http://127.0.0.1:8000/login
2. Login with: `admin@example.com` / `password`
3. You should see:
   - Purple "Admin Panel" banner
   - 4 admin cards (Create Quiz, Manage Topics, Users, Analytics)
   - "Create Quiz" and "Manage Topics" in navigation
4. Try visiting: http://127.0.0.1:8000/quizzes/create
   - Should work ✅

### Test Regular User Access:
1. Logout and login with: `user@example.com` / `password`
2. You should see:
   - 3 regular cards (Take Quiz, Browse Topics, Progress)
   - Only "Topics" in navigation (no Create Quiz)
3. Try visiting: http://127.0.0.1:8000/quizzes/create
   - Should show **403 Forbidden** ❌

## Common Use Cases

### 1. Add "Delete Quiz" button only for admins
```blade
@if(Auth::user()->isAdmin())
    <form method="POST" action="{{ route('quizzes.destroy', $quiz) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Delete Quiz</button>
    </form>
@endif
```

### 2. Restrict controller action to admins
```php
public function destroy(Quiz $quiz)
{
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Unauthorized action.');
    }
    
    $quiz->delete();
    return redirect()->route('quizzes.index');
}
```

### 3. Show different homepage for different roles
```php
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return view('admin.dashboard');
    }
    
    return view('user.dashboard');
})->middleware(['auth']);
```

## Troubleshooting

### User doesn't have any role showing
**Problem**: Role shows as "User" even after assigning
**Solution**: Check pivot table
```sql
SELECT * FROM role_user WHERE user_id = 1;
```

### 403 error when accessing admin routes
**Problem**: User marked as admin but still getting 403
**Solution**: 
1. Clear cache: `php artisan cache:clear`
2. Check role assignment in database
3. Verify middleware is registered in `bootstrap/app.php`

### Role not displaying on dashboard
**Problem**: Dashboard shows "User" for admin
**Solution**: Make sure you ran the seeder and the user-role relationship exists

## Security Best Practices

1. ✅ Always use middleware to protect routes
2. ✅ Double-check permissions in controllers
3. ✅ Hide admin UI elements from non-admins
4. ✅ Validate user roles before any sensitive action
5. ✅ Log administrative actions
6. ✅ Use different passwords for admin accounts in production

## Next Steps

- [ ] Create admin panel for user management
- [ ] Add role assignment UI
- [ ] Implement activity logging
- [ ] Add more granular permissions
- [ ] Create API endpoints with role protection

---

**🎉 Role-based authentication is now fully configured!**

Test it out by logging in with both accounts and seeing the different dashboards!
