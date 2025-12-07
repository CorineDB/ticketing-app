<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Scan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get sales report data
     */
    public function getSalesReport(Request $request)
    {
        $query = Payment::query();

        // Apply date filters if provided
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Get total tickets sold
        $totalTicketsSold = Ticket::whereHas('payment', function ($q) use ($request) {
            if ($request->has('start_date')) {
                $q->where('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $q->where('created_at', '<=', $request->end_date);
            }
        })->count();

        // Get total revenue
        $totalRevenue = $query->where('status', 'completed')
            ->sum('amount');

        // Get average ticket price
        $averageTicketPrice = $totalTicketsSold > 0
            ? $totalRevenue / $totalTicketsSold
            : 0;

        // Get sales by status
        $salesByStatus = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Get top selling events
        $topEvents = Ticket::select('event_id', DB::raw('count(*) as tickets_sold'))
            ->whereHas('payment', function ($q) use ($request) {
                $q->where('status', 'completed');
                if ($request->has('start_date')) {
                    $q->where('created_at', '>=', $request->start_date);
                }
                if ($request->has('end_date')) {
                    $q->where('created_at', '<=', $request->end_date);
                }
            })
            ->with('event:id,title')
            ->groupBy('event_id')
            ->orderByDesc('tickets_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'totalTicketsSold' => $totalTicketsSold,
                'totalRevenue' => $totalRevenue,
                'averageTicketPrice' => round($averageTicketPrice, 2),
                'salesByStatus' => $salesByStatus,
                'topEvents' => $topEvents
            ]
        ]);
    }

    /**
     * Get scan activity report data
     */
    public function getScanActivityReport(Request $request)
    {
        $query = Scan::query();

        // Apply date filters if provided
        if ($request->has('start_date')) {
            $query->where('scan_time', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('scan_time', '<=', $request->end_date);
        }

        // Get total scans
        $totalScans = $query->count();

        // Get successful scans (result = 'ok')
        $successfulScans = (clone $query)->where('result', 'ok')->count();

        // Get failed scans (result != 'ok')
        $failedScans = $totalScans - $successfulScans;

        // Get success rate
        $successRate = $totalScans > 0
            ? round(($successfulScans / $totalScans) * 100, 2)
            : 0;

        // Get scans by type
        $scansByType = (clone $query)->select('scan_type', DB::raw('count(*) as count'))
            ->groupBy('scan_type')
            ->get()
            ->pluck('count', 'scan_type');

        // Get scans by result
        $scansByResult = (clone $query)->select('result', DB::raw('count(*) as count'))
            ->groupBy('result')
            ->get()
            ->pluck('count', 'result');

        // Get hourly scan distribution
        $hourlyScans = (clone $query)
            ->select(DB::raw('HOUR(scan_time) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Get top scanners (agents)
        $topScanners = (clone $query)
            ->select('scanned_by', DB::raw('count(*) as scan_count'))
            ->whereNotNull('scanned_by')
            ->with('scanner:id,name')
            ->groupBy('scanned_by')
            ->orderByDesc('scan_count')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'totalScans' => $totalScans,
                'successfulScans' => $successfulScans,
                'failedScans' => $failedScans,
                'successRate' => $successRate,
                'scansByType' => $scansByType,
                'scansByResult' => $scansByResult,
                'hourlyDistribution' => $hourlyScans,
                'topScanners' => $topScanners
            ]
        ]);
    }

    /**
     * Get comprehensive analytics
     */
    public function getAnalytics(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30));
        $endDate = $request->input('end_date', now());

        // Get sales data
        $salesData = $this->getSalesReport($request)->getData()->data;

        // Get scan data
        $scanData = $this->getScanActivityReport($request)->getData()->data;

        // Get daily trends
        $dailyTrends = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as orders'),
                DB::raw('sum(amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => [
                'sales' => $salesData,
                'scans' => $scanData,
                'dailyTrends' => $dailyTrends,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Export report as CSV
     */
    public function exportReport(Request $request, string $type)
    {
        // TODO: Implement CSV export functionality
        // This would generate a CSV file based on the report type

        return response()->json([
            'message' => 'Export functionality coming soon',
            'type' => $type
        ]);
    }
}
