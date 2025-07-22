<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbd_products', function (Blueprint $table) {
            // Ajout de métadonnées pour les fichiers
            $table->json('image_metadata')->nullable()->after('images');
            $table->string('analysis_file_original_name')->nullable()->after('analysis_file');
            $table->bigInteger('analysis_file_size')->nullable()->after('analysis_file_original_name');
            $table->string('analysis_file_mime_type')->nullable()->after('analysis_file_size');
        });

        Schema::table('users', function (Blueprint $table) {
            // Amélioration de la gestion des avatars
            $table->string('avatar_original_name')->nullable()->after('avatar');
            $table->bigInteger('avatar_size')->nullable()->after('avatar_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('cbd_products', function (Blueprint $table) {
            $table->dropColumn([
                'image_metadata',
                'analysis_file_original_name',
                'analysis_file_size',
                'analysis_file_mime_type'
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_original_name',
                'avatar_size'
            ]);
        });
    }
};
