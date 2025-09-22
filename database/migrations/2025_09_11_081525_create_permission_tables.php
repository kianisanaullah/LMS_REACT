<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    $teams = config('permission.teams');
    $tableNames = config('permission.table_names');
    $columnNames = config('permission.column_names');
    $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
    $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

    // Permissions
    Schema::create($tableNames['permissions'], function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();

        $table->unique(['name', 'guard_name'], 'LMS_PERM_NAME_GUARD_UQ');
    });

    // Roles
    Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
        $table->bigIncrements('id');
        if ($teams || config('permission.testing')) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
            $table->index($columnNames['team_foreign_key'], 'LMS_ROLES_TEAM_IDX');
        }
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();

        if ($teams || config('permission.testing')) {
            $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name'], 'LMS_ROLE_TEAM_NAME_GUARD_UQ');
        } else {
            $table->unique(['name', 'guard_name'], 'LMS_ROLE_NAME_GUARD_UQ');
        }
    });

    // Model Has Permissions
    Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
        $table->unsignedBigInteger($pivotPermission);
        $table->string('model_type');
        $table->unsignedBigInteger($columnNames['model_morph_key']);
        $table->index([$columnNames['model_morph_key'], 'model_type'], 'LMS_MHP_MODEL_IDX');

        $table->foreign($pivotPermission, 'LMS_MHP_PERMISSION_ID_FK')
              ->references('id')
              ->on($tableNames['permissions'])
              ->onDelete('cascade');

        if ($teams) {
            $table->unsignedBigInteger($columnNames['team_foreign_key']);
            $table->index($columnNames['team_foreign_key'], 'LMS_MHP_TEAM_IDX');

            $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'LMS_MHP_PRIMARY');
        } else {
            $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'LMS_MHP_PRIMARY');
        }
    });

    // Model Has Roles
    Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
        $table->unsignedBigInteger($pivotRole);
        $table->string('model_type');
        $table->unsignedBigInteger($columnNames['model_morph_key']);
        $table->index([$columnNames['model_morph_key'], 'model_type'], 'LMS_MHR_MODEL_IDX');

        $table->foreign($pivotRole, 'LMS_MHR_ROLE_ID_FK')
              ->references('id')
              ->on($tableNames['roles'])
              ->onDelete('cascade');

        if ($teams) {
            $table->unsignedBigInteger($columnNames['team_foreign_key']);
            $table->index($columnNames['team_foreign_key'], 'LMS_MHR_TEAM_IDX');

            $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'], 'LMS_MHR_PRIMARY');
        } else {
            $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'LMS_MHR_PRIMARY');
        }
    });

    // Role Has Permissions
    Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
        $table->unsignedBigInteger($pivotPermission);
        $table->unsignedBigInteger($pivotRole);

        $table->foreign($pivotPermission, 'LMS_RHP_PERMISSION_ID_FK')
              ->references('id')
              ->on($tableNames['permissions'])
              ->onDelete('cascade');

        $table->foreign($pivotRole, 'LMS_RHP_ROLE_ID_FK')
              ->references('id')
              ->on($tableNames['roles'])
              ->onDelete('cascade');

        $table->primary([$pivotPermission, $pivotRole], 'LMS_RHP_PRIMARY');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found. Please drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
