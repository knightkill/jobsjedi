<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminRole = Role::create(['name' => 'admin',]);
        $userRole = Role::create(['name' => 'user',]);

        $readListingPermission = \Spatie\Permission\Models\Permission::create(['name' => 'read listing',]);
        $createListingPermission = \Spatie\Permission\Models\Permission::create(['name' => 'create listing',]);
        $updateListingPermission = \Spatie\Permission\Models\Permission::create(['name' => 'update listing',]);
        $deleteListingPermission = \Spatie\Permission\Models\Permission::create(['name' => 'delete listing',]);

        $adminRole->givePermissionTo($readListingPermission, $createListingPermission, $updateListingPermission, $deleteListingPermission);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
