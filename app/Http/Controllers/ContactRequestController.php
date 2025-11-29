<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    /**
     * Tüm iletişim isteklerini döner.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactRequest::query();

        // Filtreleme
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Sayfalama
        $perPage = $request->get('per_page', 15);
        $contactRequests = $query->paginate($perPage);

        return response()->json($contactRequests);
    }

    /**
     * Yeni bir iletişim isteği kaydeder (Public API).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'type' => 'nullable|string|in:contact,quote',
            'product' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
        ]);

        $contactRequest = ContactRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'] ?? null,
            'type' => $validated['type'] ?? 'contact',
            'product' => $validated['product'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'İletişim isteğiniz başarıyla gönderildi.',
            'data' => $contactRequest
        ], 201);
    }

    /**
     * Belirli bir iletişim isteğini gösterir.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $contactRequest = ContactRequest::findOrFail($id);
        return response()->json($contactRequest);
    }

    /**
     * İletişim isteğinin durumunu günceller.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,read,replied,closed',
        ]);

        $contactRequest = ContactRequest::findOrFail($id);
        $contactRequest->update($validated);

        return response()->json([
            'message' => 'İletişim isteği güncellendi.',
            'data' => $contactRequest
        ]);
    }

    /**
     * İletişim isteğini siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $contactRequest = ContactRequest::findOrFail($id);
        $contactRequest->delete();

        return response()->json([
            'message' => 'İletişim isteği silindi.'
        ]);
    }

    /**
     * Seçili iletişim isteklerini siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:contact_requests,id',
        ]);

        ContactRequest::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => 'Seçili iletişim istekleri silindi.'
        ]);
    }
}

