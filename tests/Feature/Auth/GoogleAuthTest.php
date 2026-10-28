<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_and_register_pages_render_google_button(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertOk();
        $loginResponse->assertSee('Masuk dengan Google');
        $loginResponse->assertSee(route('auth.google'));

        $registerResponse = $this->get('/register');
        $registerResponse->assertOk();
        $registerResponse->assertSee('Masuk dengan Google');
        $registerResponse->assertSee(route('auth.google'));
    }

    public function test_redirect_to_google_initiates_socialite_redirect(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_handle_callback_creates_new_customer_when_not_registered(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-unique-id-123');
        $socialiteUser->shouldReceive('getName')->andReturn('Budi Santoso');
        $socialiteUser->shouldReceive('getEmail')->andReturn('budi@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-unique-id-123', $user->google_id);
        $this->assertSame(UserRole::CUSTOMER, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('https://lh3.googleusercontent.com/avatar.jpg', $user->avatar);

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_handle_callback_links_google_id_to_existing_email_account(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
            'role' => UserRole::CUSTOMER,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-linked-id-456');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User Google');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/new-avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existingUser);

        $existingUser->refresh();
        $this->assertSame('google-linked-id-456', $existingUser->google_id);
        $this->assertSame('https://lh3.googleusercontent.com/new-avatar.jpg', $existingUser->avatar);

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_handle_callback_authenticates_existing_google_user(): void
    {
        $existingGoogleUser = User::factory()->create([
            'email' => 'googleuser@example.com',
            'google_id' => 'existing-google-id-789',
            'role' => UserRole::CUSTOMER,
            'avatar' => 'https://lh3.googleusercontent.com/old-avatar.jpg',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('existing-google-id-789');
        $socialiteUser->shouldReceive('getName')->andReturn('Google User');
        $socialiteUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/updated-avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existingGoogleUser);

        $existingGoogleUser->refresh();
        $this->assertSame('https://lh3.googleusercontent.com/updated-avatar.jpg', $existingGoogleUser->avatar);

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_handle_callback_redirects_admin_role_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@dunia-laptop.test',
            'google_id' => 'admin-google-id',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('admin-google-id');
        $socialiteUser->shouldReceive('getName')->andReturn('Admin Store');
        $socialiteUser->shouldReceive('getEmail')->andReturn('admin@dunia-laptop.test');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/admin.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_handle_callback_catches_exception_and_redirects_to_login(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andThrow(new Exception('Invalid state or OAuth cancelled'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Gagal masuk dengan Google. Silakan coba kembali.');
    }
}

