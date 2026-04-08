# Protocolo de Control de Versiones - DGE Buscador

## Rama Principal

- **main**: Código en producción (estable)

## Ramas de Desarrollo

- **develop**: Código en desarrollo (próxima versión)
- **feature/***: Nuevas funcionalidades
- **bugfix/***: Corrección de errores
- **hotfix/***: Correcciones urgentes en producción

## Flujo de Trabajo

### 1. Iniciar nueva funcionalidad
```bash
git checkout -b feature/nombre-funcionalidad
```

### 2. Desarrollo
- Hacer commits frecuentes con mensajes descriptivos
- Mantener código limpio y documentado
- Seguir WordPress Coding Standards

### 3. Finalizar funcionalidad
```bash
git checkout develop
git merge --no-ff feature/nombre-funcionalidad
git push origin develop
git branch -d feature/nombre-funcionalidad
```

### 4. Release a producción
```bash
git checkout main
git merge --no-ff develop
git tag -a v1.0.0 -m "Versión 1.0.0"
git push origin main --tags
```

## Mensajes de Commit

### Formato
```
[tipo] Descripción corta

Descripción detallada si es necesario.

Tags: #tag1, #tag2
```

### Tipos permitidos
- **feat**: Nueva funcionalidad
- **fix**: Corrección de error
- **docs**: Documentación
- **style**: Estilos (CSS/SCSS)
- **refactor**: Refactorización
- **test**: Tests
- **chore**: Mantenimiento general

### Ejemplos
```
[feat] Agregar filtro por múltiples taxonomías

Implementa la capacidad de filtrar por múltiples valores
dentro de la misma taxonomía usando OR logic.

Tags: #filter, #ajax
```

```
[fix] Corregir bug en paginación AJAX

El parámetro 'page' no se-enviaba correctamente en las
solicitudes AJAX, causando que la paginación fallara.
```

## Versionado Semántico

Formato: `MAJOR.MINOR.PATCH`

- **MAJOR**: Cambios incompatibles en la API
- **MINOR**: Nueva funcionalidad compatible
- **PATCH**: Corrección de errores兼容

### Tags de versionado
```bash
# Crear tag
git tag -a v1.0.0 -m "Versión inicial"

# Push tags
git push origin --tags
```

## Estándares de Código

### PHP
- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/
- PSR-12 para PSRs no cubiertos por WP

### JavaScript
- ESLint con configuración WordPress
- Prettier para formato

### CSS/SCSS
- WordPress CSS Coding Standards

## Revisión de Código

1. Crear Pull Request a `develop`
2. Al menos 1 revisión approve
3. Todos los checks pas必需
4. Merge con squash (opcional)

## Rollback

### Revertir último release
```bash
git revert v1.0.0
git push origin main
```

### Hotfix rápido
```bash
git checkout -b hotfix/descripcion main
# Fix
git checkout main
git merge --no-ff hotfix/descripcion
git tag -a v1.0.1 -m "Hotfix"
git push origin main --tags
```

## Recursos

- Git Flow: https://nvie.com/posts/a-successful-git-branching-model/
- Conventional Commits: https://www.conventionalcommits.org/
- Semantic Versioning: https://semver.org/