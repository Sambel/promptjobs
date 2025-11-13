<?php

namespace App\Services;

/**
 * Service de filtrage pour les jobs LLM/GenAI/Prompt Engineering
 * Remplace l'ancien AiJobFilterService avec des mots-clés optimisés
 */
class PromptEngineeringFilterService
{
    /**
     * Mots-clés optimisés pour détecter les jobs LLM/GenAI/Prompt Engineering
     * Affinés pour éviter les faux positifs (sans "RAG" seul, sans "prompt" seul)
     */
    private const LLM_KEYWORDS = [
        // ===== PROMPT ENGINEERING (Priorité maximale) =====
        'prompt engineering',
        'prompt engineer',
        'prompt designer',
        'prompt design',

        // ===== LLM & LANGUAGE MODELS =====
        'LLM',
        'large language model',
        'language model engineer',

        // ===== GENERATIVE AI =====
        'generative AI',
        'GenAI',
        'generative artificial intelligence',

        // ===== AI COMPANIES & PRODUCTS (très spécifiques) =====
        'GPT-3',
        'GPT-4',
        'GPT-3.5',
        'ChatGPT',
        'Claude AI',
        'Claude',
        'Anthropic',
        'OpenAI',
        'Gemini AI',
        'Bard',
        'LLaMA',
        'Llama 2',
        'Llama 3',
        'Mistral AI',
        'Cohere',

        // ===== AI/ML TECHNIQUES (spécifiques) =====
        'fine-tuning LLM',
        'fine tuning language model',
        'RLHF',
        'retrieval augmented generation',
        'retrieval-augmented generation',
        'vector database',
        'vector embeddings',
        'embeddings',
        'transformer model',
        'attention mechanism',

        // ===== CONVERSATIONAL AI =====
        'conversational AI',
        'chatbot AI',
        'dialogue system',
        'voice AI',

        // ===== NLP ENGINEER =====
        'NLP engineer',
        'natural language processing engineer',
        'NLP specialist',

        // ===== AI/ML TOOLS & FRAMEWORKS (spécifiques) =====
        'LangChain',
        'LlamaIndex',
        'Hugging Face',
        'vLLM',
        'Ollama',
        'LangSmith',

        // ===== SPECIFIC ROLES (bien définis) =====
        'AI engineer',
        'ML engineer',
        'machine learning engineer',
        'MLOps engineer',
    ];

    /**
     * Catégories de badges avec leurs mots-clés spécifiques
     */
    private const CATEGORIES = [
        'prompt_engineering' => [
            'prompt engineering',
            'prompt engineer',
            'prompt designer',
            'prompt design',
        ],
        'llm_engineering' => [
            'LLM',
            'large language model',
            'language model engineer',
            'GPT-3',
            'GPT-4',
            'GPT-3.5',
            'ChatGPT',
            'Claude AI',
            'Claude',
            'LLaMA',
            'Llama 2',
            'Llama 3',
            'transformer model',
        ],
        'genai' => [
            'generative AI',
            'GenAI',
            'generative artificial intelligence',
        ],
        'ml_engineer' => [
            'ML engineer',
            'machine learning engineer',
            'MLOps engineer',
        ],
    ];

    /**
     * Labels avec émojis pour l'affichage des badges
     */
    private const BADGE_LABELS = [
        'prompt_engineering' => '🎯 Prompt Engineering',
        'llm_engineering' => '🤖 LLM Engineering',
        'genai' => '⚡ GenAI',
        'ml_engineer' => '🔧 ML Engineer',
    ];

    /**
     * Vérifie si un mot-clé correspond avec word boundaries pour éviter les faux positifs
     * Utilise regex pour les acronymes courts (LLM, AI, ML, NLP, etc.)
     *
     * @param string $text Texte à chercher (en minuscules)
     * @param string $keyword Mot-clé à chercher
     * @return bool
     */
    private static function matchesKeyword(string $text, string $keyword): bool
    {
        $keywordLower = strtolower($keyword);

        // Liste des acronymes courts qui nécessitent word boundaries
        $acronyms = ['llm', 'ai', 'ml', 'nlp', 'gpt', 'genai'];

        // Si c'est un acronyme court, utiliser word boundary
        if (in_array($keywordLower, $acronyms)) {
            // \b = word boundary (début ou fin de mot)
            return preg_match('/\b' . preg_quote($keywordLower, '/') . '\b/i', $text) === 1;
        }

        // Pour les mots composés ou phrases, utiliser str_contains
        return str_contains($text, $keywordLower);
    }

    /**
     * Vérifie si un job est lié à LLM/GenAI/Prompt Engineering
     *
     * @param string $title Titre du job
     * @param string $description Description du job
     * @return bool
     */
    public static function isLLMRelated(string $title, string $description): bool
    {
        $text = strtolower($title . ' ' . $description);

        foreach (self::LLM_KEYWORDS as $keyword) {
            if (self::matchesKeyword($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte les catégories (badges) d'un job
     *
     * @param string $title Titre du job
     * @param string $description Description du job
     * @return array Array de catégories détectées (ex: ['prompt_engineering', 'llm_engineering'])
     */
    public static function detectCategories(string $title, string $description): array
    {
        $text = strtolower($title . ' ' . $description);
        $detectedCategories = [];

        foreach (self::CATEGORIES as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (self::matchesKeyword($text, $keyword)) {
                    $detectedCategories[] = $category;
                    break; // Une seule occurrence suffit pour cette catégorie
                }
            }
        }

        return array_unique($detectedCategories);
    }

    /**
     * Obtient les labels formatés (avec émojis) pour les catégories
     *
     * @param array $categories Array de catégories (ex: ['prompt_engineering', 'llm_engineering'])
     * @return array Array de labels (ex: ['🎯 Prompt Engineering', '🤖 LLM Engineering'])
     */
    public static function getBadgeLabels(array $categories): array
    {
        return array_map(
            fn($category) => self::BADGE_LABELS[$category] ?? $category,
            $categories
        );
    }

    /**
     * Obtient tous les labels de badges disponibles
     *
     * @return array
     */
    public static function getAllBadgeLabels(): array
    {
        return self::BADGE_LABELS;
    }
}
