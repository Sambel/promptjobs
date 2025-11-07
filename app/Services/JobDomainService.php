<?php

namespace App\Services;

class JobDomainService
{
    /**
     * Available job domains (based on Jobicy and Remotive categories)
     */
    public static function getDomains(): array
    {
        return [
            'software-development' => 'Software Development',
            'data-analysis' => 'Data Analysis',
            'design' => 'Design',
            'marketing' => 'Marketing',
            'sales-business' => 'Sales / Business',
            'product' => 'Product',
            'customer-service' => 'Customer Service',
            'devops-sysadmin' => 'DevOps / Sysadmin',
            'project-management' => 'Project Management',
            'qa' => 'QA',
            'writing' => 'Writing',
            'finance-legal' => 'Finance / Legal',
            'human-resources' => 'Human Resources',
            'other' => 'Other',
        ];
    }

    /**
     * Detect job domain from title and description
     */
    public static function detectDomain(string $title, ?string $description = null): ?string
    {
        $text = strtolower($title . ' ' . ($description ?? ''));

        // Define keywords for each domain (order matters - more specific first)
        $domainKeywords = [
            'data-analysis' => [
                'data scientist', 'data analyst', 'data engineer', 'analytics',
                'business intelligence', 'bi analyst', 'statistician', 'quantitative',
                'data analytics', 'sql analyst', 'data warehouse', 'etl',
                'machine learning', 'ml engineer', 'deep learning', 'neural network',
                'computer vision', 'nlp', 'natural language', 'llm', 'gpt',
                'pytorch', 'tensorflow', 'ai engineer', 'ai/ml', 'research scientist'
            ],
            'devops-sysadmin' => [
                'devops', 'sre', 'site reliability', 'system administrator', 'sysadmin',
                'infrastructure engineer', 'platform engineer', 'cloud engineer',
                'kubernetes', 'docker', 'infrastructure', 'systems engineer'
            ],
            'qa' => [
                'qa engineer', 'quality assurance', 'test engineer', 'sdet',
                'software tester', 'qa analyst', 'test automation', 'testing engineer'
            ],
            'product' => [
                'product manager', 'product owner', 'product lead', 'product director',
                'product strategy', 'technical product manager', 'tpm', 'program manager',
                'product marketing'
            ],
            'project-management' => [
                'project manager', 'scrum master', 'agile coach', 'delivery manager',
                'project coordinator', 'program coordinator'
            ],
            'design' => [
                'designer', 'ux designer', 'ui designer', 'product designer',
                'user experience', 'user interface', 'visual designer',
                'interaction designer', 'design lead', 'graphic designer', 'ui/ux'
            ],
            'marketing' => [
                'marketing manager', 'content marketing', 'digital marketing',
                'marketing director', 'seo', 'growth marketing', 'marketing analyst',
                'marketing coordinator', 'brand manager', 'demand generation'
            ],
            'sales-business' => [
                'sales', 'account executive', 'business development', 'sales engineer',
                'account manager', 'sales manager', 'business analyst', 'strategy',
                'partnerships', 'sales director'
            ],
            'customer-service' => [
                'customer success', 'customer support', 'technical support',
                'customer experience', 'support engineer', 'customer service',
                'customer operations', 'success manager', 'support specialist'
            ],
            'finance-legal' => [
                'finance', 'financial analyst', 'accountant', 'controller',
                'legal', 'lawyer', 'counsel', 'compliance', 'risk', 'treasury'
            ],
            'human-resources' => [
                'human resources', 'hr manager', 'recruiter', 'talent acquisition',
                'people operations', 'people partner', 'hr specialist', 'recruiting'
            ],
            'writing' => [
                'content writer', 'technical writer', 'copywriter', 'editor',
                'documentation', 'content creator', 'blog writer', 'communication specialist'
            ],
            'software-development' => [
                'software engineer', 'developer', 'backend', 'frontend', 'full stack',
                'full-stack', 'web developer', 'mobile developer', 'programmer',
                'software development', 'technical lead', 'architect', 'engineering'
            ],
        ];

        // Check each domain for matching keywords
        foreach ($domainKeywords as $domain => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $domain;
                }
            }
        }

        // Default to other if no match found
        return 'other';
    }

    /**
     * Get domain label from slug
     */
    public static function getDomainLabel(string $slug): ?string
    {
        $domains = self::getDomains();
        return $domains[$slug] ?? null;
    }
}
