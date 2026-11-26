<?php

namespace App\Http\Controllers;

use App\Models\InvestmentCategory;
use App\Models\OtherInvestment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\InvestmentSavings;
use Illuminate\Validation\ValidationException;

class OtherInvestmentController extends Controller
{
    public function index()
    {
        $investments = OtherInvestment::with('category')->latest('invested_at')->paginate(10);

        return view('investements.others.index', compact('investments'));
    }

    public function create()
    {
        $categories = InvestmentCategory::orderBy('name')->get();

        return view('investements.others.create', compact('categories'));
    }

    public function store(Request $request, InvestmentSavings $savings)
    {
        $validated = $request->validate($this->rules());
        $receipt = $request->file('receipt');
        unset($validated['receipt']);
        $amount = $savings->toMinorUnits((string) $validated['amount']);
        if ($amount > $savings->available()) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient savings. Available balance: ' . $savings->fromMinorUnits($savings->available()) . '.',
            ]);
        }
        $category = $this->resolveCategory($validated);

        $storedReceiptPath = null;
        if ($receipt) {
            $storedReceiptPath = $receipt->store('other-investment-receipts', 'local');
            if (!$storedReceiptPath) {
                throw ValidationException::withMessages([
                    'receipt' => 'Unable to store the purchase receipt.',
                ]);
            }
            $validated['receipt_path'] = $storedReceiptPath;
            $validated['receipt_name'] = substr($receipt->getClientOriginalName(), 0, 255);
        }

        try {
            OtherInvestment::create([
                'investment_category_id' => $category->id,
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'details' => $validated['details'] ?? null,
                'receipt_path' => $validated['receipt_path'] ?? null,
                'receipt_name' => $validated['receipt_name'] ?? null,
                'invested_at' => $validated['invested_at'],
            ]);
        } catch (\Throwable $exception) {
            if ($storedReceiptPath) {
                Storage::disk('local')->delete($storedReceiptPath);
            }
            throw $exception;
        }

        return redirect()->route('investments.others.index')->with('success', 'Other investment recorded successfully.');
    }

    public function edit(OtherInvestment $other)
    {
        $categories = InvestmentCategory::orderBy('name')->get();

        return view('investements.others.edit', ['investment' => $other, 'categories' => $categories]);
    }

    public function update(Request $request, OtherInvestment $other, InvestmentSavings $savings)
    {
        $validated = $request->validate($this->rules());
        $receipt = $request->file('receipt');
        unset($validated['receipt']);
        $oldAmount = $savings->toMinorUnits((string) $other->amount);
        $newAmount = $savings->toMinorUnits((string) $validated['amount']);
        if ($newAmount > $savings->availableAfterRemovingInvestments($oldAmount)) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient savings. Available balance: ' . $savings->fromMinorUnits($savings->availableAfterRemovingInvestments($oldAmount)) . '.',
            ]);
        }
        $category = $this->resolveCategory($validated);

        $newReceiptPath = null;
        $oldReceiptPath = $other->receipt_path;
        if ($receipt) {
            $newReceiptPath = $receipt->store('other-investment-receipts', 'local');
            if (!$newReceiptPath) {
                throw ValidationException::withMessages([
                    'receipt' => 'Unable to store the purchase receipt.',
                ]);
            }
            $validated['receipt_path'] = $newReceiptPath;
            $validated['receipt_name'] = substr($receipt->getClientOriginalName(), 0, 255);
        }

        try {
            $other->update([
                'investment_category_id' => $category->id,
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'details' => $validated['details'] ?? null,
                'receipt_path' => $validated['receipt_path'] ?? $other->receipt_path,
                'receipt_name' => $validated['receipt_name'] ?? $other->receipt_name,
                'invested_at' => $validated['invested_at'],
            ]);
        } catch (\Throwable $exception) {
            if ($newReceiptPath) {
                Storage::disk('local')->delete($newReceiptPath);
            }
            throw $exception;
        }

        if ($newReceiptPath && $oldReceiptPath && $oldReceiptPath !== $newReceiptPath) {
            Storage::disk('local')->delete($oldReceiptPath);
        }

        return redirect()->route('investments.others.index')->with('success', 'Other investment updated successfully.');
    }

    public function destroy(OtherInvestment $other)
    {
        if ($other->receipt_path) {
            Storage::disk('local')->delete($other->receipt_path);
        }
        $other->delete();

        return redirect()->route('investments.others.index')->with('success', 'Other investment deleted successfully.');
    }

    public function receipt(OtherInvestment $other)
    {
        abort_unless($other->receipt_path && Storage::disk('local')->exists($other->receipt_path), 404);

        return Storage::disk('local')->download(
            $other->receipt_path,
            $other->receipt_name ?: basename($other->receipt_path)
        );
    }

    private function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'required_without:new_category',
                Rule::exists('investment_categories', 'id'),
            ],
            'new_category' => [
                'nullable',
                'required_without:category_id',
                'string',
                'max:100',
                Rule::unique('investment_categories', 'name'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'details' => ['nullable', 'string', 'max:5000'],
            'invested_at' => ['required', 'date'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:10240'],
        ];
    }

    private function resolveCategory(array $validated): InvestmentCategory
    {
        if (!empty($validated['new_category'])) {
            return InvestmentCategory::create(['name' => $validated['new_category']]);
        }

        return InvestmentCategory::findOrFail($validated['category_id']);
    }
}
