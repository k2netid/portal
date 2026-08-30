<?php

namespace Modules\Publishing\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Publishing\Models\Content;

class SeoService
{
    /**
     * Analyze the SEO performance of a model.
     * The model should ideally have attributes like title, meta_title, meta_description, etc.
     *
     * @return array{score: int, max_score: int, percentage: float, grade: string, issues: array<int, string>, suggestions: array<int, string>}
     */
    public function analyze(Model $model): array
    {
        $score = 0;
        $maxScore = 100;
        /** @var array<int, string> $issues */
        $issues = [];
        /** @var array<int, string> $suggestions */
        $suggestions = [];

        // 1. Check Title (Common field)
        $title = $model->getAttribute('title') ?? $model->getAttribute('name');
        if ($title) {
            $titleLength = mb_strlen(is_scalar($title) ? (string) $title : '');
            if ($titleLength >= 30 && $titleLength <= 60) {
                $score += 20;
            } else {
                $issues[] = 'Title length should be between 30-60 characters';
                $suggestions[] = 'Optimize title length for better SEO';
            }
        } else {
            $issues[] = 'Title/Name is missing';
        }

        // 2. Check Meta Title
        $metaTitle = $model->getAttribute('meta_title');
        if ($metaTitle) {
            $metaTitleLength = mb_strlen(is_scalar($metaTitle) ? (string) $metaTitle : '');
            if ($metaTitleLength >= 30 && $metaTitleLength <= 60) {
                $score += 15;
            } else {
                $issues[] = 'Meta title length should be between 30-60 characters';
            }
        } else {
            $suggestions[] = 'Add meta title for better SEO search results';
        }

        // 3. Check Meta Description
        $metaDesc = $model->getAttribute('meta_description') ?? $model->getAttribute('description');
        if ($metaDesc) {
            $metaDescLength = mb_strlen(strip_tags(is_scalar($metaDesc) ? (string) $metaDesc : ''));
            if ($metaDescLength >= 120 && $metaDescLength <= 160) {
                $score += 15;
            } else {
                $issues[] = 'Meta description length should be between 120-160 characters';
            }
        } else {
            $suggestions[] = 'Add meta description for better search snippets';
        }

        // 4. Check Body/Content Length
        $body = $model->getAttribute('body') ?? $model->getAttribute('content');
        if ($body) {
            $bodyLength = mb_strlen(strip_tags(is_scalar($body) ? (string) $body : ''));
            if ($bodyLength >= 300) {
                $score += 15;
            } else {
                $issues[] = 'Main content body is too short (minimum 300 characters recommended)';
            }
        }

        // 5. Check Featured Image
        $image = $model->getAttribute('featured_image') ?? $model->getAttribute('image') ?? $model->getAttribute('logo');
        if ($image) {
            $score += 15;
        } else {
            $suggestions[] = 'Add a featured image or logo for better social sharing';
        }

        // 6. Check Keywords
        $keywords = $model->getAttribute('meta_keywords') ?? $model->getAttribute('keywords');
        if ($keywords) {
            $keywordList = is_string($keywords) ? explode(',', $keywords) : (is_array($keywords) ? $keywords : []);
            if (count($keywordList) >= 3) {
                $score += 10;
            } else {
                $suggestions[] = 'Add at least 3 relevant keywords';
            }
        }

        // 7. Check Slug
        $slug = $model->getAttribute('slug');
        if ($slug) {
            $slugLength = mb_strlen(is_scalar($slug) ? (string) $slug : '');
            if ($slugLength <= 100) {
                $score += 10;
            } else {
                $issues[] = 'URL slug is too long';
            }
        }

        // Finalize score
        $score = min($score, $maxScore);

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100, 2),
            'grade' => $this->calculateGrade($score, $maxScore),
            'issues' => $issues,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Generate JSON-LD schema for a model.
     *
     * @param  string  $type  Default schema type
     * @return array<string, mixed>
     */
    public function generateSchema(Model $model, string $type = 'Article'): array
    {
        // Ensure relationships are loaded for Content model if it's a Jejakawan content
        if ($model instanceof Content) {
            $model->loadMissing(['category', 'author']);
        }

        $publishedAt = $model->getAttribute('published_at') ?? $model->getAttribute('created_at');
        $updatedAt = $model->getAttribute('updated_at');

        $headline = $model->getAttribute('title') ?? $model->getAttribute('name');
        $description = $model->getAttribute('meta_description') ?? $model->getAttribute('description');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'headline' => is_scalar($headline) ? (string) $headline : '',
            'description' => is_scalar($description) ? (string) $description : '',
            'datePublished' => ($publishedAt instanceof Carbon) ? $publishedAt->toIso8601String() : null,
            'dateModified' => ($updatedAt instanceof Carbon) ? $updatedAt->toIso8601String() : null,
        ];

        // Author handle
        if ($model->relationLoaded('author') || $model->getAttribute('author_id')) {
            $author = $model->getAttribute('author');
            if ($author instanceof Model) {
                $authorName = $author->getAttribute('name') ?? 'Unknown';
                $schema['author'] = [
                    '@type' => 'Person',
                    'name' => is_scalar($authorName) ? (string) $authorName : 'Unknown',
                ];
            }
        }

        // Image handle
        $image = $model->getAttribute('featured_image') ?? $model->getAttribute('image') ?? $model->getAttribute('logo');
        if ($image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => url(is_scalar($image) ? (string) $image : ''),
            ];
        }

        // Category handle
        $category = $model->getAttribute('category');
        if ($category instanceof Model) {
            $catName = $category->getAttribute('name');
            $schema['articleSection'] = is_scalar($catName) ? (string) $catName : '';
        }

        return $schema;
    }

    /**
     * Calculate grade based on score.
     */
    protected function calculateGrade(int $score, int $maxScore): string
    {
        $percentage = ($score / $maxScore) * 100;

        if ($percentage >= 90) {
            return 'A+';
        }
        if ($percentage >= 80) {
            return 'A';
        }
        if ($percentage >= 70) {
            return 'B';
        }
        if ($percentage >= 60) {
            return 'C';
        }
        if ($percentage >= 50) {
            return 'D';
        }

        return 'F';
    }
}
