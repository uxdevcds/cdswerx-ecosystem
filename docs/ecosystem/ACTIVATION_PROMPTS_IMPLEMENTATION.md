# Activation Prompts Implementation

## Overview

This document describes the implementation of two solutions to manage the CDSWerx activation prompts system:

- **Option 2**: Admin Interface Reset Button
- **Option 3**: Automatic Reset When Users are Deactivated

## Problem Solved

The CDSWerx plugin activation prompts were not showing outside of the plugin interface due to:

1. **Global Dismissal Flag**: When users clicked "Dismiss All Prompts", the `cdswerx_activation_prompts_globally_dismissed` option was set to `true`, permanently hiding prompts.
2. **No Reset Mechanism**: Once dismissed, there was no easy way to re-enable the prompts.
3. **State Inconsistency**: When users were deactivated/removed, the prompt system didn't automatically reset to reflect the new state.

## Solution Details

### Option 2: Admin Interface Reset Button

**Purpose**: Provides manual control for administrators to reset activation prompts.

**Features**:
- Adds a "🔧 Reset Activation Prompts" button in the Testing/Debug Actions section
- Clears the global dismissal flag (`cdswerx_activation_prompts_globally_dismissed`)
- Re-enables the show prompt flag (`cdswerx_show_corey_activation_prompt`)
- Provides admin feedback via transient messages
- Secured with WordPress nonces

**Use Cases**:
- Testing activation prompt functionality
- Troubleshooting prompt display issues
- Re-enabling prompts for training or policy changes
- Manual override when needed

### Option 3: Automatic Reset When Users are Deactivated

**Purpose**: Automatically resets activation prompts when Janice or Corey users are deactivated.

**Features**:
- Monitors user deletion via `delete_user` hook
- Monitors user removal from blog via `remove_user_from_blog` hook (multisite)
- Monitors role changes via `set_user_role` hook
- Automatically resets prompts when target users lose required roles
- Updates respective user activation flags

**Use Cases**:
- Natural workflow when staff changes occur
- User account cleanup scenarios
- Role management changes
- Ensures prompt relevance matches actual user states

## Technical Implementation

### Files Modified

1. **`admin/class-cdswerx-admin.php`**
   - Added `handle_reset_activation_prompts()` method
   - Added `handle_user_deletion()` method
   - Added `handle_user_removal()` method
   - Added `handle_user_role_change()` method
   - Added `is_target_user()` helper method
   - Added `reset_activation_prompts_on_deactivation()` helper method
   - Enhanced constructor with new action hooks

2. **`admin/partials/cdswerx-admin-users-settings.php`**
   - Added "Reset Activation Prompts" button to Testing/Debug Actions section
   - Enhanced UI with proper nonce security

### New Methods Added

#### `handle_reset_activation_prompts()`
```php
public function handle_reset_activation_prompts()
```
- Handles GET request to reset activation prompts
- Verifies user capabilities and nonces
- Clears global dismissal and re-enables prompts
- Provides admin feedback

#### `handle_user_deletion($user_id)`
```php
public function handle_user_deletion($user_id)
```
- Triggered when users are deleted
- Checks if deleted user is Janice or Corey
- Resets activation prompts if target user

#### `handle_user_removal($user_id, $blog_id)`
```php
public function handle_user_removal($user_id, $blog_id)
```
- Handles multisite user removal from blogs
- Same logic as user deletion for target users

#### `handle_user_role_change($user_id, $role, $old_roles)`
```php
public function handle_user_role_change($user_id, $role, $old_roles)
```
- Monitors role changes for target users
- Resets prompts if users lose required roles
- Checks both old and new role arrays

#### `is_target_user($user)`
```php
private function is_target_user($user)
```
- Helper method to identify Janice or Corey users
- Validates both username and email address

#### `reset_activation_prompts_on_deactivation($username)`
```php
private function reset_activation_prompts_on_deactivation($username)
```
- Common method for resetting prompts
- Updates all relevant option flags
- Provides debug logging

### Action Hooks Added

```php
// Option 2: Manual reset
add_action("admin_init", [$this, "handle_reset_activation_prompts"]);

// Option 3: Automatic reset
add_action("delete_user", [$this, "handle_user_deletion"]);
add_action("remove_user_from_blog", [$this, "handle_user_removal"], 10, 2);
add_action("set_user_role", [$this, "handle_user_role_change"], 10, 3);
```

## Usage Instructions

### Option 2: Manual Reset

1. Navigate to **CDSWerx → Users Settings**
2. Go to the **Advanced** tab
3. Scroll to **Testing/Debug Actions**
4. Click **🔧 Reset Activation Prompts**
5. Confirm the action
6. Check other admin pages to see the global prompt

### Option 3: Automatic Reset

This happens automatically when:

1. **User Deletion**: Janice or Corey accounts are deleted
2. **User Removal**: Users are removed from multisite blogs
3. **Role Changes**: Users lose required roles (site_admin_manager, administrator)

No manual intervention required.

## Security Considerations

- All actions require `manage_options` capability
- WordPress nonces protect against CSRF attacks
- User validation prevents unauthorized access
- Debug logging for troubleshooting

## Options Affected

The implementation manages these WordPress options:

- `cdswerx_activation_prompts_globally_dismissed` - Global dismissal flag
- `cdswerx_show_corey_activation_prompt` - Show prompt flag
- `cdswerx_janice_activated` - Janice activation status
- `cdswerx_corey_activated` - Corey activation status

## Debug Information

Enable WordPress debug logging to see detailed information:

```php
define('WP_DEBUG_LOG', true);
```

Look for log entries with "CDSWerx DEBUG:" prefix.

## Testing

### Test Option 2
1. Dismiss activation prompts globally
2. Use the reset button
3. Verify prompts appear on other admin pages

### Test Option 3
1. Activate Janice or Corey
2. Delete or change their roles
3. Verify prompts automatically reset

## Benefits

- **Option 2**: Provides complete manual control for testing and troubleshooting
- **Option 3**: Maintains system consistency automatically
- **Combined**: Covers both planned and natural workflow scenarios
- **Redundancy**: Multiple ways to reset ensures reliability
- **User Experience**: Reduces friction while maintaining control