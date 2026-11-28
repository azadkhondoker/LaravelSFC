<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\WithdrawService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdrawals_are_recorded_and_only_approved_requests_reduce_savings(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $member = Member::create([
            'name' => 'Withdrawal member',
            'email' => 'withdrawal@example.com',
            'gender' => 0,
            'joining_date' => now()->toDateString(),
            'installment_amount' => '500.00',
            'status' => 'active',
        ]);
        $member->installments()->create([
            'amount' => '1000.25',
            'paid_at' => now()->toDateString(),
        ]);

        $this->post(route('withdrawals.store'), [
            'member_id' => $member->id,
            'amount' => '300.25',
            'withdrawal_date' => now()->toDateString(),
            'reason' => 'Member request',
        ])->assertRedirect(route('withdrawals.index'));

        $withdrawal = Withdrawal::firstOrFail();
        $this->get(route('withdrawals.index'))
            ->assertOk()
            ->assertSee('Withdrawal history')
            ->assertSee($withdrawal->transaction_id)
            ->assertSee('Withdrawal member');

        $service = app(WithdrawService::class);
        $this->assertSame('1000.25', $service->balance($member->id));
        $this->assertSame('700.00', $service->available($member->id));

        $this->post(route('withdrawals.approve', $withdrawal), [
            'admin_note' => 'Verified',
        ])->assertRedirect();
        $this->assertSame(Withdrawal::APPROVED, $withdrawal->fresh()->status);
        $this->assertSame('700.00', $service->balance($member->id));

        $this->post(route('withdrawals.store'), [
            'member_id' => $member->id,
            'amount' => '200.00',
            'withdrawal_date' => now()->toDateString(),
        ])->assertRedirect(route('withdrawals.index'));
        $pending = Withdrawal::where('status', Withdrawal::PENDING)->firstOrFail();

        $this->post(route('withdrawals.reject', $pending), [
            'admin_note' => 'Not approved',
        ])->assertRedirect();
        $this->assertSame(Withdrawal::REJECTED, $pending->fresh()->status);
        $this->assertSame('700.00', $service->balance($member->id));
    }

    public function test_request_cannot_exceed_available_member_savings(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $member = Member::create([
            'name' => 'Low balance member',
            'email' => 'low-balance@example.com',
            'gender' => 0,
            'joining_date' => now()->toDateString(),
            'installment_amount' => '500.00',
            'status' => 'active',
        ]);
        $member->installments()->create([
            'amount' => '100.00',
            'paid_at' => now()->toDateString(),
        ]);

        $this->from(route('withdrawals.index'))
            ->post(route('withdrawals.store'), [
                'member_id' => $member->id,
                'amount' => '100.01',
                'withdrawal_date' => now()->toDateString(),
            ])
            ->assertRedirect(route('withdrawals.index'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('withdrawals', 0);
    }
}
