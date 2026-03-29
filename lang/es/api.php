<?php

declare(strict_types=1);

return [
    'tenant_context_required' => 'Esta acción requiere un usuario con cuenta (tenant) asignada.',
    'tenant_not_found' => 'La cuenta asociada al usuario no existe.',
    'tenant_inactive' => 'La cuenta de la organización está desactivada. Contacta a soporte.',
    'landlord_only' => 'Solo el personal de plataforma puede acceder a este recurso.',
    'landlord_access_denied' => 'No tienes acceso al panel de plataforma.',

    'tenant_builtin_role_delete_forbidden' => 'Los roles integrados no se pueden eliminar.',
    'tenant_builtin_role_rename_forbidden' => 'Los roles integrados no se pueden renombrar.',
    'tenant_permission_not_in_catalog' => 'Algún permiso no está permitido para roles del tenant.',
    'tenant_reserved_role_name' => 'Este nombre de rol está reservado.',
    'tenant_role_has_users' => 'Quita a todos los usuarios de este rol antes de borrarlo.',
    'tenant_role_name_format' => 'Usa minúsculas, números, guiones bajos o medios (máx. 64 caracteres).',
    'tenant_role_name_taken' => 'Ya existe un rol con este nombre en tu organización.',
    'tenant_roles_unknown_or_foreign' => 'Uno o más roles no son válidos para esta organización.',
    'roles_required' => 'Asigna al menos un rol.',
];
