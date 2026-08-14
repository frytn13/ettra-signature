<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan audit trail sistem khusus Owner.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $action = (string) $request->query('action', '');
        $module = (string) $request->query('module', '');
        $dateFrom = $this->normalizeDate((string) $request->query('date_from', ''));
        $dateTo = $this->normalizeDate((string) $request->query('date_to', ''));

        if (! array_key_exists($action, ActivityLog::actionOptions())) {
            $action = '';
        }

        if (! array_key_exists($module, ActivityLog::moduleOptions())) {
            $module = '';
        }

        $baseQuery = ActivityLog::query();

        $statistics = [
            'total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'authentication' => (clone $baseQuery)->authentication()->count(),
            'user_management' => (clone $baseQuery)->userManagement()->count(),
        ];

        $logs = ActivityLog::query()
            ->with('user:id,name,email,role,deleted_at')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($action !== '', fn (Builder $query) => $query->where('action', $action))
            ->when($module !== '', fn (Builder $query) => $query->where('module', $module))
            ->when($dateFrom !== '', fn (Builder $query) => $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay()))
            ->when($dateTo !== '', fn (Builder $query) => $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay()))
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'statistics' => $statistics,
            'actionOptions' => ActivityLog::actionOptions(),
            'moduleOptions' => ActivityLog::moduleOptions(),
            'filters' => [
                'search' => $search,
                'action' => $action,
                'module' => $module,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Memastikan filter tanggal hanya menerima format YYYY-MM-DD yang valid.
     */
    private function normalizeDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }
}
