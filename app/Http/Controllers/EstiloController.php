<?php

namespace App\Http\Controllers;

use App\Models\Estilo;
use App\Models\TipoFermentacion;
use Illuminate\Http\Request;

class EstiloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estilos = Estilo::with('tipoFermentacion')->get();

        return view('estilos.index', compact('estilos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoFermentaciones = TipoFermentacion::all();
        return view('estilos.create', compact('tipoFermentaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'=>'required|unique:estilos,nombre',
            'descripcion'=>'nullable|string',
            'tipo_fermentacion_id'=>'required|exists:tipo_fermentacions,id',
        ]);

        Estilo::create($request->all());

        return redirect()->route('estilos.index')->with('success', 'Estilo creado corectamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Estilo $estilo)
    {
        return view('estilos.show', compact('estilo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estilo $estilo)
    {
        $tipoFermentaciones = TipoFermentacion::all();
        return view('estilos.edit', compact('estilo', 'tipoFermentaciones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estilo $estilo)
    {
        $request->validate([
            'nombre'=>'required|unique:estilos,nombre,' . $estilo->id,
            'descripcion'=>'nullable|string',
            'tipo_fermentacion_id'=>'required|exists:tipo_fermentacions,id',
        ]);

        $estilo->update($request->all());

        return redirect()->route('estilos.index')->with('success','Estilo actualizado con exito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estilo $estilo)
    {
        $estilo->delete();
        return redirect()->route('estilos.index')->with('success','Estilo eliminado exitosamente');
    }
}
