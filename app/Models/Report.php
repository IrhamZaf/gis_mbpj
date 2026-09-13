<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    public const REPORT_STATUSES = ['draft', 'submitted', 'completed'];

    public const WORKFLOW_STATUSES = [
        'pending_site_visit',
        'site_visit_in_progress',
        'pending_engineer_verification',
        'engineer_verified',
        'engineer_returned',
        'pending_director_approval',
        'approved',
        'director_rejected',
    ];

    protected $fillable = [
        'report_number',
        'file_number',
        'category_id',
        'user_id',
        'unit_id',
        'title',
        'description',
        'status',
        'workflow_status',
        'latitude',
        'longitude',
        'location_name',
        'address',
        'gps_accuracy',
        'vendor_name',
        'gis_data',
        'submitted_at',
        'review_note',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'gis_data'      => 'array',
            'latitude'      => 'decimal:7',
            'longitude'     => 'decimal:7',
            'gps_accuracy'  => 'decimal:2',
            'submitted_at'  => 'datetime',
            'reviewed_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($report) {
            if (empty($report->report_number)) {
                $report->report_number = static::generateCaseNumber($report->category_id);
            }
        });
    }

    public static function generateCaseNumber(?int $categoryId = null): string
    {
        $prefix = 'CS';
        if ($categoryId) {
            $category = ReportCategory::find($categoryId);
            if ($category) {
                $prefix = $category->casePrefix();
            }
        }

        $year = Carbon::now()->format('Y');
        $pattern = $prefix.'-'.$year.'-';
        $last = static::where('report_number', 'like', $pattern.'%')
            ->orderByDesc('report_number')
            ->value('report_number');

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $pattern.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function currentAttachments()
    {
        return $this->hasMany(ReportAttachment::class)->where('is_current', true);
    }

    public function requiredDocumentsProgress(): array
    {
        $category = $this->category;
        if (! $category) {
            return ['total' => 0, 'uploaded' => 0, 'items' => []];
        }

        $types = $category->attachmentTypes;
        $current = $this->attachments()->where('is_current', true)->get()->keyBy('attachment_type_id');
        $items = [];
        $uploaded = 0;

        foreach ($types as $type) {
            $has = $current->has($type->id);
            if ($has) {
                $uploaded++;
            }
            $items[] = [
                'type' => $type,
                'display_name' => $type->pivot->display_name ?? $type->name,
                'uploaded' => $has,
                'attachment' => $has ? $current->get($type->id) : null,
            ];
        }

        return [
            'total' => $types->count(),
            'uploaded' => $uploaded,
            'items' => $items,
            'complete' => $types->count() > 0 && $uploaded >= $types->count(),
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }

    public function siteVisit(): HasOne
    {
        return $this->hasOne(SiteVisit::class);
    }

    public function engineerVerifications(): HasMany
    {
        return $this->hasMany(EngineerVerification::class)->latest();
    }

    public function latestEngineerVerification(): HasOne
    {
        return $this->hasOne(EngineerVerification::class)->latestOfMany();
    }

    public function directorApprovals(): HasMany
    {
        return $this->hasMany(DirectorApproval::class)->latest();
    }

    public function latestDirectorApproval(): HasOne
    {
        return $this->hasOne(DirectorApproval::class)->latestOfMany();
    }

    public function workflowHistories(): HasMany
    {
        return $this->hasMany(WorkflowHistory::class)->orderBy('created_at');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeForEngineerQueue(Builder $query): Builder
    {
        return $query->whereIn('workflow_status', [
            'pending_engineer_verification',
            'engineer_verified',
            'engineer_returned',
            'pending_director_approval',
            'approved',
            'director_rejected',
            'site_visit_in_progress',
            'pending_site_visit',
        ])->where('status', '!=', 'draft');
    }

    public function scopeForTaQueue(Builder $query): Builder
    {
        return $query->whereIn('workflow_status', [
            'pending_site_visit',
            'site_visit_in_progress',
            'engineer_returned',
            'pending_engineer_verification',
            'engineer_verified',
            'pending_director_approval',
            'approved',
            'director_rejected',
        ]);
    }

    public function scopeForDirectorQueue(Builder $query): Builder
    {
        return $query->whereIn('workflow_status', [
            'pending_director_approval',
            'approved',
            'director_rejected',
        ]);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperadmin() || $user->isDirector()) {
            return $query;
        }

        if (! $user->unit_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('unit_id', $user->unit_id);

        if ($user->isEngineer()) {
            $query->forEngineerQueue();
        } elseif ($user->isTa()) {
            $query->forTaQueue();
        } elseif ($user->isSurveyor()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draf',
            'submitted' => 'Dihantar',
            'completed' => 'Selesai',
            default     => ucfirst((string) $this->status),
        };
    }

    public function getWorkflowStatusLabelAttribute(): string
    {
        return match ($this->workflow_status) {
            'pending_site_visit'              => 'Menunggu Lawatan Tapak',
            'site_visit_in_progress'          => 'Lawatan Sedang Dijalankan',
            'pending_engineer_verification'   => 'Menunggu Pengesahan Engineer',
            'engineer_verified'               => 'Disahkan Engineer',
            'engineer_returned'               => 'Dikembalikan Engineer',
            'pending_director_approval'       => 'Menunggu Kelulusan Pengarah',
            'approved'                        => 'Diluluskan',
            'director_rejected'               => 'Ditolak Pengarah',
            default                           => $this->status_label,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        $label = $this->workflow_status ? $this->workflow_status_label : $this->status_label;
        $key = $this->workflow_status ?: $this->status;

        $class = match ($key) {
            'draft'                           => 'warning',
            'submitted', 'pending_site_visit' => 'info',
            'site_visit_in_progress'          => 'primary',
            'pending_engineer_verification'   => 'primary',
            'engineer_verified', 'pending_director_approval' => 'info',
            'engineer_returned', 'director_rejected' => 'danger',
            'approved', 'completed'           => 'success',
            default                           => 'secondary',
        };

        return '<span class="badge bg-label-' . $class . '">' . e($label) . '</span>';
    }

    public function generateFileNumber(): string
    {
        $unitCode = $this->unit?->code ?? 'GEN';
        $year = now()->format('Y');
        $seq = static::whereYear('created_at', $year)
            ->where('unit_id', $this->unit_id)
            ->whereNotNull('file_number')
            ->count() + 1;

        return sprintf('MBSJ/ENG/%s/%s/%03d', $unitCode, $year, $seq);
    }

    public function workflowSteps(): array
    {
        $order = [
            'pending_site_visit',
            'site_visit_in_progress',
            'pending_engineer_verification',
            'engineer_verified',
            'pending_director_approval',
            'approved',
        ];

        $current = $this->workflow_status;
        $currentIdx = $current ? array_search($current, $order, true) : -1;

        if ($current === 'engineer_returned') {
            $currentIdx = array_search('pending_engineer_verification', $order, true);
        }
        if ($current === 'director_rejected') {
            $currentIdx = array_search('pending_director_approval', $order, true);
        }

        $labels = [
            'pending_site_visit'            => 'Surveyor Dihantar',
            'site_visit_in_progress'        => 'Lawatan TA',
            'pending_engineer_verification' => 'Laporan Lawatan',
            'engineer_verified'             => 'Pengesahan Engineer',
            'pending_director_approval'     => 'Kelulusan Pengarah',
            'approved'                      => 'Selesai',
        ];

        $steps = [];
        foreach ($order as $i => $key) {
            $state = 'pending';
            if ($currentIdx > $i) {
                $state = 'done';
            } elseif ($currentIdx === $i) {
                $state = in_array($current, ['engineer_returned', 'director_rejected'], true) ? 'returned' : 'current';
            }
            $steps[] = [
                'key'   => $key,
                'label' => $labels[$key],
                'state' => $state,
            ];
        }

        return $steps;
    }
}
