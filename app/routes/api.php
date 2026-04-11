<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // sử dụng Route để định nghĩa các tuyến đường cho API

Route::get('/user', function (Request $request) { // trả về thông tin người dùng đã xác thực khi truy cập vào endpoint /api/user
    return $request->user(); // trả về thông tin người dùng đã xác thực dưới dạng JSON

})->middleware('auth:sanctum'); // sử dụng middleware 'auth:sanctum' để bảo vệ endpoint này, chỉ cho phép truy cập nếu người dùng đã xác thực bằng Sanctum

Route::apiResource('tasks', TaskController::class); // định nghĩa các tuyến đường RESTful cho tài nguyên 'tasks' và liên kết chúng với TaskController, tự động tạo ra các tuyến đường như GET /api/tasks, POST /api/tasks, GET /api/tasks/{id}, PUT/PATCH /api/tasks/{id}, DELETE /api/tasks/{id}
