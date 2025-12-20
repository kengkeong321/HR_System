<?php

namespace App\Services;

use App\Models\TrainingProgram;
use App\Models\TrainingFeedback;
use App\Models\TrainingAttendance;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class TrainingService
{

    private function checkAdmin()
    {
        $userId = Session::get('user_id');
        $user = User::where('user_id', $userId)->first();
        if (!$user || $user->role !== 'Admin') {
            abort(403, 'Unauthorized action. Only Admins can perform this.');
        }
    }

    //====================================================================================================
    public function getAllTrainings()
    {
        return TrainingProgram::with('participants')->get();
    }


    //====================================================================================================

    public function getTrainingDetails($id)
    {
       return TrainingProgram::with([
        'participants.staffRecord', 
        'feedbacks.user.staffRecord'
    ])
    ->withCount('participants')
    ->findOrFail($id);
    }


    //====================================================================================================
  
    public function createTraining($data)
    {
        $this->checkAdmin();
        
        Validator::make($data, [
            'title'       => 'required|string|max:255',
            'venue'       => 'required|string|max:255',
            'capacity'    => 'required|integer|min:1',
            'start_time'  => 'required|date',
            'end_time'    => 'required|date|after:start_time',
            'description' => 'nullable|string|max:1000',
        ])->validate();

        return TrainingProgram::create($data);
    }


    //====================================================================================================
 
    public function updateTraining($id, $data)
    {
        $this->checkAdmin();
        $training = TrainingProgram::findOrFail($id);

        Validator::make($data, [
            'title'      => 'required|string|max:255',
            'venue'      => 'required|string|max:255',
            'capacity'   => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ])->validate();

        $currentCount = $training->participants()->count();
        if ($data['capacity'] < $currentCount) {
            throw new \Exception("Currently, {$currentCount} people have signed up. Capacity cannot be lower than this!");
        }

        return $training->update($data);
    }

    
//====================================================================================================




public function assignStaff($trainingId, $userId)
{
    
    $userExists = \App\Models\User::where('user_id', $userId)->exists();
    if (!$userExists) {
        throw new \Exception('Invalid Data: The selected Staff ID does not exist in our records.');
    }

    
    $newTraining = TrainingProgram::findOrFail($trainingId);

    $newStart = $newTraining->start_time;
    $newEnd   = $newTraining->end_time;

    if (!$newStart || !$newEnd) {
        throw new \Exception('Error: This training program does not have valid start/end times.');
    }

 
    if ($newTraining->participants()->where('training_attendance.user_id', $userId)->exists()) {
        throw new \Exception('This staff is already on the training list.');
    }

   
    if (!is_null($newTraining->capacity) && $newTraining->participants()->count() >= $newTraining->capacity) {
        throw new \Exception('Training slots are full!');
    }

   
    $hasConflict = TrainingAttendance::where('training_attendance.user_id', $userId)
        ->join('training_programs', 'training_attendance.training_program_id', '=', 'training_programs.id')
        ->where('training_programs.status', '!=', 'Ended') 
        ->where(function ($query) use ($newStart, $newEnd) {
            $query->where('training_programs.start_time', '<', $newEnd)
                  ->where('training_programs.end_time', '>', $newStart);
        })->exists();

    if ($hasConflict) {
        throw new \Exception('Time Conflict: This staff is already assigned to another ACTIVE program at this time.');
    }

   
    return $newTraining->participants()->attach($userId, ['status' => 'Assigned']);
}


 
//====================================================================================================




public function updateParticipantStatus($trainingId, $userId, $data)
{
    $this->checkAdmin();

    Validator::make($data, [
        'status' => 'required|in:Assigned,Attended,Missed', 
    ])->validate();

    $training = TrainingProgram::findOrFail($trainingId);

    return $training->participants()->updateExistingPivot($userId, [
        'status' => $data['status'],
    ]);
}


//====================================================================================================


   
    public function submitFeedback($trainingId, $userId, $data)
    {
        Validator::make($data, [
            'comments' => 'required|string|max:2000',
            'rating'   => 'required|integer|min:1|max:5',
        ])->validate();

        return TrainingFeedback::create([
            'training_program_id' => $trainingId,
            'user_id'             => $userId,
            'comments'            => $data['comments'],
            'rating'              => $data['rating']
        ]);
    }



//====================================================================================================



public function deleteTraining($id)
{
    $this->checkAdmin();
    $training = TrainingProgram::findOrFail($id);

    if ($training->participants()->count() === 0) {
        return $training->delete();
    }

    return $training->update(['status' => 'Ended']);
}




public function activateTraining($id)
{
    $this->checkAdmin(); 
    $training = TrainingProgram::findOrFail($id);
    

    return $training->update(['status' => 'Active']);
}


//====================================================================================================

public function getAllStaffForRecords()
{
  
    return User::where('role', 'Staff')
        ->with('staffRecord') 
        ->get();
}


//====================================================================================================


public function getStaffTrainingHistory($userId)
{
  
    return User::with(['trainings' => function($query) {
        $query->withPivot('status');
    }])->findOrFail($userId);
}




//====================================================================================================


public function removeStaff($trainingId, $userId)
{
    $this->checkAdmin();
    return TrainingProgram::findOrFail($trainingId)->participants()->detach($userId);
}
}