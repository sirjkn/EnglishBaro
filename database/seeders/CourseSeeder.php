<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['title' => 'English Foundations for Beginners', 'level' => 'Beginner', 'price' => 19.99, 'featured' => true],
            ['title' => 'Everyday Conversational English', 'level' => 'Elementary', 'price' => 24.99, 'featured' => true],
            ['title' => 'Grammar Essentials', 'level' => 'Pre-Intermediate', 'price' => 29.99, 'featured' => true],
            ['title' => 'Business English Communication', 'level' => 'Intermediate', 'price' => 39.99, 'featured' => true],
            ['title' => 'IELTS Preparation Intensive', 'level' => 'Upper-Intermediate', 'price' => 49.99, 'featured' => true],
            ['title' => 'Advanced Academic Writing', 'level' => 'Advanced', 'price' => 59.99, 'featured' => true],
        ];

        foreach ($courses as $data) {
            $level = Level::query()->where('name', $data['level'])->first();

            $course = Course::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'description' => "A comprehensive {$data['level']} level English course covering listening, speaking, reading, and writing skills.",
                    'learning_outcomes' => 'By the end of this course you will be able to communicate confidently in real-world English scenarios.',
                    'level_id' => $level?->id,
                    'price' => $data['price'],
                    'currency' => 'USD',
                    'duration_days' => 120,
                    'subscription_days' => 120,
                    'status' => 'published',
                    'is_featured' => $data['featured'],
                ]
            );

            if ($course->sections()->count() === 0) {
                foreach (['Getting Started', 'Core Skills', 'Practice & Review'] as $index => $sectionTitle) {
                    $section = CourseSection::query()->create([
                        'course_id' => $course->id,
                        'title' => $sectionTitle,
                        'description' => "Section covering {$sectionTitle} for {$data['title']}.",
                        'order' => $index,
                    ]);

                    foreach (range(1, 3) as $lessonNumber) {
                        $section->lessons()->create([
                            'course_id' => $course->id,
                            'title' => "{$sectionTitle} - Lesson {$lessonNumber}",
                            'description' => 'Lesson content coming soon.',
                            'order' => $lessonNumber - 1,
                            'duration_seconds' => 300,
                            'is_preview' => $index === 0 && $lessonNumber === 1,
                        ]);
                    }
                }
            }
        }
    }
}
