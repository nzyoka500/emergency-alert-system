<?php
/*
|--------------------------------------------------------------------------
| create-user.php
|--------------------------------------------------------------------------
| Handles creation of new users by Admin
| Called via AJAX from users.php
| Returns JSON response
*/

require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Authentication & Authorization
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required.'
    ]);
    exit;
}

if ($_SESSION['role_id'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized action.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Process POST Request
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Collect Form Data
    |--------------------------------------------------------------------------
    */

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role_id   = (int) ($_POST['role-id'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($full_name === '' || $email === '' || $phone === '' || $password === '') {

        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format.'
        ]);
        exit;
    }

    if (strlen($password) < 6) {

        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters.'
        ]);
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    */

    $pdo = getPDO();


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Email
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {

        echo json_encode([
            'success' => false,
            'message' => 'Email already exists.'
        ]);
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Hash Password
    |--------------------------------------------------------------------------
    */

    $password_hash = password_hash($password, PASSWORD_DEFAULT);


    /*
    |--------------------------------------------------------------------------
    | Insert User
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO users 
        (full_name, email, phone, password_hash, role_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $created = $stmt->execute([
        $full_name,
        $email,
        $phone,
        $password_hash,
        $role_id
    ]);


    if ($created) {

        echo json_encode([
            'success' => true,
            'message' => 'User created successfully.',
            'redirect' => 'users.php'
        ]);
    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Failed to create user.'
        ]);
    }
} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unexpected error occurred.',
        'error' => $e->getMessage()
    ]);
}

?>





<div class="card border-0 shadow-lg rounded-3 mb-4">
    <div class="card-body">
        <h5 class="fw-semibold mb-3">All Users</h5>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- User dropdown menu for actions, view, edit and delete -->

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $user['id']; ?>">
                                            <li><a class="dropdown-item" href="view-user.php?id=<?php echo $user['id']; ?>">View</a></li>
                                            <li><a class="dropdown-item disabled" href="edit-user.php?id=<?php echo $user['id']; ?>">Edit</a></li>
                                            <?php if ($user['id'] != $_SESSION['user_id']): // Prevent self-deletion 
                                            ?>
                                                <li><a class="dropdown-item disabled" href="delete-user.php?id=<?php echo $user['id']; ?>">Delete</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>



                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>