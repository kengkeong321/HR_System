<?php

namespace App\Services;

use App\Models\TrainingAttendance;
use Illuminate\Support\Facades\Auth;
use App\Models\TrainingFeedback;
use Illuminate\Http\Request;

class StaffTrainingService
{

//==========================================================================================

    public function getMyTrainings()
    {
    
        return TrainingAttendance::where('user_id', Auth::id())
            ->with('trainingProgram')
            ->get();
    }


//==========================================================================================

    public function index()
    {
        $trainings = StaffTrainingService::getMyTrainings();
        return view('staff.trainings.index', compact('trainings'));
    }

  //==========================================================================================

  
    public function storeFeedback(Request $request)
    {
        
        $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        
        TrainingFeedback::create([
            'user_id' => Auth::id(),
            'training_program_id' => $request->training_program_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);


        return redirect()->back()->with('success', 'Feedback submitted successfully!');
    }
}