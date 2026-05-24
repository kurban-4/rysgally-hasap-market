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
        $expenses      = Expense::whereDate('created_at', now())->latest()->limit(100)->get();
        $totalExpenses = $expenses->sum('amount');

        // Use database aggregation instead of N+1 queries
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $tills = Till::with(['sales' => function($q) use ($today, $weekStart, $monthStart) {
            $q->select('id', 'till_id', 'total_price', 'product_id', 'quantity', 'created_at')
              ->where(function($query) use ($monthStart) {
                  $query->where('created_at', '>=', $monthStart);
              });
        }, 'sales.product' => function($q) {
            $q->select('id', 'received_price');
        }])->get();

        $tilsData = $tills->map(function ($till) use ($today, $weekStart, $monthStart) {
            $sales = $till->sales;
            
            $dayRev   = $sales->where('created_at', '>=', $today)->sum('total_price');
            $weekRev  = $sales->where('created_at', '>=', $weekStart)->sum('total_price');
            $monthRev = $sales->where('created_at', '>=', $monthStart)->sum('total_price');
            $totalRev = $sales->sum('total_price');

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

        $dayEarned   = $tilsData->sum('day_rev');
        $weekEarned  = $tilsData->sum('week_rev');
        $monthEarned = $tilsData->sum('month_rev');
        $totalEarned = $tilsData->sum('all_time_rev');
        $netProfit   = $monthEarned - $totalExpenses;

        // Efficient aggregation queries
        $allSales = \App\Models\Sale::where('created_at', '>=', $monthStart)
                        ->with('product:id,received_price')
                        ->limit(10000)
                        ->get();
        
        $totalReceivedPrice = 0;
        $totalSellingPrice = $allSales->sum('total_price');
        
        foreach ($allSales as $sale) {
            if ($sale->product && $sale->product->received_price) {
                $totalReceivedPrice += $sale->product->received_price * $sale->quantity;
            }
        }
        
        $totalNetProfit = $totalSellingPrice - $totalReceivedPrice;
        $totalProfitMargin = $totalReceivedPrice > 0 ? ($totalNetProfit / $totalReceivedPrice) * 100 : 0;

        $tills = $tilsData;
        
        return view('admin.dashboard', compact(
            'tills', 'expenses', 'dayEarned', 'weekEarned',
            'monthEarned', 'totalEarned', 'totalExpenses', 'netProfit',
            'totalReceivedPrice', 'totalSellingPrice', 'totalNetProfit', 'totalProfitMargin'
        ));
    }
        
        $totalNetProfit = $totalSellingPrice - $totalReceivedPrice;
        $totalProfitMargin = $totalReceivedPrice > 0 ? ($totalNetProfit / $totalReceivedPrice) * 100 : 0;

        return view('admin.dashboard', compact(
            'tills', 'expenses', 'dayEarned', 'weekEarned',
            'monthEarned', 'totalEarned', 'totalExpenses', 'netProfit',
            'totalReceivedPrice', 'totalSellingPrice', 'totalNetProfit', 'totalProfitMargin'
        ));
    }
