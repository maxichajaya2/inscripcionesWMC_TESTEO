<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\Inscripcion;
use App\Models\InscripcionesBeneficio;
use App\Models\Pais;
use App\Models\TipoDocumento;
use App\Models\TipoDocumentoPago;
use App\Models\TipoPago;
use App\Models\TipoServicio;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Carbon\Carbon;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $sharedData = parent::share($request);


        // --- DATOS DEL USUARIO (Lo que faltaba) ---
        $sharedData['auth'] = [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                // Obtenemos los nombres de los roles de Spatie
                'roles' => $request->user()->getRoleNames(),
                // Rol principal formateado para mostrar en la UI
                'role_name' => ucfirst($request->user()->roles->first()?->name ?? 'Participante'),
                // Avatar profesional basado en iniciales (Gratis y elegante)
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($request->user()->name) . '&color=7F9CF5&background=EBF4FF&bold=true',
            ] : null,
        ];

        // --- TUS DATOS GENERALES EXISTENTES ---

        $sharedData['general.paises'] = Pais::where('isactive', true)->get();
        /** PASO 1 */
        $sharedData['general.tipDocPer'] = TipoDocumento::where('isactive', true)
                ->whereIn('sie_code', [0,1,4,7])
                ->whereJsonContains('tipo', 'persona')
                ->get();
        $sharedData['general.tipoDocumentoPago'] = TipoDocumentoPago::where('isactive', true)->get();
        /** PASO 2 */
        $sharedData['general.tipDocEmp'] = TipoDocumento::where('isactive', true)
                // ->whereJsonContains('tipo', 'empresa')
                ->orWhere('name_en', '=','DNI')
                ->orWhere('name_en', '=','PASSPORT')
                // ->orWhere('name_en', '=','RUT')
                ->orWhere('name_en', '=','RUC')
                ->get(); // se agrego dni como documento para el pago
        $sharedData['general.tipoServicios'] = TipoServicio::where('isactive', true)->get();
        $sharedData['general.generos'] = config('data.generos');
        $sharedData['general.reglamento_inscripciones'] = config('app.reglamento_inscripciones');
        $sharedData['flash'] = [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'warning' => $request->session()->get('warning'),
            'info' => $request->session()->get('info'),
            // LÍNEA PARA EL ALERT DE NIUBIZ
            'payment_error_code' => $request->session()->get('payment_error_code'),
        ];

        $sharedData['data'] = [
            'response' => $request->session()->get('response'),
        ];

        // dd($sharedData); // Para depurar y ver qué datos se están compartiendo con Inertia
        return $sharedData;

    }



}
