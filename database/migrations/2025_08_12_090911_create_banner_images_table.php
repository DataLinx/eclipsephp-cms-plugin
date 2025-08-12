<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_banner_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')
                ->constrained('cms_banners', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('type_id')
                ->constrained('cms_banner_image_types', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('file');
            $table->boolean('is_hidpi');
            $table->smallInteger('image_width');
            $table->smallInteger('image_height');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_banner_images');
    }
};
