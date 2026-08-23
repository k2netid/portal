<?php

namespace Modules\Search\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Library\Dto\TaxonomySearchSnapshot;
use Modules\Publishing\Dto\SearchableContentSnapshot;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Support\SqlLikeEscape;
use Modules\Search\Contracts\SearchIndexerInterface;
use Modules\Search\Models\SearchIndex;
use Modules\Search\Models\SearchQuery;

class SearchService
{
    public function __construct(
        protected SearchIndexerInterface $searchIndexer,
    ) {}

    /**
     * Search the index
     *
     * @param  array<string, mixed>  $filters
     * @return array{results: Collection<int, array<string, mixed>>, total: int, query: string, suggestions: array<int, array{text: string, type: string, url?: string|null}>, is_loose?: bool}
     */
    public function search(string $query, array $filters = [], int $limit = 20): array
    {
        if (in_array(trim($query), ['', '0'], true)) {
            return [
                'results' => collect(),
                'total' => 0,
                'query' => (string) $query,
                'suggestions' => [],
            ];
        }

        $searchQuery = SearchIndex::query();
        $isLoose = false;
        $suggestions = [];

        // 1. Try Strict Search (AND)
        $this->applySearchLogic($searchQuery, $query, true);
        $this->applyFilters($searchQuery, $filters);

        $results = $searchQuery->orderByDesc('relevance_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // 2. Fallback to Loose Search (OR) if no results
        if ($results->isEmpty()) {
            $isLoose = true;
            $searchQuery = SearchIndex::query();
            $this->applySearchLogic($searchQuery, $query, false);
            $this->applyFilters($searchQuery, $filters);

            $results = $searchQuery->orderByDesc('relevance_score')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            // 2.5 Smart Levenshtein similarity fallback for search results (Database-independent)
            if ($results->isEmpty()) {
                $allIndexQuery = SearchIndex::query();
                $this->applyFilters($allIndexQuery, $filters);
                $allIndexes = $allIndexQuery->get();

                $scored = [];
                $queryLower = mb_strtolower($query, 'UTF-8');

                foreach ($allIndexes as $index) {
                    $titleLower = mb_strtolower($index->title, 'UTF-8');
                    $contentLower = mb_strtolower($index->content ?? '', 'UTF-8');

                    // Title distance
                    $distTitle = levenshtein($titleLower, $queryLower);
                    $maxLenTitle = max(strlen($titleLower), strlen($queryLower));
                    $simTitle = $maxLenTitle > 0 ? (1 - $distTitle / $maxLenTitle) : 0;

                    // Boost score if title or content has substring match
                    if (str_contains($titleLower, $queryLower) || str_contains($contentLower, $queryLower)) {
                        $simTitle = max($simTitle, 0.6);
                    }

                    if ($simTitle >= 0.4) {
                        $scored[] = [
                            'index' => $index,
                            'score' => $simTitle,
                        ];
                    }
                }

                usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
                $results = collect(array_column(array_slice($scored, 0, $limit), 'index'));
            }

            // 3. If still empty, get suggestions
            if ($results->isEmpty()) {
                $suggestions = $this->getSuggestions($query, 5, $filters);
            }
        }

        // Log search query
        SearchQuery::log($query, $results->count(), $filters);

        return [
            'results' => $results->map(function ($index) {
                /** @var SearchIndex $index */
                /** @var array<string, mixed> $mapped */
                $mapped = [
                    'id' => $index->id,
                    'type' => (string) $index->type,
                    'title' => (string) $index->title,
                    'excerpt' => is_scalar($index->excerpt) ? (string) $index->excerpt : null,
                    'url' => is_scalar($index->url) ? (string) $index->url : null,
                    'searchable_type' => (string) $index->searchable_type,
                    'searchable_id' => (string) $index->searchable_id,
                    'relevance_score' => $index->getAttribute('relevance_score'),
                ];

                return $mapped;
            }),
            'total' => $results->count(),
            'query' => (string) $query,
            'is_loose' => $isLoose,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @param  Builder<SearchIndex>  $queryBuilder
     */
    protected function applySearchLogic(Builder $queryBuilder, string $query, bool $strict = true): void
    {
        $queryTrim = trim($query);
        $cleanQuery = preg_replace('/[^a-zA-Z0-9]/', '', $queryTrim);
        $isUuid = false;
        $uuidQuery = $queryTrim;
        if (is_string($cleanQuery) && preg_match('/^[0-9a-fA-F]{32}$/', $cleanQuery)) {
            $isUuid = true;
            $uuidQuery = sprintf(
                '%s-%s-%s-%s-%s',
                substr($cleanQuery, 0, 8),
                substr($cleanQuery, 8, 4),
                substr($cleanQuery, 12, 4),
                substr($cleanQuery, 16, 4),
                substr($cleanQuery, 20, 12)
            );
        }

        if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
            $prepared = $this->prepareSearchQuery($query, $strict);
            if ($prepared !== '' && $prepared !== '0') {
                $queryBuilder->where(function ($q) use ($prepared, $isUuid, $uuidQuery): void {
                    $q->whereRaw(
                        'MATCH(title, content) AGAINST(? IN BOOLEAN MODE)',
                        [$prepared]
                    );
                    if ($isUuid) {
                        $q->orWhere('searchable_id', $uuidQuery);
                    }
                });
            } elseif ($isUuid) {
                $queryBuilder->where('searchable_id', $uuidQuery);
            }
        } else {
            // Fallback for SQLite/PostgreSQL (use ILIKE for PostgreSQL case-insensitivity)
            $driver = config('database.default');
            $operator = $driver === 'pgsql' ? 'ILIKE' : 'like';

            // For PostgreSQL, if it's not strict (loose search), apply fuzzy wildcard
            if ($driver === 'pgsql' && ! $strict) {
                $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $query);
                $chars = str_split(is_string($normalized) ? $normalized : '');
                $fuzzyQuery = '%'.implode('%', $chars).'%';

                $queryBuilder->where(function ($q) use ($fuzzyQuery, $operator, $isUuid, $uuidQuery): void {
                    $q->where('title', $operator, $fuzzyQuery)
                        ->orWhere('content', $operator, $fuzzyQuery);
                    if ($isUuid) {
                        $q->orWhere('searchable_id', $uuidQuery);
                    }
                });

                return;
            }

            $terms = explode(' ', $query);
            $sqlOperator = $operator === 'ILIKE' ? 'ilike' : 'like';
            $queryBuilder->where(function ($q) use ($terms, $strict, $operator, $sqlOperator, $isUuid, $uuidQuery): void {
                $q->where(function ($inner) use ($terms, $strict, $operator, $sqlOperator): void {
                    foreach ($terms as $term) {
                        // Gather synonyms and stemmed base for each search term
                        $stemsAndSynonyms = $this->getSynonyms($term);
                        $stemmed = $this->stemIndonesian($term);
                        if (! in_array($stemmed, $stemsAndSynonyms, true)) {
                            $stemsAndSynonyms[] = $stemmed;
                        }

                        if ($strict) {
                            $inner->where(function ($sub) use ($stemsAndSynonyms, $operator, $sqlOperator): void {
                                foreach ($stemsAndSynonyms as $syn) {
                                    $pat = $operator === 'ILIKE'
                                        ? SqlLikeEscape::contains($syn)
                                        : SqlLikeEscape::contains(mb_strtolower($syn, 'UTF-8'));
                                    $sub->orWhere(function ($match) use ($pat, $sqlOperator): void {
                                        $match->where('title', $sqlOperator, $pat)
                                            ->orWhere('content', $sqlOperator, $pat);
                                    });
                                }
                            });
                        } else {
                            foreach ($stemsAndSynonyms as $syn) {
                                $pat = $operator === 'ILIKE'
                                    ? SqlLikeEscape::contains($syn)
                                    : SqlLikeEscape::contains(mb_strtolower($syn, 'UTF-8'));
                                $inner->orWhere(function ($match) use ($pat, $sqlOperator): void {
                                    $match->where('title', $sqlOperator, $pat)
                                        ->orWhere('content', $sqlOperator, $pat);
                                });
                            }
                        }
                    }
                });
                if ($isUuid) {
                    $q->orWhere('searchable_id', $uuidQuery);
                }
            });
        }
    }

    /**
     * @param  Builder<SearchIndex>  $queryBuilder
     * @param  array<string, mixed>  $filters
     */
    protected function applyFilters(Builder $queryBuilder, array $filters): void
    {
        if (isset($filters['types']) && is_array($filters['types'])) {
            $types = array_values(array_filter($filters['types'], fn ($type): bool => is_string($type) && $type !== ''));
            if ($types !== []) {
                $queryBuilder->whereIn('type', $types);
            }
        } elseif (isset($filters['type']) && is_string($filters['type'])) {
            $queryBuilder->where('type', $filters['type']);
        }
        if (isset($filters['date_from']) && is_string($filters['date_from'])) {
            $queryBuilder->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to']) && is_string($filters['date_to'])) {
            $queryBuilder->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Search by specific type
     *
     * @return array{results: Collection<int, array<string, mixed>>, total: int, query: string, suggestions: array<int, array{text: string, type: string}>, is_loose?: bool}
     */
    public function searchByType(string $query, string $type, int $limit = 20): array
    {
        return $this->search((string) $query, ['type' => $type], $limit);
    }

    /**
     * Get search suggestions
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array{text: string, type: string, url: string|null}>
     */
    public function getSuggestions(string $query, int $limit = 5, array $filters = []): array
    {
        $queryClean = trim($query);
        if (in_array($queryClean, ['', '0'], true)) {
            return [];
        }

        // Fetch user's personal recent search history matching the prefix
        $historySuggestions = [];
        $userId = Auth::id();
        $ip = IpHelper::getClientIp(request());

        $historyQuery = SearchQuery::query();
        if ($userId) {
            $historyQuery->where('user_id', $userId);
        } else {
            $historyQuery->where('ip_address', $ip);
        }

        $driver = config('database.default');
        $historyQueryTerm = SqlLikeEscape::contains(
            $driver === 'pgsql' ? $queryClean : mb_strtolower($queryClean, 'UTF-8')
        );
        if ($driver === 'pgsql') {
            $historyQuery->where('query', 'ilike', $historyQueryTerm);
        } else {
            $historyQuery->whereRaw('LOWER(query) LIKE ?', [$historyQueryTerm]);
        }

        $historyLogs = $historyQuery->select('query')
            ->distinct()
            ->limit(3)
            ->get();

        foreach ($historyLogs as $log) {
            $queryText = is_scalar($log->getAttribute('query')) ? (string) $log->getAttribute('query') : '';
            $historySuggestions[] = [
                'text' => $queryText,
                'type' => 'history',
                'url' => '/ja-dash/search?q='.urlencode($queryText),
            ];
        }
        $cleanQuery = preg_replace('/[^a-zA-Z0-9]/', '', $queryClean);
        $isUuid = false;
        $uuidQuery = $queryClean;
        if (is_string($cleanQuery) && preg_match('/^[0-9a-fA-F]{32}$/', $cleanQuery)) {
            $isUuid = true;
            $uuidQuery = sprintf(
                '%s-%s-%s-%s-%s',
                substr($cleanQuery, 0, 8),
                substr($cleanQuery, 8, 4),
                substr($cleanQuery, 12, 4),
                substr($cleanQuery, 16, 4),
                substr($cleanQuery, 20, 12)
            );
        }
        $driver = config('database.default');

        // 1. Primary: Case-Insensitive search (ILIKE for PostgreSQL, LIKE for MySQL)
        if ($driver === 'pgsql') {
            // PostgreSQL: Use ILIKE (native case-insensitive)
            $suggestionQuery = SearchIndex::query();
            $pat = SqlLikeEscape::contains($queryClean);
            $suggestionQuery->where(function ($q) use ($pat, $isUuid, $uuidQuery): void {
                $q->where('title', 'ilike', $pat)
                    ->orWhere('content', 'ilike', $pat);
                if ($isUuid) {
                    $q->orWhere('searchable_id', $uuidQuery);
                }
            });
            $this->applyFilters($suggestionQuery, $filters);
            $suggestions = $suggestionQuery
                ->select('title', 'type', 'url')
                ->distinct()
                ->limit($limit)
                ->get();
        } else {
            // MySQL/MariaDB: Use LOWER + LIKE
            $queryLower = mb_strtolower($queryClean, 'UTF-8');
            $pat = SqlLikeEscape::contains($queryLower);
            $suggestionQuery = SearchIndex::query();
            $suggestionQuery->where(function ($q) use ($pat, $isUuid, $uuidQuery): void {
                $q->whereRaw('LOWER(title) LIKE ?', [$pat])
                    ->orWhereRaw('LOWER(content) LIKE ?', [$pat]);
                if ($isUuid) {
                    $q->orWhere('searchable_id', $uuidQuery);
                }
            });
            $this->applyFilters($suggestionQuery, $filters);
            $suggestions = $suggestionQuery
                ->select('title', 'type', 'url')
                ->distinct()
                ->limit($limit)
                ->get();
        }

        // 2. Fuzzy/Typo Tolerance fallback (Aggressive Fuzzy Wildcard)
        if ($suggestions->isEmpty()) {
            if ($driver === 'pgsql') {
                // PostgreSQL: Ultra-loose matching (e.g., "lorm" -> "%l%o%r%m%")
                $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $queryClean);
                $chars = str_split(is_string($normalized) ? $normalized : '');
                $fuzzyQuery = '%'.implode('%', $chars).'%';

                if (count($chars) >= 2) {
                    $suggestionQuery = SearchIndex::query();
                    $suggestionQuery->where(function ($q) use ($fuzzyQuery): void {
                        $q->where('title', 'ILIKE', $fuzzyQuery)
                            ->orWhere('content', 'ILIKE', $fuzzyQuery);
                    });
                    $this->applyFilters($suggestionQuery, $filters);
                    $suggestions = $suggestionQuery
                        ->select('title', 'type', 'url')
                        ->limit($limit)
                        ->get();
                }
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                // MySQL / MariaDB: SOUNDEX (tidak tersedia di SQLite testing)
                $suggestionQuery = SearchIndex::query();
                $suggestionQuery->whereRaw('SOUNDEX(title) = SOUNDEX(?)', [$queryClean]);
                $this->applyFilters($suggestionQuery, $filters);
                $suggestions = $suggestionQuery
                    ->select('title', 'type', 'url')
                    ->distinct()
                    ->limit($limit)
                    ->get();
            } else {
                // SQLite & driver lain: fuzzy wildcard tanpa SOUNDEX
                $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $queryClean);
                $chars = str_split(is_string($normalized) ? $normalized : '');
                if (count($chars) >= 2) {
                    $fuzzyQuery = '%'.implode('%', $chars).'%';
                    $suggestionQuery = SearchIndex::query();
                    $suggestionQuery->where(function ($q) use ($fuzzyQuery): void {
                        $q->where('title', 'like', $fuzzyQuery)
                            ->orWhere('content', 'like', $fuzzyQuery);
                    });
                    $this->applyFilters($suggestionQuery, $filters);
                    $suggestions = $suggestionQuery
                        ->select('title', 'type', 'url')
                        ->distinct()
                        ->limit($limit)
                        ->get();
                }
            }
        }

        // 2.5 Smart Levenshtein similarity fallback (Database-independent)
        if ($suggestions->isEmpty()) {
            $allTitlesQuery = SearchIndex::query();
            $this->applyFilters($allTitlesQuery, $filters);
            $allTitles = $allTitlesQuery->select('title', 'type', 'url')
                ->distinct()
                ->get();

            $scored = [];
            $queryLower = mb_strtolower($queryClean, 'UTF-8');

            foreach ($allTitles as $item) {
                $titleLower = mb_strtolower($item->title, 'UTF-8');

                // Levenshtein distance
                $dist = levenshtein($titleLower, $queryLower);
                $maxLen = max(strlen($titleLower), strlen($queryLower));
                $similarity = $maxLen > 0 ? (1 - $dist / $maxLen) : 0;

                // Boost for substring match
                if (str_contains($titleLower, $queryLower) || str_contains($queryLower, $titleLower)) {
                    $similarity = max($similarity, 0.7);
                }

                if ($similarity >= 0.4) {
                    $scored[] = [
                        'item' => $item,
                        'score' => $similarity,
                    ];
                }
            }

            usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
            $suggestions = collect(array_column(array_slice($scored, 0, $limit), 'item'));
        }

        /** @var array<int, array{text: string, type: string, url: string|null}> $result */
        $result = $suggestions->map(function ($index): array {
            /** @var SearchIndex $index */
            return [
                'text' => (string) $index->title,
                'type' => (string) $index->type,
                'url' => is_scalar($index->url) ? (string) $index->url : null,
            ];
        })->toArray();

        // Merge personal search history at the very top and deduplicate names
        $merged = array_merge($historySuggestions, $result);
        $unique = [];
        $seen = [];
        foreach ($merged as $item) {
            $key = mb_strtolower($item['text'], 'UTF-8');
            if (! in_array($key, $seen, true)) {
                $seen[] = $key;
                $unique[] = $item;
            }
        }

        return array_slice($unique, 0, $limit);
    }

    /**
     * Prepare query for MySQL FullText
     */
    protected function prepareSearchQuery(string $query, bool $strict = true): string
    {
        // Prepare query for MySQL FULLTEXT search
        $terms = explode(' ', trim($query));
        $prepared = [];

        foreach ($terms as $term) {
            $term = trim($term);
            if (strlen($term) >= 2) {
                // Gather synonyms and stemmed base
                $stemsAndSynonyms = $this->getSynonyms($term);
                $stemmed = $this->stemIndonesian($term);
                if (! in_array($stemmed, $stemsAndSynonyms, true)) {
                    $stemsAndSynonyms[] = $stemmed;
                }

                $groupedTerms = [];
                foreach ($stemsAndSynonyms as $syn) {
                    $groupedTerms[] = "{$syn}*";
                }

                $prefix = $strict ? '+' : '';
                $prepared[] = "{$prefix}(".implode(' ', $groupedTerms).')';
            }
        }

        return implode(' ', $prepared);
    }

    public function syncPublishing(SearchableContentSnapshot $snapshot): void
    {
        $this->searchIndexer->syncPublishing($snapshot);
    }

    public function syncTaxonomy(TaxonomySearchSnapshot $snapshot): void
    {
        $this->searchIndexer->syncTaxonomy($snapshot);
    }

    /**
     * Dynamically crawls and indexes all frontend navigation menu items as system pages
     */
    public function indexSystemPages(): int
    {
        $modulesPath = base_path('../frontend/src/modules');

        if (! is_dir($modulesPath)) {
            $modulesPath = dirname(base_path()).'/frontend/src/modules';
        }

        $files = $this->findFrontendNavigationFiles($modulesPath);
        if ($files === []) {
            return 0;
        }

        $indexedCount = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (! $content) {
                continue;
            }

            // Extract the array block: everything between first [ and last ]
            $startPos = strpos($content, '[');
            $endPos = strrpos($content, ']');
            if ($startPos === false || $endPos === false) {
                continue;
            }

            $arrayStr = substr($content, $startPos, $endPos - $startPos + 1);

            // Extract objects that have both "to:" and "label:" fields
            preg_match_all('/\{\s*([^{}]+)\s*\}/s', $arrayStr, $matches);

            if (empty($matches[1])) {
                continue;
            }

            foreach ($matches[1] as $objStr) {
                // Parse properties inside the object string using clean regexes
                preg_match('/to\s*:\s*[\'"]([^\'"]+)[\'"]/', $objStr, $toMatch);
                preg_match('/label\s*:\s*[\'"]([^\'"]+)[\'"]/', $objStr, $labelMatch);
                preg_match('/permission\s*:\s*[\'"]([^\'"]+)[\'"]/', $objStr, $permMatch);
                preg_match('/name\s*:\s*[\'"]([^\'"]+)[\'"]/', $objStr, $nameMatch);

                if (! empty($toMatch[1]) && ! empty($labelMatch[1])) {
                    $url = $toMatch[1];
                    $label = $labelMatch[1];
                    $permission = $permMatch[1] ?? null;
                    $name = $nameMatch[1] ?? $label;

                    // Normalize frontend /dash/... to backend admin /ja/dash/... prefix
                    $adminUrl = str_starts_with($url, '/dash') ? '/ja'.$url : $url;

                    // Generate a stable, deterministic UUID from the URL path to ensure PostgreSQL UUID compatibility
                    $hash = md5($url);
                    $uuid = sprintf(
                        '%s-%s-%s-%s-%s',
                        substr($hash, 0, 8),
                        substr($hash, 8, 4),
                        substr($hash, 12, 4),
                        substr($hash, 16, 4),
                        substr($hash, 20, 12)
                    );

                    // Build a comprehensive keywords content string
                    $keywords = [
                        'System Page', 'Admin Settings', 'Menu', 'Navigation',
                        $label, $name, $permission,
                    ];

                    // Split the label into words
                    $labelWords = explode(' ', $label);
                    foreach ($labelWords as $word) {
                        $wordClean = trim((string) preg_replace('/[^a-zA-Z0-9]/', '', $word));
                        if (strlen($wordClean) >= 2) {
                            $keywords[] = $wordClean;
                        }
                    }

                    // Special custom keywords based on paths
                    if (str_contains($url, 'security') || str_contains($url, 'journal')) {
                        $keywords[] = 'shield';
                        $keywords[] = 'botshield';
                        $keywords[] = 'bot-shield';
                        $keywords[] = 'firewall';
                        $keywords[] = 'integrity';
                        $keywords[] = 'vulnerabilities';
                    }
                    if (str_contains($url, 'backup')) {
                        $keywords[] = 'restore';
                        $keywords[] = 'recovery';
                    }
                    if (str_contains($url, 'redis') || str_contains($url, 'settings')) {
                        $keywords[] = 'cache';
                        $keywords[] = 'clear';
                        $keywords[] = 'warming';
                    }

                    $contentKeywords = implode(', ', array_unique($keywords));

                    SearchIndex::updateOrCreate(
                        [
                            'searchable_type' => 'SystemPage',
                            'searchable_id' => $uuid,
                        ],
                        [
                            'title' => $label,
                            'content' => $contentKeywords,
                            'excerpt' => "Access the {$label} administrative panel and settings.",
                            'url' => $adminUrl,
                            'type' => 'page',
                            'meta' => [
                                'permission' => $permission,
                                'frontend_route' => $url,
                            ],
                            'relevance_score' => 500, // High ranking relevance for admin shortcuts
                        ]
                    );

                    $indexedCount++;
                }
            }
        }

        return $indexedCount;
    }

