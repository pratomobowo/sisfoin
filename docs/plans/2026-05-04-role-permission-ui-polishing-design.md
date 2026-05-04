# Role Permission UI Polishing Design

## Goal

Improve role and permission management so admins can grant granular permissions per module instead of only selecting entire modules.

## Current State

The role form currently shows module-level checkboxes. Selecting a module grants every permission in that module. This is simple, but it prevents partial access such as allowing payroll viewing without payroll editing or deleting.

The role list table shows module badges, but it does not show whether a role has full or partial access to a module.

## Proposed Design

### Role Form

Keep the basic role fields:

- Role name
- Display name
- Description

Replace the module checkbox grid with a module permission matrix:

- Each module appears as a card or accordion section.
- Each module shows its label and selected permission count.
- Each module has a “select all” action for that module.
- Each permission is individually selectable.

Example module:

- Manajemen Slip Gaji
- Lihat Slip Gaji
- Buat Slip Gaji
- Edit Slip Gaji
- Hapus Slip Gaji
- Download Slip Gaji

### Behavior

- Saving a role syncs exactly the selected permissions.
- Selecting all permissions in a module marks the module as fully selected.
- Selecting some permissions marks the module as partially selected.
- Staff role restrictions remain unchanged: `staff` only keeps `.view` permissions and `profile.edit`.
- The `super-admin` role remains protected from editing.

### Role List Table

The “Modul Akses” column should show coverage badges instead of only module names.

Examples:

- `Slip Gaji 2/5`
- `Absensi 3/5`
- `Sekretariat 4/4`

This makes partial access visible from the list page.

### Bulk Assign

Bulk permission assignment should stay additive and simple:

- Choose one module.
- See the permissions inside that module.
- Select which permissions to grant.
- Apply to selected roles.

Bulk assignment should not replace existing role permissions.

### Data Flow

- `config/modules.php` remains the source of truth for modules and permission labels.
- `RoleForm::$selectedPermissions` becomes the primary form state.
- `RoleForm::$selectedModules` may remain as derived state for compatibility, but saving should be based on selected permissions.
- Editing a role loads assigned permissions into `selectedPermissions`.
- Module coverage is computed from selected permissions and module definitions.

## Testing

Add focused Livewire tests for:

- Saving a role with partial permissions only syncs selected permissions.
- Saving a role with one permission in a module does not grant the whole module.
- Staff role still keeps only `.view` permissions and `profile.edit`.
- Module coverage helpers report selected and total counts correctly.

## Non-Goals

- Do not build a dynamic menu builder.
- Do not move module definitions into the database.
- Do not change route or action authorization rules.
- Do not make `super-admin` editable.
