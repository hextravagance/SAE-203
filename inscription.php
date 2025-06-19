<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Inscription - Brickothèque</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            background: url('./image/car-7947765.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            font-family: Arial, sans-serif;
            color: #fff;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 30px;
        }

        .container-form {
            padding-top: 40px;
            display: flex;
            justify-content: flex-start;
            padding-left: 5vw;
            box-sizing: border-box;
            min-height: 80vh;
        }

        .form-wrapper {
            max-width: 400px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"], 
        input[type="password"], 
        input[type="email"] {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: none;
            outline: none;
            font-size: 1em;
        }

        button {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            margin-top: 15px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #b52a39;
        }

        a {
            color: #fff;
            text-decoration: underline;
        }

        p {
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .container-form {
                justify-content: center;
                padding-left: 0;
            }
            .form-wrapper {
                max-width: 90vw;
            }
        }
    </style>
</head>
<body>

<div class="container-form">
    <div class="form-wrapper glass-card animate__animated animate__fadeInLeft">
        <h2>Formulaire d'inscription</h2>

        <form method="POST" action="verif_inscription.php" novalidate>
            <label for="login">Login :</label>
            <input type="text" id="login" name="login" required />

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required />

            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input type="password" id="confirm_password" name="confirm_password" required />

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required />

            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà inscrit ? <a href="authentification.php">Se connecter</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
