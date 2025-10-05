<?php

// database/migrations/2025_09_29_000000_add_geo_fields_to_state_assets.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('state_assets', function (Blueprint $table) {
            // Type de bien
            // $table->enum('asset_type', ['immobilier', 'materiel'])->default('immobilier')->after('name');

            // Localisation simple (centre)
            // $table->decimal('lat', 10, 7)->nullable()->after('asset_type');
            // $table->decimal('lng', 10, 7)->nullable()->after('lat');

            // Emprise carrée (côté en mètres) -> utilisé pour IMMOBILIER
            $table->unsignedInteger('extent_side_m')->nullable()->after('lng');

            // Empreinte polygonale (GeoJSON-like: [["lat","lng"],...])
            $table->json('footprint')->nullable()->after('extent_side_m');

            // Métadonnées matériel
            $table->string('material_category')->nullable()->after('footprint'); // ex: vehicle, computer, furniture...
            $table->string('material_image_path')->nullable()->after('material_category'); // upload optionnel
        });
    }

    public function down(): void
    {
        Schema::table('state_assets', function (Blueprint $table) {
            $table->dropColumn(['asset_type','lat','lng','extent_side_m','footprint','material_category','material_image_path']);
        });
    }
};
