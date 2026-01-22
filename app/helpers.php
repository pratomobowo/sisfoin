<?php

/**
 * Set the active role in session
 */
function setActiveRole(string $role): void
{
    session(['active_role' => $role]);
}

/**
 * Get the current active role from session
 */
function getActiveRole(): ?string
{
    return session('active_role', auth()->user()?->roles->first()?->name);
}

/**
 * Check if user has the specified role
 */
function hasRole(string $role): bool
{
    return auth()->user()?->hasRole($role) ?? false;
}

/**
 * Check if the current active role matches the specified role
 */
function isActiveRole(string|array $roles): bool
{
    $activeRole = getActiveRole();
    
    if (is_array($roles)) {
        return in_array($activeRole, $roles);
    }
    
    if (str_contains($roles, '|')) {
        return in_array($activeRole, explode('|', $roles));
    }
    
    return $activeRole === $roles;
}

/**
 * Get all roles for the current user
 *
 * @return \Illuminate\Database\Eloquent\Collection
 */
function getUserRoles()
{
    return auth()->user()?->roles ?? collect();
}

/**
 * Check if user can switch to the specified role
 */
function canSwitchToRole(string $role): bool
{
    return auth()->user()?->hasRole($role) ?? false;
}

/**
 * Generate breadcrumb items automatically based on current URL
 */
function generateBreadcrumb(): array
{
    $currentUrl = request()->url();
    $path = parse_url($currentUrl, PHP_URL_PATH);
    $segments = $path ? explode('/', trim($path, '/')) : [];
    
    $breadcrumbs = [];
    $url = '';
    
    // Skip the first segment if it's the app URL base
    $baseUrl = config('app.url');
    $basePath = parse_url($baseUrl, PHP_URL_PATH);
    if ($basePath) {
        $basePathSegments = explode('/', trim($basePath, '/'));
        $segments = array_diff($segments, $basePathSegments);
        $segments = array_values($segments);
    }
    
    foreach ($segments as $index => $segment) {
        $url .= '/' . $segment;
        
        // Skip empty segments and dashboard (already included in component)
        if (empty($segment) || $segment === 'dashboard') continue;
        
        // Convert segment to readable title
        $title = ucfirst(str_replace('-', ' ', $segment));
        
        // Custom mappings for better readability
        $titleMappings = [
            'superadmin' => 'Superadmin',
            'users' => 'Manajemen Pengguna',
            'roles' => 'Manajemen Peran',
            'activity-logs' => 'Log Aktifitas',
            'settings' => 'Pengaturan',
            'profile' => 'Profil',
            'dashboard' => 'Dashboard',
            'create' => 'Tambah',
            'edit' => 'Edit',
            'show' => 'Detail',
            'index' => 'Daftar'
        ];
        
        if (isset($titleMappings[$segment])) {
            $title = $titleMappings[$segment];
        }
        
        // Don't create URL for the last segment (current page)
        $isLast = ($index === count($segments) - 1);
        $itemUrl = $isLast ? null : url($url);
        
        $breadcrumbs[] = [
            'title' => $title,
            'url' => $itemUrl
        ];
    }
    
    return $breadcrumbs;
}

/**
 * Get breadcrumb with custom items or automatic generation
 */
function getBreadcrumb(?array $customItems = null): array
{
    if ($customItems !== null) {
        return $customItems;
    }
    
    return generateBreadcrumb();
}
