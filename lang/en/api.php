<?php

declare(strict_types=1);

return [
    'tenant_context_required' => 'This action requires a user with an assigned tenant account.',
    'tenant_not_found' => 'The tenant associated with this user was not found.',
    'tenant_inactive' => 'This organization account is inactive. Contact support.',
    'landlord_only' => 'Only platform staff may access this resource.',
    'landlord_access_denied' => 'You do not have access to the platform panel.',

    'tenant_builtin_role_delete_forbidden' => 'Built-in roles cannot be deleted.',
    'tenant_builtin_role_rename_forbidden' => 'Built-in roles cannot be renamed.',
    'tenant_permission_not_in_catalog' => 'One or more permissions are not allowed for tenant roles.',
    'tenant_reserved_role_name' => 'This role name is reserved.',
    'tenant_role_has_users' => 'Remove all users from this role before deleting it.',
    'tenant_role_name_format' => 'Use lowercase letters, digits, underscores or hyphens (max 64 characters).',
    'tenant_role_name_taken' => 'A role with this name already exists in your organization.',
    'tenant_roles_unknown_or_foreign' => 'One or more roles are invalid for this organization.',
    'roles_required' => 'Assign at least one role.',
];
