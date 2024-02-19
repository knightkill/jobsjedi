<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Monitor::class)->constrained();

            $table->string('service_id');
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('pay_range')->nullable();
            $table->string('pay_range_start')->nullable();
            $table->string('pay_range_end')->nullable();
            $table->string('currency')->nullable();
            $table->string('working_hours')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->text('link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
};
