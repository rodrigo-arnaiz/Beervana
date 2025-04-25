<?php

namespace App\Http\Controllers;

use App\Models\TipoFermentacion;
use Illuminate\Http\Request;

class TipoFermentacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipoFermentaciones = TipoFermentacion::all();
        return view('tipo-fermentaciones.index', compact('tipoFermentaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tipo-fermentaciones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'=>'required|unique:tipo_fermentacions,nombre',
            'descripcion'=>'nullable|string',
        ]);

        TipoFermentacion::create($request->all());

        return redirect()->route('tipo-fermentaciones.index')->with('success', 'Tipo de fermentacion creada corectamente');;
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoFermentacion $tipoFermentacion)
    {
        return view('tipo-fermentaciones.show', compact('tipoFermentacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoFermentacion $tipoFermentacion)
    {
        return view('tipo-fermentaciones.edit', compact('tipoFermentacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoFermentacion $tipoFermentacion)
    {
        $request->validate([
            'nombre'=>'required|unique:tipo_fermentacions,nombre,' . $tipoFermentacion->id,
            'descripcion'=>'nullable|string', 
        ]);

        $tipoFermentacion->update($request->all());

        return redirect()->route('tipo-fermentaciones.index')->with('success','Tipo de fermentacion actualizada exitosamente');;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoFermentacion $tipoFermentacion)
    {
        $tipoFermentacion->delete();

        return redirect()->route('tipo-fermentaciones.index')->with('success','Tipo de fermentacion eliminada exitosamente');;
    }
}
