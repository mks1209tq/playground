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
        Schema::create('emirates_ids', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 1);
            $table->string('country_code', 3);
            $table->string('card_number', 10);
            $table->string('id_number', 18);
            $table->date('date_of_birth');
            $table->string('gender', 6);
            $table->date('expiry_date');
            $table->string('nationality', 3);
            $table->string('surname');
            $table->string('given_names');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emirates_ids');
    }
};
