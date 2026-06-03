<?php
session_start();

require_once __DIR__ . '/load-env.php';
supera_load_env(__DIR__ . '/.env.local');

$PASSWORD = getenv('SUPERA_ADMIN_PASSWORD') ?: '';
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}
if ($PASSWORD === '' || !is_string($PASSWORD)) {
    http_response_code(500);
    exit('Falta configuración: crea .env.local con SUPERA_ADMIN_PASSWORD (usa .env.example como plantilla).');
}

$UPLOAD_DIR = __DIR__ . '/catalogos/';

if (!file_exists($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === $PASSWORD) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = 'Contraseña incorrecta. Vuelve a intentarlo.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$message = '';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file']) && isset($_POST['categoria'])) {
        $categoria = $_POST['categoria'];
        $categorias_validas = ['preventa', 'futbol', 'basket', 'mujer', 'hombre'];
        if (in_array($categoria, $categorias_validas, true)) {
            $file = $_FILES['pdf_file'];
            if ($file['type'] === 'application/pdf' || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'pdf') {
                $destination = $UPLOAD_DIR . $categoria . '.pdf';
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $message = "<div class='alert success' role='status' aria-live='polite'>Catálogo de " . htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') . " actualizado correctamente.</div>";
                } else {
                    $message = "<div class='alert error' role='alert' aria-live='polite'>Error al subir el archivo. Verifica los permisos de escritura del hosting.</div>";
                }
            } else {
                $message = "<div class='alert error' role='alert' aria-live='polite'>El archivo debe ser formato PDF.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin - Catálogos SUPERA</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --red: #e8392c;
    --red-dark: #c42d22;
    --black: #070707;
    --surface: #181818;
    --border: #2a2a2a;
    --white: #fff;
    --muted: #aaa;
    --ff-display: 'Barlow Condensed', sans-serif;
    --ff-body: 'DM Sans', system-ui, sans-serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { color-scheme: dark; }
  body {
    font-family: var(--ff-body);
    background: var(--black);
    color: var(--white);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    padding: 2rem;
    touch-action: manipulation;
  }
  .page-head {
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
    width: 100%;
    max-width: 600px;
  }
  .page-head::after {
    content: '';
    display: block;
    width: 48px;
    height: 2px;
    background: var(--red);
    margin: 1rem auto 0;
  }
  h1 {
    font-family: var(--ff-display);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-size: clamp(1.8rem, 5vw, 2.4rem);
  }
  h1 em { color: var(--red); font-style: normal; }
  .subtitle { color: var(--muted); font-size: 0.85rem; margin-top: 0.5rem; letter-spacing: 0.06em; text-transform: uppercase; }
  .container {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 2rem;
    border-radius: 8px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    position: relative;
    overflow: hidden;
  }
  .container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(to right, transparent, var(--red), transparent);
  }
  .form-group { margin-bottom: 1.5rem; }
  label {
    display: block;
    font-family: var(--ff-display);
    font-size: 0.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.12em;
  }
  input[type="password"],
  input[type="file"] {
    width: 100%;
    padding: 0.8rem;
    background: var(--black);
    border: 1px solid var(--border);
    color: var(--white);
    border-radius: 4px;
    font-family: inherit;
    font-size: 1rem;
  }
  input[type="password"]:focus-visible,
  input[type="file"]:focus-visible {
    outline: 2px solid var(--red);
    outline-offset: 2px;
    border-color: var(--red);
  }
  .btn {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    background: var(--red);
    color: var(--white);
    border: none;
    border-radius: 4px;
    font-family: var(--ff-display);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    text-decoration: none;
    text-align: center;
    font-size: 0.85rem;
  }
  .btn:hover { background: var(--red-dark); }
  .btn:focus-visible { outline: 2px solid var(--white); outline-offset: 3px; }
  .upload-card {
    border: 1px dashed var(--border);
    padding: 1.5rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    transition: border-color 0.2s, background 0.2s;
    background: rgba(0, 0, 0, 0.2);
  }
  .upload-card:hover { border-color: var(--red); background: rgba(232, 57, 44, 0.05); }
  .upload-card h3 {
    font-family: var(--ff-display);
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  input[type="file"] { padding: 1rem 0; color: var(--muted); cursor: pointer; }
  input[type="file"]::file-selector-button {
    background: var(--border);
    color: var(--white);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 1rem;
    transition: background 0.2s;
    font-family: var(--ff-display);
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }
  input[type="file"]::file-selector-button:hover { background: var(--red); }
  .alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-weight: 600;
    font-size: 0.9rem;
  }
  .success { background: rgba(37, 211, 102, 0.1); border: 1px solid #25d366; color: #25d366; }
  .error { background: rgba(232, 57, 44, 0.1); border: 1px solid var(--red); color: var(--red); }
  .intro { color: var(--muted); font-size: 0.9rem; margin-bottom: 2rem; line-height: 1.5; }
  .logout {
    display: block;
    text-align: center;
    margin-top: 2rem;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.8rem;
  }
  .logout:hover { color: var(--white); }
  .logout:focus-visible { outline: 2px solid var(--red); outline-offset: 3px; }
  .visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
  @media (prefers-reduced-motion: reduce) {
    .btn, .upload-card, input[type="file"]::file-selector-button { transition: none; }
  }
</style>
</head>
<body>

<div class="page-head">
  <h1>Panel <em>Catálogos</em></h1>
  <p class="subtitle">SUPERA · Move is Life</p>
</div>

<div class="container">
    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>

        <?php if (isset($error)): ?>
        <div class="alert error" role="alert" aria-live="polite"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="admin-password">Contraseña de acceso</label>
                <input type="password" id="admin-password" name="password" autocomplete="current-password" spellcheck="false" placeholder="Tu contraseña…" required>
            </div>
            <button type="submit" class="btn" style="width: 100%">Entrar al panel</button>
        </form>

    <?php else: ?>

        <?php if ($message) echo $message; ?>
        <p class="intro">
            Los catálogos públicos están en Canva (configurados en <code>index.html</code>). Usa este panel solo si necesitas subir PDF de respaldo a la carpeta <code>catalogos/</code>.
        </p>

        <?php
        $categorias = [
            'preventa' => 'Preventa',
            'futbol'   => 'Fútbol',
            'basket'   => 'Basket',
            'mujer'    => 'Mujer',
            'hombre'   => 'Hombre',
        ];

        foreach ($categorias as $key => $name):
            $fileId = 'pdf-' . $key;
        ?>
        <div class="upload-card">
            <h3><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h3>
            <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
                <div style="flex: 1; min-width: 200px;">
                    <label class="visually-hidden" for="<?php echo $fileId; ?>">PDF <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="file" id="<?php echo $fileId; ?>" name="pdf_file" accept=".pdf,application/pdf" required>
                </div>
                <button type="submit" class="btn">Actualizar PDF</button>
            </form>
        </div>
        <?php endforeach; ?>

        <a href="?logout=1" class="logout">Cerrar sesión</a>

    <?php endif; ?>
</div>

</body>
</html>
