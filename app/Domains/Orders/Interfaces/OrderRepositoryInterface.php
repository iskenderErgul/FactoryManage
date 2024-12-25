<?php

namespace App\Domains\Orders\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface OrderRepositoryInterface
{
    public function index(): JsonResponse ;

    public function store(Request $request): JsonResponse;

    public function update(Request $request, $id): JsonResponse;

    public function destroy($id): JsonResponse;
}
