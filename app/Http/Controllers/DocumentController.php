<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Entities\Document;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $documents = Document::all();

        $success = [
            "code" => Response::HTTP_OK,
            "content" => "Datos obtenidos correctamente.",
        ];
        $error = [
            "code" => null,
            "content" => null,
        ];

        $responseMessage = [
            "success" => $success,
            "error"   => $error,
            "data"    => $documents
        ];
        
        return response()->json($responseMessage, Response::HTTP_OK);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Subir archivos a AWS.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeAWS(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'min:5',
            'filename' => 'max:500000', //Kilobytes. Max: 10240(10MB)
            'filename' => 'dimensions:min_width=100,min_height=200', //Dimensiones de la imagen
            'filename' => 'mimes:jpg,jpeg,bmp,png' //extensiones permitidas
        ]);

        if ( $validator->fails() ) {

            $success = [
                "code" => null,
                "content" => null,
            ];            
            $error = [
                "code" => Response::HTTP_BAD_REQUEST,
                "content" => $validator->errors(),
            ];
            $responseMessage = [
                "success" => $success,
                "error"   => $error,
                "data"    => null
            ];
            
            return response()->json($responseMessage, Response::HTTP_BAD_REQUEST);
        
        } else {

            /**
             * Subir el archivo en AWS
             */
            $file      = $request->file('filename');
            $path      = 'clientes/CL00001/tramites/2021/enero/IMP-10/';
            $extension = $file->extension(); //Obtener la extensión del archivo
            $filename  = $file->getClientOriginalName();
            $fullpath  = $path . base64_encode($filename);
            
            //Storage::disk('public')->put($fullpath, File::get($file), 'public'); //Almacenar archivo localmente
            Storage::disk('s3')->put($fullpath, File::get($file), 'public'); //Almacenar archivo en Amazon

            /**
             * Insercción en la Base de Datos
             */            
            $document = new Document;
            $document->name = $request->name;
            $document->file_name = $filename;
            $document->path = $path;
            $document->active = 1;
            $document->save();
            $document->path = $document->path . base64_encode($document->file_name);
            
            $success = [
                "code" => Response::HTTP_CREATED,
                "content" => "Documento almacenados correctamente.",
            ];            
            $error = [
                "code" => null,
                "content" => null,
            ];
            $responseMessage = [
                "success" => $success,
                "error"   => $error,
                "data"    => $document
            ];
            
            return response()->json($responseMessage, Response::HTTP_CREATED);
        }
    }
}
