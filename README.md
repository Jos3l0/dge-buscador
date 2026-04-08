# DGE Buscador - Plugin de Búsqueda AJAX para WordPress

Plugin de búsqueda y filtrado AJAX para el portal de Recursos Educativos de la Dirección General de Educación (DGE) de Argentina.

## Descripción

Plugin personalizado para WordPress que provee una interfaz de búsqueda y filtrado para recursos educativos. Utiliza AJAX para cargar resultados sin recargar la página.

## Características

- Búsqueda en tiempo real con debounce
- Filtros por múltiples taxonomías
- Ordenamiento (fecha, título, relevancia)
- Paginación AJAX
- Diseño responsive
- Compatible con cualquier Custom Post Type
- **Soporte para múltiples CPTs** (búsqueda unificada)

## Instalación

1. Descarga el plugin
2. Copia la carpeta `dge-buscador` a `wp-content/plugins/`
3. Activa el plugin desde el panel de WordPress

## Uso

### Shortcode básico

```php
[dge_buscador post_type="recurso" taxonomias="area_tematica,nivel_educativo,grado,tipo_recurso"]
```

### Múltiples CPTs

Para buscar en varios CPTs simultáneamente, séparalos con coma:

```php
[dge_buscador post_type="recurso,evento,curso" taxonomias="area_tematica,nivel_educativo"]
```

Esto buscará en los tres CPTs y mostrará los resultados combinados. Cada resultado incluirá el tipo de post (`post_type`) para distinguir su origen.

### Parámetros

| Parámetro | Descripción | Valor por defecto |
|-----------|-------------|-------------------|
| post_type | CPT a buscar | recurso |
| taxonomias | Taxonomías para filtros (separadas por coma) | vacío |
| per_page | Resultados por página | 12 |
| columns | Columnas en el grid (1-4) | 3 |
| show_search | Mostrar búsqueda de texto | true |
| show_filters | Mostrar filtros de taxonomía | true |
| show_sort | Mostrar opciones de ordenamiento | true |
| placeholder_search | Placeholder del input de búsqueda | "Search resources..." |

## Estructura de Archivos

```
dge-buscador/
├── assets/
│   ├── css/
│   │   └── frontend.css
│   └── js/
│       └── frontend.js
├── includes/
│   ├── class-search-query.php
│   └── class-taxonomy-helper.php
├── templates/
│   └── frontend.php
├── dge-buscador.php
└── readme.txt
```

## Taxonomías del CPT "recurso"

- **area_tematica**: Área temática (Arte, Ciencias Naturales, Geografía, etc.)
- **nivel_educativo**: Nivel educativo (Primario, Secundario)
- **grado**: Grado/año (Primero a Quinto)
- **tipo_recurso**: Tipo de recurso (PDF, Video, Audio, etc.)

## Desarrollo

### Requisitos

- WordPress 6.0+
- PHP 7.4+
- jQuery

### Protocolo de control de versiones

Este proyecto sigue el protocolo de control de versiones basado en el documento `protocolo_control_versiones.md` en la raíz del repositorio.

## Licencia

GPL v2 o posterior