<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webkul\Lead\Models\Pipeline;
use Webkul\User\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goals_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id'); // Cambiado a unsignedInteger para coincidir con int(10) UNSIGNED
            $table->unsignedInteger('pipeline_id'); // Asumiendo que pipeline también usa int(10) UNSIGNED

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pipeline_id')
                ->references('id')
                ->on('lead_pipelines')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals_user');
    }
};
