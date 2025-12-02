<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Scan\ScanConfirmRequest;
use App\Http\Requests\Api\Scan\ScanRequestRequest;
use App\Services\Contracts\ScanServiceContract;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    protected ScanServiceContract $scanService;

    public function __construct(ScanServiceContract $scanService)
    {
        $this->scanService = $scanService;
    }

    public function request(ScanRequestRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->scanService->requestScan($data['ticket_id'], $data['sig']);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'ERROR',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    public function confirm(ScanConfirmRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->scanService->confirmScan(
                $data['scan_session_token'],
                $data['scan_nonce'],
                $data['gate_id'],
                $data['agent_id'],
                $data['action']
            );
            return response()->json($result);
        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code === 0 || !is_int($code)) {
                $code = 400;
            }
            return response()->json([
                'code' => 'ERROR',
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    public function history(Request $request)
    {
        $filters = $request->only([
            'event_id', 'gate_id', 'scanner_id', 'scan_type', 'result',
            'start_date', 'end_date', 'search'
        ]);
        $perPage = $request->query('per_page', 15);

        $scanHistory = $this->scanService->getScanHistory($filters, $perPage);

        return response()->json($scanHistory);
    }
}