    /**
     * Reindex all searchable items
     *
     * @return array<string, int>
     */
    public function reindexAll(): array
    {
        $stats = $this->searchIndexer->reindexAll();
        $stats['system_pages'] = $this->indexSystemPages();

        return $stats;
    }

    /**
     * Get synonyms for a given search term
     *
     * @return array<int, string>
     */
    protected function getSynonyms(string $term): array
    {
        $termLower = mb_strtolower($term, 'UTF-8');

        $synonymGroups = [
            ['keamanan', 'security', 'firewall', 'shield', 'botshield', 'bot-shield', 'proteksi', 'integrity'],
            ['artikel', 'konten', 'post', 'tulisan', 'berita', 'Jejakawan', 'studio', 'naskah'],
            ['kategori', 'category', 'rubrik', 'klasifikasi', 'grup'],
            ['user', 'pengguna', 'admin', 'anggota', 'akun', 'profil', 'staff'],
            ['log', 'journal', 'catatan', 'audit', 'riwayat', 'logs', 'journals', 'aktivitas'],
            ['backup', 'restore', 'cadangan', 'pulihkan', 'arsip', 'recovery'],
            ['cache', 'warming', 'bersihkan', 'clear', 'speed', 'performa', 'optimasi'],
            ['analisis', 'analytics', 'statistik', 'laporan', 'traffic', 'pengunjung', 'visitor'],
        ];

        $results = [$term];

        foreach ($synonymGroups as $group) {
            if (in_array($termLower, $group, true)) {
                $results = array_merge($results, $group);
                break;
            }
        }

        return array_values(array_unique($results));
    }

