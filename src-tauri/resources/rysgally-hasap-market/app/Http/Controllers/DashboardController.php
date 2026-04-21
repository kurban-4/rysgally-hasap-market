<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Till;
use App\Models\Shift;
use App\Models\Expense;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $expenses      = Expense::whereDate('created_at', now())->latest()->get();
        $totalExpenses = $expenses->sum('amount');

        $tills = Till::all()->map(function ($till) {
            $dayRev   = $till->sales()->whereDate('created_at', now())->sum('total_price');
            $weekRev  = $till->sales()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_price');
            $monthRev = $till->sales()->whereMonth('created_at', now()->month)->sum('total_price');
            $totalRev = $till->sales()->sum('total_price');

            return [
                'id'             => $till->id,
                'name'           => $till->name,
                'day_rev'        => $dayRev,
                'week_rev'       => $weekRev,
                'month_rev'      => $monthRev,
                'all_time_rev'   => $totalRev,
                'filtered_total' => $dayRev,
                'shift'          => '08:00 AM',
            ];
        });

        $dayEarned   = $tills->sum('day_rev');
        $weekEarned  = $tills->sum('week_rev');
        $monthEarned = $tills->sum('month_rev');
        $totalEarned = $tills->sum('all_time_rev');
        $netProfit   = $monthEarned - $totalExpenses;

        return view('admin.dashboard', compact(
            'tills', 'expenses', 'dayEarned', 'weekEarned',
            'monthEarned', 'totalEarned', 'totalExpenses', 'netProfit'
        ));
    }

    public function shiftLogs()
    {
        $shifts = Shift::with('user')
                       ->orderBy('opened_at', 'desc')
                       ->paginate(20);

        $totalRevenue = Shift::where('status', 'closed')->sum('total_revenue');
        $activeCount  = Shift::where('status', 'active')->count();

        return view('admin.shift', compact('shifts', 'totalRevenue', 'activeCount'));
    }

    public function expensesIndex()
    {
        $expenses      = Expense::latest()->paginate(15);
        $totalExpenses = Expense::sum('amount');

        return view('admin.expenses.index', compact('expenses', 'totalExpenses'));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'title'  => 'required|string',
            'amount' => 'required|numeric',
        ]);

        Expense::create([
            'title'        => $request->title,
            'amount'       => $request->amount,
            'expense_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Расход успешно записан!');
    }

    public function showTill($id)
    {
        $till = Till::with(['sales.product'])->findOrFail($id);

        $from = request('from') ? Carbon::parse(request('from'))->startOfDay() : null;
        $to   = request('to')   ? Carbon::parse(request('to'))->endOfDay()     : null;

        $dayEarned   = $till->sales->filter(fn($s) => $s->created_at >= Carbon::today())->sum('total_price');
        $weekEarned  = $till->sales->filter(fn($s) => $s->created_at >= Carbon::now()->startOfWeek())->sum('total_price');
        $monthEarned = $till->sales->filter(fn($s) => $s->created_at >= Carbon::now()->startOfMonth())->sum('total_price');
        $totalEarned = $till->sales->sum('total_price');

        $filteredSales = $till->sales;
        if ($from) $filteredSales = $filteredSales->filter(fn($s) => $s->created_at >= $from);
        if ($to)   $filteredSales = $filteredSales->filter(fn($s) => $s->created_at <= $to);

        $filteredTotal = $filteredSales->sum('total_price');

        $soldproducts = $filteredSales->groupBy('product_id')->map(function ($sales) use ($till) {
            $allSales = $till->sales->where('product_id', $sales->first()->product_id);
            return [
                'name'      => $sales->first()->product->name,
                'quantity'  => $sales->sum('quantity'),
                'total'     => $sales->sum('total_price'),
                'day_qty'   => $allSales->filter(fn($s) => $s->created_at >= Carbon::today())->sum('quantity'),
                'week_qty'  => $allSales->filter(fn($s) => $s->created_at >= Carbon::now()->startOfWeek())->sum('quantity'),
                'month_qty' => $allSales->filter(fn($s) => $s->created_at >= Carbon::now()->startOfMonth())->sum('quantity'),
            ];
        })->values();

        return view('admin.till_detail', compact(
            'till', 'dayEarned', 'weekEarned', 'monthEarned',
            'totalEarned', 'filteredTotal', 'soldproducts'
        ));
    }

    public function totalRevenue(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : null;
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()     : null;

        $dayEarned   = \App\Models\Sale::whereDate('created_at', Carbon::today())->sum('total_price');
        $weekEarned  = \App\Models\Sale::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total_price');
        $monthEarned = \App\Models\Sale::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->sum('total_price');
        $totalEarned = \App\Models\Sale::sum('total_price');

        $query = \App\Models\Sale::query();
        if ($from) $query->where('created_at', '>=', $from);
        if ($to)   $query->where('created_at', '<=', $to);
        $filteredTotal = $query->sum('total_price');

        $tills = Till::all()->map(function ($till) use ($from, $to) {
            $salesQuery = $till->sales();
            $dayRev     = (clone $salesQuery)->whereDate('created_at', Carbon::today())->sum('total_price');
            $weekRev    = (clone $salesQuery)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total_price');
            $monthRev   = (clone $salesQuery)->whereMonth('created_at', Carbon::now()->month)->sum('total_price');

            $filteredQuery = (clone $salesQuery);
            if ($from) $filteredQuery->where('created_at', '>=', $from);
            if ($to)   $filteredQuery->where('created_at', '<=', $to);

            return [
                'id'             => $till->id,
                'name'           => $till->name,
                'day_rev'        => $dayRev,
                'week_rev'       => $weekRev,
                'month_rev'      => $monthRev,
                'all_time_rev'   => $salesQuery->sum('total_price'),
                'filtered_total' => $filteredQuery->sum('total_price'),
            ];
        });

        return view('admin.revenue', compact(
            'dayEarned', 'weekEarned', 'monthEarned', 'totalEarned',
            'filteredTotal', 'tills'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT: Revenue by Till  →  revenue_YYYY-MM-DD.xlsx
    // Respects the same from/to filters as totalRevenue()
    // ─────────────────────────────────────────────────────────────────────────
    public function exportRevenue(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : null;
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()     : null;

        $tills = Till::all()->map(function ($till) use ($from, $to) {
            $q = $till->sales();

            $filteredQ = clone $q;
            if ($from) $filteredQ->where('created_at', '>=', $from);
            if ($to)   $filteredQ->where('created_at', '<=', $to);

            return [
                'name'           => $till->name,
                'day_rev'        => (clone $q)->whereDate('created_at', Carbon::today())->sum('total_price'),
                'week_rev'       => (clone $q)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total_price'),
                'month_rev'      => (clone $q)->whereMonth('created_at', Carbon::now()->month)->sum('total_price'),
                'all_time_rev'   => $q->sum('total_price'),
                'filtered_total' => $filteredQ->sum('total_price'),
            ];
        });

        $periodLabel = ($from && $to)
            ? $from->format('d.m.Y') . ' – ' . $to->format('d.m.Y')
            : 'All time';

        $headers = ['Till', 'Today (TMT)', 'This Week (TMT)', 'This Month (TMT)', 'All Time (TMT)', "Period: {$periodLabel} (TMT)"];
        $rows    = [];

        foreach ($tills as $t) {
            $rows[] = [
                $t['name'],
                number_format($t['day_rev'],        2, '.', ''),
                number_format($t['week_rev'],       2, '.', ''),
                number_format($t['month_rev'],      2, '.', ''),
                number_format($t['all_time_rev'],   2, '.', ''),
                number_format($t['filtered_total'], 2, '.', ''),
            ];
        }

        // Totals row
        $rows[] = [];
        $rows[] = [
            'TOTAL',
            number_format($tills->sum('day_rev'),        2, '.', ''),
            number_format($tills->sum('week_rev'),       2, '.', ''),
            number_format($tills->sum('month_rev'),      2, '.', ''),
            number_format($tills->sum('all_time_rev'),   2, '.', ''),
            number_format($tills->sum('filtered_total'), 2, '.', ''),
        ];

        $fileName = 'revenue_' . date('Y-m-d') . '.xlsx';
        $binary   = WholesaleController::buildXlsx($headers, $rows);

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($binary),
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT: Shift Logs  →  shifts_YYYY-MM-DD.xlsx
    // ─────────────────────────────────────────────────────────────────────────
    public function exportShifts()
    {
        $shifts = Shift::with('user')->orderBy('opened_at', 'desc')->get();

        $headers = ['Employee', 'Email', 'Shift Start', 'Shift End', 'Duration', 'Revenue (TMT)', 'Status'];
        $rows    = [];

        foreach ($shifts as $shift) {
            if ($shift->closed_at) {
                $h        = $shift->opened_at->diffInHours($shift->closed_at);
                $m        = $shift->opened_at->diffInMinutes($shift->closed_at) % 60;
                $duration = "{$h}h {$m}m";
                $end      = $shift->closed_at->format('d.m.Y H:i');
            } else {
                $h        = $shift->opened_at->diffInHours(now());
                $m        = $shift->opened_at->diffInMinutes(now()) % 60;
                $duration = "{$h}h {$m}m (active)";
                $end      = '—';
            }

            $rows[] = [
                $shift->user->name  ?? '—',
                $shift->user->email ?? '—',
                $shift->opened_at->format('d.m.Y H:i'),
                $end,
                $duration,
                number_format($shift->total_revenue, 2, '.', ''),
                ucfirst($shift->status),
            ];
        }

        $rows[] = [];
        $rows[] = ['', '', '', '', 'TOTAL', number_format($shifts->where('status', 'closed')->sum('total_revenue'), 2, '.', ''), ''];

        $fileName = 'shifts_' . date('Y-m-d') . '.xlsx';
        $binary   = WholesaleController::buildXlsx($headers, $rows);

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($binary),
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT: Single Till Detail  →  till_{name}_YYYY-MM-DD.xlsx
    // Respects same from/to filters as showTill()
    // ─────────────────────────────────────────────────────────────────────────
    public function exportTill($id)
    {
        $till = Till::with(['sales.product'])->findOrFail($id);

        $from = request('from') ? Carbon::parse(request('from'))->startOfDay() : null;
        $to   = request('to')   ? Carbon::parse(request('to'))->endOfDay()     : null;

        $filteredSales = $till->sales;
        if ($from) $filteredSales = $filteredSales->filter(fn($s) => $s->created_at >= $from);
        if ($to)   $filteredSales = $filteredSales->filter(fn($s) => $s->created_at <= $to);

        $soldproducts = $filteredSales->groupBy('product_id')->map(function ($sales) use ($till) {
            $allSales = $till->sales->where('product_id', $sales->first()->product_id);
            return [
                'name'      => $sales->first()->product->name ?? '—',
                'qty'       => $sales->sum('quantity'),
                'total'     => $sales->sum('total_price'),
                'day_qty'   => $allSales->filter(fn($s) => $s->created_at >= Carbon::today())->sum('quantity'),
                'week_qty'  => $allSales->filter(fn($s) => $s->created_at >= Carbon::now()->startOfWeek())->sum('quantity'),
                'month_qty' => $allSales->filter(fn($s) => $s->created_at >= Carbon::now()->startOfMonth())->sum('quantity'),
            ];
        })->values();

        $headers = ['product', 'Period Qty', 'Period Total (TMT)', 'Today Qty', 'This Week Qty', 'This Month Qty'];
        $rows    = [];

        foreach ($soldproducts as $med) {
            $rows[] = [
                $med['name'],
                $med['qty'],
                number_format($med['total'],     2, '.', ''),
                $med['day_qty'],
                $med['week_qty'],
                $med['month_qty'],
            ];
        }

        $rows[] = [];
        $rows[] = ['TOTAL', $soldproducts->sum('qty'), number_format($soldproducts->sum('total'), 2, '.', ''), '', '', ''];

        $safeName = preg_replace('/[^a-z0-9]/i', '_', $till->name);
        $fileName = "till_{$safeName}_" . date('Y-m-d') . '.xlsx';
        $binary   = WholesaleController::buildXlsx($headers, $rows);

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($binary),
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT: Expenses  →  expenses_YYYY-MM-DD.xlsx
    // ─────────────────────────────────────────────────────────────────────────
    public function exportExpenses()
    {
        $expenses = Expense::latest()->get();

        $headers = ['Date', 'Time', 'Description', 'Amount (TMT)'];
        $rows    = [];

        foreach ($expenses as $ex) {
            $rows[] = [
                $ex->created_at->format('d.m.Y'),
                $ex->created_at->format('H:i'),
                $ex->title,
                number_format($ex->amount, 2, '.', ''),
            ];
        }

        $rows[] = [];
        $rows[] = ['', '', 'TOTAL', number_format($expenses->sum('amount'), 2, '.', '')];

        $fileName = 'expenses_' . date('Y-m-d') . '.xlsx';
        $binary   = WholesaleController::buildXlsx($headers, $rows);

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($binary),
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}