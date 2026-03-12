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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('work_number', 100)->nullable();
            $table->date('plan_issue_date')->nullable();

            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('designer_id')->nullable()->constrained('designers')->nullOnDelete();
            $table->foreignId('general_designer_id')->constrained('general_designers')->restrictOnDelete();
            $table->foreignId('geodesy_id')->nullable()->constrained('geodesies')->nullOnDelete();

            $table->boolean('road_construction_plan')->nullable();
            $table->boolean('water_network_plan')->nullable();
            $table->boolean('sewage_plan')->nullable();
            $table->boolean('stormwater_drainage_plan')->nullable();
            $table->boolean('public_lighting_plan')->nullable();

            $table->string('other_work_parts')->nullable();

            $table->date('eutility_statement_issue_date')->nullable();
            $table->date('road_construction_permit_date')->nullable();
            $table->date('water_rights_permit_date')->nullable();

            $table->string('notes')->nullable();
            $table->string('folder_number')->nullable();

            $table->tinyInteger('min_role_level');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
