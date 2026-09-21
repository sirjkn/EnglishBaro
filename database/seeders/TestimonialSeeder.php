<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['name' => 'Amina Yusuf', 'role_label' => 'IELTS Candidate', 'content' => 'EnglishBaro helped me improve my band score in just two months of consistent practice.', 'rating' => 5],
            ['name' => 'David Otieno', 'role_label' => 'Working Professional', 'content' => 'The business English course gave me the confidence to lead meetings in English.', 'rating' => 5],
            ['name' => 'Fatima Ali', 'role_label' => 'Student', 'content' => 'Clear video lessons and helpful eBooks. Highly recommended for beginners.', 'rating' => 4],
        ];

        foreach ($testimonials as $index => $data) {
            Testimonial::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'role_label' => $data['role_label'],
                    'content' => $data['content'],
                    'rating' => $data['rating'],
                    'is_published' => true,
                    'order' => $index,
                ]
            );
        }
    }
}
