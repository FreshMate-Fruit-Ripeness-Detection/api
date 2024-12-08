<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('detection_histories', function (Blueprint $table) {
            $table->id();
            $table->string('fruit_type');
            $table->string('ripeness');
            $table->float('confidence');
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_histories');
    }
};
