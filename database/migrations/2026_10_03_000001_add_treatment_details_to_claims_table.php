<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('hospital_name')->nullable()->after('description');
            $table->date('treatment_start_date')->nullable()->after('hospital_name');
            $table->date('treatment_end_date')->nullable()->after('treatment_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['hospital_name', 'treatment_start_date', 'treatment_end_date']);
        });
    }
};
