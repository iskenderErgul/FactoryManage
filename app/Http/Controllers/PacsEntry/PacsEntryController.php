<?php

namespace App\Http\Controllers\PacsEntry;

use App\Domains\PacsEntry\Repositories\PacsEntryRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacsEntryController extends Controller
{
    protected PacsEntryRepository $pacsEntryRepository;

    public function __construct(PacsEntryRepository $pacsEntryRepository)
    {
        $this->pacsEntryRepository = $pacsEntryRepository;
    }

    /**
     * Tüm PACS girişlerini döner.
     *
     * @return JsonResponse
     */
    public function getAllPacsEntries(): JsonResponse
    {
        return $this->pacsEntryRepository->getAllPacsEntries();
    }

    /**
     * Tüm PACS giriş loglarını döner.
     *
     * @return JsonResponse
     */
    public function getAllPacsEntriesLogs(): JsonResponse
    {
        return $this->pacsEntryRepository->getAllPacsEntriesLogs();
    }

    /**
     * Yeni bir PACS girişi oluşturur.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPacsEntry(Request $request): JsonResponse
    {
     return $this->pacsEntryRepository->createPacsEntry($request);
    }
}
