<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;

class TaskController extends Controller
{
    protected TaskService $taskService;
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        // return response()->json(Task::latest()->get());
        $query = Task::query()->where('user_id', $request->user()->id); // tạo một truy vấn mới cho model Task, chỉ lấy task của user đăng nhập
        //Filter status
        if ($request->filled('status')) { // kiểm tra nếu có tham số 'status' trong yêu cầu và nó không rỗng
            $query->where('status', $request->status); // thêm điều kiện lọc theo trường 'status' với giá trị từ tham số 'status' trong yêu cầu
        }

        // Filter priority
        if ($request->filled('priority')) { // kiểm tra nếu có tham số 'priority' trong yêu cầu và nó không rỗng
            $query->where('priority', $request->priority); // thêm điều kiện lọc theo trường 'priority' với giá trị từ tham số 'priority' trong yêu cầu
        }

        //Search keyword
        if ($request->filled('keyword')) { // kiểm tra nếu có tham số 'keyword' trong yêu cầu và nó không rỗng
            $query->where(function ($q) use ($request) { 
                $q->where('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('description', 'like', '%' . $request->keyword . '%');
             });
        }
        
        // add sorting
        $allowedSorts = ['created_at', 'status', 'priority']; // danh sách các trường được phép sắp xếp
        if ($request->filled('sort_by') && in_array($request->sort_by, $allowedSorts)) { // kiểm tra nếu có tham số 'sort_by' trong yêu cầu và giá trị của nó nằm trong danh sách các trường được phép sắp xếp
            $direction = $request->get('order', 'desc');
            // $query->orderBy($request->sort_by, $direction);
            if (!in_array($direction, ['asc', 'desc'])) { // kiểm tra nếu giá trị của tham số 'order' không phải là 'asc' hoặc 'desc'
                $direction = 'desc'; // nếu không hợp lệ, mặc định sử dụng 'desc'
            }
            $query->orderBy($request->sort_by, $direction); // thêm điều kiện sắp xếp theo trường được chỉ định và hướng sắp xếp đã xác định 

        } else {
            $query->latest(); // nếu không có tham số 'sort_by' hoặc giá trị của nó không hợp lệ, sắp xếp theo trường 'created_at' theo thứ tự giảm dần (mới nhất trước)
        }

        // return response()->json(
        //     $query->get()
        // );
        $task = $query->paginate(10); // phân trang kết quả với 10 mục mỗi trang
        return TaskResource::collection($task);
        // return response()->json($task); // trả về kết quả dưới dạng JSON, bao gồm cả thông tin phân trang như tổng số trang, trang hiện tại, và các liên kết phân trang 
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        // $data = $request->validated(); // lấy dữ liệu đã được xác thực từ StoreTaskRequest
        // $task = Task::create($data); // tạo một task mới với dữ kiệu đã được xác thực và lưu vào cơ sở dữ liệu

        $task = $this->taskService->create(
            $request->validated(),
            $request->user()->id
            ); // sử dụng phương thức create của TaskService để tạo một task mới với dữ liệu đã được xác thực từ StoreTaskRequest 
            // và lưu vào cơ sở dữ liệu. Tạo task mới và gán user hiện tại.
        
        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201); // trả về một instance của TaskResource, truyền vào task đã được tạo. TaskResource sẽ định dạng dữ liệu của task theo cách mà bạn đã định nghĩa trong phương thức toArray() của nó và trả về dưới dạng JSON khi được trả về trong response. Đồng thời, thiết lập mã trạng thái HTTP là 201 (Created) để chỉ ra rằng một tài nguyên mới đã được tạo thành công.

        // return response()->json($task, 201); // trả về task đã được tạo dưới dạng Json với mã trạng thái HTTP 201 (Created)
    }

    // public function store(Request $request)
    // {
    //     //
    //     $data = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'status' => 'required|string|in:todo,in_progress,done',
    //         'priority' => 'required|string|in:low,medium,high',
    //         'due_date' => 'nullable|date',
    //     ]);
    //     $task = Task::create($data); // tạo một task mới với dữ liệu đã được xác thực và lưu vào cơ sở dữ liệu
    //     return response()->json($task, 201);

    // }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task)
    {
        //
        // return response()->json($task);
        $this->authorizeTaskOwnership($request, $task);
        return new TaskResource($task); // trả về một instance của TaskResource, truyền vào task cần hiển thị. TaskResource sẽ định dạng dữ liệu của task theo cách mà bạn đã định nghĩa trong phương thức toArray() của nó và trả về dưới dạng JSON khi được trả về trong response.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        // $data = $request->validated(); // lấy dữ liệu đã được xác thực từ UpdateTaskRequest
        // $task->update($data); // cập nhật task với dữ liệu đã được xác thực

        $task = $this->taskService->update($task, $request->validated()); // sử dụng phương thức update của TaskService để cập nhật task với dữ liệu đã được xác thực từ UpdateTaskRequest và lưu vào cơ sở dữ liệu.
        return new TaskResource($task); // trả về một instance của TaskResource, truyền vào task đã được cập nhật. TaskResource sẽ định dạng dữ liệu của task theo cách mà bạn đã định nghĩa trong phương thức toArray() của nó và trả về dưới dạng JSON khi được trả về trong response.
        
        // return response()->json($task); // trả về task đã được cập nhật dưới dạng JSON
    }
    // public function update(Request $request, Task $task)
    // {
    //     //
    //     $data = $request->validate([ // sử dụng 'sometimes' để cho phép cập nhật một số trường mà không cần phải cung cấp tất cả
    //         'title' => 'sometimes|required|string|max:255', // nếu trường 'title' được cung cấp thì nó phải là một chuỗi và có độ dài tối đa 255 ký tự
    //         'description' => 'sometimes|required|string', // nếu trường 'description' được cung cấp thì nó phải là một chuỗi
    //         'status' => 'sometimes|required|string|in:todo,in_progress,done', // nếu trường 'status' được cung cấp thì nó phải là một chuỗi và có giá trị là 'todo', 'in_progress', hoặc 'done'
    //         'priority' => 'sometimes|required|string|in:low,medium,high', // nếu trường 'priority' được cung cấp thì nó phải là một chuỗi và có giá trị là 'low', 'medium', hoặc 'high'
    //         'due_date' => 'nullable|date', // trường 'due_date' có thể null hoặc phải là một ngày hợp lệ
    //     ]);
    //     $task->update($data); // cập nhật task với dữ liệu đã được xác thực
    //     return response()->json($task); // trả về task đã được cập nhật dưới dạng JSON
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        // $task->delete();
        $this->authorizeTaskOwnership($request, $task);
        $this->taskService->delete($task); // sử dụng phương thức delete của TaskService để xóa task khỏi cơ sở dữ liệu
        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    private function authorizeTaskOwnership(Request $request, Task $task):void {
        $ownedTask = $request->user()
            ->tasks()
            ->whereKey($task->id)
            ->exists();

        abort_unless($ownedTask, 403, 'You do not have permission to access this task.');
    }
}
