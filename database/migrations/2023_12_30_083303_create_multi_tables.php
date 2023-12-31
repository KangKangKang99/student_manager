<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->bigInteger('semester_id')->unsigned();
            $table->bigInteger('student_id')->unsigned();
            $table->string('name');
            $table->tinyInteger('type')->comment('1: tài năng , 2: hỗ trợ học tập ');
            $table->integer('amount')->comment('số tiền học bổng');
            $table->timestamps();
        });
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('note');
            $table->timestamps();
        });
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->tinyInteger('semester');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('admission_date');
            $table->string('cid');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->bigInteger('major_id')->unsigned();
            $table->foreign('major_id')->references('id')->on('majors')->onDelete('cascade');
            $table->tinyInteger('status')->default(1)->comment('1: đang học, 2: nghỉ học, 3: đã tốt nghiệp');
            $table->timestamps();
        });
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('credit');
            $table->text('note')->nullable();
            $table->timestamps();
        });
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->json('schedule')->nullable();
            $table->bigInteger('semester_id')->unsigned();
            $table->bigInteger('course_id')->unsigned();
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('class_id')->unsigned();
            $table->bigInteger('student_id')->unsigned();
            $table->double('attendance_score')->nullable()->comment('điểm danh');
            $table->double('midterm_score')->nullable()->comment('điểm giữa kỳ');
            $table->double('final_score')->nullable()->comment('điểm cuối kỳ');
            $table->tinyInteger('status')->default(1)->comment('1: chưa tổng kết, 2: đã tổng kết');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_semester_students');
        Schema::dropIfExists('course_semesters');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('students');
        Schema::dropIfExists('majors');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('courses');


    }
};
