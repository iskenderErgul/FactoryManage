<?php

namespace App\Domains\PacsEntry\Repositories;

use App\Common\Services\LoggerService;
use App\Domains\PacsEntry\Interfaces\PacsEntryRepositoryInterface;
use App\Domains\PacsEntry\Models\PacsEntriesLog;
use App\Domains\PacsEntry\Models\PacsEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacsEntryRepository implements PacsEntryRepositoryInterface
{
    protected LoggerService $loggerService;

    public function __construct( LoggerService $loggerService)
    {
        $this->loggerService = $loggerService;
    }

    /**
     * Tüm PACS girişlerini alır.
     *
     * @return JsonResponse
     */
    public function getAllPacsEntries(): JsonResponse
    {

        $pacsEntries = PacsEntry::with('user')->get();
        return response()->json($pacsEntries);
    }

    /**
     * Tüm PACS giriş loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllPacsEntriesLogs(): JsonResponse
    {

        $pacsEntriesLogs = PacsEntriesLog::with('user')->get();

        return response()->json($pacsEntriesLogs);
    }

    /**
     * Yeni bir PACS girişi oluşturur.
     *
     * @param Request $request  Giriş bilgilerini içeren HTTP isteği
     * @return JsonResponse
     */
    public function createPacsEntry(Request $request): JsonResponse
    {

    $lastEntry = PacsEntry::where('user_id', $request->user_id)
        ->orderBy('created_at', 'desc')
        ->first();

    if ($request->entry_type === 'checkin') {
        if ($lastEntry && $lastEntry->entry_type === 'checkin') {
            return response()->json(['message' => 'Zaten en son bu işlemi gerçekleştirdiniz.'], 400);
        }
    }
    if ($request->entry_type === 'checkout') {
        if (!$lastEntry || $lastEntry->entry_type === 'checkout') {
            return response()->json(['message' => 'Kullanıcı çıkış yapmadan önce giriş yapmalıdır!'], 400);
        }
    }

        $pacsEntry = PacsEntry::create([

                'user_id' => $request->user_id,
                'entry_type' => $request->entry_type,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->loggerService->createPacsEntryLog($pacsEntry->id, $request->entry_type);

        return response()->json(['message' => 'Entry işlem başarılı!', 'data' => $pacsEntry], 201);
    }

}
