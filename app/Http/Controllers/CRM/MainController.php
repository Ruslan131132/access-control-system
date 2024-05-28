<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Services\CRMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MainController extends Controller
{
    public function index(CRMService $crmService)
    {
        $logs = Log::query()
            ->orderByDesc('created_at')
            ->whereDate('created_at', Carbon::now())
            ->get();

        $qrGenerations = $crmService->getQrActivationsInterval();
        $qrActivations = $crmService->getQrActivationsInterval(true);

        return view('main', compact('logs', 'qrActivations', 'qrGenerations'));
    }

    public function logs(Request $request)
    {
        $logs = Log::query()
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->created_from);
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->created_to);
            })
            ->when($request->filled('text'), function ($query) use ($request) {
                $query->where('text', 'like', '%'.$request->text.'%');
            })
            ->orderByDesc('created_at')
            ->get();

        return view('logs', compact('logs'));
    }
}
