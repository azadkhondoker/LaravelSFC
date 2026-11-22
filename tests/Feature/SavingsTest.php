<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_and_member_raw_savings_include_deposits_of_deleted_members(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $activeMember = $this->createMember('Active saver', 'active@example.com');
        $deletedMember = $this->createMember('Deleted saver', 'deleted@example.com');

        $activeMember->installments()->create([
            'amount' => '850.25',
            'paid_at' => '2026-11-18',
        ]);
        $deletedMember->installments()->create([
            'amount' => '500.00',
            'paid_at' => '2026-11-19',
        ]);
        $deletedMember->delete();

        $this->get(route('savings.index'))
            ->assertOk()
            ->assertSee('Total savings')
            ->assertSee('1,350.25')
            ->assertSee('Active saver')
            ->assertSee('850.25')
            ->assertSee('Deleted saver')
            ->assertSee('Deleted')
            ->assertSee('500.00')
            ->assertSee('Investment categories and their profit/loss are not tracked yet.');
    }

    private function createMember(string $name, string $email): Member
    {
        return Member::create([
            'name' => $name,
            'email' => $email,
            'gender' => 0,
            'joining_date' => '2026-11-01',
            'installment_amount' => '500.00',
            'status' => 'active',
        ]);
    }
}
