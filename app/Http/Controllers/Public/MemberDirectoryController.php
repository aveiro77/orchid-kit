<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MemberDirectoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberDirectoryController extends Controller
{
    public function __construct(
        protected MemberDirectoryService $memberDirectoryService
    ) {}

    /**
     * Display a listing of active members with optional filtering.
     */
    public function index(Request $request): View
    {
        $kota = $request->query('kota');
        $nama = $request->query('nama');

        $members = $this->memberDirectoryService->getActiveMembers($kota, $nama);

        return view('public.members.index', compact('members', 'kota', 'nama'));
    }

    /**
     * Display the specified member profile.
     */
    public function show(User $user): View
    {
        abort_unless($user->status_aktif, 404);

        return view('public.members.show', ['member' => $user]);
    }
}
