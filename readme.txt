=== DGE Buscador ===
Contributors: Equipo DGE
Tags: search, ajax, filter, cpt, taxonomy, recursos
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 6.6
Stable tag: 1.0.0
License: GPL v2 or later

Buscador AJAX con filtros facetados para CPT recursos y futuros CPTs del Portal Educativo Mendoza.

== Description ==

DGE Buscador es un plugin personalizado para el Portal Educativo Mendoza que proporciona:

* Búsqueda en tiempo real AJAX
* Filtros facetados por taxonomías
* Grid de resultados responsivo
* Paginación AJAX
* Ordenamiento de resultados

== Installation ==

1. Sube la carpeta `dge-buscador` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el menú Plugins de WordPress
3. Usa el shortcode `[dge_buscador]` en tus páginas

== Usage ==

Shortcode básico:
[dge_buscador]

Con atributos personalizados:
[dge_buscador post_type="recurso" taxonomias="area_tematica,nivel_educativo" per_page="12" columns="3"]

== Changelog ==

= 1.0.0 =
* Initial release
* Búsqueda AJAX
* Filtros por taxonomías
* Grid de resultados
* Paginación
* Ordenamiento

== Credits ==

Desarrollado por el Equipo Portal Educativo Gob. de Mendoza
