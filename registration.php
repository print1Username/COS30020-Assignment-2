<?php
session_start();

$errors = $_SESSION["registration_errors"] ?? [];
$old = $_SESSION["registration_old"] ?? [];

unset($_SESSION["registration_errors"]);
unset($_SESSION["registration_old"]);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Sign Up</title>

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

	<main class="signup-page">

		<form
			class="signup-card"
			action="process_registration.php"
			method="POST"
			novalidate
		>

			<!-- Back -->
			<button
				type="button"
				class="back-button"
				onclick="history.back()"
			>
				<i class="bi bi-arrow-left"></i>
				<span>Back</span>
			</button>

			<h1 class="signup-title">Sign Up</h1>

			<!-- Error messages -->
			<?php if (!empty($errors)): ?>
				<div class="alert alert-danger signup-error" role="alert">
					<ul class="mb-0 ps-3">
						<?php foreach ($errors as $error): ?>
							<li><?= htmlspecialchars($error) ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="signup-content">

				<!-- Left -->
				<div class="signup-column-left">

					<div class="signup-row">

						<div class="signup-column signup-field">
							<label for="firstName" class="form-label">
								First Name
							</label>

							<input
								type="text"
								class="form-control"
								id="firstName"
								name="first_name"
								value="<?= htmlspecialchars($old["first_name"] ?? "") ?>"
								required
							>
						</div>

						<div class="signup-column signup-field">
							<label for="lastName" class="form-label">
								Last Name
							</label>

							<input
								type="text"
								class="form-control"
								id="lastName"
								name="last_name"
								value="<?= htmlspecialchars($old["last_name"] ?? "") ?>"
								required
							>
						</div>

					</div>

					<div class="signup-row">

						<div class="signup-column signup-field">
							<label for="dob" class="form-label">
								Date of Birth
							</label>

							<input
								type="date"
								class="form-control"
								id="dob"
								name="dob"
								value="<?= htmlspecialchars($old["dob"] ?? "") ?>"
								required
							>
						</div>

						<div class="signup-column signup-field">
							<label for="gender" class="form-label">
								Gender
							</label>

							<select
								class="form-select"
								id="gender"
								name="gender"
								required
							>
								<option
									value="Female"
									<?= (($old["gender"] ?? "Female") === "Female") ? "selected" : "" ?>
								>
									Female
								</option>

								<option
									value="Male"
									<?= (($old["gender"] ?? "") === "Male") ? "selected" : "" ?>
								>
									Male
								</option>
							</select>
						</div>

					</div>

					<div class="signup-field">
						<label for="hometown" class="form-label">
							Hometown
						</label>

						<input
							type="text"
							class="form-control"
							id="hometown"
							name="hometown"
							value="<?= htmlspecialchars($old["hometown"] ?? "") ?>"
							required
						>
					</div>
				</div>


				<!-- Right -->
				<div class="signup-column-right">

					<div class="signup-field">
						<label for="email" class="form-label">
							Email
						</label>

						<input
							type="text"
							class="form-control"
							id="email"
							name="email"
							value="<?= htmlspecialchars($old["email"] ?? "") ?>"
							required
						>
					</div>

					<div class="signup-field">
						<label for="password" class="form-label">
							Password
						</label>

						<div class="password-wrapper">

							<input
								type="password"
								class="form-control"
								id="password"
								name="password"
								required
							>

							<button
								type="button"
								class="password-toggle"
								id="passwordToggle"
							>
								<i class="bi bi-eye" id="passwordIcon"></i>
							</button>

						</div>
					</div>

					<div class="signup-field">
						<label for="confirmPassword" class="form-label">
							Confirm Password
						</label>

						<div class="password-wrapper">

							<input
								type="password"
								class="form-control"
								id="confirmPassword"
								name="confirm_password"
								required
							>

							<button
								type="button"
								class="password-toggle"
								id="confirmPasswordToggle"
							>
								<i
									class="bi bi-eye"
									id="confirmPasswordIcon"
								></i>
							</button>

						</div>
					</div>

				</div>

			</div>

			<!-- Log In -->
			<div class="signup-footer">
				Already have account?
				<a href="login.php">Log In</a>
			</div>

			<!-- Sign Up -->
			<button
				type="submit"
				class="signup-button"
			>
				Sign Up
			</button>

		</form>

	</main>

	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwxH9j09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>

	<!-- Navbar JavaScript -->
	<script src="components/navbar.js"></script>

	<script>
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("confirmPassword");

        const passwordToggle = document.getElementById("passwordToggle");
        const confirmPasswordToggle = document.getElementById("confirmPasswordToggle");

        const passwordIcon = document.getElementById("passwordIcon");
        const confirmPasswordIcon = document.getElementById("confirmPasswordIcon");

        function showPasswords() {
            passwordInput.type = "text";
            confirmPasswordInput.type = "text";

            passwordIcon.classList.remove("bi-eye");
            passwordIcon.classList.add("bi-eye-slash");

            confirmPasswordIcon.classList.remove("bi-eye");
            confirmPasswordIcon.classList.add("bi-eye-slash");
        }

        function hidePasswords() {
            passwordInput.type = "password";
            confirmPasswordInput.type = "password";

            passwordIcon.classList.remove("bi-eye-slash");
            passwordIcon.classList.add("bi-eye");

            confirmPasswordIcon.classList.remove("bi-eye-slash");
            confirmPasswordIcon.classList.add("bi-eye");
        }

        passwordToggle.addEventListener("mousedown", showPasswords);
        passwordToggle.addEventListener("mouseup", hidePasswords);
        passwordToggle.addEventListener("mouseleave", hidePasswords);

        confirmPasswordToggle.addEventListener("mousedown", showPasswords);
        confirmPasswordToggle.addEventListener("mouseup", hidePasswords);
        confirmPasswordToggle.addEventListener("mouseleave", hidePasswords);
	</script>

	</body>
</html>