<?php

namespace App\Http\Controllers;
use App\Project;
use App\Task;

use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function index(){
      $projects = auth()->user()->projects;
      return view('projects.index', compact('projects'));
    }

    public function store(){
      //dd('No no no.Im clear consuela');
        $attributes = request()->validate([
          'title'=>'required',
          'description'=>'required',
          'notes'=>'min:3'
        ]);

        //dd($attributes);

        //$attributes['owner_id'] = auth()->id();

        $project = auth()->user()->projects()->create($attributes);

        return redirect($project->path());

    }

    public function show(Project $project){

        if(auth()->user()->isNot($project->owner)){
          abort(403);
        }

        return view('projects.show', compact('project'));

    }

    public function create(){

      return view('projects.create');

    }

    public function update(Project $project, Task $task){

      if(auth()->user()->isNot($project->owner)){
        abort(403);
      }

      $project->update([
        'notes' => request('notes')
      ]);

      return view($project->path());

    }



}
