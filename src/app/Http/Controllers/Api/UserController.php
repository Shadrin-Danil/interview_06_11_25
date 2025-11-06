<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\api\BaseAPIController;
use App\Models\User; 

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class UserController extends BaseAPIController
{
    public function balance(Request $request) {
        $validated = Validator::make($request->all(), [
            'user_id' => 'required',
            // 'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'user_id обязателен',
            // 'user_id.exists' => 'такого user_id нет',
        ]);

        if ($validated->fails()) {
            return $this->sendError('Ошибка валидации', ['error' => $validated->errors()], 422);
        }

        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return $this->sendError('Пользователь не найден', ['user_id' => $userId], 404);
        }
      
        return $this->sendResponse(
            ['balance' => $user->balance ?? 0],
            'Баланс пользователя успешно получен.'
        );
    }
    
}
