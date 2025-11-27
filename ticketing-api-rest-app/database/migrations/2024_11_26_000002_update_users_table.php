<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Apply changes to 'id' column first if PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Drop default and identity, then change type with casting
            DB::statement('ALTER TABLE users ALTER COLUMN id DROP DEFAULT');
            DB::statement('ALTER TABLE users ALTER COLUMN id DROP IDENTITY IF EXISTS');
            DB::statement('ALTER TABLE users ALTER COLUMN id TYPE UUID USING (id::text::uuid)');
        }

        Schema::table('users', function (Blueprint $table) {
            // Only add other columns and foreign key, 'id' is already handled for pgsql
            // For non-pgsql, the id change still needs to happen here.
            if (DB::connection()->getDriverName() !== 'pgsql') {
                 $table->uuid('id')->change(); // This handles id for non-pgsql databases
            }

            $table->string('type')->default('organizer')->after('email');
            $table->uuid('role_id')->nullable()->after('type');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['type', 'role_id']);
            // Reverting 'id' column type from UUID to bigIncrements is complex and not easily done
            // without data loss or significant migration logic, especially if data has been inserted.
            // Typically, for 'down' migrations involving primary key type changes, a full rollback
            // is not expected to restore previous primary key data.
            // If strict reversibility is needed, this would require adding a new column,
            // migrating data, dropping the old, and renaming.
        });
    }
};
