<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getAll()
    {
        return $this->userService->getAllUsers();
    }

    public function getById($id)
    {
        return $this->userService->getUserById($id);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        return $this->userService->update($request, $id);
    }

    public function softDelete($id)
    {
        return $this->userService->softDelete($id);
    }
}
