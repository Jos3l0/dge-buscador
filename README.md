# DGE Buscador - Plugin de Búsqueda AJAX para WordPress

Plugin de búsqueda y filtrado AJAX genérico para WordPress. Compatible con cualquier Custom Post Type.

## Descripción

Plugin personalizado para WordPress que provee una interfaz de búsqueda y filtrado mediante AJAX sin recargar la página. Diseñado para ser flexible y adaptarse a cualquier tipo de contenido.

## Características

- Búsqueda en tiempo real con debounce
- Filtros por múltiples taxonomías (AND/OR logic)
- Ordenamiento (fecha, título, relevancia, random)
- Paginación AJAX
- Diseño responsive
- Compatible con cualquier Custom Post Type
- Soporte para múltiples CPTs (búsqueda unificada)
- Compatible con WordPress 6.0+

## Instalación

1. Descarga el plugin
2. Copia la carpeta `dge-buscador` a `wp-content/plugins/`
3. Activa el plugin desde el panel de WordPress

## Uso

### Shortcode básico

```php
[dge_buscador post_type="mi_cpt" taxonomias="taxonomia1,taxonomia2"]
```

### Múltiples CPTs

Para buscar en varios CPTs simultáneamente, séparalos con coma:

```php
[dge_buscador post_type="post,producto,evento" taxonomias="categoria,etiqueta"]
```

Esto buscará en los tres CPTs y mostrará los resultados combinados.

### Parámetros

| Parámetro | Descripción | Valor por defecto |
|-----------|-------------|-------------------|
| post_type | CPT a buscar (string o comma-separated) | post |
| taxonomias | Taxonomías para filtros (separadas por coma) | vacío |
| per_page | Resultados por página | 12 |
| columns | Columnas en el grid (1-4) | 3 |
| show_search | Mostrar búsqueda de texto | true |
| show_filters | Mostrar filtros de taxonomía | true |
| show_sort | Mostrar opciones de ordenamiento | true |
| placeholder_search | Placeholder del input de búsqueda | "Search..." |

## Estructura de Archivos

```
dge-buscador/
├── assets/
│   ├── css/
│   │   └── frontend.css
│   └── js/
│       └── frontend.js
├── includes/
│   ├── class-ajax-handler.php
│   ├── class-search-query.php
│   ├── class-shortcode.php
│   └── class-taxonomy-helper.php
├── dge-buscador.php
├── README.md
└── readme.txt
```

## Desarrollo

### Requisitos

- WordPress 6.0+
- PHP 7.4+
- jQuery

### Protocolo de control de versiones

Este proyecto sigue el protocolo de control de versiones basado en el documento `protocolo_control_versiones.md` en la raíz del repositorio.

## Licencia

GPL v2 o posterior