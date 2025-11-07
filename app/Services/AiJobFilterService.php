<?php

namespace App\Services;

class AiJobFilterService
{
    /**
     * Comprehensive list of AI-related keywords for filtering jobs
     */
    private const AI_KEYWORDS = [
        // AI/ML Core Terms
        'artificial intelligence',
        'machine learning',
        'deep learning',
        'neural network',
        'computer vision',
        'natural language processing',
        'nlp',
        'generative ai',
        'gen ai',
        'genai',
        'llm',
        'large language model',
        'foundation model',
        'transformer',
        'diffusion model',

        // AI Technologies & Frameworks
        'tensorflow',
        'pytorch',
        'keras',
        'scikit-learn',
        'hugging face',
        'huggingface',
        'langchain',
        'llamaindex',
        'openai api',
        'anthropic api',
        'stable diffusion',
        'midjourney',
        'dall-e',
        'whisper',

        // AI Companies & Products
        'openai',
        'chatgpt',
        'gpt-3',
        'gpt-4',
        'gpt-5',
        'claude',
        'anthropic',
        'gemini',
        'bard',
        'copilot',
        'deepseek',
        'mistral',
        'cohere',
        'perplexity',
        'midjourney',
        'runway',
        'stability ai',
        'replicate',

        // ML/AI Specializations
        'computer vision',
        'image recognition',
        'object detection',
        'semantic segmentation',
        'speech recognition',
        'text-to-speech',
        'speech-to-text',
        'recommendation system',
        'recommendation engine',
        'predictive modeling',
        'predictive analytics',
        'anomaly detection',
        'reinforcement learning',
        'supervised learning',
        'unsupervised learning',
        'transfer learning',
        'few-shot learning',
        'zero-shot learning',
        'prompt engineering',
        'prompt tuning',
        'fine-tuning',
        'model training',
        'model optimization',

        // AI/ML Roles
        'machine learning engineer',
        'ml engineer',
        'ai engineer',
        'data scientist',
        'ai researcher',
        'ml researcher',
        'research scientist',
        'applied scientist',
        'mlops',
        'ml ops',
        'ai product manager',
        'prompt engineer',

        // Data Science (AI-adjacent)
        'data science',
        'data scientist',
        'data engineering',
        'data pipeline',
        'feature engineering',
        'model deployment',
        'model serving',
        'ml infrastructure',

        // AI Applications
        'chatbot',
        'conversational ai',
        'voice assistant',
        'virtual assistant',
        'intelligent automation',
        'ai automation',
        'ai agent',
        'autonomous agent',
        'retrieval augmented generation',
        'rag',
        'vector database',
        'vector search',
        'embedding',
        'embeddings',
        'semantic search',

        // AI Ethics & Safety
        'ai safety',
        'ai alignment',
        'responsible ai',
        'ai ethics',
        'ai governance',
        'model interpretability',
        'explainable ai',
        'xai',

        // Specific Tools
        'jupyter',
        'mlflow',
        'wandb',
        'weights & biases',
        'kubeflow',
        'sagemaker',
        'vertex ai',
        'azure ml',
        'databricks',
        'snowflake ml',

        // Programming & AI
        'python ml',
        'python ai',
        'cuda',
        'gpu programming',
        'distributed training',
        'model optimization',
        'quantization',
        'pruning',
        'distillation',
    ];

    /**
     * Check if a job is AI-related based on title and description
     */
    public static function isAiRelated(string $title, string $description): bool
    {
        $text = strtolower($title . ' ' . $description);

        foreach (self::AI_KEYWORDS as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all AI keywords (useful for debugging or display)
     */
    public static function getKeywords(): array
    {
        return self::AI_KEYWORDS;
    }

    /**
     * Extract AI-related tags from text
     */
    public static function extractAiTags(string $text): array
    {
        $text = strtolower($text);
        $foundTags = [];

        // Check for each keyword
        foreach (self::AI_KEYWORDS as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                // Use proper casing for tags
                $foundTags[] = ucwords($keyword);
            }
        }

        return array_unique($foundTags);
    }
}
