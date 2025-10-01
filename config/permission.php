<?php

return [

   'models' => [
    'role'       => App\Models\CustomRole::class,
    'permission' => App\Models\CustomPermission::class,
],


 'table_names' => [
    'roles' => 'LMS.ROLES',
    'permissions' => 'LMS.PERMISSIONS',
    'model_has_permissions' => 'LMS.USER_PERMISSION', // ← your pivot for user-permissions
    'model_has_roles' => 'LMS.USER_ROLE',            // ← your pivot for user-roles
    'role_has_permissions' => 'LMS.PERMISSION_ROLE', // ← your pivot for role-permissions
],


 'column_names' => [
    /*
     * Default Spatie keys are:
     *   role_pivot_key         => 'role_id'
     *   permission_pivot_key   => 'permission_id'
     *   model_morph_key        => 'model_id'
     *
     * Your Oracle columns are UPPERCASE and different,
     * so set them here:
     */
    'role_pivot_key'       => 'ROLE_ID',        // PK in LMS.ROLES
    'permission_pivot_key' => 'PERMISSION_ID',  // PK in LMS.PERMISSIONS
    'model_morph_key'      => 'USER_ID',        // Morph key in LMS.USER_ROLE / LMS.USER_PERMISSION
    'team_foreign_key'     => 'TEAM_ID',        // only if you ever enable teams
],


    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'events_enabled' => false,

    'teams' => false,

    'team_resolver' => \Spatie\Permission\DefaultTeamResolver::class,

    'use_passport_client_credentials' => false,

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
