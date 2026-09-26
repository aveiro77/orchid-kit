<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessDirectoryController extends Controller
{
    /**
     * Display a listing of active businesses with optional filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $categoryId = $request->query('kategori');
        $status = $request->query('status', 'aktif');

        $categories = BusinessCategory::orderBy('nama', 'asc')->get();

        $query = Business::with(['user', 'category'])
            ->whereHas('user', fn ($q) => $q->where('status_aktif', true));

        if ($status && in_array($status, ['aktif', 'non_aktif'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('nama_usaha', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('business_category_id', $categoryId);
        }

        $businesses = $query->orderBy('nama_usaha', 'asc')->paginate(12)->withQueryString();

        return view('public.businesses.index', compact('businesses', 'categories', 'search', 'categoryId', 'status'));
    }

    /**
     * Display the specified active business detail.
     */
    public function show(Business $business): View
    {
        abort_unless($business->status === 'aktif' && $business->user?->status_aktif, 404);

        $business->load(['user', 'category']);

        return view('public.businesses.show', compact('business'));
    }
}
