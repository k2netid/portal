<?php

namespace Modules\Forms\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Forms\Exports\FormSubmissionsExport;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormAnalytics;
use Modules\Forms\Models\FormSubmission;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FormSubmissionController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['store']);
        $this->middleware('permission:view forms')->except(['store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ?Form $form = null): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $query = FormSubmission::with(['form', 'user']);

        // Multi-tenancy scoping
        if (! $user->can('manage forms')) {
            $query->whereHas('form', function ($q) use ($user): void {
                $q->where('author_id', $user->id);
            });
        }

        if ($form instanceof Form) {
            $query->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $formIdRaw = $request->input('form_id');
            $formId = is_string($formIdRaw) ? $formIdRaw : 0;
            $query->where('form_id', $formId);
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashed = $request->input('trashed');
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        if ($request->has('status')) {
            $statusRaw = $request->input('status');
            $status = is_string($statusRaw) ? $statusRaw : '';
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? trim($searchRaw) : '';
            if ($search !== '') {
                $pattern = '%'.str_replace(['%', '_'], '', mb_strtolower($search, 'UTF-8')).'%';
                $query->where(function ($q) use ($pattern): void {
                    $q->whereRaw('lower(cast(data as text)) ilike ?', [$pattern])
                        ->orWhere('ip_address', 'ilike', $pattern);
                });
            }
        }

        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sorting logic
        $sortByRaw = $request->input('sort_by', 'created_at');
        $sortBy = is_string($sortByRaw) ? $sortByRaw : 'created_at';
        $sortOrderRaw = $request->input('sort_order', 'desc');
        $sortOrder = is_string($sortOrderRaw) ? $sortOrderRaw : 'desc';
        $sortDirection = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        // Validate sort column to prevent SQL injection or errors
        $allowedSortColumns = ['status', 'created_at', 'ip_address'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest(); // Default to created_at desc
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 100) : 15;
        $submissions = $query->paginate($perPage);

        return $this->paginated($submissions, 'Form submissions retrieved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(FormSubmission $formSubmission): JsonResponse
    {
        $this->checkOwnership($formSubmission);

        return $this->success($formSubmission->load(['form.fields', 'user']), 'Form submission retrieved successfully');
    }

    /**
     * Mark the specified resource as read.
     */
    public function markAsRead(FormSubmission $formSubmission): JsonResponse
    {
        $this->checkOwnership($formSubmission);
        $formSubmission->markAsRead();

        return $this->success([
            'submission' => $formSubmission,
        ], 'Submission marked as read');
    }

    /**
     * Archive the specified resource.
     */
    public function archive(FormSubmission $formSubmission): JsonResponse
    {
        $this->checkOwnership($formSubmission);
        $formSubmission->archive();

        return $this->success([
            'submission' => $formSubmission,
        ], 'Submission archived');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormSubmission $formSubmission): JsonResponse
    {
        $this->checkOwnership($formSubmission);
        $formSubmission->delete();

        return $this->success(null, 'Submission deleted successfully');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id): JsonResponse
    {
        /** @var FormSubmission $submission */
        $submission = FormSubmission::withTrashed()->findOrFail($id);
        $this->checkOwnership($submission);
        $submission->restore();

        return $this->success(null, 'Submission restored successfully');
    }

    /**
     * Permanently remove the specified resource from storage.
     */
    public function forceDelete(string $id): JsonResponse
    {
        /** @var FormSubmission $submission */
        $submission = FormSubmission::withTrashed()->findOrFail($id);
        $this->checkOwnership($submission);
        $submission->forceDelete();

        return $this->success(null, 'Submission permanently deleted');
    }

    /**
     * Export the resource.
     *
     * @return BinaryFileResponse|Response|JsonResponse
     */
    public function export(Request $request, Form $form)
    {
        $query = $form->submissions();

        // Search filter
        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? trim($searchRaw) : '';
            if ($search !== '') {
                $pattern = '%'.str_replace(['%', '_'], '', mb_strtolower($search, 'UTF-8')).'%';
                $query->where(function ($q) use ($pattern): void {
                    $q->whereRaw('lower(cast(data as text)) ilike ?', [$pattern])
                        ->orWhere('ip_address', 'ilike', $pattern);
                });
            }
        }

        // Date range filter
        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Status filter (match index logic)
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Sort logic
        $sortByRaw = $request->input('sort_by', 'created_at');
        $sortBy = is_string($sortByRaw) ? $sortByRaw : 'created_at';
        $sortOrderRaw = $request->input('sort_order', 'desc');
        $sortOrder = is_string($sortOrderRaw) ? $sortOrderRaw : 'desc';
        $sortDirection = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['status', 'created_at', 'ip_address'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        // Collect field keys for headers
        $submissions = (clone $query)->get();
        $fieldKeys = [];
        foreach ($submissions as $submission) {
            /** @var FormSubmission $submission */
            if ($submission->data) {
                $fieldKeys = array_merge($fieldKeys, array_keys($submission->data));
            }
        }
        $fieldKeys = array_values(array_unique($fieldKeys));

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = str_replace(' ', '_', $form->name)."_submissions_{$timestamp}";
        $format = $request->input('format', 'xlsx');

        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        /** @var Builder<FormSubmission> $exportQuery */
        $exportQuery = $query->getQuery();

        if ($format === 'csv') {
            return Excel::download(new FormSubmissionsExport($exportQuery, $fieldKeys), "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        if ($format === 'pdf') {
            $submissions = $exportQuery->get();
            if ($fieldKeys === []) {
                $fieldKeys = collect($submissions)->flatMap(fn ($s) => array_keys($s->data ?? []))->unique()->values()->toArray();
            }

            $html = view('pdf.submissions-list', [
                'form' => $form,
                'submissions' => $submissions,
                'headers' => $fieldKeys,
            ])->render();

            $mpdf = new Mpdf([
                'format' => 'A4-L',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);
            $mpdf->WriteHTML($html);

            return response($mpdf->Output("{$filename}.pdf", 'D'))
                ->header('Content-Type', 'application/pdf');
        }

        return Excel::download(new FormSubmissionsExport($exportQuery, $fieldKeys), "{$filename}.xlsx");
    }

    /**
     * Export the resource as PDF.
     */
    public function exportPdf(FormSubmission $formSubmission): Response
    {
        $this->checkOwnership($formSubmission);
        @ini_set('memory_limit', '512M');
        $formSubmission->load(['form', 'user']);
        // ... rest of method

        $html = view('pdf.submission', [
            'submission' => $formSubmission,
            'form' => $formSubmission->form,
            'data' => $formSubmission->data,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output("submission-{$formSubmission->id}.pdf", 'D'))
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Get statistics.
     *
     * Without date filters: returns all-time totals (Submissions list).
     * With `days`, `date_from`/`date_to`, or `aggregate_field`: adds range-scoped metrics + charts (Analytics page).
     */
    public function statistics(Request $request, ?Form $form = null): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        $query = FormSubmission::query();

        if (! $user->can('manage forms')) {
            $query->whereHas('form', function ($q) use ($user): void {
                $q->where('author_id', $user->id);
            });
        }

        if ($form instanceof Form) {
            $query->where('form_id', $form->id);
        }

        $hasRange = $form && (
            $request->filled('days')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('aggregate_field')
        );

        if (! $hasRange) {
            $stats = [
                'total' => (clone $query)->count(),
                'new' => (clone $query)->where('status', 'new')->count(),
                'read' => (clone $query)->where('status', 'read')->count(),
                'archived' => (clone $query)->where('status', 'archived')->count(),
                'today' => (clone $query)->whereDate('created_at', today())->count(),
            ];

            return $this->success($stats, 'Form submission statistics retrieved successfully');
        }

        /** @var Form $form */
        [$rangeFrom, $rangeTo] = $this->resolveStatisticsDateRange($request);
        $rangeQuery = (clone $query)->whereBetween('created_at', [$rangeFrom, $rangeTo]);

        $currentTotal = (clone $rangeQuery)->count();
        $daysInRange = max(1, $rangeFrom->diffInDays($rangeTo) + 1);
        $prevTo = $rangeFrom->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($daysInRange - 1)->startOfDay();
        $previousTotal = (clone $query)->whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $growth = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : ($currentTotal > 0 ? 100.0 : 0.0);

        $allTimeTotal = (clone $query)->count();

        $dailySubmissionStats = $this->buildDailySubmissionStats(clone $rangeQuery, $rangeFrom, $rangeTo);

        $stats = [
            'total' => $currentTotal,
            'previous_total' => $previousTotal,
            'growth' => $growth,
            'new' => (clone $rangeQuery)->where('status', 'new')->count(),
            'read' => (clone $rangeQuery)->where('status', 'read')->count(),
            'archived' => (clone $rangeQuery)->where('status', 'archived')->count(),
            'all_time_total' => $allTimeTotal,
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'daily_stats' => $dailySubmissionStats,
            'hourly_stats' => $this->buildHourlySubmissionStats(clone $rangeQuery),
            'weekly_stats' => $this->buildWeeklySubmissionStats(clone $rangeQuery),
        ];

        $stats['range_views_total'] = (int) FormAnalytics::query()
            ->where('form_id', $form->id)
            ->whereBetween('date', [$rangeFrom->toDateString(), $rangeTo->toDateString()])
            ->sum('views');
        $stats['range_starts_total'] = (int) FormAnalytics::query()
            ->where('form_id', $form->id)
            ->whereBetween('date', [$rangeFrom->toDateString(), $rangeTo->toDateString()])
            ->sum('starts');
        $stats['daily_views_stats'] = $this->buildDailyFormAnalyticsSeries($form->id, $rangeFrom, $rangeTo, $dailySubmissionStats, 'views');

        $form->loadMissing('fields');
        $stats['chartable_fields'] = $form->fields
            ->filter(static fn ($f): bool => in_array($f->type, ['select', 'radio', 'checkbox', 'multiselect', 'boolean'], true))
            ->map(static fn ($f): array => [
                'id' => $f->id,
                'name' => $f->name,
                'label' => $f->label,
            ])
            ->values()
            ->all();

        $aggregateRaw = $request->input('aggregate_field');
        $aggregateField = is_string($aggregateRaw) ? $aggregateRaw : '';
        $allowedNames = $form->fields->pluck('name')->all();
        if ($aggregateField !== '' && in_array($aggregateField, $allowedNames, true)) {
            $stats['field_distribution'] = $this->buildFieldDistribution(clone $rangeQuery, $aggregateField);
        } else {
            $stats['field_distribution'] = [];
        }

        return $this->success($stats, 'Form submission statistics retrieved successfully');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveStatisticsDateRange(Request $request): array
    {
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateFromInput = $request->input('date_from');
            $dateToInput = $request->input('date_to');
            $from = Carbon::parse(is_string($dateFromInput) ? $dateFromInput : now()->toDateString())->startOfDay();
            $to = Carbon::parse(is_string($dateToInput) ? $dateToInput : now()->toDateString())->endOfDay();
            if ($from->gt($to)) {
                return [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to];
        }

        $daysRaw = $request->input('days', '30');
        $days = is_numeric($daysRaw) ? max(1, min(366, (int) $daysRaw)) : 30;
        $to = now()->endOfDay();
        $from = now()->copy()->subDays($days - 1)->startOfDay();

        return [$from, $to];
    }

    /**
     * @param  Builder<FormSubmission>  $rangeQuery
     * @return array<int, array{period: string, visits: int}>
     */
    private function buildDailySubmissionStats(Builder $rangeQuery, Carbon $from, Carbon $to): array
    {
        $counts = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $counts[$cursor->toDateString()] = 0;
            $cursor->addDay();
        }

        foreach ($rangeQuery->select('created_at')->cursor() as $row) {
            $d = $row->created_at?->toDateString();
            if ($d !== null && array_key_exists($d, $counts)) {
                $counts[$d]++;
            }
        }

        $out = [];
        foreach ($counts as $period => $visits) {
            $out[] = ['period' => $period, 'visits' => $visits];
        }

        return $out;
    }

    /**
     * Align form_analytics daily rows with submission chart periods (same keys as LineChart compare series).
     *
     * @param  array<int, array{period: string, visits: int}>  $dailySubmissionStats
     * @return array<int, array{period: string, visits: int}>
     */
    private function buildDailyFormAnalyticsSeries(string $formId, Carbon $from, Carbon $to, array $dailySubmissionStats, string $metric): array
    {
        $allowed = ['views', 'starts', 'submissions'];
        if (! in_array($metric, $allowed, true)) {
            $metric = 'views';
        }

        $rows = FormAnalytics::query()
            ->where('form_id', $formId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(static function (FormAnalytics $r): string {
                $d = $r->date;

                return $d->format('Y-m-d');
            });

        $out = [];
        foreach ($dailySubmissionStats as $day) {
            $p = $day['period'];
            $row = $rows->get($p);
            $val = $row ? (int) $row->{$metric} : 0;
            $out[] = ['period' => $p, 'visits' => $val];
        }

        return $out;
    }

    /**
     * @param  Builder<FormSubmission>  $rangeQuery
     * @return array<int, array{hour: string, count: int}>
     */
    private function buildHourlySubmissionStats(Builder $rangeQuery): array
    {
        $bins = array_fill(0, 24, 0);
        foreach ($rangeQuery->select('created_at')->cursor() as $row) {
            if ($row->created_at) {
                $h = (int) $row->created_at->format('G');
                $bins[$h]++;
            }
        }

        $out = [];
        for ($h = 0; $h < 24; $h++) {
            $out[] = [
                'hour' => sprintf('%02d:00', $h),
                'count' => $bins[$h],
            ];
        }

        return $out;
    }

    /**
     * @param  Builder<FormSubmission>  $rangeQuery
     * @return array<int, array{day: string, count: int}>
     */
    private function buildWeeklySubmissionStats(Builder $rangeQuery): array
    {
        $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $bins = array_fill(0, 7, 0);
        foreach ($rangeQuery->select('created_at')->cursor() as $row) {
            if ($row->created_at) {
                $w = (int) $row->created_at->format('w');
                $bins[$w]++;
            }
        }

        $out = [];
        for ($i = 0; $i < 7; $i++) {
            $out[] = ['day' => $labels[$i], 'count' => $bins[$i]];
        }

        return $out;
    }

    /**
     * @param  Builder<FormSubmission>  $rangeQuery
     * @return array<int, array{label: string, count: int}>
     */
    private function buildFieldDistribution(Builder $rangeQuery, string $fieldName): array
    {
        $buckets = [];
        foreach ($rangeQuery->select('data')->cursor() as $row) {
            $data = $row->data;
            $raw = $data[$fieldName] ?? null;
            $parts = $this->distributionBucketKeys($raw);
            foreach ($parts as $p) {
                $buckets[$p] = ($buckets[$p] ?? 0) + 1;
            }
        }

        arsort($buckets);
        $out = [];
        $limit = 0;
        foreach ($buckets as $label => $count) {
            $out[] = ['label' => (string) $label, 'count' => $count];
            $limit++;
            if ($limit >= 20) {
                break;
            }
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    private function distributionBucketKeys(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return ['(empty)'];
        }
        if (is_bool($raw)) {
            return [$raw ? 'true' : 'false'];
        }
        if (is_array($raw)) {
            if ($raw === []) {
                return ['(empty)'];
            }
            $keys = [];
            foreach ($raw as $item) {
                if (is_scalar($item)) {
                    $keys[] = (string) $item;
                } else {
                    $encoded = json_encode($item);
                    $keys[] = is_string($encoded) ? $encoded : '(invalid)';
                }
            }

            return $keys;
        }

        return [is_scalar($raw) ? (string) $raw : '(invalid)'];
    }

    /**
     * Check if the authenticated user owns the form for this submission.
     *
     * @throws AuthorizationException
     */
    protected function checkOwnership(FormSubmission $submission): void
    {
        $user = auth()->user();

        // Admins with 'manage forms' can see everything
        if ($user && $user->can('manage forms')) {
            return;
        }

        // Check if submission belongs to a form owned by the user
        if (! $user || $submission->form->author_id !== $user->id) {
            abort(403, 'Unauthorized access to form submission.');
        }
    }
}