    /**
     * Stem common Indonesian suffixes from a search term
     */
    protected function stemIndonesian(string $term): string
    {
        $termLower = mb_strtolower($term, 'UTF-8');

        // Only stem words with a length greater than 4 characters to avoid over-stemming
        if (mb_strlen($termLower, 'UTF-8') <= 4) {
            return $term;
        }

        // 1. Strip possessive pronouns: -nya, -mu, -ku
        if (str_ends_with($termLower, 'nya')) {
            $termLower = substr($termLower, 0, -3);
        } elseif (str_ends_with($termLower, 'mu') || str_ends_with($termLower, 'ku')) {
            $termLower = substr($termLower, 0, -2);
        }

        // Re-check length
        if (mb_strlen($termLower, 'UTF-8') <= 4) {
            return $termLower;
        }

        // 2. Strip common suffixes: -kan, -an, -i
        if (str_ends_with($termLower, 'kan')) {
            $termLower = substr($termLower, 0, -3);
        } elseif (str_ends_with($termLower, 'an')) {
            $termLower = substr($termLower, 0, -2);
        } elseif (str_ends_with($termLower, 'i')) {
            $termLower = substr($termLower, 0, -1);
        }

        return $termLower;
    }

    /**
     * @return array<int, string>
     */
    protected function findFrontendNavigationFiles(string $modulesPath): array
    {
        if (! is_dir($modulesPath)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modulesPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo) {
                continue;
            }
            if ($file->isFile() && $file->getFilename() === 'navigation.ts') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
