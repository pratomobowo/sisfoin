# CRUSH.md - Laravel Project Guidelines

## Build/Lint/Test Commands
- `composer test` - Run all tests
- `composer test --filter=TestName` - Run single test
- `php artisan test tests/Feature/SpecificTest.php` - Run specific test file
- `./vendor/bin/pint` - Code formatting/linting
- `npm run build` - Build frontend assets
- `npm run dev` - Development server with Vite
- `composer dev` - Full development environment (server, queue, logs, vite)

## Code Style Guidelines

### PHP/Laravel
- Use Laravel's built-in validation and authorization
- Models should use proper fillable arrays and type hints
- Controllers should extend base Controller and use validation requests
- Use Spatie packages: Permission for roles, ActivityLog for logging
- Livewire components should use WithPagination trait and proper validation
- Services should be in app/Services/ directory for business logic
- Use Livewire for dynamic components with real-time updates

### Naming Conventions
- Models: PascalCase (User, Employee, Dosen)
- Controllers: PascalCase + Controller (UserController, EmployeeController)
- Livewire Components: PascalCase (UserFingerManagement, ActivityLogs)
- Methods: camelCase for methods, snake_case for database columns
- Variables: camelCase, descriptive names
- Classes: PascalCase, following PSR-4 autoloading

### Imports & Organization
- Group imports: use statements for Laravel, then third-party, then app classes
- Use proper namespace declarations matching file structure
- Services should handle external API integrations (SevimaApiService, FingerprintService)
- Traits should be in app/Traits/ for reusable functionality
- Livewire components should be in app/Livewire/ directory with appropriate subdirectories

### Error Handling
- Use Laravel's built-in exception handling
- Log activities using Spatie ActivityLog
- Validate user input with proper validation rules
- Use try-catch blocks for external API calls and database operations
- Implement proper error messages for user feedback

## Project Architecture

### Module Structure
- **Superadmin**: Administrative functions (users, roles, fingerprint management)
- **SDM**: Human Resource management (employees, lecturers, payroll)
- **Staff**: User-specific functions (profile, salary slips)

### Fingerprint Module
- **MesinFinger**: Machine management and configuration
- **UserFinger**: User management on fingerprint machines with pagination
- **TarikDataFingerprint**: Attendance data pulling from machines
- **AttendanceLogs**: Attendance log viewing and management

### Services Pattern
- **FingerprintService**: Core fingerprint operations
- **X100CSoapService**: SOAP communication with fingerprint machines
- Services should handle API communication, data transformation, and error handling

### Livewire Components
- Use WithPagination trait for table pagination
- Implement proper loading states and error handling
- Use wire:model for real-time form updates
- Follow naming convention: ModuleName + FeatureName (e.g., UserFingerManagement)

### Database Patterns
- Use proper foreign key constraints
- Implement soft deletes where appropriate
- Use Laravel's built-in timestamps (created_at, updated_at)
- Follow naming convention: table_name in snake_case

## Frontend Guidelines

### Blade Templates
- Use @extends for layout inheritance
- Implement @section for content areas
- Use components for reusable UI elements
- Follow responsive design patterns

### JavaScript
- Use Alpine.js for simple interactivity
- Use Livewire for complex components
- Implement proper error handling in API calls
- Use async/await for asynchronous operations

### CSS/Styling
- Use Tailwind CSS for styling
- Follow the established color scheme and spacing
- Implement responsive design using Tailwind breakpoints
- Use consistent button and form styles

## API Integration

### Fingerprint Machine API
- Use SOAP protocol for X100C machines
- Implement proper connection handling and timeouts
- Cache responses where appropriate to reduce machine load
- Log all API interactions for debugging

### Error Handling for External APIs
- Implement retry logic for transient failures
- Use proper timeout settings
- Log detailed error information
- Provide user-friendly error messages

## Testing Guidelines

### Unit Tests
- Test individual methods and functions
- Mock external dependencies
- Test both success and failure scenarios

### Feature Tests
- Test complete user workflows
- Test form validation and error handling
- Test authentication and authorization
- Use database transactions for test isolation

### Livewire Testing
- Test component initialization
- Test user interactions and state changes
- Test pagination and filtering functionality
- Mock service dependencies

## Performance Considerations

### Database Optimization
- Use proper indexes for frequently queried columns
- Implement eager loading to avoid N+1 queries
- Use pagination for large datasets
- Cache frequently accessed data

### Frontend Optimization
- Use lazy loading for images and large datasets
- Implement debouncing for search inputs
- Use proper caching strategies
- Optimize asset loading

## Security Guidelines

### Authentication & Authorization
- Use Laravel's built-in authentication
- Implement role-based access control using Spatie Permission
- Validate all user inputs
- Use CSRF protection for all forms

### Data Protection
- Encrypt sensitive data at rest
- Use HTTPS for all communications
- Implement proper logging for security events
- Follow principle of least privilege

## Development Workflow

### Git Workflow
- Use feature branches for new development
- Write descriptive commit messages
- Create pull requests for code review
- Tag releases for production deployments

### Code Quality
- Run linting and formatting tools before commits
- Write tests for new functionality
- Review code for security vulnerabilities
- Document complex business logic

### Deployment
- Use environment-based configuration
- Run database migrations as part of deployment
- Clear caches after deployment
- Monitor application performance and errors