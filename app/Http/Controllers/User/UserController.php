<?php

namespace App\Http\Controllers\User;


use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Apicontroller;

class UserController extends Apicontroller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //listar todos los usuarios formato json raiz data
        $usuarios = User::all();
        return $this->showAll($usuarios);
       // return response()->json(['data' => $usuarios],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //reglas de verificacion de datos para user
        $rules=[
            'name'      =>  'required',
            'email'     =>  'required|email|unique:users',
            'password'  =>  'required|min:6|confirmed' 
        ]; 

        $this->validate($request,$rules);
        //crear instancias de usuarios
        $campos = $request->all();
        $campos['password'] = bcrypt($request->password);
        $campos['verified'] = User::USUARIO_NO_VERIFICADO;
        $campos['verification_token'] = User::generarVerificationToken();
        $campos['admin'] = User::USUARIO_REGULAR;

        $usuario = User::create($campos);
        return $this->showOne($usuario,201);
        //return response()->json(['data' => $usuario , 201]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // mostramos un usuario por id
        //$usuario = User::findOrFail($id);
        //return response()->json(['data' => $usuario],200);
        return $this->showOne($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // obtenemos el id del usuario a actualizar 
       // $user = User::findOrFail($id);
        /*
        *   reglas para la actualizacion
        *   
        */
         $rules=[
            'email'     =>  'email|unique:users,email' . $user->id,
            'password'  =>  'min:6|confirmed' ,
            'admin'     =>  'in:' . User::USUARIO_ADMINISTRADOR . ',' . User::USUARIO_REGULAR,
        ]; 
        // si la peticion contiene nombre actualizamos nombre
            if($request->has('name')){
                $user->name = $request->name;
            }

        //si la peticion contiene email y si es diferente al qque ya existe
            if ($request->has('email') && $user->email != $request->email) {

                    $user->verified = User::USUARIO_NO_VERIFICADO;
                    $user->verification_token = User::generarVerificationToken();
                    $user->email = $request->email;
             }
        // si password 
            if ($request->has('password')){
                $user->password = bcrypt($request->password);
            }     
        // convertir usuario a admin 
            /*
            * solo un usuario administrador puede convertir a un usuario normal a administrador 
            * y debera estar verificado
            */    
            if ($request->has('admin')){

                if(!$user->esVerificado())
                {
                    //return response()->json(['error' => 'Error: El usuario no esta verificado','code'=> 409],409);
                    return $this->errorResponse('Error: El usuario no esta verificado',409);
                }

                $user->admin = $request->admin;

            }

            // si el request no trae valores o ninguno es diferente 
            if(!$user->isDirty()){
                //return response()->json(['error' => 'Error: No hay valores para actualizar','code' => 422],422);
                return $this->errorResponse('Error: No hay valores para actualizar',422);
            } 
            //si paso todas las restricciones 
            $user->save();
            //return response()->json(['data' => $user],200);
            return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //verificamos que el usuario existe
        //$user = User::findOrFail($id);
        $user->delete();

        //return response()->json(['data' => $user],200);
        return $this->showOne($user);
    }
}
