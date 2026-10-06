<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\Request;

class CertificadoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $certificados = Certificado::query()
            ->when($buscar, function ($query) use ($buscar) {
                $query->where('nombres_apellidos', 'like', "%{$buscar}%")
                    ->orWhere('dni', 'like', "%{$buscar}%")
                    ->orWhere('remitente_email', 'like', "%{$buscar}%")
                    ->orWhere('nombre_actividad', 'like', "%{$buscar}%");
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view(
            'certificados.index',
            compact('certificados', 'buscar')
        );
    }
}