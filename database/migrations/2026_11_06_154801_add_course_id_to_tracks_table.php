<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('id')->constrained('courses')->restrictOnDelete();
        });

        // Every track that already exists today is one of the four English
        // CEFR units, so they all move under a single new "English" course,
        // carrying over its highest-priced track's catalog pricing as the
        // course's own price (pricing moves off individual tracks next).
        $referenceTrack = DB::table('tracks')->where('is_default', true)->first()
            ?? DB::table('tracks')->orderByDesc('price')->first();

        if (! $referenceTrack) {
            return;
        }

        $courseId = DB::table('courses')->insertGetId([
            'course_code' => 'ENG',
            'name' => 'English',
            'slug' => Str::slug('English'),
            'description' => 'Learn English from absolute beginner to advanced, one unit at a time.',
            'learning_outcomes' => $referenceTrack->learning_outcomes,
            'thumbnail_id' => $referenceTrack->thumbnail_id,
            'price' => $referenceTrack->price,
            'price_africa' => $referenceTrack->price_africa,
            'price_europe' => $referenceTrack->price_europe,
            'price_north_america' => $referenceTrack->price_north_america,
            'price_asia' => $referenceTrack->price_asia,
            'currency' => $referenceTrack->currency,
            'subscription_days' => $referenceTrack->subscription_days,
            'status' => 'published',
            'is_featured' => true,
            'order' => 1,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tracks')->update(['course_id' => $courseId]);
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
