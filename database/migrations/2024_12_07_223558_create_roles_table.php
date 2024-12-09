<?php

use App\Models\Role;
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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Role::firstOrCreate(
            ['name' => 'Admin'],
            ['name' => 'Admin', 'description' => 'Administrator with full permissions.']
        );

        Role::firstOrCreate(
            ['name' => 'User'],
            ['name' => 'User', 'description' => 'Regular user with limited permissions.']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
