<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'login')) {
                $table->string('login')->nullable()->after('email');
            }
        });

        DB::table('users')
            ->whereNull('login')
            ->orderBy('id')
            ->lazy()
            ->each(function ($user) {
                $base = preg_replace('/[^A-Za-z0-9._-]/', '', strtok($user->email ?? 'user', '@'));
                $base = $base ?: 'usuario';
                $login = $base;
                $suffix = 1;

                while (DB::table('users')->where('login', $login)->exists()) {
                    $login = $base . $suffix;
                    $suffix++;
                }

                DB::table('users')->where('id', $user->id)->update([
                    'login' => $login,
                    'updated_at' => now(),
                ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_login_unique');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('login');
            });
        }
    }
};
