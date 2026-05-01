@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="mb-8">
    <h1 class="section-header">Editar producto o servicio</h1>
    <p class="section-subtitle">Actualiza la información de tu publicación</p>
</div>

<!-- Formulario -->
<div class="max-w-2xl mx-auto">
    <div class="card p-8 shadow-elevated">
        
        <form method="POST" action="/producto/{{ $producto->id }}/actualizar" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Tipo de publicación -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Tipo de publicación *</label>
                    <div class="flex gap-3">
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="tipo" value="producto" {{ $producto->tipo === 'producto' ? 'checked' : '' }}
                                   class="w-4 h-4 text-uts-600">
                            <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-uts-600">📦 Producto</span>
                        </label>
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="tipo" value="servicio" {{ $producto->tipo === 'servicio' ? 'checked' : '' }}
                                   class="w-4 h-4 text-uts-600">
                            <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-uts-600">🎓 Servicio</span>
                        </label>
                    </div>
                    @error('tipo')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">Nombre o título *</label>
                <input type="text" id="nombre" name="nombre" class="input-base" value="{{ $producto->nombre }}" required>
                @error('nombre')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Especificación (Categoría) -->
            <div>
                <label for="especificacion" class="block text-sm font-semibold text-gray-700 mb-2">
                    Categoría *
                </label>
                <select id="especificacion" name="especificacion" class="input-base" required>
                    <option value="">-- Selecciona una categoría --</option>
                    <optgroup label="📦 PRODUCTOS">
                        <option value="Libros y útiles escolares" {{ $producto->especificacion === 'Libros y útiles escolares' ? 'selected' : '' }}>Libros y útiles escolares</option>
                        <option value="Ropa y calzado" {{ $producto->especificacion === 'Ropa y calzado' ? 'selected' : '' }}>Ropa y calzado</option>
                        <option value="Electrónica" {{ $producto->especificacion === 'Electrónica' ? 'selected' : '' }}>Electrónica</option>
                        <option value="Alimentos y bebidas" {{ $producto->especificacion === 'Alimentos y bebidas' ? 'selected' : '' }}>Alimentos y bebidas</option>
                        <option value="Deporte y recreación" {{ $producto->especificacion === 'Deporte y recreación' ? 'selected' : '' }}>Deporte y recreación</option>
                        <option value="Accesorios" {{ $producto->especificacion === 'Accesorios' ? 'selected' : '' }}>Accesorios</option>
                    </optgroup>
                    <optgroup label="🎓 SERVICIOS">
                        <option value="Tutoría académica" {{ $producto->especificacion === 'Tutoría académica' ? 'selected' : '' }}>Tutoría académica</option>
                        <option value="Asesoría profesional" {{ $producto->especificacion === 'Asesoría profesional' ? 'selected' : '' }}>Asesoría profesional</option>
                        <option value="Clases de idiomas" {{ $producto->especificacion === 'Clases de idiomas' ? 'selected' : '' }}>Clases de idiomas</option>
                        <option value="Trabajos y proyectos" {{ $producto->especificacion === 'Trabajos y proyectos' ? 'selected' : '' }}>Trabajos y proyectos</option>
                        <option value="Transporte" {{ $producto->especificacion === 'Transporte' ? 'selected' : '' }}>Transporte</option>
                        <option value="Otros servicios" {{ $producto->especificacion === 'Otros servicios' ? 'selected' : '' }}>Otros servicios</option>
                    </optgroup>
                </select>
                @error('especificacion')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">Descripción *</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="input-base" required>{{ $producto->descripcion }}</textarea>
                @error('descripcion')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Precio -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="precio" class="block text-sm font-semibold text-gray-700 mb-2">Precio ($) *</label>
                    <input type="number" id="precio" name="precio" class="input-base" step="1000" min="0" value="{{ $producto->precio }}" required>
                    @error('precio')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contacto -->
                <div>
                    <label for="contacto" class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp o contacto *</label>
                    <input type="text" id="contacto" name="contacto" class="input-base" value="{{ $producto->contacto }}" required>
                    @error('contacto')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Imagen -->
            <div>
                <label for="imagen" class="block text-sm font-semibold text-gray-700 mb-3">
                    Imagen del producto o servicio (opcional)
                </label>
                
                <!-- Imagen actual -->
                @if($producto->imagen)
                <div class="mb-4 p-4 bg-uts-50 rounded-lg">
                    <p class="text-sm text-gray-700 font-medium mb-2">Imagen actual:</p>
                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="h-32 w-32 object-cover rounded-lg">
                </div>
                @endif

                <div class="border-2 border-dashed border-uts-300 rounded-lg p-6 text-center cursor-pointer 
                            hover:border-uts-500 hover:bg-uts-50 transition-all" onclick="document.getElementById('imagen-input').click()">
                    <div id="preview-container" class="hidden mb-4">
                        <img id="preview-img" src="" alt="Vista previa" class="max-h-48 mx-auto rounded-lg">
                    </div>
                    <div id="upload-placeholder">
                        <div class="text-4xl mb-2">📷</div>
                        <p class="text-gray-600 font-medium">Haz clic para cambiar la imagen</p>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG o GIF (máx 2MB)</p>
                    </div>
                </div>
                <input type="file" id="imagen-input" name="imagen" accept="image/*" class="hidden" onchange="previewImagen(this)">
                @error('imagen')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-4 border-t">
                <a href="/mis-productos" class="btn-secondary flex-1 text-center">
                    ← Cancelar
                </a>
                <button type="submit" class="btn-primary flex-1">
                    ✓ Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function previewImagen(input) {
    const preview = document.getElementById('preview-container');
    const previewImg = document.getElementById('preview-img');
    const placeholder = document.getElementById('upload-placeholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@endsection
