@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="mb-8">
    <h1 class="section-header">Publica un producto o servicio</h1>
    <p class="section-subtitle">Comparte lo que tienes para ofrecer con la comunidad UTS</p>
</div>

<!-- Formulario -->
<div class="max-w-2xl mx-auto">
    <div class="card p-8 shadow-elevated">
        
        <form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Tipo de publicación -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Tipo de publicación *</label>
                    <div class="flex gap-3">
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="tipo" value="producto" checked 
                                   class="w-4 h-4 text-uts-600" onchange="actualizarEtiqueta()">
                            <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-uts-600">📦 Producto</span>
                        </label>
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="tipo" value="servicio" 
                                   class="w-4 h-4 text-uts-600" onchange="actualizarEtiqueta()">
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
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Calculus II, Nike Jordan Rojos" 
                       class="input-base" value="{{ old('nombre') }}" required>
                @error('nombre')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Especificación (Categoría) - DROPDOWN -->
            <div>
                <label for="especificacion" id="etiqueta-especificacion" class="block text-sm font-semibold text-gray-700 mb-2">
                    Categoría del producto *
                </label>
                <select id="especificacion" name="especificacion" class="input-base" required>
                    <option value="">-- Selecciona una categoría --</option>
                    <!-- Categorías de Productos -->
                    <optgroup label="📦 PRODUCTOS">
                        <option value="Libros y útiles escolares">Libros y útiles escolares</option>
                        <option value="Ropa y calzado">Ropa y calzado</option>
                        <option value="Electrónica">Electrónica</option>
                        <option value="Alimentos y bebidas">Alimentos y bebidas</option>
                        <option value="Deporte y recreación">Deporte y recreación</option>
                        <option value="Accesorios">Accesorios</option>
                    </optgroup>
                    <!-- Categorías de Servicios -->
                    <optgroup label="🎓 SERVICIOS">
                        <option value="Tutoría académica">Tutoría académica</option>
                        <option value="Asesoría profesional">Asesoría profesional</option>
                        <option value="Clases de idiomas">Clases de idiomas</option>
                        <option value="Trabajos y proyectos">Trabajos y proyectos</option>
                        <option value="Transporte">Transporte</option>
                        <option value="Otros servicios">Otros servicios</option>
                    </optgroup>
                </select>
                @error('especificacion')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">Descripción *</label>
                <textarea id="descripcion" name="descripcion" placeholder="Describe en detalle qué ofreces..." 
                          rows="4" class="input-base" required>{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Precio -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="precio" class="block text-sm font-semibold text-gray-700 mb-2">Precio ($) *</label>
                    <input type="number" id="precio" name="precio" placeholder="15000" 
                           class="input-base" step="1000" min="0" value="{{ old('precio') }}" required>
                    @error('precio')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contacto -->
                <div>
                    <label for="contacto" class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp o contacto *</label>
                    <input type="text" id="contacto" name="contacto" placeholder="300 123 4567" 
                           class="input-base" value="{{ old('contacto') }}" required>
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
                <div class="border-2 border-dashed border-uts-300 rounded-lg p-6 text-center cursor-pointer 
                            hover:border-uts-500 hover:bg-uts-50 transition-all" onclick="document.getElementById('imagen-input').click()">
                    <div id="preview-container" class="hidden mb-4">
                        <img id="preview-img" src="" alt="Vista previa" class="max-h-48 mx-auto rounded-lg">
                    </div>
                    <div id="upload-placeholder">
                        <div class="text-4xl mb-2">📷</div>
                        <p class="text-gray-600 font-medium">Haz clic para subir una imagen</p>
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
                <a href="{{ route('home') }}" class="btn-secondary flex-1 text-center">
                    ← Cancelar
                </a>
                <button type="submit" class="btn-primary flex-1">
                    ✓ Publicar
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function actualizarEtiqueta() {
    const tipo = document.querySelector('input[name="tipo"]:checked').value;
    const etiqueta = document.getElementById('etiqueta-especificacion');
    if (tipo === 'servicio') {
        etiqueta.textContent = 'Categoría de servicio *';
    } else {
        etiqueta.textContent = 'Categoría del producto *';
    }
}

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
