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
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE reports DROP CONSTRAINT IF EXISTS reports_report_type_check');
        }

        Schema::table('reports', function (Blueprint $table): void {
            $table->string('report_type')->change();
        });

        DB::table('reports')
            ->where('report_type', "I''don't want to see this")
            ->update(['report_type' => "I don't want to see this"]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $reportTypes = [
            'Problem involving someone under 18',
            'Bullying, harassment or abuse',
            'Suicide or self-harm',
            'Violent, hateful or disturbing content',
            'Selling or promoting restricted items',
            'Adult content',
            'Scam, fraud or false information',
            'Intellectual property',
            "I don't want to see this",
        ];

        if (DB::table('reports')->whereNotIn('report_type', $reportTypes)->exists()) {
            throw new RuntimeException('Remove reports with custom types before reverting this migration.');
        }

        Schema::table('reports', function (Blueprint $table) use ($reportTypes): void {
            $table->enum('report_type', $reportTypes)->change();
        });
    }
};
