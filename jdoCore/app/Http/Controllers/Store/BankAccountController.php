<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = auth()->user()->bankAccounts()->latest()->get();
        return view('theme::account.bank', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
        ]);

        $data = $request->all();
        $user = auth()->user();

        if ($user->bankAccounts()->count() === 0) {
            $data['is_main'] = true;
        }

        $user->bankAccounts()->create($data);

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(Request $request, BankAccount $account)
    {
        if ($account->user_id !== auth()->id())
            abort(403);

        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
        ]);

        $account->update($request->all());

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(BankAccount $account)
    {
        if ($account->user_id !== auth()->id())
            abort(403);

        $account->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }

    public function setDefault(BankAccount $account)
    {
        if ($account->user_id !== auth()->id())
            abort(403);

        auth()->user()->bankAccounts()->update(['is_main' => false]);
        $account->update(['is_main' => true]);

        return back()->with('success', 'Rekening utama berhasil diubah.');
    }
}
