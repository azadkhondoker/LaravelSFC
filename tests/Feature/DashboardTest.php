<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\posts;
use App\Models\User;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LandInvestment;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_highlights_the_current_page_instead_of_always_highlighting_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $installmentsHtml = $this->get(route('installments.index'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<li class="active">\s*<a href="'.preg_quote(route('installments.index'), '/').'"/',
            $installmentsHtml
        );
        $this->assertMatchesRegularExpression(
            '/<li class="">\s*<a href="'.preg_quote(route('dashboard'), '/').'"/',
            $installmentsHtml
        );

        $dashboardHtml = $this->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<li class="active">\s*<a href="'.preg_quote(route('dashboard'), '/').'"/',
            $dashboardHtml
        );
    }

    public function test_dashboard_shows_project_metrics_and_recent_member_activity(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(10));
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['name' => 'Dashboard Admin']);
        $user->assignRole('admin');
        $this->actingAs($user);

        $activeMember = $this->createMember('Active saver', 'active@example.com', 'active');
        $this->createMember('Inactive saver', 'inactive@example.com', 'inactive');

        $activeMember->installments()->create([
            'amount' => '1250.00',
            'deposit_date' => now()->toDateString(),
            'paid_at' => now()->toDateString(),
        ]);
        $activeMember->installmentPayments()->create([
            'receipt_no' => 'RCPT-DASHBOARD-001',
            'total_amount' => '1250.00',
            'installment_amount' => '1250.00',
            'installments_count' => 1,
            'paid_at' => now()->toDateString(),
        ]);
        posts::create([
            'title' => 'Monthly meeting',
            'content' => 'The monthly meeting will be held this weekend.',
            'post_date' => now()->toDateString(),
        ]);
        $loan = Loan::create([
            'member_id' => $activeMember->id,
            'amount' => '1000.00',
            'interest_rate' => '12',
            'duration_months' => 10,
            'start_date' => now()->toDateString(),
        ]);
        LoanPayment::create([
            'loan_id' => $loan->id,
            'month_number' => 1,
            'due_date' => now()->addMonth()->toDateString(),
            'principal' => '100.00',
            'interest' => '10.00',
            'total_amount' => '110.00',
            'paid_at' => now()->toDateString(),
            'receipt_no' => 'LOAN-RCPT-DASHBOARD-001',
        ]);
        $land = LandInvestment::create([
            'name' => 'Dashboard land',
            'purchase_price' => '500.00',
            'area' => '1.000',
            'area_unit' => 'decimal',
            'purchased_at' => now()->toDateString(),
        ]);
        $land->sales()->create([
            'area_sold' => '0.500',
            'sale_price' => '700.00',
            'buyer_name' => 'Dashboard buyer',
            'sold_at' => now()->toDateString(),
        ]);
        foreach ([
            ['profit', '100.00', 'Other profit'],
            ['fine', '50.00', 'Membership fine'],
            ['loss', '30.00', 'Investment loss'],
            ['expense', '20.00', 'Office expense'],
        ] as [$type, $amount, $note]) {
            $this->post(route('investments.financial-entries.store'), [
                'entry_type' => $type,
                'member_id' => $type === 'fine' ? $activeMember->id : null,
                'amount' => $amount,
                'entry_date' => now()->toDateString(),
                'note' => $note,
            ])->assertRedirect(route('investments.financial-entries.index'));
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Dashboard Admin')
            ->assertSee('Total members')
            ->assertSee('2')
            ->assertSee('Active members')
            ->assertSee('1,250.00')
            ->assertSee('Active saver')
            ->assertSee('Monthly meeting')
            ->assertSee('RCPT-DASHBOARD-001')
            ->assertSee('Invested amount')
            ->assertSee('Available savings')
            ->assertSee('Recent loan repayments')
            ->assertSee('LOAN-RCPT-DASHBOARD-001')
            ->assertSee('Loans with two guarantors')
            ->assertSee('Loans with collateral')
            ->assertSee('Land sale proceeds')
            ->assertSee('700.00')
            ->assertSee('Savings deposits')
            ->assertSee('Profit')
            ->assertSee('550.00')
            ->assertSee('Interest income')
            ->assertSee('Fines')
            ->assertSee('Losses')
            ->assertSee('Expenses')
            ->assertSee('Adjusted total funds')
            ->assertSee('1,810.00');

        $dashboardHtml = $this->get(route('dashboard'))->assertOk()->getContent();
        foreach (['profit', 'interest', 'loss', 'expense'] as $type) {
            $this->assertStringContainsString(
                'href="'.route('investments.financial-entries.index', ['type' => $type]).'" class="card dashboard-stat-card"',
                $dashboardHtml
            );
        }

        $pastDate = now()->subDay()->toDateString();
        $this->post(route('investments.financial-entries.store'), [
            'entry_type' => 'profit',
            'amount' => '70.00',
            'entry_date' => $pastDate,
            'note' => 'Earlier profit',
        ])->assertRedirect(route('investments.financial-entries.index'));
        $previousMonthDate = now()->subMonth()->toDateString();
        $this->post(route('investments.financial-entries.store'), [
            'entry_type' => 'profit',
            'amount' => '40.00',
            'entry_date' => $previousMonthDate,
            'note' => 'Previous month profit',
        ])->assertRedirect(route('investments.financial-entries.index'));

        $profitReport = $this->get(route('investments.financial-entries.index', ['type' => 'profit']))
            ->assertOk()
            ->assertSee('Profit history')
            ->assertSee('Serial')
            ->assertSee('450.00')
            ->assertSee('100.00')
            ->assertSee('Monthly total')
            ->assertSee('620.00')
            ->assertSee('660.00')
            ->assertSee('Land sale: Dashboard land — Dashboard buyer')
            ->assertSee('Other profit')
            ->assertDontSee('Add financial entry');
        $profitReportHtml = $profitReport->getContent();
        $this->assertLessThan(
            strpos($profitReportHtml, $pastDate),
            strpos($profitReportHtml, now()->toDateString())
        );
        $this->get(route('investments.financial-entries.index', [
            'type' => 'profit',
            'month' => now()->subMonth()->month,
            'year' => now()->subMonth()->year,
        ]))
            ->assertOk()
            ->assertSee('40.00')
            ->assertSee('Previous month profit')
            ->assertSee('660.00');

        $this->get(route('investments.financial-entries.index', ['type' => 'interest']))
            ->assertOk()
            ->assertSee('Interest income history')
            ->assertSee('10.00')
            ->assertSee('Monthly total')
            ->assertSee('Active saver')
            ->assertDontSee('Add financial entry');

        $this->get(route('investments.financial-entries.index', ['type' => 'loss']))
            ->assertOk()
            ->assertSee('Loss history')
            ->assertSee('30.00')
            ->assertSee('Monthly total')
            ->assertSee('Investment loss')
            ->assertDontSee('Add financial entry');

        $this->get(route('investments.financial-entries.index', ['type' => 'expense']))
            ->assertOk()
            ->assertSee('Expense history')
            ->assertSee('20.00')
            ->assertSee('Monthly total')
            ->assertDontSee('Add financial entry');
    }

    public function test_dashboard_hides_member_and_savings_data_without_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $member = $this->createMember('Private member', 'private@example.com', 'active');
        $member->installments()->create([
            'amount' => '900.00',
            'paid_at' => now()->toDateString(),
        ]);
        $member->installmentPayments()->create([
            'receipt_no' => 'RCPT-PRIVATE-001',
            'total_amount' => '900.00',
            'installment_amount' => '900.00',
            'installments_count' => 1,
            'paid_at' => now()->toDateString(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent notices')
            ->assertDontSee('Total members')
            ->assertDontSee('Total savings')
            ->assertDontSee('Private member')
            ->assertDontSee('RCPT-PRIVATE-001');
    }

    private function createMember(string $name, string $email, string $status): Member
    {
        return Member::create([
            'name' => $name,
            'email' => $email,
            'gender' => 0,
            'joining_date' => now()->toDateString(),
            'status' => $status,
        ]);
    }
}
