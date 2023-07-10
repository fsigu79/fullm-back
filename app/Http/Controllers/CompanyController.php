<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;



class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function findById($id)
    {
        $entity = Company::find($id);
        if (is_object($entity)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'company' => $entity,
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error: Empresa no existe',
            );
        }
        return response()->json($data, $data['code']);
    }



    public function edit(Request $request, $id)
    {
        //recoger datos por post
        // $json = $request->input('json', null);
        // $params_array = json_decode($json, true);
        $params_array = $request->all();;

        if (!empty($params_array)) {
            //validar los datos
            $validate = \Validator::make($params_array, [
                'name'    => 'required',
                'ruc'  => 'required',
                'iva'  => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'     => $validate->errors(),
                );
            } else {
                try {
                    //MODIFICAR REGISTRO
                    $entity = Company::find($id);
                    $entity->update($params_array);

                    //devolver el array con el resultado
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'data'      => $entity,
                    );
                } catch (\Exception $e) {
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Error: No se pudo modificar, existe un conflicto en la base de datos: ',
                        'error'     =>  $e->getMessage(),
                    );
                }
            }
        } else {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
                'data'      => $params_array,
            );
        }
        return response()->json($data, $data['code']);
    }

    public function updateEmailServer(Request $request, $id)
    {

        $params_array = $request->all();;


        //validar los datos
        $validate = \Validator::make($params_array, [
            'email_host'    => 'required',
            'email_port'  => 'required',
            'email_user'  => 'required',
            'email_ssl'  => 'required',
        ]);
        if ($validate->fails()) {
            //LA VALIDACION A FALLADO
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                'error'     => $validate->errors(),
            );
        } else {
            try {

                //MODIFICAR REGISTRO
                $entity = Company::find($id);
                //$entity->update($params_array);

                if (isset($params_array['email_password'])) {
                    $pass = $entity->encriptPassword($params_array['email_password']);
                    $entity->update([
                        "email_password" => $pass
                    ]);
                }

                $entity->update([
                    'email_host' => $params_array['email_host'],
                    'email_port' => $params_array['email_port'],
                    'email_user' => $params_array['email_user'],
                    'email_ssl' => $params_array['email_ssl'],
                    'email_subject' => $params_array['email_subject'],
                    'email_message' => $params_array['email_message'],
                ]);


                //devolver el array con el resultado
                $data = array(
                    'code'          => 200,
                    'status'        => 'success',
                    'message'       => 'Se modifico correctamente.',
                    'data'      => $entity,
                );
            } catch (\Exception $e) {
                //. $e->getMessage()
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'Error: No se pudo modificar, existe un conflicto en la base de datos: ',
                    'error'     =>  $e->getMessage(),
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    public function uploadEditSignature(Request $request, $id)
    {
        $signature_password = $request->input('signature_password');
        $file = $request->file('signature_file');

        if (!isset($file)) {
            return response()->json(['message' => 'File requerido.'], 400);
        }

        if ($file->getClientOriginalExtension() != 'p12') {
            return response()->json(['message' => 'El archivo debe tener extensión .p12'], 400);
        }

        if (!isset($signature_password)) {
            return response()->json(['message' => 'Contraseña requerida.'], 400);
        }

        $data = $request->all();
        $company = Company::find($id);
        if (!$company) {
            return response()->json([
                'status' => 'error',
                'code' => 'error',
                'message' => 'Company not found',
            ], 404);
        }

        try {
            if (isset($file)) {
                if (Storage::disk("public")->exists($company->signature_file)) {
                    Storage::disk("public")->delete($company->signature_file);
                }
                $file = \Illuminate\Http\UploadedFile::createFromBase($file);
                $uuid = Str::uuid()->toString();
                $filename = $uuid . '.' . $file->getClientOriginalExtension();
                $path = Storage::putFileAs('files', $file, $filename);

                $tempFilePath = $file->getRealPath();
                $p12 = file_get_contents($tempFilePath);
                $pkcs12 = openssl_pkcs12_read($p12, $certs, $signature_password);
                $certificate = openssl_x509_read($certs['cert']);
                $issuer = openssl_x509_parse($certificate)["name"];
                $expirationDate = openssl_x509_parse($certificate)['validTo_time_t'];
                $expirationDate = date('Y-m-d', $expirationDate);

                $company->signature_detail = $issuer;
                $company->signature_expiration = $expirationDate;
                $company->signature_file = $path;
            }

            if (isset($signature_password)) {
                $company->signature_password = $company->encriptPassword($signature_password);
            }

            $company->save();

            $data = array(
                'data' => $company,
                'message' => "ok",
            );
            return response()->json($data);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }
    }
}
