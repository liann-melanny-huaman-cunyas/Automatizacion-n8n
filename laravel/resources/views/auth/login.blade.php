<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CEFIC | Acceso</title>

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f7fb;
}
.login-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1.15fr .85fr;
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes pulseBg {
    0% { transform: scale(1); }
    100% { transform: scale(1.05); }
}

.hero {
    background: #0b1324;
    color: white;
    padding: 70px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute;
    top: -20%; left: -20%; width: 140%; height: 140%;
    background: radial-gradient(circle, rgba(43,76,126,0.3) 0%, transparent 60%);
    animation: pulseBg 8s infinite alternate ease-in-out;
    z-index: 0;
}
.hero-content {
    position: relative;
    z-index: 1;
    animation: fadeIn 0.8s ease-out;
}
.logo-img {
    max-width: 280px;
    margin-bottom: 25px;
    background: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.hero h1 {
    max-width: 650px;
    font-size: 40px;
    line-height: 1.2;
    margin: 10px 0 20px;
}
.hero p {
    max-width: 570px;
    color: #aeb9cc;
    line-height: 1.7;
    font-size: 17px;
}

.login-area {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 50px;
    animation: fadeIn 0.8s ease-out 0.2s both;
}
.login-card {
    width: 100%;
    max-width: 440px;
    background: white;
    padding: 45px;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.04);
}
.login-card h2 {
    font-size: 32px;
    margin: 0 0 8px 0;
    color: #111827;
}
.subtitle {
    color: #6b7280;
    margin-bottom: 35px;
}

label {
    display: block;
    font-weight: 600;
    margin: 20px 0 8px;
    color: #374151;
}
.input-group {
    position: relative;
}
input {
    width: 100%;
    padding: 14px;
    border: 1px solid #d5dbe5;
    border-radius: 10px;
    font-size: 15px;
    background: #fdfdfd;
    transition: all 0.3s ease;
}
input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
    outline: none;
    background: white;
}

.toggle-btn {
    position: absolute;
    right: 14px;
    top: 14px;
    background: none;
    border: none;
    color: #6b7280;
    font-weight: 600;
    cursor: pointer;
    font-size: 13px;
    padding: 0;
}
.toggle-btn:hover {
    color: #111827;
}

.btn-submit {
    width: 100%;
    margin-top: 30px;
    padding: 15px;
    border: 0;
    border-radius: 10px;
    background: #111827;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-submit:hover {
    background: #1f2937;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(17,24,39,0.2);
}
.btn-submit:active {
    transform: translateY(0);
}

.error {
    background: #fff1f2;
    color: #be123c;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #be123c;
    font-weight: 500;
}

/* Responsividad */
@media (max-width: 960px) {
    .login-page {
        grid-template-columns: 1fr;
    }
    .hero {
        padding: 60px 30px;
        text-align: center;
    }
    .hero h1 { font-size: 32px; margin: 20px auto; }
    .hero p { margin: 0 auto; }
    .logo-img { margin: 0 auto 15px; }
    .login-card { padding: 30px; }
}
</style>
</head>

<body>

<div class="login-page">

    <section class="hero">
        <div class="hero-content">
            <!-- Logo extraído del archivo proporcionado -->
            <img src="{{ asset('img/cefic-logo.png') }}" alt="CEFIC Logo" class="logo-img">
            <h1>Centro Peruano de Formación e Investigación Continua</h1>
            
            <p>
                Institución educativa especializada en formación continua sobre Administración Pública, Gestión Corporativa y Derecho. Realizamos Seminarios, Cursos y Diplomados, así como difundimos Pasantías y Maestrías de universidades extranjeras.
            </p>
        </div>
    </section>

    <section class="login-area">
        <div class="login-card">

            <h2>Bienvenido</h2>
            <div class="subtitle">Ingresa a tu panel administrativo.</div>

            @if($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}">
                @csrf

                <label>Correo electrónico</label>
                <input 
                    type="email" 
                    name="correo" 
                    value="{{ old('correo') }}" 
                    placeholder="admin@cefic.edu.pe" 
                    required
                >

                <label>Contraseña</label>
                <div class="input-group">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword()">Mostrar</button>
                </div>

                <button type="submit" class="btn-submit">
                    Iniciar sesión
                </button>
            </form>

        </div>
    </section>

</div>

<script>
    // Función dinámica para alternar la visibilidad de la contraseña
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.querySelector('.toggle-btn');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.textContent = 'Ocultar';
        } else {
            passwordInput.type = 'password';
            toggleButton.textContent = 'Mostrar';
        }
    }
</script>

</body>
</html>