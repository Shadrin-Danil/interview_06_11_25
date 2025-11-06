<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseAPIController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Transaction;

class TransactionController extends BaseAPIController
{
    public function deposit(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'user_id'   => 'required|integer|min:1',
            'amount'    => 'required|numeric|min:0.01',
            'comment'   => 'nullable|string|max:255',
        ], [
            'user_id.required'  => 'user_id обязателен',
            'user_id.integer'   => 'user_id должен быть числом',
            'user_id.min'       => 'user_id должен быть положительным',

            'amount.required'   => 'amount обязателен',
            'amount.numeric'    => 'amount должен быть числом',
            'amount.min'        => 'amount не должен быть менее 0.01',
            
            'comment.string'    => 'comment должен быть строкой', 
            'comment.max'       => 'comment не должен быть длиннее 256 символов', 
        ]);

        if ($validated->fails()) {
            return $this->sendError('Ошибка валидации', ['error' => $validated->errors()], 422);
        }

        $userId = (int)$request->input('user_id');
        $amount = (float)$request->input('amount');
        $comment = $request->input('comment');

        DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::firstOrCreate(
                ['id' => $userId],
                ['balance' => 0.00]
            );
            
            $user->increment('balance', $amount);

            Transaction::create([
                    'receiver_id' => $user->id,
                    'type'        => "deposit",
                    'amount'      => $amount,
                    'comment'     => $comment,
                ]);
        });

        $user = User::find($userId);
        return $this->sendResponse(
            [
                'user_id' => $userId, 
                'balance' => $user->fresh()->balance
            ],
            'Баланс пользователя успешно пополнен'
        );

    }

    public function withdraw(Request $request) {
        $validated = Validator::make($request->all(), [
            'user_id'   => 'required|integer|min:1',
            'amount'    => 'required|numeric|min:0.01',
            'comment'   => 'nullable|string|max:255',
        ], [
            'user_id.required'  => 'user_id обязателен',
            'user_id.integer'   => 'user_id должен быть числом',
            'user_id.min'       => 'user_id должен быть положительным',

            'amount.required'   => 'amount обязателен',
            'amount.numeric'    => 'amount должен быть числом',
            'amount.min'        => 'amount не должен быть менее 0.01',
            
            'comment.string'    => 'comment должен быть строкой', 
            'comment.max'       => 'comment не должен быть длиннее 256 символов', 
        ]);

        if ($validated->fails()) {
            return $this->sendError('Ошибка валидации', ['error' => $validated->errors()], 422);
        }

        $amount = (float)$request->input('amount');
        $comment = $request->input('comment');
        $userId = (int)$request->input('user_id');
    
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::lockForUpdate()->find($userId);
            if (!$user) {
                return $this->sendError('Пользователь не найден', ['user_id' => $userId], 404);
            }
            if ($user->balance < $amount) {
                return $this->sendError('Недостаточно средств', [
                    'user_id' => $userId,
                    'balance' => $user->balance,
                ], 409);
            }
            $user->decrement('balance', $amount);

            Transaction::create([
                    'sender_id' => $user->id,
                    'type'        => "withdraw",
                    'amount'      => $amount,
                    'comment'     => $comment,
            ]);

            return $this->sendResponse(
                [
                    'user_id' => $userId, 
                    'balance' => $user->fresh()->balance,
                ],
                'Списание прошло успешно'
            );
        });
    }   

    public function transfer(Request $request) {
        $validated = Validator::make($request->all(), [
                'from_user_id'     => 'required|integer|min:1',
                'to_user_id'   => 'required|integer|min:1',
                'amount'        => 'required|numeric|min:0.01',
                'comment'       => 'nullable|string|max:255',
            ], [
                'from_user_id.required'  => 'from_user_id обязателен',
                'from_user_id.integer'   => 'from_user_id должен быть числом',
                'from_user_id.min'       => 'from_user_id должен быть положительным',
                
                'to_user_id.required'  => 'to_user_id обязателен',
                'to_user_id.integer'   => 'to_user_id должен быть числом',
                'to_user_id.min'       => 'to_user_id должен быть положительным',

                'amount.required'   => 'amount обязателен',
                'amount.numeric'    => 'amount должен быть числом',
                'amount.min'        => 'amount не должен быть менее 0.01',
                
                'comment.string'    => 'comment должен быть строкой', 
                'comment.max'       => 'comment не должен быть длиннее 256 символов', 
        ]);
        if ($validated->fails()) {
            return $this->sendError('Ошибка валидации', ['error' => $validated->errors()], 422);
        }

        $amount = (float)$request->input('amount');
        $comment = $request->input('comment');
        $sender_id = (int)$request->input(key: 'from_user_id');
        $receiver_id = (int)$request->input(key: 'to_user_id');

        return DB::transaction(function () use ($sender_id, $receiver_id, $amount, $comment) {
            $sender = User::lockForUpdate()->find($sender_id);
            if (!$sender) {
                return $this->sendError('Отправитель не найден', ['from_user_id' => $sender_id], 404);
            }
            if ($sender->balance < $amount) {
                return $this->sendError('Недостаточно средств', [
                    'user_id' => $sender_id,
                    'balance' => $sender->balance,
                ], 409);
            }

            $receiver = User::firstOrCreate(
                ['id' => $receiver_id],
                ['balance' => 0.00]
            );

            $sender->decrement('balance', $amount);
            $receiver->increment('balance', $amount);
            
            Transaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'type'        => "transfer_in",
                    'amount'      => $amount,
                    'comment'     => $comment,
            ]);            
            Transaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'type'        => "transfer_out",
                    'amount'      => $amount,
                    'comment'     => $comment,
            ]);

            return $this->sendResponse(
                [
                    'sender_id' => $sender_id, 
                    'sender_balance' => $sender->fresh()->balance,
                    
                    'receiver_id' => $receiver_id, 
                    'receiver_balance' => $receiver->fresh()->balance,
                ],
                'Перевод успешно выполнен'
            );
        });
    }
}
