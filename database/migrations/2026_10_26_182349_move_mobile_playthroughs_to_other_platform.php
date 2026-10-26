<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The Mobile platform was removed, so its playthroughs are moved to Other.
     */
    public function up(): void
    {
        DB::table('playthroughs')->where('platform', 'mobile')->update(['platform' => 'other']);
    }

    /**
     * The original platform cannot be recovered, so there is nothing to undo.
     */
    public function down(): void
    {
        //
    }
};
