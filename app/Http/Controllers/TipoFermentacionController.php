<?php

namespace App\Http\Controllers;

use App\Models\TipoFermentacion;
use Illuminate\Http\Request;

class TipoFermentacionController extends Controller
{
    public function index()
    {
        $tiposFermentacion = TipoFermentacion::all();
        return view('tipo-fermentaciones.index', compact('tiposFermentacion'));
    }

    public function create()
    {
        return view('tipo-fermentaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|unique:tipo_fermentacions,nombre',
            'descripcion'  => 'nullable|string',
            'levadura'     => 'required|string',
            'temperatura'  => 'required|string',
            'tiempo'       => 'required|string',
        ]);

        TipoFermentacion::create($request->all());

        return redirect()
               ->route('tipo-fermentaciones.index')
               ->with('success', 'Tipo de fermentación creado correctamente');
    }

    public function show(TipoFermentacion $tipoFermentacion)
    {
        return view('tipo-fermentaciones.show', compact('tipoFermentacion'));
    }

    public function edit(TipoFermentacion $tipoFermentacion)
    {
        return view('tipo-fermentaciones.edit', compact('tipoFermentacion'));
    }

    public function update(Request $request, TipoFermentacion $tipoFermentacion)
    {
        $request->validate([
            'nombre'       => 'required|unique:tipo_fermentacions,nombre,' . $tipoFermentacion->id,
            'descripcion'  => 'nullable|string',
            'levadura'     => 'required|string',
            'temperatura'  => 'required|string',
            'tiempo'       => 'required|string',
        ]);

        $tipoFermentacion->update($request->all());

        return redirect()
               ->route('tipo-fermentaciones.index')
               ->with('success', 'Tipo de fermentación actualizada exitosamente');
    }

    public function destroy(TipoFermentacion $tipoFermentacion)
    {
        $tipoFermentacion->delete();

        return redirect()
               ->route('tipo-fermentaciones.index')
               ->with('success', 'Tipo de fermentación eliminada exitosamente');
    }
}
