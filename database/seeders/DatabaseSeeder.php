<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobAttributeValue;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $languages = [
            'PHP',
            'JavaScript',
            'Python',
            'Java',
            'C#',
            'C++',
            'Go',
            'Ruby',
            'Swift',
            'Kotlin'
        ];

        foreach ($languages as $language) {
            Language::create(['name' => $language]);
        }

        // Create locations
        $locations = [
            ['city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['city' => 'London', 'state' => null, 'country' => 'UK'],
            ['city' => 'Berlin', 'state' => null, 'country' => 'Germany'],
            ['city' => 'Remote', 'state' => null, 'country' => 'Global'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }

        // Create categories
        $categories = [
            'Backend Development',
            'Frontend Development',
            'Full Stack',
            'DevOps',
            'Data Science',
            'Machine Learning',
            'QA',
            'Project Management'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }

        // Create attributes
        $attributes = [
            [
                'name' => 'years_experience',
                'type' => 'number',
                'options' => null
            ],
            [
                'name' => 'benefits',
                'type' => 'text',
                'options' => null
            ],
            [
                'name' => 'has_equity',
                'type' => 'boolean',
                'options' => null
            ],
            [
                'name' => 'seniority',
                'type' => 'select',
                'options' => json_encode(['Junior', 'Mid-level', 'Senior', 'Lead', 'Director'])
            ],
            [
                'name' => 'application_deadline',
                'type' => 'date',
                'options' => null
            ],
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }

        // Create sample jobs
        $jobsData = [
            [
                'title' => 'Senior PHP Developer',
                'description' => 'We are looking for an experienced PHP developer with Laravel expertise.',
                'company_name' => 'Tech Solutions Ltd',
                'salary_min' => 80000,
                'salary_max' => 110000,
                'is_remote' => true,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now(),
                'languages' => ['PHP', 'JavaScript'],
                'locations' => ['Remote'],
                'categories' => ['Backend Development'],
                'attributes' => [
                    'years_experience' => '5',
                    'benefits' => 'Health insurance, 401k, flexible working hours',
                    'has_equity' => '1',
                    'seniority' => 'Senior',
                    'application_deadline' => '2025-05-15',
                ]
            ],
            [
                'title' => 'Frontend React Developer',
                'description' => 'Join our team to build modern web applications with React.',
                'company_name' => 'WebApp Inc',
                'salary_min' => 70000,
                'salary_max' => 95000,
                'is_remote' => false,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'languages' => ['JavaScript'],
                'locations' => ['San Francisco', 'New York'],
                'categories' => ['Frontend Development'],
                'attributes' => [
                    'years_experience' => '3',
                    'benefits' => 'Competitive salary, health benefits, annual bonus',
                    'has_equity' => '1',
                    'seniority' => 'Mid-level',
                    'application_deadline' => '2025-05-30',
                ]
            ],
            [
                'title' => 'Python Data Scientist',
                'description' => 'Work on machine learning models for our innovative products.',
                'company_name' => 'AI Innovations',
                'salary_min' => 100000,
                'salary_max' => 140000,
                'is_remote' => true,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'languages' => ['Python'],
                'locations' => ['Remote', 'New York'],
                'categories' => ['Data Science', 'Machine Learning'],
                'attributes' => [
                    'years_experience' => '4',
                    'benefits' => 'Flexible hours, remote work option, professional development budget',
                    'has_equity' => '1',
                    'seniority' => 'Senior',
                    'application_deadline' => '2025-06-15',
                ]
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'Help us build and maintain our cloud infrastructure.',
                'company_name' => 'Cloud Systems',
                'salary_min' => 85000,
                'salary_max' => 120000,
                'is_remote' => false,
                'job_type' => 'contract',
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'languages' => ['Python', 'Go'],
                'locations' => ['London'],
                'categories' => ['DevOps'],
                'attributes' => [
                    'years_experience' => '5',
                    'benefits' => 'Competitive salary, flexible hours',
                    'has_equity' => '0',
                    'seniority' => 'Senior',
                    'application_deadline' => '2025-04-30',
                ]
            ],
            [
                'title' => 'Full Stack Java Developer',
                'description' => 'Work on our enterprise application with Java and Angular.',
                'company_name' => 'Enterprise Solutions',
                'salary_min' => 90000,
                'salary_max' => 115000,
                'is_remote' => false,
                'job_type' => 'full-time',
                'status' => 'draft',
                'published_at' => null,
                'languages' => ['Java', 'JavaScript'],
                'locations' => ['Berlin'],
                'categories' => ['Full Stack'],
                'attributes' => [
                    'years_experience' => '6',
                    'benefits' => 'Health insurance, annual leave, company events',
                    'has_equity' => '0',
                    'seniority' => 'Lead',
                    'application_deadline' => '2025-06-01',
                ]
            ],
        ];

        foreach ($jobsData as $jobData) {
            // Extract relationships and attributes
            $languagesData = $jobData['languages'];
            $locationsData = $jobData['locations'];
            $categoriesData = $jobData['categories'];
            $attributesData = $jobData['attributes'];

            unset($jobData['languages'], $jobData['locations'], $jobData['categories'], $jobData['attributes']);

            // Create job
            $job = Job::create($jobData);

            // Attach languages
            $languageIds = Language::whereIn('name', $languagesData)->pluck('id');
            $job->languages()->attach($languageIds);

            // Attach locations
            $locationIds = Location::whereIn('city', $locationsData)->pluck('id');
            $job->locations()->attach($locationIds);

            // Attach categories
            $categoryIds = Category::whereIn('name', $categoriesData)->pluck('id');
            $job->categories()->attach($categoryIds);

            // Add attribute values
            foreach ($attributesData as $name => $value) {
                $attribute = Attribute::where('name', $name)->first();
                if ($attribute) {
                    JobAttributeValue::create([
                        'job_id' => $job->id,
                        'attribute_id' => $attribute->id,
                        'value' => $value
                    ]);
                }
            }
        }
    }
}
