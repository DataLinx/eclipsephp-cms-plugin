<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cms_banner_image_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->smallInteger('image_width')->nullable();
            $table->smallInteger('image_height')->nullable();
            $table->boolean('is_hidpi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_banner_image_types');
    }
};
