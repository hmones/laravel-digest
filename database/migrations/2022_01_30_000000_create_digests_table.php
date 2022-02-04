<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigestsTable extends Migration
{
    public function up(): void
    {
        Schema::create('digests', function (Blueprint $table) {
            $table->id();
            $table->string('batch');
            $table->string('mailable');
            $table->string('frequency')->nullable();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digests');
    }
}
