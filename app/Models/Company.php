<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    protected $fillable = [
        'id',
        'name',
        'nickname',
        'ruc',
        'city',
        'address',
        'phone',
        'closedate',
        'iva',
        'mora',
        'image',
        'social_name',
        'cash',
        'final_customer',
        'print_direct',
        'edit_price',
        'edit_discount',
        'isstore',
        'isseries',
        'num_decimal',
        'account_now',
        'account_old',
        'special_contributor',
        'retention_agent',
        'is_accounting',
        'special_number',
        'retention_number',
        'environment',
        'signature_detail',
        'signature_expiration',
        'signature_password',
        'signature_file',
        'logo',
        'email_host',
        'email_port',
        'email_user',
        'email_password',
        'email_ssl',
        'email_subject',
        'email_message',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'signature_password',
        'email_password',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function encriptPassword($password){
        // Obtén la clave de encriptación desde el archivo .env
        $encryptionKey = env('HASH_PASS_SECRET');

        // Genera un vector de inicialización aleatorio
        $iv = random_bytes(16);

        // Cifra la contraseña utilizando AES-256-CBC
        $encryptedPassword = openssl_encrypt($password, 'AES-256-CBC', $encryptionKey, 0, $iv);

        // Combina el vector de inicialización y la contraseña cifrada en una cadena codificada en base64
        $encodedPassword = base64_encode($iv . $encryptedPassword);

        return $encodedPassword;
    }

    public function decriptPassword($encodedPassword){
        // Obtén la clave de encriptación desde el archivo .env
        $encryptionKey = env('HASH_PASS_SECRET');

        // Decodifica la cadena codificada en base64
        $decodedPassword = base64_decode($encodedPassword);

        // Obtiene el vector de inicialización y la contraseña cifrada de la cadena decodificada
        $iv = substr($decodedPassword, 0, 16);
        $encryptedPassword = substr($decodedPassword, 16);

        // Descifra la contraseña utilizando AES-256-CBC
        $decryptedPassword = openssl_decrypt($encryptedPassword, 'AES-256-CBC', $encryptionKey, 0, $iv);

        return $decryptedPassword;
    }

}
