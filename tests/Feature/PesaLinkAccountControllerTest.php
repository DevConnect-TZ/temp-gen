<?php

namespace Tests\Feature;

use App\Models\PesaLinkAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesaLinkAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): void
    {
        session(['admin_authenticated' => true]);
    }

    public function test_it_lists_pesalink_accounts(): void
    {
        $this->authenticateAdmin();

        PesaLinkAccount::create([
            'name' => 'Account One',
            'api_key' => 'key-one-abcdefghij',
            'is_active' => true,
        ]);

        PesaLinkAccount::create([
            'name' => 'Account Two',
            'api_key' => 'key-two-abcdefghij',
            'is_active' => false,
        ]);

        $response = $this->get(route('pesalink-accounts.index'));

        $response->assertOk();
        $response->assertSee('Account One');
        $response->assertSee('Account Two');
    }

    public function test_it_stores_a_new_pesalink_account(): void
    {
        $this->authenticateAdmin();

        $response = $this->post(route('pesalink-accounts.store'), [
            'name' => 'New Account',
            'api_key' => 'new-key-abcdefghij',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pesa_link_accounts', [
            'name' => 'New Account',
            'api_key' => 'new-key-abcdefghij',
            'is_active' => true,
        ]);
    }

    public function test_it_updates_a_pesalink_account(): void
    {
        $this->authenticateAdmin();

        $account = PesaLinkAccount::create([
            'name' => 'Old Name',
            'api_key' => 'old-key-abcdefghij',
            'is_active' => true,
        ]);

        $response = $this->post(route('pesalink-accounts.update', $account), [
            'name' => 'Updated Name',
            'api_key' => 'updated-key-abcdefghij',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pesa_link_accounts', [
            'id' => $account->id,
            'name' => 'Updated Name',
            'api_key' => 'updated-key-abcdefghij',
        ]);
    }

    public function test_it_toggles_a_pesalink_account(): void
    {
        $this->authenticateAdmin();

        $account = PesaLinkAccount::create([
            'name' => 'Toggle Account',
            'api_key' => 'toggle-key-abcdefghij',
            'is_active' => true,
        ]);

        $response = $this->post(route('pesalink-accounts.toggle', $account));

        $response->assertRedirect();

        $this->assertDatabaseHas('pesa_link_accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function test_it_deletes_a_pesalink_account(): void
    {
        $this->authenticateAdmin();

        $account = PesaLinkAccount::create([
            'name' => 'Delete Me',
            'api_key' => 'delete-key-abcdefghij',
            'is_active' => true,
        ]);

        $response = $this->delete(route('pesalink-accounts.destroy', $account));

        $response->assertRedirect();

        $this->assertDatabaseMissing('pesa_link_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_validation_requires_name_and_api_key(): void
    {
        $this->authenticateAdmin();

        $response = $this->post(route('pesalink-accounts.store'), [
            'name' => '',
            'api_key' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'api_key']);
    }

    public function test_validation_requires_api_key_min_length(): void
    {
        $this->authenticateAdmin();

        $response = $this->post(route('pesalink-accounts.store'), [
            'name' => 'Too Short',
            'api_key' => 'short',
        ]);

        $response->assertSessionHasErrors(['api_key']);
    }

    public function test_guest_cannot_access_pesalink_accounts(): void
    {
        $response = $this->get(route('pesalink-accounts.index'));

        $response->assertRedirect('/login');
    }
}
