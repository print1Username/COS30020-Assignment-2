<?php
require_once __DIR__."/login_db.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    <link rel="icon" href="public/logo.ico" sizes="any">
    <link rel="icon" type="image/png" href="public/logo.png">

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Login</title>

		<!-- Bootstrap 5.3.8 -->
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous"
		>

		<!-- Bootstrap Icons -->
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>

		<!-- Custom Navbar CSS -->
		<link
			rel="stylesheet"
			href="style/navbar.css"
		>

		<link rel="stylesheet" href="style/login.css">
	</head>


	<body>
	<!-- Navbar -->
	<div id="navbar-container"></div>

	<!-- Login Page -->
	<main class="login-page">

		<div class="login-card">

			<!-- Back -->
			<button
				type="button"
				class="back-button"
				onclick="history.back()"
			>
				<i class="bi bi-arrow-left"></i>
				<span>Back</span>
			</button>


			<!-- Login Title -->
			<div class="login-heading">
				<h1>Login</h1>
			</div>


			<!-- Error -->
			<?php if ($error !== ""): ?>

				<div
					class="alert alert-danger login-alert"
					role="alert"
				>
					<i class="bi bi-exclamation-circle me-2"></i>

					<?php
					echo htmlspecialchars($error);
					?>
				</div>

			<?php endif; ?>


			<!-- Login Form -->
			<form
				action="login.php"
				method="POST"
			>

				<!-- Email -->
				<div class="mb-3">

					<label
						for="email"
						class="form-label"
					>
						Email
					</label>

					<div class="input-group login-input-group">

                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>

						<input
							type="email"
							class="form-control"
							id="email"
							name="email"
							placeholder="Enter your email"
							value="<?php echo htmlspecialchars($email); ?>"
							required
						>

					</div>

				</div>


				<!-- Password -->
				<div class="mb-3">

					<label
						for="password"
						class="form-label"
					>
						Password
					</label>

					<div class="input-group login-input-group">

                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>

						<input
							type="password"
							class="form-control"
							id="password"
							name="password"
							placeholder="Enter your password"
							required
						>

						<button
							type="button"
							class="password-toggle"
							id="passwordToggle"
							aria-label="Show password"
						>
							<i
								class="bi bi-eye"
								id="passwordIcon"
							></i>
						</button>

					</div>

				</div>


				<!-- Sign Up -->
				<div class="signup-text">

					<span>Doesn't have account?</span>

					<a href="registration.php">
						Sign Up
					</a>

				</div>


				<!-- Login Button -->
				<button
					type="submit"
					class="btn login-button w-100"
				>
					Login
				</button>

			</form>

		</div>

	</main>


	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>


	<!-- Compiled Navbar JavaScript -->
	<script src="components/navbar.js?v=<?= filemtime(__DIR__."/components/navbar.js") ?>"></script>


	<!-- Password Show / Hide -->
	<script>

        const passwordInput =
            document.getElementById("password");

        const passwordToggle =
            document.getElementById("passwordToggle");

        const passwordIcon =
            document.getElementById("passwordIcon");


        // Mouse button pressed
        passwordToggle.addEventListener(
            "mousedown",
            function () {

                passwordInput.type = "text";

                passwordIcon.classList.remove("bi-eye");

                passwordIcon.classList.add("bi-eye-slash");

            }
        );


        // Mouse button released
        passwordToggle.addEventListener(
            "mouseup",
            function () {

                passwordInput.type = "password";

                passwordIcon.classList.remove("bi-eye-slash");

                passwordIcon.classList.add("bi-eye");

            }
        );


        // Prevent password from remaining visible
        // if the mouse leaves the eye button
        passwordToggle.addEventListener(
            "mouseleave",
            function () {

                passwordInput.type = "password";

                passwordIcon.classList.remove("bi-eye-slash");

                passwordIcon.classList.add("bi-eye");

            }
        );

	</script>

	</body>
</html>
