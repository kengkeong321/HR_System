<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\TrainingProgram;
use App\Models\TrainingFeedback;
use App\Models\User;
use App\Http\Controllers\Controller;

class TrainingController extends Controller
{
    private function currentUserId() {
        return session('user_id');
    }

    private function isAdmin() {
        $userId = session('user_id');
        if (!$userId) return false;
        
        // 確保 User Model 設定正確 (protected $table = 'user')
        $user = User::where('user_id', $userId)->first();
        
        return $user && $user->role === 'Admin';
    }

    /**
     * Function: View Staff Training Records / Manage Training Programs
     */
    public function index()
    {
        $trainings = TrainingProgram::all();
        $isAdmin = $this->isAdmin();
        
        return view('training.index', compact('trainings', 'isAdmin'));
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized action. Only Admins can create trainings.');
        }
        return view('training.create');
    }

    public function store(Request $request)
{
    if (!$this->isAdmin()) {
        abort(403, 'Unauthorized.');
    }

    // 將所有名稱統一為 'capacity'
    $validated = $request->validate([
        'title'       => 'required|string|max:255',
        'venue'       => 'required|string|max:255',
        'capacity'    => 'required|integer|min:1', // 改回 capacity
        'start_time'  => 'required|date',
        'end_time'    => 'required|date|after:start_time',
        'description' => 'nullable|string|max:1000',
    ]);

    // 因為 $validated 現在包含 'capacity'，資料庫就能正確寫入
    TrainingProgram::create($validated);

    return redirect()->route('training.index')->with('success', 'Training Program Created Successfully!');
}

    public function show($id)
    {
        $training = TrainingProgram::with(['participants', 'feedbacks.user'])->findOrFail($id);
        
        $staffList = User::where('role', 'Staff')->get();
        $isAdmin = $this->isAdmin(); 

        return view('training.show', compact('training', 'staffList', 'isAdmin'));
    }

    /**
     * 關鍵修正：指派員工
     */
    public function assign(Request $request, $id)
    {
        // 1. 驗證：確保檢查的是 'user' 表 (單數)
        $request->validate([
            'user_id' => 'required|exists:user,user_id', 
        ]);

        $training = TrainingProgram::findOrFail($id);

        // 2. 檢查重複指派：使用 'user.user_id' 避免 SQL 錯誤
        if ($training->participants()->where('user.user_id', $request->user_id)->exists()) {
            return redirect()->back()->with('error', 'This staff member is already assigned.');
        }

        // 3. 檢查人數上限：邏輯修正為 >= (大於等於)
    // 檢查人數上限邏輯
if (!is_null($training->capacity)) { // 這裡也要改用 capacity
    $currentCount = $training->participants()->count();
    
    if ($currentCount >= $training->capacity) {
        return redirect()->back()->with('error', 'Training is full!');
    }
}

        // 4. 執行指派
        $training->participants()->attach($request->user_id, ['status' => 'Assigned']);

        return redirect()->back()->with('success', 'Staff assigned successfully.');
    }

    public function assignPage($id)
    {
        $training = TrainingProgram::with('participants')->findOrFail($id);
        $staffList = User::where('role', 'Staff')->get();
        
        return view('training.assign', compact('training', 'staffList'));
    }

    public function updateStatus(Request $request, $id, $userId)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'status' => 'required|in:Assigned,Attended,Missed,Completed'
        ]);

        $training = TrainingProgram::findOrFail($id);

        $training->participants()->updateExistingPivot($userId, [
            'status' => $request->status
        ]);

        return back()->with('success', 'Participant Status Updated!');
    }

    public function storeFeedback(Request $request, $id)
    {
        $validated = $request->validate([
            'comments' => 'required|string|max:2000',
            'rating'   => 'required|integer|min:1|max:5',
        ]);

        TrainingFeedback::create([
            'training_program_id' => $id,
            'user_id'             => $this->currentUserId(), 
            'comments'            => $validated['comments'],
            'rating'              => $validated['rating']
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function destroy($id)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized.');
        }
        
        $training = TrainingProgram::findOrFail($id);
        $training->delete();
        
        return redirect()->route('training.index')->with('success', 'Training Deleted.');
    }

    public function edit($id)
    {
        $training = TrainingProgram::findOrFail($id);
        return view('training.edit', compact('training'));
    }

    /**
     * 關鍵修正：更新培訓資料
     */
public function update(Request $request, $id)
{
    $request->validate([
        'title'      => 'required|string|max:255',
        'venue'      => 'required|string|max:255',
        'capacity'   => 'nullable|integer|min:1', // 這裡用 capacity
        'start_time' => 'required|date',
        'end_time'   => 'required|date|after:start_time', 
    ]);

    $training = TrainingProgram::findOrFail($id);
    
    $training->update([
        'title'      => $request->title,
        'venue'      => $request->venue,
        'capacity'   => $request->capacity, // 這裡確保與資料庫欄位一致
        'start_time' => $request->start_time,
        'end_time'   => $request->end_time,
        'description'=> $request->description,
    ]);

    return redirect()->route('training.show', $id)->with('success', 'Updated!');
}
    public function records(Request $request)
    {
        $staffList = User::where('role', 'Staff')->get();
        $selectedUser = null;

        if ($request->isMethod('post') && $request->user_id) {
            $selectedUser = User::with('trainings')->find($request->user_id);
        }

        return view('training.records', compact('staffList', 'selectedUser'));
    }

    public function detachParticipant($id, $userId)
    {
        // 1. 找到該培訓活動
        $training = TrainingProgram::findOrFail($id);
        
        // 2. 解除關聯 (Detach) 該員工
        $training->participants()->detach($userId);

        // 3. 返回上一頁並顯示訊息
        return redirect()->back()->with('success', 'Staff removed successfully.');
    }
}