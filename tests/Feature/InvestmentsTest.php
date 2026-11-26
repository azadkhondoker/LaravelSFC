<?php

namespace Tests\Feature;

use App\Models\InvestmentCategory;
use App\Models\LandInvestment;
use App\Models\LandSale;
use App\Models\Loan;
use App\Models\LoanClient;
use App\Models\Member;
use App\Models\OtherInvestment;
use App\Models\User;
use App\Services\InvestmentSavings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvestmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_existing_member_can_be_selected_and_enrolled_for_a_loan(): void
    {
        Storage::fake('local');
        $member = Member::create([
            'name' => 'Member Borrower',
            'email' => 'borrower@example.com',
            'gender' => 0,
            'joining_date' => '2026-11-01',
            'installment_amount' => '500.00',
            'status' => 'active',
        ]);
        $member->installments()->create([
            'amount' => '100000.00',
            'paid_at' => '2026-11-26',
            'deposit_date' => '2026-11-01',
        ]);

        $this->get(route('investments.loans.create'))
            ->assertOk()
            ->assertSee('Member Borrower')
            ->assertSee('data-code="' . $member->member_code . '"', false);

        $this->post(route('investments.loans.store'), [
            'borrower_type' => 'member',
            'member_id' => $member->id,
            'amount' => '25000.00',
            'interest_rate' => '8.5',
            'duration_months' => 12,
            'start_date' => '2026-11-25',
            'guarantors' => $this->validGuarantors(),
            'collaterals' => [
                [
                    'asset_type' => 'Land deed',
                    'description' => 'Borrower land title deed',
                    'estimated_value' => '50000.00',
                    'document' => UploadedFile::fake()->create('land-deed.pdf', 20, 'application/pdf'),
                ],
            ],
        ])->assertRedirect(route('investments.loans.index'));

        $loan = Loan::firstOrFail();
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'member_id' => $member->id,
            'loan_client_id' => null,
            'amount' => '25000.00',
            'interest_rate' => '8.500',
            'duration_months' => 12,
        ]);
        $this->assertDatabaseCount('loan_guarantors', 2);
        $collateral = $loan->collaterals()->firstOrFail();
        $this->assertSame('Land deed', $collateral->asset_type);
        Storage::disk('local')->assertExists($collateral->document_path);

        $this->get(route('investments.loans.schedule', $loan))
            ->assertOk()
            ->assertSee('Guarantor One')
            ->assertSee('Land deed')
            ->assertSee(route('investments.loans.collateral-document', $collateral), false);
        $this->get(route('investments.loans.collateral-document', $collateral))->assertOk();
        $this->get(route('investments.loans.edit', $loan))
            ->assertOk()
            ->assertSee('Guarantor One')
            ->assertSee('Land deed');

        $updatedGuarantors = $this->validGuarantors();
        $updatedGuarantors[0]['name'] = 'Updated Guarantor';
        $this->put(route('investments.loans.update', $loan), [
            'borrower_type' => 'member',
            'member_id' => $member->id,
            'amount' => '25000.00',
            'interest_rate' => '8.5',
            'duration_months' => 12,
            'start_date' => '2026-11-25',
            'guarantors' => $updatedGuarantors,
            'collaterals' => [
                [
                    'id' => $collateral->id,
                    'asset_type' => 'Land deed',
                    'description' => 'Updated title deed details',
                    'estimated_value' => '55000.00',
                ],
            ],
        ])->assertRedirect(route('investments.loans.index'));
        $this->assertDatabaseHas('loan_guarantors', [
            'loan_id' => $loan->id,
            'position' => 1,
            'name' => 'Updated Guarantor',
        ]);
        Storage::disk('local')->assertExists($collateral->fresh()->document_path);
    }

    public function test_non_member_must_be_registered_before_loan_enrollment(): void
    {
        $fundMember = Member::create([
            'name' => 'Fund Member',
            'email' => 'fund@example.com',
            'gender' => 0,
            'joining_date' => '2026-11-01',
            'installment_amount' => '1000.00',
            'status' => 'active',
        ]);
        $fundMember->installments()->create([
            'amount' => '100000.00',
            'paid_at' => '2026-11-26',
            'deposit_date' => '2026-11-01',
        ]);

        $this->post(route('investments.clients.store'), [
            'name' => 'Outside Client',
            'email' => 'client@example.com',
            'phone' => '01700000000',
            'nid' => '0001234567',
            'address' => 'Dhaka',
        ])->assertRedirect(route('investments.clients.index'));

        $client = LoanClient::firstOrFail();
        $this->get(route('investments.clients.edit', $client))
            ->assertOk()
            ->assertSee('Outside Client');

        $this->put(route('investments.clients.update', $client), [
            'name' => 'Updated Outside Client',
            'email' => 'updated@example.com',
            'phone' => '01800000000',
            'nid' => '0001234568',
            'address' => 'Chattogram',
        ])->assertRedirect(route('investments.clients.index'));
        $this->assertDatabaseHas('loan_clients', [
            'id' => $client->id,
            'name' => 'Updated Outside Client',
            'phone' => '01800000000',
        ]);

        $this->get(route('investments.loans.create'))
            ->assertOk()
            ->assertSee('Updated Outside Client');

        $this->post(route('investments.loans.store'), [
            'borrower_type' => 'client',
            'loan_client_id' => $client->id,
            'amount' => '10000.00',
            'interest_rate' => '5',
            'duration_months' => 6,
            'start_date' => '2026-11-25',
            'guarantors' => $this->validGuarantors(),
        ])->assertRedirect(route('investments.loans.index'));

        $this->assertDatabaseHas('loans', [
            'member_id' => null,
            'loan_client_id' => $client->id,
            'amount' => '10000.00',
        ]);

        $this->post(route('investments.loans.store'), [
            'borrower_type' => 'client',
            'loan_client_id' => 99999,
            'amount' => '10000.00',
            'interest_rate' => '5',
            'duration_months' => 6,
            'start_date' => '2026-11-25',
        ])->assertSessionHasErrors('loan_client_id');
    }

    public function test_client_can_be_deleted_and_loan_schedule_is_sorted_by_month(): void
    {
        $client = LoanClient::create(['name' => 'Schedule Client']);
        $loan = Loan::create([
            'loan_client_id' => $client->id,
            'amount' => '12000.00',
            'interest_rate' => '12',
            'duration_months' => 4,
            'start_date' => '2026-11-25',
        ]);

        $this->get(route('investments.loans.schedule', $loan))
            ->assertOk()
            ->assertSee('2026-12-25')
            ->assertSee('2027-03-25')
            ->assertSee('Paid 0%', false)
            ->assertSee('Remaining 100%', false)
            ->assertSee('Loan installment schedule');

        $this->delete(route('investments.clients.destroy', $client))
            ->assertRedirect(route('investments.clients.index'));

        $this->assertDatabaseMissing('loan_clients', ['id' => $client->id]);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'loan_client_id' => null,
        ]);
    }

    public function test_investments_reduce_available_savings_and_loan_payment_creates_receipt(): void
    {
        $member = Member::create([
            'name' => 'Savings Member',
            'email' => 'savings@example.com',
            'gender' => 0,
            'joining_date' => '2026-11-01',
            'installment_amount' => '1000.00',
            'status' => 'active',
        ]);
        $member->installments()->create([
            'amount' => '10000.00',
            'paid_at' => '2026-11-26',
            'deposit_date' => '2026-11-01',
        ]);

        $this->post(route('investments.loans.store'), [
            'borrower_type' => 'member',
            'member_id' => $member->id,
            'amount' => '8000.00',
            'interest_rate' => '12',
            'duration_months' => 10,
            'start_date' => '2026-11-25',
            'guarantors' => $this->validGuarantors(),
        ])->assertRedirect(route('investments.loans.index'));

        $loan = Loan::firstOrFail();
        $permission = Permission::findOrCreate('view members', 'web');
        $user = User::firstOrFail();
        $user->givePermissionTo($permission);
        $this->actingAs($user);
        $this->get(route('savings.index'))
            ->assertOk()
            ->assertSee('Available savings')
            ->assertSee('2,000.00');

        $this->post(route('investments.loans.store'), [
            'borrower_type' => 'member',
            'member_id' => $member->id,
            'amount' => '3000.00',
            'interest_rate' => '12',
            'duration_months' => 10,
            'start_date' => '2026-11-25',
            'guarantors' => $this->validGuarantors(),
        ])->assertSessionHasErrors('amount');

        $this->post(route('investments.loans.pay', [$loan, 1]), [
            'paid_at' => '2026-12-25',
            'note' => 'First installment',
        ])->assertRedirect(route('investments.loans.schedule', $loan));

        $payment = $loan->payments()->firstOrFail();
        $this->get(route('investments.loans.schedule', $loan))
            ->assertOk()
            ->assertSee('Installment paid successfully.')
            ->assertSee('Paid 10%', false)
            ->assertSee('Paid</span>', false)
            ->assertSee(route('investments.loans.receipt', $payment), false);

        $this->get(route('investments.loans.receipt', $payment))
            ->assertOk()
            ->assertSee($payment->receipt_no)
            ->assertSee('First installment');

        $this->get(route('investments.loans.schedule', $loan))
            ->assertOk()
            ->assertSee('Paid 10%', false)
            ->assertSee('Remaining 90%', false);
    }

    public function test_land_deed_is_stored_privately_and_other_categories_can_be_added(): void
    {
        Storage::fake('local');
        $fundMember = Member::create([
            'name' => 'Land Fund Member',
            'email' => 'land-fund@example.com',
            'gender' => 0,
            'joining_date' => '2026-11-01',
            'installment_amount' => '1000.00',
            'status' => 'active',
        ]);
        $fundMember->installments()->create([
            'amount' => '1000000.00',
            'paid_at' => '2026-11-26',
            'deposit_date' => '2026-11-01',
        ]);
        $deed = UploadedFile::fake()->create('deed.pdf', 20, 'application/pdf');

        $this->post(route('investments.land.store'), [
            'name' => 'Association land',
            'location' => 'Dhaka',
            'purchase_price' => '750000.00',
            'area' => '4.5',
            'area_unit' => 'katha',
            'purchased_at' => '2026-11-25',
            'deed' => $deed,
        ])->assertRedirect(route('investments.land.index'));

        $land = LandInvestment::firstOrFail();
        $this->assertNotNull($land->deed_path);
        Storage::disk('local')->assertExists($land->deed_path);
        $this->get(route('investments.land.deed', $land))->assertOk();

        $this->post(route('investments.others.store'), [
            'new_category' => 'Livestock',
            'name' => 'Silver bars',
            'amount' => '12000.00',
            'details' => 'Stored in the safe',
            'invested_at' => '2026-11-25',
        ])->assertRedirect(route('investments.others.index'));

        $category = InvestmentCategory::where('name', 'Livestock')->firstOrFail();
        $this->assertDatabaseHas('other_investments', [
            'investment_category_id' => $category->id,
            'name' => 'Silver bars',
            'amount' => '12000.00',
        ]);
        $this->assertSame(1, OtherInvestment::count());
    }

    public function test_land_can_be_sold_in_parts_and_sale_proceeds_return_to_available_savings(): void
    {
        Storage::fake('local');
        $fundMember = Member::create([
            'name' => 'Land Sale Fund',
            'email' => 'land-sale-fund@example.com',
            'gender' => 0,
            'joining_date' => '2026-11-01',
            'installment_amount' => '1000.00',
            'status' => 'active',
        ]);
        $fundMember->installments()->create([
            'amount' => '1000000.00',
            'paid_at' => '2026-11-26',
            'deposit_date' => '2026-11-01',
        ]);

        $this->post(route('investments.land.store'), [
            'name' => 'Association field',
            'location' => 'Dhaka',
            'purchase_price' => '750000.00',
            'area' => '4.500',
            'area_unit' => 'decimal',
            'purchased_at' => '2026-11-25',
        ])->assertRedirect(route('investments.land.index'));

        $land = LandInvestment::firstOrFail();
        $this->get(route('investments.land.sell', $land))
            ->assertOk()
            ->assertSee('4.500 decimal');

        $this->post(route('investments.land.sales.store', $land), [
            'area_sold' => '2.000',
            'sale_price' => '400000.00',
            'buyer_name' => 'First buyer',
            'buyer_phone' => '01700000000',
            'sold_at' => '2026-11-26',
            'deed' => UploadedFile::fake()->create('sale-deed.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('investments.land.sell', $land));

        $firstSale = LandSale::firstOrFail();
        Storage::disk('local')->assertExists($firstSale->deed_path);
        $this->get(route('investments.land.sales.deed', $firstSale))->assertOk();
        $this->assertSame(65000000, app(InvestmentSavings::class)->available());

        $this->post(route('investments.land.sales.store', $land), [
            'area_sold' => '2.501',
            'sale_price' => '100000.00',
            'buyer_name' => 'Invalid buyer',
            'sold_at' => '2026-11-27',
        ])->assertSessionHasErrors('area_sold');

        $this->post(route('investments.land.sales.store', $land), [
            'area_sold' => '2.500',
            'sale_price' => '300000.00',
            'buyer_name' => 'Second buyer',
            'sold_at' => '2026-11-27',
        ])->assertRedirect(route('investments.land.sell', $land));

        $this->assertSame(95000000, app(InvestmentSavings::class)->available());
        $this->get(route('investments.land.sell', $land))
            ->assertOk()
            ->assertSee('fully sold', false)
            ->assertSee('First buyer')
            ->assertSee('Second buyer');

        $this->delete(route('investments.land.destroy', $land))
            ->assertSessionHasErrors('land');
    }

    private function validGuarantors(): array
    {
        return [
            [
                'name' => 'Guarantor One',
                'phone' => '01700000001',
                'nid' => '1111111111',
                'address' => 'Dhaka',
                'liability_confirmed' => '1',
            ],
            [
                'name' => 'Guarantor Two',
                'phone' => '01700000002',
                'nid' => '2222222222',
                'address' => 'Chattogram',
                'liability_confirmed' => '1',
            ],
        ];
    }
}
