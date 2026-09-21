<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['name' => 'Amina Yusuf', 'role_label' => 'IELTS Candidate', 'content' => 'EnglishBaro helped me improve my band score in just two months of consistent practice.', 'rating' => 5, 'avatar' => 47],
            ['name' => 'David Otieno', 'role_label' => 'Working Professional', 'content' => 'The business English course gave me the confidence to lead meetings in English.', 'rating' => 5, 'avatar' => 12],
            ['name' => 'Fatima Ali', 'role_label' => 'Student', 'content' => 'Clear video lessons and helpful eBooks. Highly recommended for beginners.', 'rating' => 4, 'avatar' => 25],
            ['name' => 'Mohamed Hassan', 'role_label' => 'IELTS Candidate', 'content' => 'The assessments felt just like the real exam. I walked in prepared and confident.', 'rating' => 5, 'avatar' => 33],
            ['name' => 'Grace Wanjiru', 'role_label' => 'University Student', 'content' => 'I love that I can study on my phone between classes. The progress tracking keeps me motivated.', 'rating' => 5, 'avatar' => 44],
            ['name' => 'Ibrahim Noor', 'role_label' => 'Working Professional', 'content' => 'Affordable, practical, and the certificate looks great on my CV. Worth every shilling.', 'rating' => 4, 'avatar' => 15],
        ];

        foreach ($testimonials as $index => $data) {
            $avatar = Media::query()->firstOrCreate(
                ['url' => "https://i.pravatar.cc/150?img={$data['avatar']}"],
                [
                    'type' => 'image',
                    'source_type' => 'external',
                    'provider' => 'other',
                    'title' => $data['name'].' avatar',
                ]
            );

            Testimonial::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'role_label' => $data['role_label'],
                    'content' => $data['content'],
                    'rating' => $data['rating'],
                    'avatar_media_id' => $avatar->id,
                    'is_published' => true,
                    'order' => $index,
                ]
            );
        }
    }
}
