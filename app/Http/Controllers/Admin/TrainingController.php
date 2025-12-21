<?php

namespace App\Http\Controllers\Admin;
use App\Models\TrainingProgram;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\Training; 
use App\Models\User;

class TrainingController extends Controller
{


//API

public function myApiExport()
{
   
    $trainings = TrainingProgram::select('id', 'title', 'description', 'venue', 'start_time', 'end_time', 'status')
                                ->where('status', 'Active') 
                                ->get();

    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toDateTimeString(),
        'data' => $trainings
    ]);
}
    
       //===========================================================================================
    private function isAdmin() {
        $user = User::where('user_id', session('user_id'))->first();
        return $user && $user->role === 'Admin';
    }

       //===========================================================================================

    public function index() {
        return view('admin.training.index', [
            'trainings' => Training::getAllTrainings(),
            'isAdmin' => $this->isAdmin()
        ]);
    }

       //===========================================================================================

    public function create() {
        if (!$this->isAdmin()) abort(403);
        return view('admin.training.create');
    }

       //===========================================================================================

    public function store(Request $request) {
        Training::createTraining($request->all());
        return redirect()->route('training.index')->with('success', 'Training Program Created Successfully!');
    }

       //===========================================================================================

    public function show($id) {
        return view('admin.training.show', [
            'training' => Training::getTrainingDetails($id),
            'staffList' => User::where('role', 'Staff')->get(),
            'isAdmin' => $this->isAdmin()
        ]);
    }

       //===========================================================================================

    public function assign(Request $request, $id) {
        try {
            Training::assignStaff($id, $request->user_id);
            return redirect()->route('training.show', $id)->with('success', 'Staff assigned successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

       //===========================================================================================

    public function assignPage($id) {
        return view('admin.training.assign', [
            'training' => Training::getTrainingDetails($id),
            'staffList' => User::where('role', 'Staff')->get()
        ]);
    }

       //===========================================================================================

    public function updateStatus(Request $request, $id, $userId) {
        Training::updateParticipantStatus($id, $userId, $request->all());
        return back()->with('success', 'Participant Status Updated!');
    }

       //===========================================================================================

    public function storeFeedback(Request $request, $id) {
        Training::submitFeedback($id, session('user_id'), $request->all());
        return back()->with('success', 'Thank you for your feedback!');
    }

       //===========================================================================================



public function destroy($id)
{
    try {
      
        Training::deleteTraining($id);
        return redirect()->route('training.index')->with('success', 'Training program deleted successfully.');
    } catch (\Exception $e) {
       
        return redirect()->back()->with('error', $e->getMessage());
    }
}





public function activate(Request $request, $id) {
  
    $program = TrainingProgram::findOrFail($id);
    $program->status = ($program->status === 'Active') ? 'Inactive' : 'Active';
    $program->save();

    return back()->with('success', 'Status updated!');
}
       //===========================================================================================

    public function edit($id) {
        return view('admin.training.edit', [
            'training' => Training::getTrainingDetails($id)
        ]);
    }

       //===========================================================================================

    public function update(Request $request, $id) {
        try {
            Training::updateTraining($id, $request->all());
            return redirect()->route('training.show', $id)->with('success', 'Updated!');
        } catch (\Exception $e) {
            return back()->withErrors(['capacity' => $e->getMessage()])->withInput();
        }
    }

       //===========================================================================================

public function records(Request $request)
{

    $staffList = Training::getAllStaffForRecords();

    $selectedUser = null;
    if ($request->filled('user_id')) {
  
        $selectedUser = Training::getStaffTrainingHistory($request->user_id);
    }

    return view('admin.training.records', compact('staffList', 'selectedUser'));
}

       //===========================================================================================

    public function detachParticipant($id, $userId) {
        Training::removeStaff($id, $userId);
        return redirect()->back()->with('success', 'Staff removed successfully.');
    }

       //===========================================================================================

    
}