<?php

namespace App\Http\Controllers;

use App\Models\LandInvestment;
use App\Models\LandSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\InvestmentSavings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class LandInvestmentController extends Controller
{
    public function index()
    {
        $lands = LandInvestment::withSum('sales', 'area_sold')
            ->withSum('sales', 'sale_price')
            ->latest('purchased_at')
            ->paginate(10);

        return view('investements.land.index', compact('lands'));
    }

    public function create()
    {
        return view('investements.land.create');
    }

    public function store(Request $request, InvestmentSavings $savings)
    {
        $validated = $request->validate($this->rules());
        unset($validated['deed']);
        $amount = $savings->toMinorUnits((string) $validated['purchase_price']);
        if ($amount > $savings->available()) {
            throw ValidationException::withMessages([
                'purchase_price' => 'Insufficient savings. Available balance: ' . $savings->fromMinorUnits($savings->available()) . '.',
            ]);
        }
        $land = LandInvestment::create($validated);

        if ($request->hasFile('deed')) {
            $land->deed_path = $request->file('deed')->store('land-deeds', 'local');
            $land->save();
        }

        return redirect()->route('investments.land.index')->with('success', 'Land investment recorded successfully.');
    }

    public function edit(LandInvestment $land)
    {
        return view('investements.land.edit', compact('land'));
    }

    public function update(Request $request, LandInvestment $land, InvestmentSavings $savings)
    {
        $validated = $request->validate($this->rules());
        unset($validated['deed']);
        $soldAreaMilliunits = $this->areaToMilliunits((string) $land->sales()->sum('area_sold'));
        if ($this->areaToMilliunits((string) $validated['area']) < $soldAreaMilliunits) {
            throw ValidationException::withMessages([
                'area' => 'Land area cannot be less than the area already sold.',
            ]);
        }
        $oldAmount = $savings->toMinorUnits((string) $land->purchase_price);
        $newAmount = $savings->toMinorUnits((string) $validated['purchase_price']);
        if ($newAmount > $savings->availableAfterRemovingInvestments($oldAmount)) {
            throw ValidationException::withMessages([
                'purchase_price' => 'Insufficient savings. Available balance: ' . $savings->fromMinorUnits($savings->availableAfterRemovingInvestments($oldAmount)) . '.',
            ]);
        }
        $land->update($validated);

        if ($request->hasFile('deed')) {
            $newPath = $request->file('deed')->store('land-deeds', 'local');
            if ($land->deed_path) {
                Storage::disk('local')->delete($land->deed_path);
            }
            $land->deed_path = $newPath;
            $land->save();
        }

        return redirect()->route('investments.land.index')->with('success', 'Land investment updated successfully.');
    }

    public function destroy(LandInvestment $land)
    {
        if ($land->sales()->exists()) {
            throw ValidationException::withMessages([
                'land' => 'A land record with completed sales cannot be deleted.',
            ]);
        }
        if ($land->deed_path) {
            Storage::disk('local')->delete($land->deed_path);
        }
        $land->delete();

        return redirect()->route('investments.land.index')->with('success', 'Land investment deleted successfully.');
    }

    public function deed(LandInvestment $land)
    {
        abort_unless($land->deed_path && Storage::disk('local')->exists($land->deed_path), 404);

        return Storage::disk('local')->download(
            $land->deed_path,
            'land-deed-' . $land->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function sell(LandInvestment $land)
    {
        $land->load(['sales']);
        $areaRemaining = max(
            0,
            $this->areaToMilliunits((string) $land->area)
                - $this->areaToMilliunits((string) $land->sales->sum('area_sold'))
        );

        return view('investements.land.sell', compact('land', 'areaRemaining'));
    }

    public function storeSale(Request $request, LandInvestment $land)
    {
        $validated = $request->validate([
            'area_sold' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'sale_price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'buyer_name' => ['required', 'string', 'max:150'],
            'buyer_phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]+$/'],
            'sold_at' => ['required', 'date'],
            'deed' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        $file = $request->file('deed');
        unset($validated['deed']);
        $storedPath = null;

        try {
            DB::transaction(function () use ($land, $validated, $file, &$storedPath) {
                $lockedLand = LandInvestment::query()->lockForUpdate()->findOrFail($land->id);
                $totalArea = $this->areaToMilliunits((string) $lockedLand->area);
                $soldArea = $this->areaToMilliunits((string) $lockedLand->sales()->sum('area_sold'));
                $saleArea = $this->areaToMilliunits((string) $validated['area_sold']);

                if ($saleArea > $totalArea - $soldArea) {
                    throw ValidationException::withMessages([
                        'area_sold' => 'Sale area exceeds the remaining land area of '
                            . number_format(($totalArea - $soldArea) / 1000, 3, '.', '')
                            . ' ' . $lockedLand->area_unit . '.',
                    ]);
                }

                $attributes = $validated;
                if ($file) {
                    $storedPath = $file->store('land-sale-deeds', 'local');
                    if (!$storedPath) {
                        throw new \RuntimeException('Unable to store the land sale deed.');
                    }
                    $attributes['deed_path'] = $storedPath;
                    $attributes['deed_name'] = substr($file->getClientOriginalName(), 0, 255);
                }
                $lockedLand->sales()->create($attributes);
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $exception;
        }

        return redirect()->route('investments.land.sell', $land)->with('success', 'Land sale recorded successfully.');
    }

    public function saleDeed(LandSale $sale)
    {
        abort_unless($sale->deed_path && Storage::disk('local')->exists($sale->deed_path), 404);

        return Storage::disk('local')->download(
            $sale->deed_path,
            $sale->deed_name ?: basename($sale->deed_path)
        );
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:2000'],
            'purchase_price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'area' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'area_unit' => ['required', 'string', 'max:30'],
            'purchased_at' => ['required', 'date'],
            'deed' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    private function areaToMilliunits(string $area): int
    {
        [$whole, $fraction] = array_pad(explode('.', $area, 2), 2, '');

        return ((int) $whole * 1000) + (int) substr(str_pad($fraction, 3, '0'), 0, 3);
    }
}
