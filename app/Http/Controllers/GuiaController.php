<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guia;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuiaController extends Controller{
    
    public function __construct() {
          $this -> middleware('api.auth',['except' => ['todos','guiasEstado','all','guiasReporte1']]);
    }
   
    
    private function identificate($request) {       
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token, true);
        return $user;    
    }


    
    public function all($vrol,$vid)
    {
               
        try{   
            if($vrol!='CHOFER'){
                $sql=  "SELECT guias.*, 
                        DATE_FORMAT(fecha_el, '%Y-%m-%d') as fecha_el_F,
                        DATE_FORMAT(fecha_el, '%T')       as fecha_el_H,
                        DATE_FORMAT(fecha_ei, '%Y-%m-%d') as fecha_ei_F,
                        DATE_FORMAT(fecha_ei, '%T')       as fecha_ei_H,
                        DATE_FORMAT(fecha_ef, '%Y-%m-%d') as fecha_ef_F,
                        DATE_FORMAT(fecha_ef, '%T')       as fecha_ef_H,
                        DATE_FORMAT(fecha_er, '%Y-%m-%d') as fecha_er_F,
                        DATE_FORMAT(fecha_er, '%T')       as fecha_er_H,
                        DATE_FORMAT(fecha_es, '%Y-%m-%d') as fecha_es_F,
                        DATE_FORMAT(fecha_es, '%T')       as fecha_es_H,
                        DATE_FORMAT(fecha_dl, '%Y-%m-%d') as fecha_dl_F,
                        DATE_FORMAT(fecha_dl, '%T')       as fecha_dl_H,
                        DATE_FORMAT(fecha_di, '%Y-%m-%d') as fecha_di_F,
                        DATE_FORMAT(fecha_di, '%T')       as fecha_di_H,
                        DATE_FORMAT(fecha_df, '%Y-%m-%d') as fecha_df_F,
                        DATE_FORMAT(fecha_df, '%T')       as fecha_df_H,
                        DATE_FORMAT(fecha_ds, '%Y-%m-%d') as fecha_ds_F,
                        DATE_FORMAT(fecha_ds, '%T')       as fecha_ds_H,
                        productos.nombre as NombreProducto,
                        vehiculos.placa as PlacaVehiculo,
                        destinos.nombre as NombreDestino,
                        choferes.nombre as NombreChofer,
                        users.email as NombreUsuario 
                        FROM guias,productos,vehiculos,destinos,choferes,users 
                        WHERE guias.producto_id=productos.id
                        AND guias.vehiculo_id=vehiculos.id
                        AND guias.destino_id=destinos.id
                        AND guias.chofer_id=choferes.id 
                        AND guias.user_id=users.id  
                        ";
                $guias = DB::select($sql);
                
            }else{
                $sql= " SELECT guias.*, 
                        DATE_FORMAT(fecha_el, '%Y-%m-%d') as fecha_el_F,
                        DATE_FORMAT(fecha_el, '%T')       as fecha_el_H,
                        DATE_FORMAT(fecha_ei, '%Y-%m-%d') as fecha_ei_F,
                        DATE_FORMAT(fecha_ei, '%T')       as fecha_ei_H,
                        DATE_FORMAT(fecha_ef, '%Y-%m-%d') as fecha_ef_F,
                        DATE_FORMAT(fecha_ef, '%T')       as fecha_ef_H,
                        DATE_FORMAT(fecha_er, '%Y-%m-%d') as fecha_er_F,
                        DATE_FORMAT(fecha_er, '%T')       as fecha_er_H,
                        DATE_FORMAT(fecha_es, '%Y-%m-%d') as fecha_es_F,
                        DATE_FORMAT(fecha_es, '%T')       as fecha_es_H,
                        DATE_FORMAT(fecha_dl, '%Y-%m-%d') as fecha_dl_F,
                        DATE_FORMAT(fecha_dl, '%T')       as fecha_dl_H,
                        DATE_FORMAT(fecha_di, '%Y-%m-%d') as fecha_di_F,
                        DATE_FORMAT(fecha_di, '%T')       as fecha_di_H,
                        DATE_FORMAT(fecha_df, '%Y-%m-%d') as fecha_df_F,
                        DATE_FORMAT(fecha_df, '%T')       as fecha_df_H,
                        DATE_FORMAT(fecha_ds, '%Y-%m-%d') as fecha_ds_F,
                        DATE_FORMAT(fecha_ds, '%T')       as fecha_ds_H,
                        productos.nombre as NombreProducto,
                        vehiculos.placa as PlacaVehiculo,
                        destinos.nombre as NombreDestino,
                        choferes.nombre as NombreChofer,                  
                        users.email as NombreUsuario 
                        FROM guias,productos,vehiculos,destinos,choferes,users 
                        WHERE guias.producto_id=productos.id
                        AND guias.vehiculo_id=vehiculos.id
                        AND guias.destino_id=destinos.id
                        AND guias.chofer_id=choferes.id 
                        AND guias.user_id=users.id  
                        AND guias.user_id=? 
                        ";
                $guias = DB::select($sql,[$vid]);
            }
            
            
            if ($guias){
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información y sus estado se consiguió sin problemas.',
                    'guias'     => $guias,
                );                
            } else {                
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe informacion con los parametros dados.',
                    'parametro' =>  $vrol,
                );                                            
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información y sus estado  no se logro conseguir. ',
                'parametro' =>  $vrol,
                'error'     =>  $e,
            );
        }                
        return response()->json($data);
        //return $guias;

        
    }
    
    
    public function todos($vestado,$vdestino)
    {
        /*$guias = \App\Models\Guia::all() -> load('user')
                                         -> load('producto')
                                         -> load('vehiculo')
                                         -> load('chofer')
                                         -> load('destino');*/
        
        $vpaso=0;
        switch ($vestado){
            case 'FI':
                   $vpaso=0;
                   break;                        
            case 'EL':
                   $vpaso=0;
                   break;
            case 'EI':
                   $vpaso=0;
                   break;
            case 'EF':
                   $vpaso=0;
                   break;
            case 'ER':
                   $vpaso=0;
                   break;
            case 'ES':
                   $vpaso=1;
                   break;
            case 'DL':
                   $vpaso=1;
                   break;
            case 'DI':
                   $vpaso=1;
                   break;
            case 'DF':
                   $vpaso=1;
                   break;
            /*case 'DS':
                   $vpaso=1;
                   break;*/
        }
                
        try{   
            if($vpaso==0){
                $sql=  "SELECT guias.*, 
                        DATE_FORMAT(fecha_el, '%Y-%m-%d') as fecha_el_F,
                        DATE_FORMAT(fecha_el, '%T')       as fecha_el_H,
                        DATE_FORMAT(fecha_ei, '%Y-%m-%d') as fecha_ei_F,
                        DATE_FORMAT(fecha_ei, '%T')       as fecha_ei_H,
                        DATE_FORMAT(fecha_ef, '%Y-%m-%d') as fecha_ef_F,
                        DATE_FORMAT(fecha_ef, '%T')       as fecha_ef_H,
                        DATE_FORMAT(fecha_er, '%Y-%m-%d') as fecha_er_F,
                        DATE_FORMAT(fecha_er, '%T')       as fecha_er_H,
                        DATE_FORMAT(fecha_es, '%Y-%m-%d') as fecha_es_F,
                        DATE_FORMAT(fecha_es, '%T')       as fecha_es_H,
                        DATE_FORMAT(fecha_dl, '%Y-%m-%d') as fecha_dl_F,
                        DATE_FORMAT(fecha_dl, '%T')       as fecha_dl_H,
                        DATE_FORMAT(fecha_di, '%Y-%m-%d') as fecha_di_F,
                        DATE_FORMAT(fecha_di, '%T')       as fecha_di_H,
                        DATE_FORMAT(fecha_df, '%Y-%m-%d') as fecha_df_F,
                        DATE_FORMAT(fecha_df, '%T')       as fecha_df_H,
                        DATE_FORMAT(fecha_ds, '%Y-%m-%d') as fecha_ds_F,
                        DATE_FORMAT(fecha_ds, '%T')       as fecha_ds_H,
                        productos.nombre as NombreProducto,
                        vehiculos.placa as PlacaVehiculo,
                        destinos.nombre as NombreDestino,
                        choferes.nombre as NombreChofer,                   
                        users.email as NombreUsuario 
                        FROM guias,productos,vehiculos,destinos,choferes,users 
                        WHERE guias.producto_id=productos.id
                        AND guias.vehiculo_id=vehiculos.id
                        AND guias.destino_id=destinos.id
                        AND guias.chofer_id=choferes.id 
                        AND guias.user_id=users.id  
                        AND guias.estado IN ('FI','EL','EI','EF','ER')
                        ";
                $guias = DB::select($sql);
                
            }else{
                $sql= " SELECT guias.*, 
                        DATE_FORMAT(fecha_el, '%Y-%m-%d') as fecha_el_F,
                        DATE_FORMAT(fecha_el, '%T')       as fecha_el_H,
                        DATE_FORMAT(fecha_ei, '%Y-%m-%d') as fecha_ei_F,
                        DATE_FORMAT(fecha_ei, '%T')       as fecha_ei_H,
                        DATE_FORMAT(fecha_ef, '%Y-%m-%d') as fecha_ef_F,
                        DATE_FORMAT(fecha_ef, '%T')       as fecha_ef_H,
                        DATE_FORMAT(fecha_er, '%Y-%m-%d') as fecha_er_F,
                        DATE_FORMAT(fecha_er, '%T')       as fecha_er_H,
                        DATE_FORMAT(fecha_es, '%Y-%m-%d') as fecha_es_F,
                        DATE_FORMAT(fecha_es, '%T')       as fecha_es_H,
                        DATE_FORMAT(fecha_dl, '%Y-%m-%d') as fecha_dl_F,
                        DATE_FORMAT(fecha_dl, '%T')       as fecha_dl_H,
                        DATE_FORMAT(fecha_di, '%Y-%m-%d') as fecha_di_F,
                        DATE_FORMAT(fecha_di, '%T')       as fecha_di_H,
                        DATE_FORMAT(fecha_df, '%Y-%m-%d') as fecha_df_F,
                        DATE_FORMAT(fecha_df, '%T')       as fecha_df_H,
                        DATE_FORMAT(fecha_ds, '%Y-%m-%d') as fecha_ds_F,
                        DATE_FORMAT(fecha_ds, '%T')       as fecha_ds_H,
                        productos.nombre as NombreProducto,
                        vehiculos.placa as PlacaVehiculo,
                        destinos.nombre as NombreDestino,
                        choferes.nombre as NombreChofer,                    
                        users.email as NombreUsuario 
                        FROM guias,productos,vehiculos,destinos,choferes,users 
                        WHERE guias.producto_id=productos.id
                        AND guias.vehiculo_id=vehiculos.id
                        AND guias.destino_id=destinos.id
                        AND guias.chofer_id=choferes.id 
                        AND guias.user_id=users.id  
                        AND guias.estado IN ('ES','DL','DI','DF')
                        AND guias.destino_id=?
                        ";
                $guias = DB::select($sql,[$vdestino]);
            }
            
            
            if ($guias){
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información y sus estado se consiguió sin problemas.',
                    'guias'     => $guias,
                );                
            } else {                
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe informacion con los parametros dados.',
                    'parametro' =>  $vestado,
                );                                            
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información y sus estado  no se logro conseguir. ',
                'parametro' =>  $vestado,
                'error'     =>  $e,
            );
        }                
        return response()->json($data);
        //return $guias;

        
    }
    
           
    
    public function guiaId($id) 
    {   
        try{
            $guia = Guia::find($id) -> load('user')
                                    -> load('producto')
                                    -> load('vehiculo')
                                    -> load('destino');
            if (is_object($guia)) {
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información se consiguió sin problemas.',
                    'guia' => $guia,
                );
            } else {
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe.',
                );            
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }                
        return response()->json($data, $data['code']);
    }


    public function guiasEstado($vestado,$vdestino,$vchofer)
    {   
        $vpaso=0;
        switch ($vestado){
            case 'FI':
                   $vpaso=0;
                   break;                        
            case 'EL':
                   $vpaso=0;
                   break;
            case 'EI':
                   $vpaso=0;
                   break;
            case 'EF':
                   $vpaso=0;
                   break;
            case 'ER':
                   $vpaso=0;
                   break;
            case 'ES':
                   $vpaso=1;
                   break;
            case 'DL':
                   $vpaso=1;
                   break;
            case 'DI':
                   $vpaso=1;
                   break;
            case 'DF':
                   $vpaso=1;
                   break;
            case 'DS':
                   $vpaso=1;
                   break;
        }
                
        try{   
            if($vpaso==0){
                $sql=  "SELECT guias.*, 
                        DATE_FORMAT(fecha_el, '%Y-%m-%d') as fecha_el_F,
                        DATE_FORMAT(fecha_el, '%T')       as fecha_el_H,
                        DATE_FORMAT(fecha_ei, '%Y-%m-%d') as fecha_ei_F,
                        DATE_FORMAT(fecha_ei, '%T')       as fecha_ei_H,
                        DATE_FORMAT(fecha_ef, '%Y-%m-%d') as fecha_ef_F,
                        DATE_FORMAT(fecha_ef, '%T')       as fecha_ef_H,
                        DATE_FORMAT(fecha_er, '%Y-%m-%d') as fecha_er_F,
                        DATE_FORMAT(fecha_er, '%T')       as fecha_er_H,
                        DATE_FORMAT(fecha_es, '%Y-%m-%d') as fecha_es_F,
                        DATE_FORMAT(fecha_es, '%T')       as fecha_es_H,
                        DATE_FORMAT(fecha_dl, '%Y-%m-%d') as fecha_dl_F,
                        DATE_FORMAT(fecha_dl, '%T')       as fecha_dl_H,
                        DATE_FORMAT(fecha_di, '%Y-%m-%d') as fecha_di_F,
                        DATE_FORMAT(fecha_di, '%T')       as fecha_di_H,
                        DATE_FORMAT(fecha_df, '%Y-%m-%d') as fecha_df_F,
                        DATE_FORMAT(fecha_df, '%T')       as fecha_df_H,
                        DATE_FORMAT(fecha_ds, '%Y-%m-%d') as fecha_ds_F,
                        DATE_FORMAT(fecha_ds, '%T')       as fecha_ds_H,
                        productos.nombre as NombreProducto,
                        vehiculos.placa as PlacaVehiculo,
                        destinos.nombre as NombreDestino,
                        choferes.nombre as NombreChofer,                 
                        users.email as NombreUsuario 
                        FROM guias,productos,vehiculos,destinos,choferes,users 
                        WHERE guias.producto_id=productos.id
                        AND guias.vehiculo_id=vehiculos.id
                        AND guias.destino_id=destinos.id
                        AND guias.chofer_id=choferes.id 
                        AND guias.user_id=users.id  
                        AND guias.estado IN ('FI','EL','EI','EF','ER')
                        AND guias.user_id=?";
                $guias = DB::select($sql,[$vchofer]);
                
            }else{
                $sql= " SELECT guias.*, 
                        DATE_FORMAT(fecha_el, '%Y-%m-%d') as fecha_el_F,
                        DATE_FORMAT(fecha_el, '%T')       as fecha_el_H,
                        DATE_FORMAT(fecha_ei, '%Y-%m-%d') as fecha_ei_F,
                        DATE_FORMAT(fecha_ei, '%T')       as fecha_ei_H,
                        DATE_FORMAT(fecha_ef, '%Y-%m-%d') as fecha_ef_F,
                        DATE_FORMAT(fecha_ef, '%T')       as fecha_ef_H,
                        DATE_FORMAT(fecha_er, '%Y-%m-%d') as fecha_er_F,
                        DATE_FORMAT(fecha_er, '%T')       as fecha_er_H,
                        DATE_FORMAT(fecha_es, '%Y-%m-%d') as fecha_es_F,
                        DATE_FORMAT(fecha_es, '%T')       as fecha_es_H,
                        DATE_FORMAT(fecha_dl, '%Y-%m-%d') as fecha_dl_F,
                        DATE_FORMAT(fecha_dl, '%T')       as fecha_dl_H,
                        DATE_FORMAT(fecha_di, '%Y-%m-%d') as fecha_di_F,
                        DATE_FORMAT(fecha_di, '%T')       as fecha_di_H,
                        DATE_FORMAT(fecha_df, '%Y-%m-%d') as fecha_df_F,
                        DATE_FORMAT(fecha_df, '%T')       as fecha_df_H,
                        DATE_FORMAT(fecha_ds, '%Y-%m-%d') as fecha_ds_F,
                        DATE_FORMAT(fecha_ds, '%T')       as fecha_ds_H,
                        productos.nombre as NombreProducto,
                        vehiculos.placa as PlacaVehiculo,
                        destinos.nombre as NombreDestino,
                        choferes.nombre as NombreChofer,                    
                        users.email as NombreUsuario 
                        FROM guias,productos,vehiculos,destinos,choferes,users 
                        WHERE guias.producto_id=productos.id
                        AND guias.vehiculo_id=vehiculos.id
                        AND guias.destino_id=destinos.id
                        AND guias.chofer_id=choferes.id 
                        AND guias.user_id=users.id  
                        AND guias.estado IN ('ES','DL','DI','DF')
                        AND guias.destino_id=?
                        AND guias.user_id=?";
                $guias = DB::select($sql,[$vdestino,$vchofer]);
            }
            
            
            if ($guias){
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información y sus estado se consiguió sin problemas.',
                    'guias'     => $guias,
                );                
            } else {                
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe informacion con los parametros dados.',
                    'parametro' =>  $vestado,
                );                                            
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información y sus estado  no se logro conseguir. ',
                'parametro' =>  $vestado,
                'error'     =>  $e,
            );
        }                
        return response()->json($data);
        //return $guias;
    }
    
    
    
    /*
    $users = User::where("estado","=",1)
    ->whereNotNull('updated_at')
    ->whereNull('email')
    ->whereIn('id', [1, 2, 3])
    ->whereBetween('edad', [1, 30])
    ->where('username','like','%ad%')
    ->orderBy('username')
    ->orderBy('created_at','desc')
    ->skip(10)->take(5)
    ->get();
    */
    
    
    public function crear(Request $request) 
    {
        $date= date('Y-m-d H:i:s');
        //RECOGER DATOS POR POST       
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_arrray = json_decode($json,true); //consigo un objeto

        if (!empty($params_arrray)) 
        {
            //CONSEGUIR USUARIO IDENTIFICADO
            $user = $this->identificate($request);
            
            //QUITAR ESPACIOS INCIO Y FIN
            $params_arrray = array_map('trim', $params_arrray);

            //VALIDAR DATOS
            $validate = \Validator::make($params_arrray, [
                        'producto_id' => 'required',
                        'vehiculo_id' => 'required',
                        'destino_id'  => 'required',
                        'chofer_id'  => 'required',
                        //'documento'   => 'required',
                        //'cantidad'  => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos.',
                    'error'     => $validate->errors(),
                );
            } else {                             
                try{
                    //PREPARAR DATOS
                    $guia               = new Guia();
                    $guia->user_id      = $user->sub;
                    $guia->producto_id  = $params->producto_id;                
                    $guia->vehiculo_id  = $params->vehiculo_id;                
                    $guia->destino_id   = $params->destino_id;  
                    $guia->chofer_id   = $params->chofer_id;  
                    $guia->documento    = $params->documento;                
                    $guia->cantidad    = $params->cantidad;                
                    $guia->peso    =    $params->peso;                
                    $guia->estado    = 'FI';
                    //$guia->fecha_el  = $date;
                    //CREAR REGISTRO
                    $guia->save();
                    //Confirma en Mensaje
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'Se creo correctamente.',
                        'guia'  => $guia,
                    );
                
                } catch (\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'    => 'Error: No se pudo crear, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }                            
            }
        } else {
            //NO SE ENVIO NADA
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }    
        return response()->json($data);
    }




    public function modificar(Request $request, $id) {
        $date= date('Y-m-d H:i:s');
        
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $params = json_decode($json);

        if (!empty($params_array)) {
            //validar los datos            
            $validate = \Validator::make($params_array, [
                'producto_id' => 'required',
                'vehiculo_id' => 'required',
                'destino_id'  => 'required',
                'chofer_id'  => 'required',
                //'documento' => 'required',
                //'cantidad'  => 'required',                
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos.',
                    'error'    => $validate->errors(),
                );
            } else {      
                try{
                    //Quitar campos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['user_id']);
                    unset($params_array['create_at']);
                    unset($params_array['updated_at']);       
                    unset($params_array['estado']);
                    unset($params_array['fecha_el']);       
                    unset($params_array['fecha_ei']);       
                    unset($params_array['fecha_ef']);       
                    unset($params_array['fecha_er']);       
                    unset($params_array['fecha_es']);       

                    //MODIFICAR REGISTRO            
                    $vdocumento= $params_array['documento'];
                    $vcantidad= $params_array['cantidad'];
                    $vpeso= $params_array['peso'];

                    $guia = Guia::find($id)-> load('user')
                                            -> load('producto')
                                            -> load('vehiculo')
                                            -> load('destino');
                    
                    
                    $guia->documento= $vdocumento;
                    $guia->cantidad= $vcantidad;
                    $guia->peso= $vpeso;
                    $guia->producto_id  = $params->producto_id;                
                    $guia->vehiculo_id  = $params->vehiculo_id;                
                    $guia->destino_id   = $params->destino_id;                     
                    $guia->chofer_id   = $params->chofer_id;                      
                    $guia->save();
                    
                    
                    //devolver el array con el resultado
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'guia'          => $params_array,
                    );  
                }catch(\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Error: No se pudo modificar, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }
            }            
        }else {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }
        return response()->json($data, $data['code']);
    }


    public function modificar2(Request $request, $id) {
        $date= date('Y-m-d H:i:s');
        
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $params = json_decode($json);

        if (!empty($params_array)) {
            //validar los datos            
            $validate = \Validator::make($params_array, [
                'estado' => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos.',
                    'error'    => $validate->errors(),
                );
            } else {      
                try{
                    //Quitar campos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['create_at']);
                    unset($params_array['updated_at']);       
                    //MODIFICAR REGISTRO
                    $vestado= trim($params_array['estado']);
                    $vfecha_el= $params_array['fecha_el'];
                    $vfecha_ei= $params_array['fecha_ei'];
                    $vfecha_ef= $params_array['fecha_ef'];
                    $vfecha_er= $params_array['fecha_er'];
                    $vfecha_es= $params_array['fecha_es'];

                    $vfecha_dl= $params_array['fecha_dl'];
                    $vfecha_di= $params_array['fecha_di'];
                    $vfecha_df= $params_array['fecha_df'];
                    $vfecha_ds= $params_array['fecha_ds'];

                    $vobservacion= $params_array['observacion'];
                    $vcantidad= $params_array['cantidad'];
                    $vpeso= $params_array['peso'];
                    $vdocumento= $params_array['documento'];
                    
                    switch ($vestado){
                        case 'FI':
                               $vestado = "EL";
                               $vfecha_el=$date;
                               break;                        
                        case 'EL':
                               $vestado = "EI";
                               $vfecha_ei=$date;
                               break;
                        case 'EI':
                               $vestado = "EF";
                               $vfecha_ef=$date;
                               break;
                        case 'EF':
                               $vestado = "ER";
                               $vfecha_er=$date;
                               break;
                        case 'ER':
                               $vestado = "ES";
                               $vfecha_es=$date;
                               break;
                        case 'ES':
                               $vestado = "DL";
                               $vfecha_dl=$date;
                               break;
                        case 'DL':
                               $vestado = "DI";
                               $vfecha_di=$date;
                               break;
                        case 'DI':
                               $vestado = "DF";
                               $vfecha_df=$date;
                               break;
                        case 'DF':
                               $vestado = "DS";
                               $vfecha_ds=$date;
                               break;
                    }

                    $guia = Guia::find($id)-> load('user')
                                            -> load('producto')
                                            -> load('vehiculo')
                                            -> load('destino');
                    
                    
                    $guia->estado   = $vestado;  
                    $guia->fecha_el = $vfecha_el;
                    $guia->fecha_ei = $vfecha_ei;
                    $guia->fecha_ef = $vfecha_ef;
                    $guia->fecha_er = $vfecha_er;
                    $guia->fecha_es = $vfecha_es;
                    $guia->fecha_dl = $vfecha_dl;
                    $guia->fecha_di = $vfecha_di;
                    $guia->fecha_df = $vfecha_df;
                    $guia->fecha_ds = $vfecha_ds;
                    $guia->observacion = $vobservacion;
                    $guia->cantidad = $vcantidad;
                    $guia->peso = $vpeso;
                    $guia->documento = $vdocumento;
                    $guia->save();
                                        
                    //devolver el array con el resultado
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'guia'          => $params_array,
                    );  
                }catch(\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Error: No se pudo modificar, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }
            }            
        }else {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }
        return response()->json($data, $data['code']);
    }


    
    public function eliminar(Request $request, $id) {        
        //CONSEGUIR USUARIO IDENTIFICADO
        $user = $this->identificate($request);

        //CONSEGUIR EL REGISTRO
        try {
            $guia = Guia::where('id',$id)->first();
            
            if (!empty($guia)) {
                //BORRARLO
                try {
                    //Eliminar registro
                    $guia->delete();   
                    //DEVOLVER ALGO
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'La información se elimino sin problemas.',
                        'guia'  => $guia
                    );
                } catch(\Exception $e){
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Imposible de Eliminar, el registro esta asociado a otra información.',
                        'error'     =>  $e,                        
                    );
                }            
            }else{
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'No existe el registro.',
                );                        
            }
            
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Imposible de Eliminar, no se pudo conseguir la información.',
                'error'     =>  $e,
            );
        }                    
        return response()->json($data, $data['code']);
    }
    
    
    

    public function guiasReporte1()
    {   
                
        try{   
                $sql=  "select UNIX_TIMESTAMP(fecha_ei)*1000 as fecha,
			CAST(avg(TIMESTAMPDIFF(MINUTE,fecha_ei,fecha_es))AS UNSIGNED) AS minutos,
			avg(TIMESTAMPDIFF(MINUTE,fecha_ei,fecha_es)) AS minutos2,
                        300 as media 
                        from guias
                        GROUP BY fecha";
                $guias = DB::select($sql);

                $sql1=  "select UNIX_TIMESTAMP(fecha_ei)*1000 as fecha,
			CAST(avg(TIMESTAMPDIFF(MINUTE,fecha_ei,fecha_es))AS UNSIGNED) AS minutos,
			avg(TIMESTAMPDIFF(MINUTE,fecha_ei,fecha_es)) AS minutos2,
                        300 as media 
                        from guias where destino_id=1 
                        GROUP BY fecha";
                $guias1 = DB::select($sql1);

                $sql2=  "select UNIX_TIMESTAMP(fecha_ei)*1000 as fecha,
			CAST(avg(TIMESTAMPDIFF(MINUTE,fecha_ei,fecha_es))AS UNSIGNED) AS minutos,
			avg(TIMESTAMPDIFF(MINUTE,fecha_ei,fecha_es)) AS minutos2,
                        300 as media 
                        from guias where destino_id=2 
                        GROUP BY fecha";
                $guias2 = DB::select($sql2);
                


                $sql3=  "SELECT destino_id,count(id) as numero_guias FROM `guias` GROUP by destino_id";
                $guias3 = DB::select($sql3);
        

                
                if ($guias){
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'La información se consiguió sin problemas.',
                        'guias'     => $guias,
                        'guias1'     => $guias1,
                        'guias2'     => $guias2,
                        'guias3'     => $guias3,
                    );                
                } else {                
                    $data = array(
                        'code'      => 404,
                        'status'    => 'error',
                        'message'   => 'Error: No existe informacion con los parametros dados.',
                    );                                            
                }
                
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir. ',
                'error'     =>  $e,
            );
        }                
        return response()->json($data);
        //return $guias;
    }
    
        

    
    
    
}
