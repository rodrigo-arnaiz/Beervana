<?php

namespace App\Http\Controllers;

use App\Models\Cerveza;
use App\Models\Estilo;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CervezaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $marcas  = Marca::orderBy('nombre')->get();
        $estilos = Estilo::orderBy('nombre')->get();

        $cervezas = Cerveza::with('marca', 'estilo');

        if ($request->filled('marca_id')) {
            $cervezas->where('marca_id', $request->marca_id);
        }

        if ($request->filled('estilo_id')) {
            $cervezas->where('estilo_id', $request->estilo_id);
        }

        //$cervezas = $cervezas->get();
        $cervezas = $cervezas->paginate(5)->withQueryString();

        return view('cervezas.index', compact('cervezas', 'marcas', 'estilos'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $marcas = Marca::all();
        $estilos = Estilo::all();

        return view('cervezas.create', compact('marcas', 'estilos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'precio' => 'required|numeric',
            'marca_id' => 'required|exists:marcas,id',
            'graduacion' => 'required|numeric',
            'tipo_envase' => 'required|string|max:20',
            'estilo_id' => 'required|exists:estilos,id',
            'ibu' => 'required|numeric',
            'capacidad' => 'required|string|max:20',
            'imagen' => 'required|image|mimes:png,jpg,jpeg',
            'stock' => 'required|numeric',
            'descripcion' => 'nullable|string',
        ]);

        $rutaImagen = $request->file('imagen')->store('imagenes', 'public');

        Cerveza::create([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'marca_id' => $request->marca_id,
            'graduacion' => $request->graduacion,
            'tipo_envase' => $request->tipo_envase,
            'estilo_id' => $request->estilo_id,
            'ibu' => $request->ibu,
            'capacidad' => $request->capacidad,
            'imagen' => $rutaImagen, // se guarda el path de la imagen
            'stock' => $request->stock,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('cervezas.index')->with('success', 'Cerveza creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cerveza $cerveza)
    {
        return view('cervezas.show', compact('cerveza'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cerveza $cerveza)
    {
        $marcas = Marca::all();
        $estilos = Estilo::all();

        return view('cervezas.edit', compact('cerveza', 'marcas', 'estilos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cerveza $cerveza)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'precio' => 'required|numeric',
            'marca_id' => 'required|exists:marcas,id',
            'graduacion' => 'required|numeric',
            'tipo_envase' => 'required|string|max:20',
            'estilo_id' => 'required|exists:estilos,id',
            'ibu' => 'required|numeric',
            'capacidad' => 'required|string|max:20',
            'imagen' => 'nullable|image|mimes:jpg,png,jpeg',
            'stock' => 'required|numeric',
            'descripcion' => 'nullable|string',
        ]);

        if ($request->hasFile('imagen')) {
            // Opcional: eliminar imagen anterior
            if ($cerveza->imagen && Storage::disk('public')->exists($cerveza->imagen)) {
                Storage::disk('public')->delete($cerveza->imagen);
            }

            // Guardar la nueva imagen
            $path = $request->file('imagen')->store('imagenes', 'public');
            $cerveza->imagen = $path;
        }

        // Actualizar los demás campos
        $cerveza->fill($request->except('imagen'));
        $cerveza->save();

        return redirect()->route('cervezas.index')->with('success', 'Cerveza actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cerveza $cerveza)
    {
        // Eliminar la imagen del disco si existe
        if ($cerveza->imagen && Storage::disk('public')->exists($cerveza->imagen)) {
            Storage::disk('public')->delete($cerveza->imagen);
        }

        // Eliminar la cerveza
        $cerveza->delete();

        return redirect()->route('cervezas.index')->with('success', 'Cerveza eliminada exitosamente');
    }
}
