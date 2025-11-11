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
        Schema::create('area_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['area_id', 'user_id']);
        });

        if (Schema::hasTable('group_user') && Schema::hasTable('area_group')) {
            $now = now();

            $pairs = DB::table('group_user')
                ->join('area_group', 'group_user.group_id', '=', 'area_group.group_id')
                ->select('area_group.area_id', 'group_user.user_id')
                ->distinct()
                ->get();

            if ($pairs->isNotEmpty()) {
                DB::table('area_user')->insert(
                    $pairs->map(fn ($row) => [
                        'area_id' => $row->area_id,
                        'user_id' => $row->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->toArray()
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_user');
    }
};
