<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listing', function (Blueprint $table) {
            $table->id();
            $table->string('pavadinimas', 100);
            $table->text('aprasymas');
            $table->decimal('kaina', 10, 2);
            $table->string('tipas', 20);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->string('statusas', 30)->default('aktyvus');

            $table->boolean('is_hidden')->default(false);
            $table->integer('kiekis')->default(1);
            $table->string('package_size', 2)->default('S');
            $table->boolean('is_renewable')->default(0);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing');
    }
};