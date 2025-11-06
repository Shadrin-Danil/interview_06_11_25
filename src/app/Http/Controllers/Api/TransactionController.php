<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseAPIController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Transaction;
use App\Models\User;

class TransactionController extends BaseAPIController
{
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:255',
        ]);
    }
}
