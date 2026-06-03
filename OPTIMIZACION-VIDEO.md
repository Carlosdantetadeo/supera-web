# Optimización del video del hero

La web carga el video **solo cuando el hero es visible** y la conexión lo permite.

## Archivos recomendados (subir al hosting junto a `index.html`)

| Archivo | Uso |
|---------|-----|
| `hero-720.mp4` | Móvil (prioridad) |
| `hero-1080.mp4` | Escritorio (prioridad) |
| `13633626-uhd_3840_2160_30fps.mp4` | Respaldo si no existen los anteriores |

## Crear versiones livianas (FFmpeg)

```bash
ffmpeg -i 13633626-uhd_3840_2160_30fps.mp4 -vf "scale=-2:720" -c:v libx264 -crf 28 -preset slow -an -movflags +faststart hero-720.mp4

ffmpeg -i 13633626-uhd_3840_2160_30fps.mp4 -vf "scale=-2:1080" -c:v libx264 -crf 26 -preset slow -an -movflags +faststart hero-1080.mp4
```

Sin esos archivos, se usará automáticamente el MP4 4K como respaldo.
