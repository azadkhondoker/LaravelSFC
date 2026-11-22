<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\InstallmentSchedule;
use Tests\TestCase;

class InstallmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_members_appear_and_deposits_are_saved_to_the_member_profile(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $this->post(route('members.store'), [
            'name' => 'First member',
            'email' => 'first@example.com',
            'gender' => '0',
            'joining_date' => '2026-11-01',
            'installment_amount' => '1250.50',
            'status' => 'active',
        ])->assertRedirect(route('members.index'));
        $firstMember = Member::where('email', 'first@example.com')->firstOrFail();

        $this->post(route('members.store'), [
            'name' => 'Second member',
            'email' => 'second@example.com',
            'gender' => '0',
            'joining_date' => '2026-11-02',
            'installment_amount' => '875.00',
            'status' => 'active',
        ])->assertRedirect(route('members.index'));
        $secondMember = Member::where('email', 'second@example.com')->firstOrFail();

        $this->get(route('installments.index'))
            ->assertOk()
            ->assertSee('First member')
            ->assertSee('Second member');

        $this->from(route('members.show', $firstMember))
            ->post(route('members.installments.store', $firstMember), [
                'total_amount' => '1000.00',
                'paid_at' => '2026-11-18',
            ])
            ->assertRedirect(route('members.show', $firstMember))
            ->assertSessionHasErrors('total_amount');

        $this->assertDatabaseCount('installments', 0);
        $this->get(route('members.show', $firstMember))
            ->assertOk()
            ->assertSee('Total amount must be a whole multiple of the monthly installment amount');

        $paymentResponse = $this->post(route('members.installments.store', $firstMember), [
            'total_amount' => '6252.50',
            'paid_at' => '2026-11-18',
            'note' => 'November installment',
        ]);

        $this->assertDatabaseHas('installments', [
            'member_id' => $firstMember->id,
            'note' => 'November installment',
        ]);
        $this->assertDatabaseCount('installments', 5);

        $installments = Installment::where('member_id', $firstMember->id)
            ->orderBy('deposit_date')
            ->get();
        $this->assertSame(
            ['2026-11-01', '2026-12-01', '2027-01-01', '2027-02-01', '2027-03-01'],
            $installments->map(fn (Installment $item) => $item->deposit_date->format('Y-m-d'))->all(),
        );
        $this->assertSame(
            array_fill(0, 5, '2026-11-18'),
            $installments->map(fn (Installment $item) => $item->paid_at->format('Y-m-d'))->all(),
        );
        $installment = $installments->firstOrFail();
        $this->assertSame('1250.50', $installment->amount);
        $this->assertSame('2026-11-18', $installment->paid_at->format('Y-m-d'));
        $payment = InstallmentPayment::firstOrFail();
        $this->assertSame('6252.50', $payment->total_amount);
        $this->assertSame(5, $payment->installments_count);
        $this->assertSame(5, $payment->installments()->count());
        $paymentResponse->assertRedirect(route('installment-payments.receipt', $payment));
        $this->get(route('installment-payments.receipt', $payment))
            ->assertOk()
            ->assertSee($payment->receipt_no)
            ->assertSee('6,252.50')
            ->assertSee('Nov 01, 2026')
            ->assertSee('Dec 01, 2026')
            ->assertSee('Mar 01, 2027');

        $this->get(route('members.show', $firstMember))
            ->assertOk()
            ->assertSee('6,252.50')
            ->assertSee('data-installment-amount="1250.50"', false)
            ->assertSee('name="total_amount"', false)
            ->assertSee('value="2027-04-01"', false)
            ->assertSee('Input the right amount')
            ->assertSee('post-details-modal installment-error-modal', false)
            ->assertSee('bootstrap.Modal.getOrCreateInstance', false)
            ->assertDontSee('window.alert')
            ->assertSee('November installment');

        $this->get(route('installments.index'))
            ->assertOk()
            ->assertSee('6,252.50')
            ->assertSee('Second member');

        $this->delete(route('members.destroy', $firstMember))
            ->assertRedirect(route('members.index'));
        $this->assertSoftDeleted('members', ['id' => $firstMember->id]);
        $this->assertDatabaseHas('installments', [
            'member_id' => $firstMember->id,
            'note' => 'November installment',
        ]);

        $this->get(route('installments.index'))
            ->assertOk()
            ->assertDontSee('First member')
            ->assertSee('Second member');
        $this->get(route('installment-payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Deleted member');
        $this->get(route('members.deleted'))
            ->assertOk()
            ->assertSee('First member');

        $this->post(route('members.restore', $firstMember))
            ->assertRedirect(route('members.deleted'));
        $this->assertNotSoftDeleted('members', ['id' => $firstMember->id]);
        $this->get(route('installments.index'))
            ->assertOk()
            ->assertSee('First member')
            ->assertSee('6,252.50');
    }

    public function test_monthly_due_dates_keep_the_joining_day_across_short_months(): void
    {
        $member = Member::create([
            'name' => 'Month end saver',
            'email' => 'month-end@example.com',
            'gender' => 0,
            'joining_date' => '2026-01-31',
            'installment_amount' => '500.00',
            'status' => 'active',
        ]);

        $schedule = app(InstallmentSchedule::class);
        $this->assertSame(
            ['2026-01-31', '2026-02-28', '2026-03-31'],
            array_map(
                fn ($date) => $date->format('Y-m-d'),
                $schedule->nextDueDates($member, 3),
            ),
        );

        $member->installments()->create([
            'amount' => '500.00',
            'deposit_date' => '2026-01-31',
            'paid_at' => '2026-01-31',
        ]);

        $this->assertSame(
            ['2026-02-28', '2026-03-31'],
            array_map(
                fn ($date) => $date->format('Y-m-d'),
                $schedule->nextDueDates($member, 2),
            ),
        );
    }
}
