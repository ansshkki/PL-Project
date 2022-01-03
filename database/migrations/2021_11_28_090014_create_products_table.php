<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->text('image_url');
            $table->date('expiration_date');
            $table->double('price');
            $table->text('periods');
            $table->integer('quantity')->default(1);
            $table->integer('views')->default(0);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // $table->foreignId('comment')->constrained('comment');
            // $table->foreignId('like')->constrained('like');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
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
        Schema::dropIfExists('products');
    }
}
