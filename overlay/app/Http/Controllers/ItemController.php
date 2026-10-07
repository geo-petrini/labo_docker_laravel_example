<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Item::orderBy('id')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $item = Item::create([
            'title' => $request->input('title', ''),
            'note'  => $request->input('note', ''),
        ]);
        return response()->json($item, 201);
    }

    public function show(int $id): JsonResponse
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['error' => 'non trovato'], 404);
        }
        return response()->json($item);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['error' => 'non trovato'], 404);
        }
        $item->update([
            'title' => $request->input('title', ''),
            'note'  => $request->input('note', ''),
        ]);
        return response()->json($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = Item::destroy($id);
        if (!$deleted) {
            return response()->json(['error' => 'non trovato'], 404);
        }
        return response()->json(['ok' => true]);
    }
}