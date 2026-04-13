<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // sử dụng Route để định nghĩa các tuyến đường cho API
use App\Http\Controllers\AuthController; // sử dụng AuthController để xử lý các yêu cầu liên quan đến xác thực người dùng

Route::post('/register', [AuthController::class, 'register']); // định nghĩa tuyến đường POST /api/register để đăng ký người dùng mới và liên kết nó với phương thức 'register' trong AuthController
Route::post('/login', [AuthController::class, 'login']); // định nghĩa tuyến đường POST /api/login để đăng nhập người dùng và liên kết nó với phương thức 'login' trong AuthController

Route::middleware('auth:sanctum')->group(function () { // nhóm các tuyến đường cần xác thực bằng Sanctum
    Route::post('/logout', [AuthController::class, 'logout']); // định nghĩa tuyến đường POST /api/logout để đăng xuất người dùng và liên kết nó với phương thức 'logout' trong AuthController
});

Route::get('/user', function (Request $request) { // trả về thông tin người dùng đã xác thực khi truy cập vào endpoint /api/user
    return $request->user(); // trả về thông tin người dùng đã xác thực dưới dạng JSON

})->middleware('auth:sanctum'); // sử dụng middleware 'auth:sanctum' để bảo vệ endpoint này, chỉ cho phép truy cập nếu người dùng đã xác thực bằng Sanctum

Route::apiResource('tasks', TaskController::class); // định nghĩa các tuyến đường RESTful cho tài nguyên 'tasks' và liên kết chúng với TaskController, tự động tạo ra các tuyến đường như GET /api/tasks, POST /api/tasks, GET /api/tasks/{id}, PUT/PATCH /api/tasks/{id}, DELETE /api/tasks/{id}