public function shiftLogs()
{
    $shifts = Shift::with(['user', 'till'])
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

    public function destroyExpense($id)
    {
        Expense::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Расход успешно удален!');
    }

    public function showTill($id)
    {
        $till = Till::findOrFail($id);

        $from = request('from') ? Carbon::parse(request('from'))->startOfDay() : null;
        $to   = request('to')   ? Carbon::parse(request('to'))->endOfDay()     : null;

        // Use database queries instead of loading all sales
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $dayEarned   = \App\Models\Sale::where('till_id', $id)->where('created_at', '>=', $today)->sum('total_price');
        $weekEarned  = \App\Models\Sale::where('till_id', $id)->where('created_at', '>=', $weekStart)->sum('total_price');
        $monthEarned = \App\Models\Sale::where('till_id', $id)->where('created_at', '>=', $monthStart)->sum('total_price');
        $totalEarned = \App\Models\Sale::where('till_id', $id)->sum('total_price');

        // Limit paginated sales display
        $salesQuery = \App\Models\Sale::where('till_id', $id)->with('product:id,name,received_price');
        if ($from) $salesQuery->where('created_at', '>=', $from);
        if ($to)   $salesQuery->where('created_at', '<=', $to);
        
        $filteredSales = $salesQuery->orderBy('created_at', 'desc')->paginate(50);
        $salesQueryForSum = \App\Models\Sale::where('till_id', $id);
        if ($from) $salesQueryForSum->where('created_at', '>=', $from);
        if ($to)   $salesQueryForSum->where('created_at', '<=', $to);
        $filteredTotal = $salesQueryForSum->sum('total_price');


        // Calculate aggregates
        $totalReceivedPrice = 0;
        $totalSellingPrice = $filteredSales->sum('total_price');
        
        foreach ($filteredSales as $sale) {
            if ($sale->product && $sale->product->received_price) {
                $totalReceivedPrice += $sale->product->received_price * $sale->quantity;
            }
        }
        
        $netProfit = $totalSellingPrice - $totalReceivedPrice;
        $profitMargin = $totalReceivedPrice > 0 ? ($netProfit / $totalReceivedPrice) * 100 : 0;

        return view('admin.till_detail', compact(
            'till', 'dayEarned', 'weekEarned', 'monthEarned',
            'totalEarned', 'filteredTotal', 'filteredSales',
            'totalReceivedPrice', 'totalSellingPrice', 'netProfit', 'profitMargin'
        ));
    }
    }

    public function totalRevenue(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : null;
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()     : null;

        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $dayEarned   = \App\Models\Sale::where('created_at', '>=', $today)->sum('total_price');
        $weekEarned  = \App\Models\Sale::where('created_at', '>=', $weekStart)->sum('total_price');
        $monthEarned = \App\Models\Sale::where('created_at', '>=', $monthStart)->sum('total_price');
        $totalEarned = \App\Models\Sale::sum('total_price');

        $query = \App\Models\Sale::query();
        if ($from) $query->where('created_at', '>=', $from);
        if ($to)   $query->where('created_at', '<=', $to);
        $filteredTotal = $query->sum('total_price');

        // Efficient till query with eager loading
        $tills = Till::with(['sales' => function($q) use ($from, $to) {
            $q->select('id', 'till_id', 'total_price', 'product_id', 'quantity', 'created_at');
            if ($from) $q->where('created_at', '>=', $from);
            if ($to) $q->where('created_at', '<=', $to);
        }])->get()->map(function ($till) use ($today, $weekStart, $monthStart) {
            $sales = $till->sales;
            
            return [
                'id'             => $till->id,
                'name'           => $till->name,
                'day_rev'        => $sales->where('created_at', '>=', $today)->sum('total_price'),
                'week_rev'       => $sales->where('created_at', '>=', $weekStart)->sum('total_price'),
                'month_rev'      => $sales->where('created_at', '>=', $monthStart)->sum('total_price'),
                'all_time_rev'   => $sales->sum('total_price'),
                'filtered_total' => $sales->sum('total_price'),
            ];
        });

        // Limit data to avoid memory bloat
        $salesQuery = \App\Models\Sale::with('product:id,received_price');
        if ($from) $salesQuery->where('created_at', '>=', $from);
        if ($to)   $salesQuery->where('created_at', '<=', $to);
        $filteredSales = $salesQuery->limit(5000)->get();
        
        $totalReceivedPrice = 0;
        $totalSellingPrice = $filteredSales->sum('total_price');
        
        foreach ($filteredSales as $sale) {
            if ($sale->product && $sale->product->received_price) {
                $totalReceivedPrice += $sale->product->received_price * $sale->quantity;
            }
        }
        
        $netProfit = $totalSellingPrice - $totalReceivedPrice;
        $profitMargin = $totalReceivedPrice > 0 ? ($netProfit / $totalReceivedPrice) * 100 : 0;

        return view('admin.revenue', compact(
            'dayEarned', 'weekEarned', 'monthEarned', 'totalEarned',
            'filteredTotal', 'tills', 'totalReceivedPrice', 'totalSellingPrice', 
            'netProfit', 'profitMargin'
        ));
    }

    
    
    
    
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

    
    
    
    public function exportShifts()
{
    $shifts = Shift::with(['user', 'till'])->orderBy('opened_at', 'desc')->get();

    $headers = ['Employee', 'Email', 'Till', 'Shift Start', 'Shift End', 'Duration', 'Revenue (TMT)', 'Status'];
    $rows    = [];

    foreach ($shifts as $shift) {
        if ($shift->closed_at) {
            $totalMinutes = (int) $shift->opened_at->diffInMinutes($shift->closed_at);
            $h        = floor($totalMinutes / 60);
            $m        = $totalMinutes % 60;
            $duration = "{$h}h {$m}m";
            $end      = $shift->closed_at->format('d.m.Y H:i');
        } else {
            $totalMinutes = (int) $shift->opened_at->diffInMinutes(now());
            $h        = floor($totalMinutes / 60);
            $m        = $totalMinutes % 60;
            $duration = "{$h}h {$m}m (active)";
            $end      = '—';
        }

        $rows[] = [
            $shift->user->name   ?? '—',
            $shift->user->email  ?? '—',
            $shift->till->name   ?? '—',
            $shift->opened_at->format('d.m.Y H:i'),
            $end,
            $duration,
            number_format($shift->total_revenue, 2, '.', ''),
            ucfirst($shift->status),
        ];
    }

    $rows[] = [];
    $rows[] = ['', '', '', '', '', 'TOTAL', number_format($shifts->where('status', 'closed')->sum('total_revenue'), 2, '.', ''), ''];

    $fileName = 'shifts_' . date('Y-m-d') . '.xlsx';
    $binary   = WholesaleController::buildXlsx($headers, $rows);

    return response($binary, 200, [
        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Content-Length'      => strlen($binary),
        'Cache-Control'       => 'max-age=0',
    ]);
}

    
    
    
    
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