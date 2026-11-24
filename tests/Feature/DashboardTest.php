<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\posts;
use App\Models\User;
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

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Dashboard Admin')
            ->assertSee('Total members')
            ->assertSee('2')
            ->assertSee('Active members')
            ->assertSee('1,250.00')
            ->assertSee('Active saver')
            ->assertSee('Monthly meeting')
            ->assertSee('RCPT-DASHBOARD-001');
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
