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
    public function getAllPacsEntries(): JsonResponse
    {
        return $this->pacsEntryRepository->getAllPacsEntries();
    }

    public function getAllPacsEntriesLogs(): JsonResponse
    {
        return $this->pacsEntryRepository->getAllPacsEntriesLogs();
    }

    public function createPacsEntry(Request $request): JsonResponse
    {
     return $this->pacsEntryRepository->createPacsEntry($request);
    }
}
