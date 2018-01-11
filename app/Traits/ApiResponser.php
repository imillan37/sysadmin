<?php 
/*
*	respondera las respuestas de la api
*/ 

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

trait ApiResponser
{
	//contruye respuestas satisfactorias 
	private function successResponse($data,$code)
	{
		return response()->json($data,$code);	 
	}
	// construtye respuestas de error
	protected function errorResponse($message,$code)
	{
		return response()->json(['error' => $message,'code' => $code],$code);
	}

	//mostrar datos
	protected function showAll(Collection $collection,$code = 200)
	{
		return $this->successResponse(['data' => $collection ], $code);
	}

	protected function showOne(Model $instance,$code = 200)
	{
		return $this-> successResponse(['data' => $instance],$code);
	}

}
