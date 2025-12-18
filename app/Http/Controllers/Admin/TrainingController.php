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
        
      
        $user = User::where('user_id', $userId)->first();
        
        return $user && $user->role === 'Admin';
    }

   public function index()
{
   
    $trainings = TrainingProgram::with('participants')->get();
    
    $isAdmin = $this->isAdmin();
    
  
    return view('admin.training.index', compact('trainings', 'isAdmin'));
}




//==========================================================================================================================

    public function create()
    {
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized action. Only Admins can create trainings.');
        }
        return view('admin.training.create');
    }

    public function store(Request $request)
{
    if (!$this->isAdmin()) {
        abort(403, 'Unauthorized.');
    }

    $validated = $request->validate([
        'title'       => 'required|string|max:255',
        'venue'       => 'required|string|max:255',
        'capacity'    => 'required|integer|min:1', 
        'start_time'  => 'required|date',
        'end_time'    => 'required|date|after:start_time',
        'description' => 'nullable|string|max:1000',
    ]);

    
    TrainingProgram::create($validated);

    return redirect()->route('training.index')->with('success', 'Training Program Created Successfully!');
}


//==========================================================================================================================




public function show($id)
{
    
    $training = TrainingProgram::with(['participants', 'feedbacks.user'])
        ->withCount('participants')
        ->findOrFail($id);
    
    $staffList = User::where('role', 'Staff')->get();
    $isAdmin = $this->isAdmin(); 

    return view('admin.training.show', compact('training', 'staffList', 'isAdmin'));
}



//==========================================================================================================================


public function assign(Request $request, $id)
{
   
    $request->validate([
        'user_id' => 'required',
    ]);

    $training = TrainingProgram::findOrFail($id);
    $userId = $request->user_id;

    $isAlreadyAssigned = $training->participants()->where('user.user_id', $userId)->exists();

    if ($isAlreadyAssigned) {
        return redirect()->back()->with('error', 'This staff is already on the training list.');
    }

   
    if (!is_null($training->capacity)) {
        $currentCount = $training->participants()->count();
        if ($currentCount >= $training->capacity) {
            return redirect()->back()->with('error', 'Assignment failed: Training slots are full!');
        }
    }


    $training->participants()->attach($userId, ['status' => 'Assigned']);

    
    return redirect()->route('training.show', $id)->with('success', 'Staff assigned successfully！');
}



//==========================================================================================================================



    public function assignPage($id)
    {
        $training = TrainingProgram::with('participants')->findOrFail($id);
        $staffList = User::where('role', 'Staff')->get();
        
        return view('admin.training.assign', compact('training', 'staffList'));
    }



//==========================================================================================================================


   public function updateStatus(Request $request, $id, $userId)
{
    if (!$this->isAdmin()) {
        abort(403, 'Unauthorized.');
    }

    $request->validate([
        'status' => 'required|in:Assigned,Attended,Missed,Completed',
        'reason' => 'nullable|string|max:255' 
    ]);

    $training = TrainingProgram::findOrFail($id);

 
    $reason = ($request->status === 'Missed') ? $request->reason : null;

    $training->participants()->updateExistingPivot($userId, [
        'status' => $request->status,
        'reason' => $reason 
    ]);

    return back()->with('success', 'Participant Status Updated!');
}


//==========================================================================================================================



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



//==========================================================================================================================




    public function destroy($id)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized.');
        }
        
        $training = TrainingProgram::findOrFail($id);
        $training->delete();
        
        return redirect()->route('training.index')->with('success', 'Training Deleted.');
    }


//==========================================================================================================================




    public function edit($id)
    {
        $training = TrainingProgram::findOrFail($id);
        return view('admin.training.edit', compact('training'));
    }



//==========================================================================================================================






public function update(Request $request, $id)
{
   
    $request->validate([
        'title'      => 'required|string|max:255',
        'venue'      => 'required|string|max:255',
        'capacity'   => 'required|integer|min:1', 
        'start_time' => 'required|date',
        'end_time'   => 'required|date|after:start_time', 
    ]);

    $training = TrainingProgram::findOrFail($id);

   
    $currentCount = $training->participants()->count(); 
    if ($request->capacity < $currentCount) {
        return back()
            ->withErrors(['capacity' => "Currently, {$currentCount} people have signed up. The maximum cannot be lower than the current number!"])
            ->withInput(); 
    }
    
    $training->update([
        'title'      => $request->title,
        'venue'      => $request->venue,
        'capacity'   => $request->capacity, 
        'start_time' => $request->start_time,
        'end_time'   => $request->end_time,
        'description'=> $request->description,
    ]);

    return redirect()->route('training.show', $id)->with('success', 'Updated!');
}




//==========================================================================================================================





    public function records(Request $request)
    {
        $staffList = User::where('role', 'Staff')->get();
        $selectedUser = null;

        if ($request->isMethod('post') && $request->user_id) {
            $selectedUser = User::with('trainings')->find($request->user_id);
        }

        return view('training.records', compact('staffList', 'selectedUser'));
    }



//==========================================================================================================================




    public function detachParticipant($id, $userId)
    {
       
        $training = TrainingProgram::findOrFail($id);
        
       
        $training->participants()->detach($userId);

     
        return redirect()->back()->with('success', 'Staff removed successfully.');
    }
}