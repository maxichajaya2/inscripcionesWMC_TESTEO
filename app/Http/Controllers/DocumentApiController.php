<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use App\Http\Controllers\WebServiceController;
use Carbon\Carbon;
use stdClass;
use App\Models\Direccion;
use App\Models\Ocupacion;


class DocumentApiController extends Controller
{
    protected $urlApi;

    public function __construct()
    {
        $this->urlApi = 'https://api.apis.net.pe/v2';
        $this->now = Carbon::now()->format('Y-m-d');
    }

    protected function token()
    {
        return 'apis-token-13383.Aph50ddFaV03b9sZaRprJo5ZBpMz0yC4';
    }

    public function getData(Request $request)
    {
        $type = ($request->tipo_doc == 1) ? 'dni' : 'ruc';
        $data = $request->documento;

        if ($type == "dni") {
            $tipo_respuesta = 'persona';
            $urlApi = $this->urlApi . '/reniec/' . $type . '?numero=' . $data;
        }

        if ($type == "ruc") {
            $tipo_respuesta = 'empresa';
            $urlApi = $this->urlApi . '/sunat/ruc/full?numero=' . $data;
        }


        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token(),
            'Accept' => 'application/json'
        ])->get($urlApi);

        if ($request->ok()) {
            $request =   json_decode($request);
            if (isset($request->numeroDocumento)) {
                $response = [$tipo_respuesta => $request, 'status' => true];
            } else {
                $response = ['status' => false];
            }
        } else {
            $response = ['status' => false];
        }
        return $response;
    }

    public function getPersonData(Request $request)
    {
        if (!str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null)) {
            abort(403, 'Unauthorized POST request.');
        }

        $persona = null;
        $id_tipo_doc = $request->id_tipo_documento;
        $num_doc = $request->numero_documento;
        $status = true;

        $wsController = app(\App\Http\Controllers\WebServiceController::class);
        /** ******* LOCAL *********/
        // $urlIIMP = "https://secure2.iimp.org:8443/KBServiciosPruebaIIMPJavaEnvironment/rest/WSViewPersona";

        /** ******* PRODUCCION *********/
        $urlIIMP = "https://secure2.iimp.org:8443/KBServiciosIIMPJavaEnvironment/rest/WSViewPersona";

        $resIIMP = $wsController->sendWS($urlIIMP, json_encode([
            "id_tipo_documento" => (string)$id_tipo_doc,
            "documento" => (string)$num_doc
        ]));

        $dataIIMP = (isset($resIIMP->info_persona) && $resIIMP->info_persona->code == "00") ? $resIIMP->info_persona : null;

        if ($dataIIMP) {
            // 1. Buscar o instanciar Persona local
            $persona = Persona::where('id_tipo_documento', $id_tipo_doc)
                ->where('documento', $num_doc)
                ->firstOrNew();

            // 2. Manejo de Dirección
            $direccion = ($persona->id_direccion > 0)
                ? Direccion::find($persona->id_direccion)
                : new Direccion;

            $direccion->id_pais = (int)($dataIIMP->pais ?? 75);
            $direccion->id_departamento = ($dataIIMP->departamento > 0) ? (int)$dataIIMP->departamento : null;
            $direccion->id_provincia = ($dataIIMP->provincia > 0) ? (int)$dataIIMP->provincia : null;
            $direccion->id_distrito = ($dataIIMP->distrito > 0) ? (int)$dataIIMP->distrito : null;
            $direccion->direccion = $dataIIMP->direccion ?? 'LURIN';
            $direccion->save();

            // --- MANEJO DE OCUPACIÓN SEGURO ---
            $ocupacion = null;

            if (!empty($dataIIMP->id_ocupacion)) {
                // 1. Intentamos buscar la ocupación por ID
                $ocupacion = Ocupacion::find((int)$dataIIMP->id_ocupacion);

                if ($ocupacion) {
                    // 2. Si existe, SOLO actualizamos el nombre si la API NO viene vacía
                    if (!empty(trim($dataIIMP->ocupacion))) {
                        $ocupacion->name = trim(strtoupper($dataIIMP->ocupacion));
                        $ocupacion->save();
                    }
                } else {
                    // 3. Si NO existe, la creamos (aquí sí usamos el nombre que venga o un default)
                    $ocupacion = Ocupacion::create([
                        'id'          => (int)$dataIIMP->id_ocupacion,
                        'name'        => !empty(trim($dataIIMP->ocupacion)) ? trim(strtoupper($dataIIMP->ocupacion)) : 'OCUPACIÓN DESCONOCIDA',
                        'descripcion' => 'Creado automáticamente desde API IIMP',
                        'isactive'    => true,
                    ]);
                }
            }

            // 4. Manejo de Empresa (Solo consulta por sie_code)
            $empresaLocal = null;
            if (!empty($dataIIMP->id_empresa)) {
                $empresaLocal = Empresa::where('sie_code', (string)$dataIIMP->id_empresa)->first();
            }


            // dd($empresaLocal);

            // 5. Asignación de datos a la Persona
            if (!$persona->exists) {
                $persona->id_tipo_documento = $id_tipo_doc;
                $persona->documento = $num_doc;
            }

            $persona->id_direccion = $direccion->id;
            $persona->id_ocupacion = $ocupacion ? $ocupacion->id : null;

            if ($empresaLocal) {
                $persona->id_empresa = $empresaLocal->id;
                $persona->empresa   = $empresaLocal->nombre; // Usamos nombre de BD local
            } else {
                $persona->id_empresa = null;
                $persona->empresa    = $dataIIMP->empresa ?? null; // Usamos texto de la API
            }

            $fecha = $dataIIMP->fecha_nacimiento;

            $persona->nombres           = $dataIIMP->nombres;
            $persona->apellido_paterno  = $dataIIMP->apellido_paterno;
            $persona->apellido_materno  = $dataIIMP->apellido_materno;
            $persona->correo            = $dataIIMP->correo ?? $persona->correo;
            $persona->celular           = $dataIIMP->celular ?? $persona->celular;
            $persona->sexo              = $dataIIMP->sexo ?? $persona->sexo;
            if ($fecha === '0000-00-00' || empty($fecha)) {
                // Si es inválida, mantenemos la que tiene o ponemos null
                $persona->fecha_nacimiento = $persona->exists ? $persona->fecha_nacimiento : null;
            } else {
                $persona->fecha_nacimiento = $fecha;
            }
            $persona->es_socio          = $dataIIMP->asociado ?? false;
            $persona->sie_code          = $dataIIMP->sie_code ?? $persona->sie_code;

            $persona->save();

            // 6. Preparar para el FRONT (Aplanado)
            $persona->direccionPersona = $direccion->direccion;
            $persona->cargo            = $ocupacion ? $ocupacion->name : ($dataIIMP->ocupacion ?? "");
            $persona->nombre_empresa   = $empresaLocal ? $empresaLocal->razon_social : ($dataIIMP->empresa ?? "");
            $persona->pais             = $direccion->id_pais;
            $persona->departamento     = $direccion->id_departamento ?? 0;
            $persona->provincia        = $direccion->id_provincia ?? 0;
            $persona->distrito         = $direccion->id_distrito ?? 0;
        } elseif ($id_tipo_doc == 1) {
            // FALLBACK RENIEC
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['tipo_doc' => 1, 'documento' => $num_doc]);
            $api_reniec = $this->getData($fakeRequest);

            if ($api_reniec['status']) {
                $persona = new \stdClass();
                $persona->nombres          = $api_reniec['persona']->nombres;
                $persona->apellido_paterno = $api_reniec['persona']->apellidoPaterno;
                $persona->apellido_materno = $api_reniec['persona']->apellidoMaterno;
                $persona->documento        = $num_doc;
                $persona->id_tipo_documento = 1;
                $persona->es_socio         = false;
            } else {
                $status = false;
            }
        }

        if (!$persona) {
            return response()->json(['status' => false, 'message' => 'No encontrado']);
        }

        return response()->json(['persona' => $persona, 'status' => $status]);
    }

    public function getEmpresaData(Request $request)
    {
        $direccion = "";
        $status = true;

        // 1. Intentar buscar primero en nuestra base de datos local
        if ($request->tipo_doc == 1) { // DNI
            $empresa = Persona::where('id_tipo_documento', $request->tipo_doc)
                ->where('documento', $request->documento)
                ->first();
        } else { // RUC o Otros
            $empresa = Empresa::where('id_tipo_documento', $request->tipo_doc)
                ->where('documento', $request->documento)
                ->first();
        }

        // 2. Si existe localmente, preparamos la dirección y datos básicos
        if ($empresa) {
            if (!is_null($empresa->direccion)) {
                $empresa->pais = $empresa->direccion->id_pais;
                $empresa->departamento = intval($empresa->direccion->id_departamento) > 0 ? $empresa->direccion->id_departamento : 0;
                $empresa->provincia = intval($empresa->direccion->id_provincia) > 0 ? $empresa->direccion->id_provincia : 0;
                $empresa->distrito = intval($empresa->direccion->id_distrito) > 0 ? $empresa->direccion->id_distrito : 0;

                if ($empresa instanceof Persona) {
                    $empresa->nombre = trim($empresa->nombres . " " . $empresa->apellido_paterno . " " . $empresa->apellido_materno);
                    $empresa->telefono = $empresa->celular;
                }

                // Obtener nombres de ubicación para la cadena de dirección completa
                $dep = Departamento::where('id_pais', intval($empresa->pais))->where('id_departamento', intval($empresa->departamento))->first();
                $prov = Provincia::where('id_pais', intval($empresa->pais))->where('id_departamento', intval($empresa->departamento))->where('id_provincia', intval($empresa->provincia))->first();
                $dist = Distrito::where('id_pais', intval($empresa->pais))->where('id_departamento', intval($empresa->departamento))->where('id_provincia', intval($empresa->provincia))->where('id_distrito', intval($empresa->distrito))->first();

                $direccion = trim($empresa->direccion->direccion . " " . ($dep ? $dep->name : '') . " - " . ($prov ? $prov->name : '') . " - " . ($dist ? $dist->name : ''));
            }
        } else {
            // Inicializamos objeto vacío si no hay registro local
            $empresa = new \stdClass();
            $empresa->id_tipo_documento = $request->tipo_doc;
            $empresa->documento = $request->documento;
            $empresa->nombre = "";
            $status = false; // Cambiará a true si la API externa lo encuentra
        }

        // 3. CONSULTA A API EXTERNA (Solo para DNI y RUC)
        // Esto corrige el error de "Argument #1 ($request) must be of type Request"
        if ($request->tipo_doc == 1) { // DNI
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['tipo_doc' => 1, 'documento' => $request->documento]);

            $api_res = $this->getData($fakeRequest);

            if ($api_res['status']) {
                $p = $api_res['persona'];
                $empresa->nombre = trim($p->nombres . " " . $p->apellidoPaterno . " " . $p->apellidoMaterno);
                $status = true;
            }
        }

        if ($request->tipo_doc == 2) { // RUC
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['tipo_doc' => 2, 'documento' => $request->documento]);

            $api_res = $this->getData($fakeRequest);

            if ($api_res['status']) {
                $e = $api_res['empresa'];
                $empresa->nombre = $e->razonSocial;
                // Construir dirección desde la API
                $direccion = trim($e->direccion . " " . $e->departamento . " - " . $e->provincia . " - " . $e->distrito);
                $status = true;
            }
        }

        $empresa->direccionEmpresa = $direccion;

        return response()->json([
            'empresa' => $empresa,
            'status' => $status
        ]);
    }
    public function validatePersonSoc(Request $request)
    {

        $tipo_documento = TipoDocumento::find($request->id_tipo_documento);

        $valid = app(\App\Http\Controllers\WebServiceController::class)->validatePersonMember($tipo_documento->sie_code, $request->numero_documento);

        $persona = [
            'id_tipo_documento' => $request->id_tipo_documento,
            'numero_documento' => $request->numero_documento,
            'es_socio' => app(\App\Http\Controllers\WebServiceController::class)->validatePersonMember($tipo_documento->sie_code, $request->numero_documento)
        ];

        return json_encode(['persona' => $persona]);
    }
}
