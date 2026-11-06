<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpSystemTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'Pengguna Bantuan',
            'email' => 'help@testing.test',
            'password' => 'PasswordBantuan2026!',
            'role' => 'admin',
        ]);
    }

    public function test_faq_memerlukan_login_dan_dapat_diakses_pengguna(): void
    {
        $this->get(route('faq.index'))->assertRedirect(route('login'));

        $this->actingAs($this->user())
            ->get(route('faq.index'))
            ->assertOk()
            ->assertSee('FAQ & Petunjuk Penggunaan', false)
            ->assertSee('data-faq-search', false)
            ->assertSee('Bagaimana memindahkan inventaris?');
    }

    public function test_layout_dan_form_memuat_bantuan_kontekstual(): void
    {
        $this->actingAs($this->user());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('FAQ & Petunjuk', false)
            ->assertSee('assets/help.js', false)
            ->assertSee('data-help-tip', false);

        foreach (['gedung.create', 'jurusan.create', 'prasarana.create', 'inventaris.create'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('help-tip-trigger', false)
                ->assertSee('aria-expanded="false"', false);
        }
    }
}
