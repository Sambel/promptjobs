<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Senior AI Engineer',
                'company' => 'OpenAI',
                'company_logo' => 'https://logo.clearbit.com/openai.com',
                'description' => 'We are looking for a Senior AI Engineer to join our team working on cutting-edge language models. You will be responsible for training and fine-tuning large language models, developing novel architectures, and deploying AI systems at scale.',
                'location' => 'San Francisco, CA',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$180k - $250k',
                'apply_url' => 'https://openai.com/careers',
                'tags' => ['GPT', 'LLM', 'Python', 'PyTorch', 'Transformers'],
                'featured' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Machine Learning Engineer',
                'company' => 'Anthropic',
                'company_logo' => 'https://logo.clearbit.com/anthropic.com',
                'description' => 'Join our team to build safe and beneficial AI systems. Work on large-scale ML infrastructure, model training, and deployment of Claude AI.',
                'location' => 'Remote',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$170k - $240k',
                'apply_url' => 'https://anthropic.com/careers',
                'tags' => ['Machine Learning', 'Python', 'TensorFlow', 'AI Safety', 'LLM'],
                'featured' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Prompt Engineer',
                'company' => 'Midjourney',
                'company_logo' => 'https://logo.clearbit.com/midjourney.com',
                'description' => 'We\'re seeking a creative Prompt Engineer to help users get the most out of our AI image generation platform. Design prompt templates, create documentation, and work directly with our AI models.',
                'location' => 'Remote',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$120k - $180k',
                'apply_url' => 'https://midjourney.com/careers',
                'tags' => ['Prompt Engineering', 'Generative AI', 'Creative AI', 'GPT'],
                'featured' => false,
                'published_at' => now(),
            ],
            [
                'title' => 'AI Research Scientist',
                'company' => 'DeepMind',
                'company_logo' => 'https://logo.clearbit.com/deepmind.com',
                'description' => 'Conduct world-class research in artificial intelligence and machine learning. Work on breakthrough projects in reinforcement learning, computer vision, and language understanding.',
                'location' => 'London, UK',
                'remote' => false,
                'job_type' => 'full-time',
                'salary_range' => '£100k - £180k',
                'apply_url' => 'https://deepmind.com/careers',
                'tags' => ['Research', 'Machine Learning', 'Reinforcement Learning', 'Python', 'JAX'],
                'featured' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'LLM Integration Developer',
                'company' => 'Notion',
                'company_logo' => 'https://logo.clearbit.com/notion.so',
                'description' => 'Build and integrate AI features into Notion. Work with GPT models to create intelligent writing assistance, content generation, and automation features.',
                'location' => 'San Francisco, CA',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$140k - $200k',
                'apply_url' => 'https://notion.so/careers',
                'tags' => ['LLM', 'API Integration', 'TypeScript', 'React', 'GPT'],
                'featured' => false,
                'published_at' => now(),
            ],
            [
                'title' => 'AI Product Manager',
                'company' => 'Hugging Face',
                'company_logo' => 'https://logo.clearbit.com/huggingface.co',
                'description' => 'Lead product strategy for our AI model hub and deployment platform. Work with ML engineers and the open-source community to shape the future of AI deployment.',
                'location' => 'Remote',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$150k - $210k',
                'apply_url' => 'https://huggingface.co/careers',
                'tags' => ['Product Management', 'AI/ML', 'Transformers', 'Open Source'],
                'featured' => false,
                'published_at' => now(),
            ],
            [
                'title' => 'Computer Vision Engineer',
                'company' => 'Tesla',
                'company_logo' => 'https://logo.clearbit.com/tesla.com',
                'description' => 'Develop cutting-edge computer vision algorithms for autonomous driving. Work on perception, prediction, and planning systems for Tesla\'s Autopilot.',
                'location' => 'Palo Alto, CA',
                'remote' => false,
                'job_type' => 'full-time',
                'salary_range' => '$160k - $230k',
                'apply_url' => 'https://tesla.com/careers',
                'tags' => ['Computer Vision', 'Deep Learning', 'Python', 'C++', 'PyTorch'],
                'featured' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'NLP Engineer',
                'company' => 'Google',
                'company_logo' => 'https://logo.clearbit.com/google.com',
                'description' => 'Join Google AI to work on natural language understanding systems. Develop models for search, translation, and conversation understanding at massive scale.',
                'location' => 'Mountain View, CA',
                'remote' => false,
                'job_type' => 'full-time',
                'salary_range' => '$170k - $250k',
                'apply_url' => 'https://careers.google.com',
                'tags' => ['NLP', 'TensorFlow', 'BERT', 'Transformers', 'Python'],
                'featured' => false,
                'published_at' => now(),
            ],
            [
                'title' => 'AI Safety Researcher',
                'company' => 'Anthropic',
                'company_logo' => 'https://logo.clearbit.com/anthropic.com',
                'description' => 'Research and develop techniques to ensure AI systems are safe, beneficial, and aligned with human values. Work on interpretability, robustness, and alignment.',
                'location' => 'San Francisco, CA',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$180k - $260k',
                'apply_url' => 'https://anthropic.com/careers',
                'tags' => ['AI Safety', 'Research', 'Alignment', 'Python', 'Machine Learning'],
                'featured' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'MLOps Engineer',
                'company' => 'Databricks',
                'company_logo' => 'https://logo.clearbit.com/databricks.com',
                'description' => 'Build and maintain ML infrastructure and deployment pipelines. Work on model monitoring, versioning, and production deployment systems.',
                'location' => 'Remote',
                'remote' => true,
                'job_type' => 'full-time',
                'salary_range' => '$140k - $190k',
                'apply_url' => 'https://databricks.com/careers',
                'tags' => ['MLOps', 'Kubernetes', 'Python', 'CI/CD', 'Docker'],
                'featured' => false,
                'published_at' => now(),
            ],
        ];

        foreach ($jobs as $job) {
            Job::create($job);
        }
    }
}
