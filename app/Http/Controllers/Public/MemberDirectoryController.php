<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalRole;
use App\Models\Skill;
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
        $skillId = $request->filled('skill') ? (int) $request->query('skill') : null;
        $roleId = $request->filled('peran') ? (int) $request->query('peran') : null;

        $members = $this->memberDirectoryService->getActiveMembers($kota, $nama, $skillId, $roleId);

        $skills = Skill::orderBy('nama', 'asc')->get();
        $professionalRoles = ProfessionalRole::orderBy('nama', 'asc')->get();

        return view('public.members.index', compact(
            'members',
            'kota',
            'nama',
            'skillId',
            'roleId',
            'skills',
            'professionalRoles'
        ));
    }

    /**
     * Display the specified member profile.
     */
    public function show(User $user): View
    {
        abort_unless($user->status_aktif, 404);

        $user->load(['skills', 'professionalRoles', 'businesses']);

        return view('public.members.show', ['member' => $user]);
    }
}
