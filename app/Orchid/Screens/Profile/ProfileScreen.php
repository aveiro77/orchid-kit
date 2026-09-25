<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Profile;

use App\Models\ProfessionalRole;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProfileScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        return [
            'user' => User::where('id', Auth::id())->with(['professionalRoles', 'skills'])->firstOrFail(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Profil Saya';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Kelola informasi profil pribadi Anda.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Simpan Profil')
                ->icon('bs.check-circle')
                ->type(Color::PRIMARY())
                ->method('save'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                Layout::rows([
                    Cropper::make('user.foto')
                        ->title('Foto Profil')
                        ->targetRelativeUrl(),

                    Input::make('user.name')
                        ->title('Nama Lengkap')
                        ->placeholder('Nama Lengkap')
                        ->required(),

                    Input::make('user.email')
                        ->title('Email')
                        ->placeholder('Alamat Email')
                        ->required(),

                    Input::make('user.nomor_wa')
                        ->title('Nomor WA')
                        ->placeholder('Contoh: 08123456789'),

                    Input::make('user.kota')
                        ->title('Kota')
                        ->placeholder('Contoh: Pekalongan'),

                    TextArea::make('user.bio')
                        ->title('Bio')
                        ->rows(4)
                        ->placeholder('Tulis deskripsi singkat profil Anda'),

                    Input::make('user.linkedin')
                        ->title('LinkedIn')
                        ->placeholder('https://linkedin.com/in/username'),

                    Input::make('user.website')
                        ->title('Website')
                        ->placeholder('https://example.com'),

                    Input::make('user.instagram')
                        ->title('Instagram')
                        ->placeholder('https://instagram.com/username atau @username'),

                    Relation::make('user.professionalRoles.')
                        ->fromModel(ProfessionalRole::class, 'nama')
                        ->multiple()
                        ->title('Peran Profesi')
                        ->placeholder('Pilih peran profesi Anda'),

                    Relation::make('user.skills.')
                        ->fromModel(Skill::class, 'nama')
                        ->multiple()
                        ->title('Skill & Layanan')
                        ->placeholder('Pilih skill & layanan Anda'),
                ]),
            ])
            ->title('Informasi Profil')
            ->description('Perbarui foto profil, kontak, dan informasi pribadi Anda.')
            ->commands(
                Button::make('Simpan')
                    ->type(Color::PRIMARY())
                    ->icon('bs.check-circle')
                    ->method('save')
            ),

            Layout::block([
                Layout::rows([
                    Input::make('old_password')
                        ->type('password')
                        ->title('Password Saat Ini')
                        ->placeholder('Password Saat Ini'),

                    Input::make('password')
                        ->type('password')
                        ->title('Password Baru')
                        ->placeholder('Password Baru'),

                    Input::make('password_confirmation')
                        ->type('password')
                        ->title('Konfirmasi Password Baru')
                        ->placeholder('Konfirmasi Password Baru'),
                ]),
            ])
            ->title('Ubah Password')
            ->description('Pastikan akun Anda menggunakan password yang aman.')
            ->commands(
                Button::make('Ubah Password')
                    ->type(Color::BASIC())
                    ->icon('bs.key')
                    ->method('changePassword')
            ),
        ];
    }

    /**
     * Save profile changes.
     */
    public function save(Request $request): void
    {
        $user = User::where('id', Auth::id())->firstOrFail();

        $request->validate([
            'user.name'      => 'required|string|max:255',
            'user.email'     => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
            ],
            'user.nomor_wa'  => 'nullable|string|max:30',
            'user.kota'      => 'nullable|string|max:100',
            'user.bio'       => 'nullable|string',
            'user.linkedin'  => 'nullable|string|max:255',
            'user.website'   => 'nullable|string|max:255',
            'user.instagram'           => 'nullable|string|max:255',
            'user.foto'                => 'nullable|string',
            'user.professionalRoles'   => 'nullable|array',
            'user.professionalRoles.*' => 'integer|exists:professional_roles,id',
            'user.skills'              => 'nullable|array',
            'user.skills.*'            => 'integer|exists:skills,id',
        ]);

        $userData = $request->get('user');

        $user->fill($userData)->save();

        $user->professionalRoles()->sync($userData['professionalRoles'] ?? []);
        $user->skills()->sync($userData['skills'] ?? []);

        Toast::info('Profil berhasil diperbarui.');
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): void
    {
        $user = User::where('id', Auth::id())->firstOrFail();
        $guard = config('platform.guard', 'web');

        $request->validate([
            'old_password' => 'required|current_password:' . $guard,
            'password'     => 'required|confirmed|min:8',
        ]);

        $user->password = Hash::make($request->get('password'));
        $user->save();

        Toast::info('Password berhasil diubah.');
    }
}
