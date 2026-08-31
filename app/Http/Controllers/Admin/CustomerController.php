<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $filter  = $request->input('filter', 'active');
        $sortBy  = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['name', 'email', 'phone', 'company']);

        try {
            $query = match ($filter) {
                'deleted' => Customer::onlyTrashed(),
                'all'     => Customer::withTrashed(),
                default   => Customer::query(),
            };

            $customers = $query
                ->filter($request)
                ->orderBy($sortBy, $sortDir)
                ->paginate($perPage)
                ->withQueryString();

            return Inertia::render('Customers/Index', [
                'customers' => $customers,
                'filter'    => $filter,
                'filters'   => $filters,
                'sort_by'   => $sortBy,
                'sort_dir'  => $sortDir,
                'per_page'  => $perPage,
            ]);
        } catch (Throwable $e) {
            report($e);

            return Inertia::render('Customers/Index', [
                'customers' => null,
                'filter'    => $filter,
                'filters'   => $filters,
                'sort_by'   => $sortBy,
                'sort_dir'  => $sortDir,
                'error'     => 'No se pudieron cargar los clientes. Intenta de nuevo.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        Customer::create($validated);

        return redirect()->back()->with('success', 'Cliente creado exitosamente.');
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id . ',id',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->back()->with('success', 'Cliente eliminado exitosamente.');
    }
}
