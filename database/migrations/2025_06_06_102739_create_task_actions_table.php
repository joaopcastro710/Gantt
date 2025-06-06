<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_template_id')->constrained('task_templates');
            /*
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('stage_id')->nullable();
            */
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('deadline');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_actions');
    }
};