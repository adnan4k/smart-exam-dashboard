<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('year')->after('type_id');
        });
    }
    
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
