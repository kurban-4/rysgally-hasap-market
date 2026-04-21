<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index() 
{
    $employees = \App\Models\Employee::all(); 
    
    
    return view('employees.index', compact('employees')); 
}
    public function show($id){
        
        $employee = Employee::where("id",$id)->first();
    
        return view("employees.show",)->with([
            "employee"=> $employee
        ]);
        }

    public function create(){
        return view("employees.create");
    }
    public function store(Request $request){
        Employee::create([
              
              "name"=> $request->name,
              "phone"=> $request->phone,
              "schedule"=> $request->schedule,
              "salary"=> $request->salary,
              "salary"=> $request->position
        ]);
        return to_route("employees.index")->with([
            "success"=> "The employee has been successfully added"
        ]);
    }
}
