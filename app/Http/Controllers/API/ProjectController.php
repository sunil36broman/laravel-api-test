<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Product;
use App\Project;
use Validator;
use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\Project as ProjectResource;
use Illuminate\Support\Facades\Auth;

class ProjectController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::all();

        return $this->sendResponse(ProjectResource::collection($projects), 'Projects retrieved successfully.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
            'description' => 'required'
           // 'user_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (!Auth::user()->is_admin) {
            //Auth::user()->id
            $input['user_id']=Auth::user()->id;
            $project = Project::create($input);

            return $this->sendResponse(new ProjectResource($project), 'Project created successfully without admin.');
        }else{

            if (!$request->has('user_id')){
                $input['user_id']=Auth::user()->id;
            }
            $project = Project::create($input);

            return $this->sendResponse(new ProjectResource($project), 'Project created successfully by admin.');
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::find($id);

        if (is_null($project)) {
            return $this->sendError('Project not found.');
        }

        return $this->sendResponse(new ProjectResource($project), 'Project retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    //public function update(Request $request, $id)
    {
        $input = $request->all();


        $validator = Validator::make($input, [
            'name' => 'required',
            'description' => 'required'
           // 'user_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (!Auth::user()->is_admin) {
            if(Auth::user()->id==$project->user_id){
                if($request->has('user_id')){
                   if($project->user_id !== (int)$input['user_id']){
                        return $this->sendError("only admin can be assign to new user", ['error'=>'Unauthorised']);
                    }
                }

                $project->name = $input['name'];
                $project->description = $input['description'];
                //$project->user_id = Auth::user()->id;
                $project->save();
                return $this->sendResponse(new ProjectResource($project), "Project updated successfully by project owner");

            }else{
                return $this->sendError("only owner or admin can be updated", ['error'=>'Unauthorised']);
            }
        }else{
            $project->name = $input['name'];
            $project->description = $input['description'];
            if ($request->has('user_id')){
               $project->user_id = $input['user_id'];
            }
            $project->save();
            return $this->sendResponse(new ProjectResource($project), "Project updated successfully by by admin ");
        }



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {

        if (Auth::user()->is_admin || Auth::user()->id==$project->user_id ) {
           $project->delete();
            return $this->sendResponse([], 'Project deleted successfully.');
        }else{
            return $this->sendError("without admin/owner can't delete", ['error'=>'Unauthorised']);
        }
    }
}