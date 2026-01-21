# USB YP KP Apps - Internal Application System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-blue.svg)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-green.svg)](https://tailwindcss.com)

Internal application system for Universitas Sanggabuana Bandung built with modern web technologies.

## 🚀 Features

- **Authentication & Role Management**: Multi-role system with session-based role switching
- **Superadmin Module**: Comprehensive dashboard and user management
- **Modern UI**: TailwindCSS v4 with Poppins font and reusable components
- **Reactive Components**: Livewire for dynamic interactions
- **Comprehensive Testing**: Feature tests for critical functionality
- **Role-Based Access Control**: Spatie Laravel Permission integration

## 🏗️ Technology Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire 3 + TailwindCSS 4
- **Database**: MySQL (InnoDB engine)
- **Authentication**: Laravel Breeze
- **Permissions**: Spatie Laravel Permission
- **Testing**: PHPUnit with Laravel testing features
- **Font**: Poppins (Google Fonts)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0 or higher

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone [repository-url]
cd usbypkp-apps/web
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
Edit your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usbypkp_apps
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the database:
```bash
mysql -u your_username -p
CREATE DATABASE usbypkp_apps;
exit
```

### 5. Run Migrations and Seeders
```bash
# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RoleSeeder
```

### 6. Build Assets
```bash
# Build assets for development
npm run dev

# Or build for production
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 👤 Default Users

After seeding, you can create a superadmin user:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@usbypkp.ac.id',
    'password' => \Hash::make('password'),
    'email_verified_at' => now()
]);

$user->assignRole('super-admin');
```

## 🔑 Available Roles

- **super-admin**: Full system access, bypasses all permissions
- **admin-sdm**: Human Resources management
- **admin-sekretariat**: Secretariat management  
- **admin-sarpras**: Infrastructure & facilities management
- **staff**: Basic user role

## 🧪 Testing

Run the test suite:
```bash
# Run all tests
php artisan test

# Run specific test types
php artisan test --filter=RoleManagementTest
php artisan test --filter=SuperadminAccessTest
php artisan test --filter=UserManagementTest
```

## 🎨 UI Components

The application includes reusable Blade components following design guidelines:

### Buttons
```blade
<x-button.primary>Primary Action</x-button.primary>
<x-button.secondary>Secondary Action</x-button.secondary>
<x-icon-button type="edit" wire:click="edit({{ $id }})" />
```

### Forms
```blade
<x-form method="POST" action="/save">
    <x-form-field label="Name" name="name" required>
        <x-input.text wire:model="name" />
    </x-form-field>
    
    <x-form-field label="Role" name="role">
        <x-select :options="$roles" wire:model="selectedRole" />
    </x-form-field>
</x-form>
```

### Alerts & Notifications
```blade
<x-alert type="success" dismissible>
    User created successfully!
</x-alert>

<x-badge variant="success">Active</x-badge>
```

### Tables
```blade
<x-table>
    <x-table.th sortable wire:click="sortBy('name')">Name</x-table.th>
    <x-table.th>Email</x-table.th>
    
    @foreach($users as $user)
        <x-table.tr>
            <x-table.td>{{ $user->name }}</x-table.td>
            <x-table.td>{{ $user->email }}</x-table.td>
        </x-table.tr>
    @endforeach
</x-table>

<x-pagination :paginator="$users" />
```

## 🛡️ Security Features

- **Role-based Access Control**: Comprehensive permission system
- **Route Protection**: Middleware-based route protection
- **CSRF Protection**: Laravel's built-in CSRF protection
- **Password Hashing**: Secure password storage
- **Session Management**: Secure session handling

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── RoleController.php          # Role switching
│   └── Middleware/
│       └── CheckActiveRole.php         # Role verification
├── Livewire/
│   └── Superadmin/
│       └── UserManagement.php          # User CRUD component
├── Models/
│   └── User.php                        # User model with roles
└── helpers.php                         # Role helper functions

resources/
├── views/
│   ├── components/                     # Reusable UI components
│   ├── layouts/
│   │   └── superadmin.blade.php        # Superadmin layout
│   ├── livewire/
│   │   └── superadmin/                 # Livewire component views
│   └── superadmin/
│       └── dashboard.blade.php         # Superadmin dashboard
└── css/
    └── app.css                         # TailwindCSS configuration

tests/
├── Feature/
│   ├── Livewire/
│   │   └── UserManagementTest.php      # Component tests
│   ├── RoleManagementTest.php          # Role system tests
│   └── SuperadminAccessTest.php        # Access control tests
```

## 🚀 Development Guidelines

### Code Style
- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Write comprehensive tests for new features
- Document complex logic with comments

### Component Development
- Follow the established component patterns in `resources/views/components/`
- Use consistent prop naming and validation
- Include accessibility attributes (ARIA labels, roles)
- Follow the design system (Poppins font, blue theme, rounded corners)

### Testing
- Write feature tests for user-facing functionality
- Include edge cases and error scenarios
- Use descriptive test method names
- Mock external dependencies

## 🔧 Configuration

### Environment Variables
Key environment variables for the application:

```env
# Application
APP_NAME="USB YP KP Apps"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usbypkp_apps

# Mail (configure for notifications)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### TailwindCSS Configuration
The application uses TailwindCSS v4 with custom configuration for:
- Poppins font family
- Blue color scheme (#2563eb)
- Rounded corners (rounded-xl)
- Custom shadows and spacing

## 📝 API Documentation

### Role Management Helpers

```php
// Set active role for current user
setActiveRole('admin-sdm');

// Get current active role
$role = getActiveRole();

// Check if user has specific role
if (hasRole('super-admin')) {
    // Superadmin logic
}

// Check if specific role is active
if (isActiveRole('admin-sdm')) {
    // Active role logic
}

// Get all user roles
$roles = getUserRoles();

// Check if user can switch to role
if (canSwitchToRole('staff')) {
    // Allow role switch
}
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `.env`
   - Ensure MySQL service is running
   - Check if database exists

2. **Permission Denied Errors**
   - Run `php artisan cache:clear`
   - Check file permissions: `chmod -R 755 storage bootstrap/cache`

3. **Asset Loading Issues**
   - Run `npm run build`
   - Clear browser cache
   - Check TailwindCSS compilation

4. **Role/Permission Issues**
   - Re-run role seeder: `php artisan db:seed --class=RoleSeeder`
   - Clear application cache: `php artisan cache:clear`

## 📞 Support

For development support and issues:
- Check the issue tracker in the repository
- Review the test suite for expected behavior
- Consult the Laravel and Livewire documentation

## 📄 License

This project is proprietary software developed for Universitas Sanggabuana Bandung.

## 🏆 Contributors

- Development Team - Universitas Sanggabuana Bandung IT Department